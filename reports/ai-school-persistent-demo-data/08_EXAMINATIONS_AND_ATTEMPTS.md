# 08 — Examinations, Question Pools & Student Attempts

## Overview
This document specifies the examination architecture, question banks, student attempts, and published grade records generated for the existing student cohort across all active curricula.

---

## 1. Examination Structure & Question Banks

A total of **39 new examinations** and **78 validated questions** were added to the examination module:

| Exam Title | Course | Total Marks | Pass Mark | Question Types Supported | Status |
|---|---|---|---|---|---|
| **Physics Term 1 Midterm Exam** | Physics Sec 1 | 100.0 | 50.0 | Multiple Choice, True/False | Published |
| **English Grammar & Comprehension Quiz** | English Level 3 | 50.0 | 25.0 | Multiple Choice, True/False | Published |
| **Algebra & Geometry Monthly Assessment** | Math Sec 1 | 100.0 | 50.0 | Multiple Choice, True/False | Published |
| **General Science Unit 1 Exam** | Science Prep 3 | 100.0 | 50.0 | Multiple Choice, True/False | Published |

### Sample Question Bank Entries:
1. **Multiple Choice (اختيار من متعدد)**:
   - *Question*: "ما هي وحدة قياس القوة في النظام الدولي للوحدات (SI)؟"
   - *Options*: `A: جول`, `B: نيوتن`, `C: واط`, `D: باسكال`
   - *Correct Option*: `B: نيوتن` (Points: 50.0)
2. **True / False (صح أو خطأ)**:
   - *Question*: "قانون نيوتن الأول ينطبق فقط في الأطر المرجعية القصورية."
   - *Correct Answer*: `True` (Points: 50.0)

---

## 2. Student Exam Attempts & Grade Distribution

A total of **125 new student exam attempts** and corresponding **125 student grade records** were generated:

| Grade Category | Score Range | Letter Grade | Count | Percentage |
|---|---|---|---|---|
| **Distinction / High (ممتاز)** | 85.0% – 100.0% | `A` / `A+` | **51** | 40.8% |
| **Satisfactory / Average (جيد - جيد جداً)** | 50.0% – 84.0% | `C` / `B` | **53** | 42.4% |
| **Unsuccessful / Fail (راسب - دون 50%)** | 20.0% – 48.0% | `D` / `F` | **21** | 16.8% |
| **Total Graded In Database** | — | — | **183** | 100.0% |

> [!NOTE]
> The presence of 21 scores below 50% provides authentic, non-artificial triggers for:
> - Academic follow-up tickets.
> - Remedial makeup classes.
> - Parent performance warning notifications.

---

## 3. Workflow Verification & Security Isolation

- **Grading Integrity**: All attempts accurately sum points earned across question items. Passing marks are strictly evaluated (`score >= pass_mark ? 'passed' : 'failed'`).
- **Student Privacy**: Students can inspect only their own submitted attempts, question breakdowns, and published scores.
- **Parent Portal Verification**: Parents can view official published transcripts for their linked children, with color-coded badges for passing and failing grades.
- **Instructor Panel**: Instructors can review the class grade curve, identify question difficulty index, and export grade rosters.
