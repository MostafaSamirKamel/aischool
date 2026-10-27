# AI SCHOOL — SECURITY, MULTI-TENANCY, AND PERMISSIONS AUDIT
**Document ID:** `15_SECURITY_AND_PERMISSIONS.md`  
**Execution Date:** 2026-10-25  
**Audit Priority:** CRITICAL  
**Scope:** Authentication, Multi-Tenancy Scoping, Branch Isolation, Shield Permission Ceilings, Web Security  

---

## 1. Executive Summary

This audit evaluates the application's security posture, access control architecture, and multi-tenant isolation mechanisms. The AI School platform operates in a multi-tenant, multi-branch environment where tenant leakage or unauthorized privilege escalation would represent a severe vulnerability.

---

## 2. Multi-Tenancy and Branch Isolation

### 2.1 Global Scopes and Middleware
- Core transactional models employ tenant and branch scoping.
- HTTP requests resolve the active tenant and branch via authenticated user context and session headers.
- **Cross-Tenant Access Test:**
  - An authenticated user in Tenant 1 attempted direct URL access to view an enrollment and installment in Tenant 2.
  - **Result:** Request was intercepted with an `HTTP 403 Forbidden` / `ModelNotFoundException`. Zero data leakage occurred.
  - Verified in `InstallmentDueReportIntegrationTest::test_j_cross_tenant_installment_access_denied`.

### 2.2 Branch Manager Permission Delegation Ceilings
- **Rule:** A Branch Manager cannot assign or grant permissions that exceed their own authorization boundary.
- Filament Shield roles enforce hierarchical privilege boundaries:
  - `super_admin`: Global system configuration across tenants.
  - `company_admin`: Complete tenant configuration across all owned branches.
  - `branch_manager`: Scoped strictly to the assigned branch; cannot elevate users to company admin.
  - `accountant`: Financial operations limited to assigned branch vaults.
  - `secretary`: Student registration and attendance.

---

## 3. Web Security Assessment

| Vulnerability Category | Protection Mechanism | Audit Finding | Status |
| :--- | :--- | :--- | :--- |
| **SQL Injection (SQLi)** | Eloquent ORM + PDO parameter binding | Zero raw concatenation in user inputs | **PROTECTED** |
| **Mass Assignment** | Model `$fillable` arrays explicitly declared | All sensitive attributes (`is_admin`, `tenant_id`) protected | **PROTECTED** |
| **Cross-Site Scripting (XSS)**| Blade `{{ }}` automatic escaping | Raw `{!! !!}` sanitized; no user input rendered unescaped | **PROTECTED** |
| **Cross-Site Request Forgery**| Laravel CSRF token verification on all POST/PUT | Livewire checksum verification active | **PROTECTED** |
| **Direct Object Reference** | Policy checks in Filament Resources & Observers | Enforced via Model Policies and Shield | **PROTECTED** |
| **File Upload Safety** | MIME type and extension validation | Restricts uploads to PDF, JPG, PNG; file size capped | **PROTECTED** |

---

## 4. Test Verification Evidence

- `Tests\Feature\MasterProductionAcceptanceTest::test_cross_tenant_branch_isolation_and_vault_overdraft_prevention` (**PASSED**)
- `Tests\Feature\InstallmentDueReportIntegrationTest::test_i_reporting_only_user_cannot_collect_payments` (**PASSED**)
- `Tests\Feature\InstallmentDueReportIntegrationTest::test_j_cross_tenant_installment_access_denied` (**PASSED**)

---

## 5. Security Audit Verdict

**STATUS: PASS**  
The application is resilient against common web vulnerabilities, strictly enforces tenant and branch isolation, and adheres to the principle of least privilege.
