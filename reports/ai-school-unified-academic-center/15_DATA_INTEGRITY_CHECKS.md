# 15 — DATABASE & DATA INTEGRITY VERIFICATION

## 1. Idempotency & Concurrency Safeguards
The unified Academic Center implements rigorous concurrency and integrity protections across all critical academic mutations:
1. **DB Transactions:** All grade corrections, resubmission grant issuance, deadline extensions, and exam attempt starts execute inside `DB::transaction()`.
2. **Pessimistic Row Locking:** `lockForUpdate()` is utilized during:
   - Resubmission grant consumption (`HomeworkResubmissionGrant::where(...)->lockForUpdate()->first()`).
   - Retake grant consumption (`ExamRetakeGrant::where(...)->lockForUpdate()->first()`).
   - Official grade recalculations (`AssignmentSubmission::where(...)->lockForUpdate()->get()`).
3. **Double Click & Rapid Repeat Protection:**
   - Buttons utilize Livewire `wire:loading.attr="disabled"` to prevent duplicate HTTP requests.
   - Idempotency checks in `AcademicCenterService` guarantee that repeated clicks do not create duplicate attempts or corrupted official scores (`test_012_duplicate_publish_idempotency` => PASSED).

## 2. Foreign Key & Relational Consistency Verification
- Every `assignment_submissions` row belongs to a valid `student_id`, `assignment_id`, and `tenant_id`.
- Every `exam_attempts` row belongs to a valid `student_id`, `exam_id`, and `tenant_id`.
- Grade corrections link directly to `academic_grade_corrections` with valid foreign keys to `assignment_submissions` or `exam_attempts`.
- Historical attempts and previous submission versions are strictly preserved (no hard deletes).
- No unrelated financial, vault, payroll, or administrative records were modified.
