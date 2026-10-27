# 08 — EXAM CREATION, ATTEMPTS & OPTIONAL SESSION LINK

## 1. Optional Session Association Rule
In strict adherence to the approved architecture:
- **Session-Specific Exam:** An exam can link to an `AttendanceSession` via `exams.attendance_session_id`.
- **Comprehensive Group Exam:** An exam covering the entire course curriculum does **NOT** require a session link (`attendance_session_id = NULL`).
- No fake or guessed sessions are generated for comprehensive exams.

## 2. Multi-Step Exam Creation UI (Tab 2: الامتحانات)
1. **Step 1 — Basic Settings:** Title, course, group, optional session, exam date, duration in minutes, total marks, passing marks, and instructions.
2. **Step 2 — Question Management:** Question text, question type (MCQ, True/False, Essay), options, correct answers, and individual marks.
3. **Step 3 — Review & Policy:** Total marks aggregation, publication policy preview, draft saving.

## 3. Question Immutability Guard
- Once any student starts an attempt on an exam (`ExamAttempt::where('exam_id', $id)->exists()`):
  - Direct destructive editing of questions, answer keys, or total marks is strictly blocked.
  - The UI alerts the instructor that attempts have commenced and changes must follow authorized assessment correction procedures.

## 4. Exam Attempts Roster
When viewing an exam, the student roster displays:
- Number of attempts per student.
- Status: `لم يبدأ بعد` (gray), `جاري أداء الاختبار` (amber), `تم إتمام الاختبار` (green).
- Automatic grading breakdown (for MCQ/True-False) and manual grading inputs (for essays).
- Official score display (`score / total_marks`).
- Action to view full question-by-question attempt history.
