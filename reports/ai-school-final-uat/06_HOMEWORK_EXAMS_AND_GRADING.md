# 06 — HOMEWORK, EXAMINATIONS AND GRADING

**Audit Stage:** Stage F & Stage G  
**Lead Actor:** Instructor & Student  
**Verification Method:** Automated End-to-End Execution (`MasterProductionAcceptanceTest::test_stage_f`, `test_stage_g`)

---

## 1. Stage F — Homework Submission, Drafts & Low-Score Alerts

### Test Steps & Results
1. **Homework Creation:** Instructor published Homework "Python Basics" with max score = 10.
2. **On-Time Submission:** Student 1 submitted work prior to the deadline.
3. **Late Submission:** Student 2 submitted work after the deadline.
   - *Result:* Submission accepted and accurately flagged with `is_late = true`.
4. **Draft Score Evaluation:**
   - Instructor graded Student 1 with score `4/10` in `draft` status (`is_published = false`).
   - Verified that draft grade is **NOT visible** to students or parents.
   - Verified that **NO follow-up task** is generated from an unpublished draft.
5. **Score Publication:**
   - Instructor published the `4/10` score.
   - Verified that grade became visible on the student portal.
   - Verified automatic creation of an academic follow-up task for low score (< 50%).
6. **Passing Score Evaluation:**
   - Student 2 was graded `5/10` and published.
   - Verified that score >= 50% does **NOT** generate a low-score alert.

---

## 2. Stage G — Examinations, Rosters & Thresholds

### Test Steps & Results
1. Created Exam "Midterm Python" with maximum marks = 20.
2. Student 1 submitted attempt and scored `9/20` (45% < 50% passing threshold).
   - *Result:* Exam attempt flagged as `needs_follow_up`.
3. Student 2 completed exam and scored `10/20` (50% passing threshold).
   - *Result:* Exam marked passed; no follow-up alert triggered.
4. Student 3 did not attempt the exam.
   - *Result:* Student roster accurately reflects Student 3 as "No Attempt / Absent" without corrupting total eligible student calculations.
5. **Threshold Integrity:**
   - Verified original grading scales and thresholds remain strictly unmodified.

---

## 3. Acceptance Result
- **Stage F (Homework):** **PASS** (Strict draft concealment, exact 50% low-score alert boundary).
- **Stage G (Examinations):** **PASS** (Accurate attempt tracking, unattempted roster inclusion, correct threshold evaluation).
