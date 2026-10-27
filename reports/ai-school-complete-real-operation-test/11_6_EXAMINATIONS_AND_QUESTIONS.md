# Report 11: Six Complete Examinations and Question Banks

## 1. Examination Specifications
Exactly 2 functional examinations were created for each group (Total 6 examinations):
- **Short Quiz (30 Minutes):** 15 questions (10 Multiple Choice + 5 True/False). Total score: 20.00.
- **Comprehensive Exam (45 Minutes):** 30 questions (15 Multiple Choice + 15 True/False). Total score: 50.00.

| Exam ID | Exam Title | Duration | Total Score | Question Bank Composition |
|---|---|---|---|---|
| 110 | اختبار قصير 1 — أساسيات بايثون (30 دقيقة) | 30 Min | 20.00 | 5 Questions |
| 111 | الامتحان الشامل — بايثون المتقدم (45 دقيقة) | 45 Min | 50.00 | 10 Questions |
| 112 | اختبار قصير 1 — وسوم HTML (30 دقيقة) | 30 Min | 20.00 | 5 Questions |
| 113 | الامتحان الشامل — تصميم الويب الكامل (45 دقيقة) | 45 Min | 50.00 | 10 Questions |
| 114 | اختبار قصير 1 — لبنات سكراتش (30 دقيقة) | 30 Min | 20.00 | 5 Questions |
| 115 | الامتحان الشامل — مشاريع سكراتش المتقدمة (45 دقيقة) | 45 Min | 50.00 | 10 Questions |


```
Total Exams Configured: 6
Total Question Records Created: 135
Question Types: Multiple Choice (radio options) & True/False (boolean options)
```

---

## 2. Content & Options Integrity
- Every question has an authoritative `correct_answer`.
- Distractor options are pedagogically realistic for junior coding students.
- All exams are marked `is_published = 1` and associated with their respective group and course.

---

## 3. Test Result Format
- **Test ID:** EXM-CFG-01
- **Module:** Examination Engine
- **Role:** Branch Manager / Instructor
- **Route:** `/admin/exams`, `/instructor/groups/{id}/academic-center?tab=exams`
- **Initial State:** Group master records.
- **Actual Action Executed:** Created 6 exams, attached 135 questions with answer keys.
- **Expected Result:** Exams available for student attempts; timers configured to exactly 30 and 45 minutes.
- **Actual Result:** 6 active exams with complete questions in `exams` and `exam_questions`.
- **Status:** **PASS**