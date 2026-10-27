# 09 — EXAM RETAKE PERMISSIONS & HIGHEST OFFICIAL SCORE

## 1. Independent Exam Retake Permission Control
Exam retake permissions are completely decoupled from homework resubmissions:
- Governed by independent database records (`exam_retake_grants`).
- Branch Manager delegates permission strictly within the ceiling granted by Company Admin.
- Instructors or supervisors can only grant retakes if explicitly delegated.

## 2. Retake Grant Rules & Atomic Consumption
- **Attributes:**
  - `exam_id`, `student_id`, `granted_by`, `reason`, `expires_at`, `status = 'active'`.
- **Validation:**
  - Cannot grant if an active grant already exists.
  - Cannot start a new attempt once allowed attempts are exhausted unless an active, unexpired grant is verified.
- **Consumption:**
  - When the student clicks **"بدء محاولة جديدة"**, the grant is consumed atomically (`status = 'consumed'`) inside a database transaction, creating Attempt #N+1.

## 3. Highest Completed Attempt Rule
```
Real Test Case:
- Attempt 1: 8.00 / 20.00 (Failed, 40%) -> Official: 8.00
- Retake Grant 1 issued -> Student completes Attempt 2
- Attempt 2: 16.00 / 20.00 (Passed, 80%) -> Official: 16.00
- Retake Grant 2 issued -> Student completes Attempt 3
- Attempt 3: 12.00 / 20.00 (Passed, 60%) -> Official: 16.00 (Attempt 2 remains official!)
```
- Only completed attempts are eligible for official scoring.
- In-progress or abandoned attempts are never counted towards official grade.

## 4. Exam Publication Policy Separation
- Unlike homework (which publishes instantly upon "تم التصحيح"), **exam results obey independent publication rules**.
- Grading an attempt does not release the score to students/parents until `published_at` is set on the exam by authorized personnel.
