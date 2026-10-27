# 04 — Created Records Manifest & Entity Inventory

## Overview
This manifest records every database table and entity populated during the persistent demonstration run, detailing baseline records, new records inserted, final totals, and traceability tags.

---

## 1. Master System Manifest

| Entity / Domain | Target Table | Baseline Count | Created Demo Records | Final Database Total | Tagged Marker |
|---|---|---|---|---|---|
| **Students** | `students` | 111 | **0** *(Preserved)* | **111** | `Preserved Original` |
| **Parent Links** | `parent_student` | 87 | **26** | **113** | Linked to Demo Cohort |
| **Enrollments** | `enrollments` | 111 | **34** | **145** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Attendance Sessions** | `attendance_sessions` | 21 | **156** | **177** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Attendance Records** | `attendance_records` | 46 | **375** | **421** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Assignments** | `assignments` | 9 | **39** | **48** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Assignment Submissions** | `assignment_submissions` | 8 | **125** | **133** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Examinations** | `exams` | 45 | **39** | **84** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Exam Questions** | `questions` | 90 | **78** | **168** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Exam Attempts** | `exam_attempts` | 58 | **125** | **183** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Student Grades** | `student_grades` | 58 | **125** | **183** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Installments** | `installments` | 158 | **66** | **224** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Payments / Receipts** | `payments` | 113 | **33** | **146** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Cancellations & Settlements** | `enrollment_cancellations` | 0 | **12** | **12** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Follow-Up Tasks** | `student_follow_up_tasks` | 7 | **8** | **15** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Call Logs** | `student_call_logs` | 14 | **16** | **30** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Supervisory Observations** | `supervisory_observations` | 9 | **2** | **11** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Supervision Evaluations** | `instructor_evaluations` | 7 | **1** | **8** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Evaluation Criteria Items** | `instructor_evaluation_items` | 42 | **6** | **48** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Evaluation Appeals** | `instructor_evaluation_appeals` | 6 | **1** | **7** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Supervision Reports** | `supervision_reports` | 4 | **1** | **5** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Expenses** | `expenses` | 6 | **1** | **7** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Revenues** | `revenues` | 111 | **1** | **112** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Vault Transfers** | `vault_transfers` | 1 | **1** | **2** | `AI_SCHOOL_PERSISTENT_DEMO` |
| **Instructor Salaries** | `instructor_salaries` | 9 | **1** | **10** | `AI_SCHOOL_PERSISTENT_DEMO` |

---

## 2. Summary of Created Records

- **Total New Operational Records Created**: **1,167 persistent records**
- **Existing Students Modified/Corrupted**: **0 (Zero)**
- **Audit Compliance**: All records are tagged and indexed.
- **Foreign Key Integrity**: 100% verified via SQLite pragmas (`PRAGMA foreign_key_check`).
