# AI School — Roles, Permissions & Security Isolation Report

**Security Engines**: Laravel Gates, Spatie Laravel-Permission, Filament Panel Middleware, Tenant Query Scopes  
**Audited Roles**: `super_admin`, `branch_manager`, `secretary`, `accountant`, `supervisor`, `instructor`, `student`, `parent`  
**Status**: 100% Tested & Verified

---

## 1. Global Authorization Architecture (`Gate::before`)

The application configures authorization logic inside `AuthServiceProvider` / `AppServiceProvider`.

### Critical Security Rule Verified:
- **Super Admin (`is_super_admin = true` or role `super_admin`)**: Bypasses all gate checks via `Gate::before(fn($user, $ability) => $user->isSuperAdmin() ? true : null)`.
- **Branch Managers & Staff**: **DO NOT** automatically bypass gates. A Branch Manager requires explicit Spatie permissions (`cancel_subscription`, `settle_subscription`, `execute_refunds`) or role-based assignments. Without them, critical actions return HTTP 403 Forbidden.

---

## 2. Granular Role Permission Matrix

| Role | Panel Access | Student CRUD | Group Management | Attendance | Financial Collections | Cancellation | Debt Waiver / Settlement | Refund Payout |
|---|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **Super Admin** | All Panels | YES | YES | YES | YES | YES | YES | YES |
| **Branch Manager** | `/admin` | YES | YES | YES | YES | YES | YES | YES |
| **Registration Staff** | `/admin` | YES | View Only | View Only | View Only | YES | **DENIED** | **DENIED** |
| **Accountant** | `/admin` | View Only | View Only | View Only | YES | View Only | YES | YES |
| **Supervisor** | `/supervisor` | View Only | Oversight | Audit | View Only | **DENIED** | **DENIED** | **DENIED** |
| **Instructor** | `/instructor` | Scoped | Assigned Only | Assigned Only | **DENIED** | **DENIED** | **DENIED** | **DENIED** |
| **Student** | `/student` | Self Only | Enrolled Only | Self Only | View Invoices | **DENIED** | **DENIED** | **DENIED** |
| **Parent** | `/parent` | Children Only| View Only | Children Only | View Invoices | **DENIED** | **DENIED** | **DENIED** |

---

## 3. Negative Security & Tampering Tests

### SEC-01: Cross-Tenant Data Tampering
- **Test**: User from Tenant 2 sends direct HTTP request with `tenant_id = 1` or queries resource ID belonging to Tenant 1.
- **Result**: `BelongsToBranch` global scope and model mutators automatically reject or sanitize `tenant_id`. Record returns HTTP 404/403. **PASSED**.

### SEC-02: Parent Portal `selected_student_id` Session Tampering
- **Test**: Parent 1 attempts to tamper with session cookie or Livewire request payload by setting `selected_student_id` to Student 5 (child of Parent 3).
- **Result**: `ParentStudentAccessService` validates the parent-child relationship in `student_parents`. Unauthorized ID is cleared immediately; access is rejected. **PASSED**.

### SEC-03: Instructor Cross-Group Homework & Grade Tampering
- **Test**: Instructor 2 (Scratch) attempts to grade homework submissions belonging to Instructor 1's Python group.
- **Result**: `AssignmentPolicy::update` and Filament query constraints restrict the list strictly to `where('instructor_id', Auth::id())`. **PASSED**.

### SEC-04: Student Cross-Group Assignment Leak (Fixed)
- **Vulnerability**: In `app/Filament/Student/Pages/MyAssignments.php`, `orWhere('tenant_id')` previously allowed all students in the branch to see all assignments.
- **Fix**: Replaced with strict group membership checking `whereIn('group_id', $groupIds)` and added validation in `openAssignment` / `submitHomework`.
- **Result**: Cross-group assignment opening is now impossible. **PASSED**.

### SEC-05: Unauthorized Refund Disbursement
- **Test**: Registration staff attempts to execute refund disbursement on a cancelled enrollment.
- **Result**: Blocked by `EnrollmentPolicy::executeRefund()` -> Throws `AuthorizationException`. **PASSED**.
