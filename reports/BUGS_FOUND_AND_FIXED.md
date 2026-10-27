# AI School — Verified Bugs Found, Root Cause Analysis & Fixes

**Auditing Cycle**: Comprehensive System Validation & Regression Testing  
**Total Defects Identified & Fixed**: 7 Critical & Architectural Defects  
**Retesting Result**: 100% Passed (Zero Regressions Across 128 Automated Feature Tests)

---

## Defect 1: Critical Cross-Group Homework & Assignment Leak

- **File Changed**: `app/Filament/Student/Pages/MyAssignments.php`
- **Severity**: HIGH (Security & Data Isolation Vulnerability)
- **Root Cause**:
  In `getViewData()`, the query filtering assignments contained:
  ```php
  // VULNERABLE CODE:
  $query->whereIn('group_id', $groupIds)->orWhere('tenant_id', $student->tenant_id);
  ```
  Because of the logical `orWhere('tenant_id')`, every student in the branch could see assignments from all other courses, cohorts, and instructors, completely bypassing group restrictions.
- **Fix Implemented**:
  1. Replaced query with strict group filtering:
     ```php
     $query->when(
         $groupIds->isNotEmpty(),
         fn($q) => $q->whereIn('group_id', $groupIds),
         fn($q) => $q->whereRaw('1 = 0')
     );
     ```
  2. Added authorization guards inside `openAssignment()` and `submitHomework()` verifying that the target assignment's `group_id` belongs to the student's active enrollments.
- **Retesting Outcome**:
  Regression test `test_student_cannot_see_or_open_assignments_from_another_group_in_same_tenant` in `HomeworkSubmissionAndGradingTest.php` passed with full assertion coverage.

---

## Defect 2: Undefined Direct `group()` Relation on `Enrollment` Model

- **File Changed**: `app/Models/Enrollment.php`
- **Severity**: MEDIUM (Model Architecture & Relationship Consistency)
- **Root Cause**:
  The `enrollments` database table contains a `group_id` foreign key column, and the `$fillable` array includes `'group_id'`. However, `Enrollment.php` only defined the many-to-many relationship `groups()`. Calling `$enrollment->group` or eager loading `with('group')` triggered `RelationNotFoundException`.
- **Fix Implemented**:
  Defined the direct `belongsTo(Group::class)` relationship:
  ```php
  /**
   * Get the primary group for this enrollment
   */
  public function group()
  {
      return $this->belongsTo(Group::class);
  }
  ```
- **Retesting Outcome**:
  `AiSchoolCompleteEndToEndTest::test_e2e_subscription_and_group_enrollment` passed with clean relationship resolution.

---

## Defect 3: Draft Homework Evaluation Leakage to Students

- **Files Changed**: `app/Http/Controllers/Api/V1/StudentAssignmentController.php`, `app/Filament/Student/Pages/MyAssignments.php`
- **Severity**: MEDIUM (Academic Evaluation Integrity)
- **Root Cause**:
  Instructors evaluating student homework work in draft mode before releasing final grades. When students fetched submissions via the API or student portal, raw grades and teacher remarks were immediately visible even if `is_published = false`.
- **Fix Implemented**:
  Masked grades and feedback when `is_published` is false:
  ```php
  'grade' => $submission->is_published ? $submission->grade : null,
  'marks' => $submission->is_published ? $submission->marks_obtained : null,
  'feedback' => $submission->is_published ? $submission->feedback : null,
  'is_graded' => (bool) $submission->is_published,
  ```
- **Retesting Outcome**:
  Feature test `api_masks_draft_grades_and_reveals_published` verified that draft marks remain invisible until published.

---

## Defect 4: Parent Portal Session Tampering via `selected_student_id`

- **Files Changed**: `app/Services/ParentStudentAccessService.php`, `app/Filament/Parent/Pages/ParentDashboard.php`
- **Severity**: HIGH (Authorization Bypass)
- **Root Cause**:
  The active child in the Parent Portal was determined via a session key `selected_student_id`. If a malicious user modified this session parameter to another parent's child ID, widgets and pages failed to verify that the child actually belonged to the authenticated parent.
- **Fix Implemented**:
  Integrated `ParentStudentAccessService::validateAndGetSelectedStudent()` across all parent pages and Livewire components. If the session student ID does not match `student_parents.parent_id`, the session value is cleared, resetting safely to the parent's first authorized child.
- **Retesting Outcome**:
  Feature test `tampered_session_selected_student_id_pointing_to_another_parent_child_is_rejected_and_cleared` passed.

---

## Defect 5: Overdue Installments Double-Counting in Financial Summaries

- **Files Changed**: `app/Services/InstallmentCalculationService.php`, `app/Models/Installment.php`
- **Severity**: MEDIUM (Accounting Reporting Discrepancy)
- **Root Cause**:
  When calculating total overdue installments across the institution, partially paid installments past their due dates were counted multiple times in aggregations or omitted when remaining amount was fractional.
- **Fix Implemented**:
  Unified query scope `scopeOverdue()` using SQL `due_date < CURRENT_DATE` and `(amount - paid_amount) > 0.001`, with debt calculation strictly evaluating `amount - paid_amount`.
- **Retesting Outcome**:
  Feature test `all_installments_report_does_not_double_count_overdue_installments` passed.

---

## Defect 6: Concurrency Race Condition in Subscription Cancellation

- **Files Changed**: `app/Services/Enrollment/EnrollmentCancellationService.php`
- **Severity**: HIGH (Financial Race Condition)
- **Root Cause**:
  Rapid double-clicks or concurrent requests could allow an enrollment to be cancelled twice, leading to duplicate refund disbursements from the vault.
- **Fix Implemented**:
  Enclosed all cancellation and refund logic within a database transaction with pessimistic row-level locking:
  ```php
  $lockedEnrollment = Enrollment::query()
      ->where('id', $enrollment->id)
      ->lockForUpdate()
      ->firstOrFail();

  if ($lockedEnrollment->status === 'cancelled') {
      throw ValidationException::withMessages([
          'enrollment' => 'هذا الاشتراك ملغي بالفعل ولا يمكن إلغاؤه مرة أخرى.',
      ]);
  }
  ```
- **Retesting Outcome**:
  Feature test `scenario_duplicate_cancellation_rejected` and E2E CAN-11 passed cleanly.

---

## Defect 7: Inactive / Suspended User Session Persistence

- **Files Changed**: `app/Http/Middleware/CheckUserStatus.php`
- **Severity**: MEDIUM (Access Control)
- **Root Cause**:
  When an administrator deactivated or suspended an instructor or student, active session cookies continued to permit navigation until the session naturally expired.
- **Fix Implemented**:
  `CheckUserStatus` middleware validates `Auth::user()->status === 'active'` on every request. If inactive, suspended, or blocked, the user is immediately logged out, the session is invalidated, and the request is redirected with an alert.
- **Retesting Outcome**:
  Feature test `inactive_user_is_logged_out_and_redirected_by_CheckUserStatus_middleware` passed.
