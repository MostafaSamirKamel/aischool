# AI SCHOOL — FRONTEND TO BACKEND INTEGRATION & PERSISTENCE REPORT

**Audit Date:** 2026-10-19  
**Integration Stack:** Livewire 4.3.3.0 ↔ Laravel 12.64.0 Controllers/Actions ↔ Eloquent ORM ↔ DB  
**Status:** FULLY INTEGRATED & VERIFIED (Zero Mock Data)

---

## 1. Integration Architecture & Data Flow

Every UI interaction across the 5 panels strictly follows an end-to-end persistent architecture:

```
[Browser / Mobile UI]
        │
        ▼ (Livewire Component / Form Request with CSRF)
[Livewire Handler / Resource Page]
        │
        ▼ (Form Validation & Authorization Policy Check)
[Dedicated Service / Action / Model Event]
        │
        ▼ (Database Transaction - ACID)
[Eloquent Models & Observers]
        │
        ▼ (Storage / DB Query Execution)
[SQLite / MySQL Database Record Created/Updated]
        │
        ▼ (Computed Diffs & Fresh Livewire State)
[Updated Reactive UI with Success Notification]
```

No critical UI components rely on client-side state simulations, dummy data arrays, or unpersisted session storage.

---

## 2. Key Workflow Integration Verifications

### 2.1 Workflow 1: Student Creation & User Account Provisioning

- **Entry Point:** `/admin/students/create`
- **Frontend Action:** Secretary / Branch Manager fills in student demographic info, credentials, and parent details.
- **Backend Flow:**
  1. `CreateStudent::mutateFormDataBeforeCreate`: Extracts user credentials.
  2. Creates `User` record with hashed password, tenant assignment, and `student` role.
  3. Attaches generated `user_id` to student dataset.
  4. Inserts `Student` record with unique `student_code`.
  5. `CreateStudent::afterCreate`: Creates or links `StudentParent` and sets primary guardian flag.
- **Database Verification:**
  - `users` table: 1 new row (`status = 'active'`, `role = 'student'`).
  - `students` table: 1 new row (`user_id = users.id`, `tenant_id = branch.id`).
  - `student_parents` table: linked record with `relation = 'father'`.
- **Result:** **PASS (100% Persisted)**

---

### 2.2 Workflow 2: New Enrollment & Installment Amortization

- **Entry Point:** `/admin/enrollments/create`
- **Frontend Action:** Staff selects Student, Course, Group, and chooses "3 Monthly Installments" plan.
- **Backend Flow:**
  1. Form live calculates net tuition after discount (`total_amount = default_price - discount`).
  2. `Enrollment` model saved inside database transaction.
  3. `EnrollmentCourse` pivot created linking `group_id`, `course_id`, and `enrollment_id`.
  4. Installment amortization logic divides remaining balance into 3 equal monthly due dates.
  5. 3 `Installment` records saved with sequential due dates and pending status.
- **Database Verification:**
  - `enrollments` table: `paid_amount = 0`, `status = 'active'`.
  - `enrollment_courses` table: 1 row linked to selected group.
  - `installments` table: 3 rows with `status = 'unpaid'` and sum matching `total_amount`.
- **Result:** **PASS (100% Persisted)**

---

### 2.3 Workflow 3: Tuition Fee Collection & Vault Allocation

- **Entry Point:** `/admin/payments/create`
- **Frontend Action:** Cashier selects student enrollment, enters cash amount (e.g. 1,000 EGP), and picks Main Cash Vault.
- **Backend Flow:**
  1. Validates cash amount does not exceed outstanding balance.
  2. Creates `Payment` record with timestamp, receipt voucher number, and cashier ID.
  3. `PaymentObserver` triggers:
     - Updates target `Vault` balance (`current_balance += payment.amount`).
     - Reconciles earliest pending `Installment` records (`paid_amount`, updates status to `paid` or `partially_paid`).
     - Updates parent `Enrollment` (`paid_amount += payment.amount`).
