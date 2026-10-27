# AI SCHOOL — REAL EXAMINATION ATTEMPTS AND GRADING THRESHOLDS
**Document ID:** `09_REAL_EXAM_ATTEMPTS.md`  
**Execution Date:** 2026-10-25  
**Core Boundary Rule:** Strict $< 50\%$ triggers academic alert; exactly $50\%$ is PASSING (no alert)  

---

## 1. Executive Summary

This report documents the verification of the examination engine. Genuine student exam attempts were submitted, automatically scored, threshold-checked against passing criteria, and masked from portals while in draft status.

---

## 2. Operational Test Results Matrix

| Test ID | Operation | Real Input Tested | Expected Outcome | Actual Database State | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **EXM-01** | Exam Authoring | Midterm Exam: Total 100, Passing 50 | Exam created and attached to group | Row in `exams` with ID 109 | **PASS** |
| **EXM-02** | Real Student Attempt | Student submits exam attempt with answers | Attempt stored with start and completion times | Row in `exam_attempts` | **PASS** |
| **EXM-03** | Auto-Grading & Score | Multiple choice responses scored | Accurate score calculation | `score = 50`, `percentage = 50` | **PASS** |
| **EXM-04** | Passing Boundary (50%) | Student scores exactly 50/100 | Result = PASS; Alert = FALSE | `passed = 1`, alert not fired | **PASS** |
| **EXM-05** | Low-Grade Alert (< 50%)| Student scores 48/100 | Result = FAIL; Alert = TRUE | `passed = 0`, alert fired | **PASS** |
| **EXM-06** | Draft Grade Masking | Exam results in `draft` status | Masked from student & parent portals | Query scope excludes drafts | **PASS** |
| **EXM-07** | Official Grade Publish | Teacher publishes exam results | Visible on student & parent report cards | Published grade visible | **PASS** |

---

## 3. Strict Boundary Rules

1. **Threshold Standard:**
   - Score $< 50.0\%$: Triggers an academic low-grade warning.
   - Score $= 50.0\%$: Meets passing criteria; does **NOT** fire a low-grade alert.
2. **Alert vs. Case Independence:**
   - Low-grade alerts inform dashboards but do **NOT** automatically spam the database with unverified secretary follow-up cases.

---

## 4. Verification Verdict

**STATUS: PASS**  
The examination engine functions reliably, enforces strict boundary mathematics, and separates draft grades from published student transcripts.
