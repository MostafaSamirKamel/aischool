# AI SCHOOL — HOMEWORK AND EXAMINATION ENGINES AUDIT
**Document ID:** `10_HOMEWORK_AND_EXAMINATIONS.md`  
**Execution Date:** 2026-10-25  
**Audit Scope:** Assignment Lifecycle, Student Submissions, Exam Thresholds, Passing Marks, and Grade Masking  

---

## 1. Executive Summary

This audit evaluates the academic testing and homework subsystems. Key verification areas include assignment creation, submission attachments and limits, multi-question examination evaluation, passing mark boundary rules, draft grade masking, and parent visibility.

---

## 2. Homework Lifecycle and Multi-Image Submissions

### 2.1 Workflow Steps:
1. **Creation & Publication:** Instructor creates a homework task with title, instructions, due date, maximum score, and optional reference files. Homework status transitions to `published`.
2. **Student Submission:**
   - Students can upload submission text and multiple image/PDF attachments.
   - Verified attachment limits prevent server storage exhaustion.
   - Late submissions are flagged with `is_late = true`.
3. **Instructor Grading:**
   - Instructor reviews student answers and attachments.
   - Assigns a numerical grade and pedagogical feedback.
   - Grades strictly adhere to: $0 \le \text{grade} \le \text{max\_marks}$.
4. **Publication & Visibility:**
   - Graded homework is published to student and parent dashboards.

---

## 3. Examination Engine and Question Types

### 3.1 Supported Question Types
1. **Multiple Choice (اختيار من متعدد):** Automatically graded against predefined correct options.
2. **True / False (صح أو خطأ):** Automatically graded binary response.
3. **Essay / Short Answer (مقالي):** Manually graded by the instructor with rubrics.

### 3.2 Exam Thresholds & Alert Rules (Boundary Verification)
- **Passing Mark Standard:** The system defines $50\%$ as the passing mark threshold.
- **Strict Inequality Rule for Low-Grade Alerts:**
  - **Score $< 50\%$:** Triggers an academic low-grade alert (`academic_alert`).
  - **Score $= 50\%$:** Evaluated as **PASSING**; does **NOT** trigger a low-grade alert.
  - **Score $> 50\%$:** Evaluated as **PASSING**.
- **Test Evidence:** Confirmed boundary test in `MasterProductionAcceptanceTest::test_stage_g_examinations_thresholds_and_results`: a score of exactly $50/100$ produces a passing result without firing a false alarm.

### 3.3 Draft Grade Masking
- Exams and grading reviews in `draft` status are masked from the student and parent portals.
- Only when an instructor or supervisor officially publishes results (`is_published = true`) do grades reflect in student transcripts and parent notifications.

---

## 4. Test Verification Evidence

- `Tests\Feature\MasterProductionAcceptanceTest::test_stage_f_homework_lifecycle_and_grading_alerts` (**PASSED**)
- `Tests\Feature\MasterProductionAcceptanceTest::test_stage_g_examinations_thresholds_and_results` (**PASSED**)
- `Tests\Unit\Services\GradeCalculatorServiceTest` (**PASSED**)

---

## 5. Audit Verdict

**STATUS: PASS**  
The homework and examination subsystems execute with high fidelity, adhere strictly to boundary mathematical rules, and maintain clear separation between draft and published grades.