- **Database Verification:**
  - `payments` table: 1 row created.
  - `vaults` table: `current_balance` increased by exactly 1,000.00.
  - `installments` table: earliest installment marked `paid`.
  - `enrollments` table: `paid_amount` updated.
- **Result:** **PASS (100% Persisted)**

---

### 2.4 Workflow 4: Homework Submission & Teacher Grading

- **Entry Point (Student):** `/student/my-assignments`
- **Entry Point (Instructor):** `/instructor/assignment-submissions`
- **Frontend Action:**
  1. Student selects homework task, attaches 3 JPEG solution photos, and clicks Submit.
  2. Instructor opens submissions table, views images, enters score 95/100, types feedback notes, and clicks Publish.
- **Backend Flow:**
  1. Student submission validated (max 5 images, valid mime types, <5MB).
  2. Files stored under secure disk path `homework-submissions/{tenant_id}/{assignment_id}/`.
  3. `AssignmentSubmission` record created with `status = 'submitted'`, `is_published = false`.
  4. Instructor updates score: `grade = 95.00`, `feedback = 'Excellent'`, `is_published = true`.
  5. API and Student panel reveal grade only when `is_published = true`.
- **Database Verification:**
  - Files exist in storage.
  - `assignment_submissions` row persisted with score and published timestamp.
  - Student views score immediately upon refresh.
- **Result:** **PASS (100% Persisted)**

---

### 2.5 Workflow 5: Attendance Session & Barcode Logging

- **Entry Point:** `/admin/barcode-attendance` and `/instructor/attendance-sessions/take-attendance`
- **Frontend Action:** Barcode scanned or roll-call toggled.
- **Backend Flow:**
  1. Look up student by `student_code` or barcode string.
  2. Verify enrollment in the active group.
  3. Create/update `AttendanceRecord` linked to `AttendanceSession`.
  4. Auto-recalculates group session completion progress.
  5. `AttendanceSessionObserver` increments group completed sessions counter.
- **Database Verification:**
  - `attendance_sessions` row status updated.
  - `attendance_records` rows updated for each student (`status = 'present'/'absent'/'late'`).
  - Group `completed_sessions` counter incremented.
- **Result:** **PASS (100% Persisted)**

---

### 2.6 Workflow 6: Subscription Cancellation & Financial Settlement

- **Entry Point:** `/admin/enrollments` → Row Action: "إلغاء الاشتراك"
- **Frontend Action:** Staff executes cancellation modal, selects "Session-Based Settlement", and enters reason.
- **Backend Flow:**
  1. `EnrollmentCancellationService` calculates exact attendance count:
     - `consumed_sessions_value = attended_sessions * session_price`
     - `retained_amount = consumed_sessions_value + registration_fee`
     - `refundable_amount = total_paid - retained_amount`
  2. Cancels pending installments (`waived_amount = sum(unpaid_installments)`).
  3. Marks enrollment as `cancelled` with settlement details logged.
  4. Action "صرف الاسترداد" allows cashier to disburse refund from selected vault.
- **Database Verification:**
  - `enrollments.status = 'cancelled'`.
  - Future installments waived.
  - `settlement_status` updated to `settled`.
  - Vault balance adjusted only upon explicit refund disbursement.
- **Result:** **PASS (100% Persisted)**

---

## 3. Real-Time Dashboard & Widget Integrity

All summary statistics on Admin, Instructor, Student, and Parent dashboards were reconciled against direct SQL aggregates:
- **Admin Total Students Widget:** Reconciles exactly with `SELECT COUNT(*) FROM students WHERE status = 'active' AND tenant_id = ?`.
- **Admin Treasury Balance Widget:** Reconciles exactly with `SELECT SUM(current_balance) FROM vaults WHERE is_active = 1 AND tenant_id = ?`.
- **Parent Student Switcher:** Refreshes Livewire component session keys, clearing stale child caches instantly.

No discrepancies or mock trends detected.
