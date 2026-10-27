# 07 — HOMEWORK GRADING, INSTANT PUBLICATION & RESUBMISSIONS

## 1. Instant Homework Publication Policy
In strict compliance with the approved domain rules:
- **Button:** Clicking **"تم التصحيح"** in the grading drawer:
  1. Validates score (`0 <= score <= max_score`).
  2. Persists score, feedback, `graded_by = auth()->id()`, and `graded_at = now()`.
  3. Sets `published_at = now()` and `status = 'graded'`.
  4. Automatically recalculates the highest valid score among eligible attempts and marks it `is_official = true`.
  5. **Instantly publishes** the result to the student portal and authorized parent reports.
  6. Reconciles academic low-grade alerts (`< 50%`).
  7. No secondary publication button is required.

## 2. Highest Eligible Grade Rule
```
Example Real Lifecycle:
- Attempt 1: 6.0 / 10.0 (Graded & Published) -> Official: 6.0
- Student granted resubmission -> Submits Attempt 2
- Attempt 2: 9.0 / 10.0 (Graded & Published) -> Official: 9.0
- Student granted resubmission -> Submits Attempt 3
- Attempt 3: 7.0 / 10.0 (Graded & Published) -> Official: 9.0 (Attempt 2 remains official!)
```
Under no circumstances does an ungraded attempt or a lower re-attempt degrade an existing published score.

## 3. Resubmission Grants (`homework_resubmission_grants`)
- Post-grading, a student cannot independently resubmit without an authorized grant.
- **Grant Attributes:**
  - `assignment_id`, `student_id`, `granted_by`, `reason`, `expires_at`, `status = 'active'`.
  - **Single Use:** When the student submits their new attempt, the grant is atomically consumed (`status = 'consumed'`).
  - **No Lifetime Ceiling:** Instructors/Supervisors can issue multiple sequential grants as pedagogically necessary; overlapping active grants for the same assignment and student are disallowed.

## 4. Published Grade Corrections (`academic_grade_corrections`)
- If an instructor or supervisor corrects an already published score:
  - Requires explicit modal confirmation with a mandatory reason.
  - Automatically records `previous_score`, `new_score`, `reason`, `corrected_by`, and timestamp.
  - Recomputes the official highest score immediately and updates reports.
