# AI School — Quality Assurance Report
# Report 16: Security and Branch Isolation Report

**Date:** 2026-10-20  
**Environment:** Staging / Isolated SQLite (`:memory:` & `database/database.sqlite`)  
**Test Suite Reference:** `tests/Feature/SecurityAuthorizationMatrixTest.php`, `tests/Feature/BranchTenantIsolationTest.php`  
**Author:** Security and Authorization Engineer & Principal Laravel Architect  

---

## 1. Executive Summary

This report documents the security audit and penetration-style authorization testing across the AI School platform. The system was probed against horizontal privilege escalation (cross-tenant, cross-branch), vertical privilege escalation (student/parent to staff actions, regular staff to financial authorization), and backend direct request tampering.

Over **40 negative authorization tests** were executed against API endpoints, Livewire component actions, and controller methods. The application demonstrated robust defense-in-depth: authorization logic is strictly enforced at the database policy and service layer, ensuring that hidden UI buttons are backed by uncompromising backend gates.

---

## 2. Multi-Role Matrix Across Critical Operations

Each sensitive business operation was tested across 7 distinct personas:
1. Authorized Persona (with explicit permission).
2. Persona with related but insufficient permission.
3. Cross-Branch User (authorized in Branch 1, accessing Branch 2 resource).
4. Tenant User (accessing distinct tenant data).
5. Student Persona.
6. Parent Persona.
7. Unrelated Instructor Persona.

| Sensitive Operation | Tested Permission | Authorized User | Insufficient Perm User | Cross-Branch User | Student / Parent | Unrelated Instructor | Security Result |
|---|---|:---:|:---:|:---:|:---:|:---:|:---:|
| **Student Group Transfer** | `transfer_student_group` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **Enrollment Cancellation** | `cancel_enrollments` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **Execute Refund** | `execute_refunds` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **Payment Collection** | `collect_payments` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **Disburse Payroll** | `disburse_payroll` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **Manage Expenses** | `manage_expenses` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **Vault Transfer** | `transfer_vault_funds` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **View Vault Balance** | `view_vault_balance` | **ALLOW** (200) | **DENY** (Masked) | **DENY** (Masked) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **View Vault Statement** | `view_vault_statement` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **Save Daily Snapshot** | `save_daily_report_snapshot` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |
| **Export Reports** | `export_reports` | **ALLOW** (200) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **DENY** (403) | **SECURE** |

---

## 3. Detailed Negative Security Scenarios

### 3.1 Permission Decoupling Verification
- **Scenario:** A registration employee possessed the `edit_enrollments` permission but lacked `transfer_student_group`.
- **Test:** The employee sent a direct Livewire update payload modifying `group_id` from Group A to Group B on enrollment `ENR-SYNTH-100`.
- **Observed Outcome:** The request was blocked by `EnrollmentPolicy::transferGroup()`, throwing `AuthorizationException: User lacks transfer_student_group permission`. The database record remained pointed to Group A.
- **Verdict:** **PASS**. Granular permissions cannot be bypassed via general edit privileges.

### 3.2 Cancellation vs. Refund Decoupling
- **Scenario:** An employee held `cancel_enrollments` but NOT `execute_refunds`.
- **Test:** Submitted a cancellation request with `refund_amount = 200.00 EGP` and `vault_id = VLT-01`.
- **Observed Outcome:** The cancellation service verified refund permissions independently: `abort_unless(auth()->user()->can('execute_refunds'), 403)`.
- **Database Impact:** The entire transaction was rolled back atomically. Enrollment was NOT canceled and zero money was disbursed.
- **Verdict:** **PASS**.

### 3.3 Strict Branch Isolation & Anti-Cross-Branch Enforcement
- **Cross-Branch Vault Access:**
  - Branch 1 Accountant attempted to disburse an expense from `VLT-04` (located in Branch 2).
  - Outcome: Database query constrained by user's assigned branch (`BranchScope`). Vault was not found or yielded a `403 Forbidden` branch mismatch.
- **Cross-Branch Group Transfer Rule:**
  - An operator attempted to transfer a student from Branch 1 Python Group A to Branch 2 Python Group B.
  - Outcome: The transfer validator rejected the operation: `ValidationException: Cross-branch group transfers are strictly forbidden`.
  - Super Admin Attempt: Super Admin attempted the same action. The system rejected the transfer with identical error, confirming that business rules are enforced consistently even for super-users.

### 3.4 Livewire Direct Request Tampering
- **Test:** A student authenticated in the `/student` panel captured their browser session and issued a raw Livewire POST request calling `markAttendance` and `updateGrade` on the instructor component.
- **Observed Outcome:** Livewire component lifecycle checked `authorize('update', $session)`. The call terminated immediately with an HTTP 403 status code and logged a security alert.

### 3.5 Session Invalidation on Permission Revocation (RISK-009)
- **Scenario:** An active accountant session was executing financial operations. The Super Admin revoked the `collect_payments` permission from the user's role.
- **Test:** On the very next click (without requiring logout), the user attempted to collect an installment.
- **Observed Outcome:** Laravel's dynamic gate check re-evaluated the fresh Spatie permission cache. The request was blocked immediately with `403 Forbidden`.

---

## 4. Multi-Tenant Data Leakage Audit

Tenancy boundaries were verified across all database queries:
- Every tenant model applies a global `TenantScope`.
- Direct URL ID manipulation (e.g., requesting `/admin/students/999` where student 999 belongs to another tenant) yields a `404 Not Found` rather than leaking record existence or metadata.
- File storage URLs use scoped hash directories preventing directory traversal or unauthorized file downloads.

---

## 5. Audit Conclusion

The application exhibits exceptional security posture:
- Zero horizontal or vertical privilege escalation vectors were discovered.
- Financial operations require explicit, decoupled permissions with strict branch boundaries.
- Cross-branch transfers are comprehensively blocked.
- Livewire components and backend APIs maintain strict authorization parity.
