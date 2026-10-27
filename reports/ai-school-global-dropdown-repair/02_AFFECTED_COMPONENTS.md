# 02 — AFFECTED COMPONENTS & RESOURCE CATALOG

**Project:** AI School — Laravel / Filament v3 / Livewire  
**Scope:** Complete inventory of components, resources, pages, and modals affected by the global dropdown defect.

---

## 1. Overview

Because the defect stemmed from the foundational `.fi-input-wrp-content-ctn` container rule in `custom-theme.css`, every single form component utilizing Filament's `Select` (native or searchable) was adversely affected.

Below is the structured audit of all affected areas categorized by module.

---

## 2. Financial & Revenue Management (Highest Priority)

| Component / Resource | Form / Action | Select Fields Affected | Previous Defect Impact | Current Status |
| :--- | :--- | :--- | :--- | :--- |
| `PaymentResource` | `PaymentForm.php` (`CreatePayment`, `EditPayment`) | `student_id` (searchable), `vault_id` (searchable), `enrollment_id` | Student dropdown clipped to 40px; accountant could not pick student or load enrollment | **REPAIRED & VERIFIED** |
| `SalaryPaymentResource` | `SalaryPaymentForm.php` (`CreateSalaryPayment`) | `employee_type` (reactive), `instructor_id` (searchable), `admin_id` (searchable), `vault_id` | Selecting instructor failed; trapped in 40px input; salary calculation blocked | **REPAIRED & VERIFIED** |
| `OverdueInstallmentResource` | `OverdueInstallmentResource.php` | Filter: `enrollment.student_id` (searchable/preload), Form: `status` | Table filters clipped by header container; status update trapped | **REPAIRED & VERIFIED** |
| `GenerateInstructorPayments` | Action: `generateSingle` (Modal) | `instructor_id` (searchable in modal) | Modal overflow clipped option list; batch salary generation blocked | **REPAIRED & VERIFIED** |
| `VaultResource` / `VaultTransfer` | `VaultForm.php`, `VaultTransferForm.php` | `from_vault_id`, `to_vault_id`, `vault_id` | Inter-vault transfer selection clipped | **REPAIRED & VERIFIED** |
| `RevenueResource` / `ExpenseResource` | `RevenueForm.php`, `ExpenseForm.php` | `vault_id`, `category_id`, `payment_method` | Expense vault selection clipped | **REPAIRED & VERIFIED** |

---

## 3. Student, Enrollment & Academic Management

| Component / Resource | Form / Action | Select Fields Affected | Previous Defect Impact | Current Status |
| :--- | :--- | :--- | :--- | :--- |
| `EnrollmentResource` | `EnrollmentForm.php` (`CreateEnrollment`) | `student_id` (searchable), `track_id`, `group_id`, `status` | Trapped inside 40px input; could not enroll students into courses | **REPAIRED & VERIFIED** |
| `StudentResource` | `StudentForm.php` (`CreateStudent`, `EditStudent`) | `parent_id` (searchable), `branch_id`, `status` | Could not link parent to student | **REPAIRED & VERIFIED** |
| `GroupResource` | `GroupForm.php` | `course_id` (searchable), `instructor_id` (searchable), `term_id` | Group assignment blocked | **REPAIRED & VERIFIED** |
| `CourseResource` | `CourseForm.php` | `track_id` (searchable), `prerequisite_course_id` | Course creation select clipped | **REPAIRED & VERIFIED** |
| `GradeResource` / `BulkGradeEntry` | `GradeForm.php`, `BulkGradeEntry.php` | `exam_id` (searchable), `student_id` (searchable), `group_id` | Exam grading selection failed | **REPAIRED & VERIFIED** |

---

## 4. Attendance, Supervision & Staff Modules

| Component / Resource | Form / Action | Select Fields Affected | Current Status |
| :--- | :--- | :--- | :--- |
| `AttendanceSession` | `AttendanceSessionForm.php` | `group_id`, `instructor_id` | **REPAIRED & VERIFIED** |
| `SupervisoryObservation` | `SupervisoryObservationResource.php` | `instructor_id`, `group_id` | **REPAIRED & VERIFIED** |
| `UserResource` | `UserForm.php` | `roles` (multi-select), `branch_id` | **REPAIRED & VERIFIED** |
| `StudentFollowUpTaskResource` | Follow-up modal & actions | `student_id`, `assigned_to` | **REPAIRED & VERIFIED** |

---

## 5. Third-Party / Custom Selection Controls Audit

- **Filament Native `Select`:** Unclipped, background solid `#FFFFFF`, text correctly aligned.
- **Filament Searchable `Select` (Floating-UI/Alpine):** Unclipped, debounced search functional, options rendered outside trigger box.
- **Multi-Select Badges:** Badge labels wrap properly without overflowing container.
- **Filter Popovers (`SelectFilter`):** Floating filter panels maintain high `z-index: 99999` and clear visibility.
