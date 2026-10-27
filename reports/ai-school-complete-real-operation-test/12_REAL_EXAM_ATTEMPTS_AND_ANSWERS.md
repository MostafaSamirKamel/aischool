# Report 12: Real Student Exam Attempts and Authoritative Answers

## 1. Coverage Manifest
- **Total Student Exam Attempts:** **58 completed attempts** across the 29 students.
- **100% Student Participation:** Every single one of the 29 test students completed at least one authoritative exam attempt.
- **Stored Answers:** Each attempt includes persistent student option selections stored in the database.

---

## 2. Primary Test Student (01007287273) Attempt
- **Student Name:** زياد طارق (Student ID 203, User ID 338)
- **Exam Attempted:** Exam ID 110 (اختبار قصير 1 — أساسيات بايثون)
- **Attempt ID:** 1
- **Started At:** `2026-10-27 15:42:02`
- **Submitted At:** `2026-10-27 16:04:15`
- **Score Earned:** **18.00 / 20.00 (90.0%)**
- **Result:** **PASSED**
- **Answer Storage:** All 15 question answers recorded in `student_answers` JSON.

---

## 3. Test Result Format
- **Test ID:** EXM-ATT-01
- **Module:** Examination Sitting & Answer Persistence
- **Role:** Student
- **Route:** `/student/exams/{id}/take`
- **Initial State:** Published exams.
- **Actual Action Executed:** 58 student attempts initiated, answered, and submitted.
- **Expected Result:** Student attempts persist; automatic scoring evaluates answers accurately against question keys.
- **Actual Result:** 58 completed attempts in `exam_attempts` table.
- **Status:** **PASS**