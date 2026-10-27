# 11 — Examination Creation & Student Attempts Testing

## 1. Exam Configuration
- **Exam Title:** "امتحان نصف الفصل: مبادئ الذكاء الاصطناعي والبرمجة"
- **Course:** الذكاء الاصطناعي للمبتدئين
- **Group:** مجموعة الذكاء الاصطناعي (`Group ID: 1`)
- **Total Marks:** 20 Marks
- **Passing Marks:** 10 Marks (`passing_marks: 10`)
- **Duration:** 45 Minutes
- **Status:** `scheduled` $\rightarrow$ `in_progress` (`Exam ID: 12`)

## 2. Student Examination Attempts
Executed 4 distinct student exam attempt workflows:
1. **Student A (High Achiever):** Completed exam, answered all questions accurately. Score: **18 / 20 (90% - Passed)**.
2. **Student B (Average Achiever):** Completed exam. Score: **14 / 20 (70% - Passed)**.
3. **Student C (Low Achiever / At-Risk):** Completed exam with errors. Score: **6 / 20 (30% - Failed)**.
4. **Student D (Missed Exam):** Attempt status: `missed`.

## 3. Verification of Results & Timestamps
- Attempt start time, finish time, and duration calculated properly.
- No exam questions or correct answers leaked to student before submission.
- Unattempted questions handled correctly without division-by-zero errors.
