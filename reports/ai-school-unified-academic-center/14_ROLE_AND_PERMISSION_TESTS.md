# 14 — ROLES & PERMISSIONS VERIFICATION MATRIX

## 1. Permission Matrix & Delegation Ceilings

| Capability | Super Admin / Owner | Branch Manager | Instructor | Educational Supervisor | Secretary | Student | Parent |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **View Group Academic Center** | Allowed | Allowed | Allowed (Own Groups) | Allowed | Restricted | Restricted | Restricted |
| **Create & Edit Homework** | Allowed | Allowed | Allowed (Own Groups) | View Only | Blocked | Blocked | Blocked |
| **Grade Homework ("تم التصحيح")** | Allowed | Allowed | Allowed (Own Groups) | Blocked | Blocked | Blocked | Blocked |
| **Save Grading Draft** | Allowed | Allowed | Allowed (Own Groups) | Blocked | Blocked | Blocked | Blocked |
| **Correct Published Grade** | Allowed | Allowed | If Permitted (Audited) | If Permitted (Audited) | Blocked | Blocked | Blocked |
| **Extend Group Deadline** | Allowed | Allowed | If Permitted | If Permitted | Blocked | Blocked | Blocked |
| **Reopen Student Submission** | Allowed | Allowed | If Permitted | If Permitted | Blocked | Blocked | Blocked |
| **Grant Homework Resubmission**| Allowed | Allowed | If Permitted | If Permitted | Blocked | Blocked | Blocked |
| **Create & Edit Exams** | Allowed | Allowed | Allowed (Own Groups) | View Only | Blocked | Blocked | Blocked |
| **Grant Exam Retake** | Allowed | Allowed (Ceiling) | If Delegated | If Delegated | Blocked | Blocked | Blocked |
| **Toggle Exam Publication** | Allowed | Allowed | If Permitted | If Permitted | Blocked | Blocked | Blocked |
| **Submit Homework** | Blocked | Blocked | Blocked | Blocked | Blocked | Allowed (Eligible) | Blocked |
| **Take Exam** | Blocked | Blocked | Blocked | Blocked | Blocked | Allowed (Eligible) | Blocked |
| **View Published Grades** | Allowed | Allowed | Allowed | Allowed | View Only | Allowed (Self) | Allowed (Linked) |

## 2. Server-Side Enforcement Tests
- **Foreign Group Isolation:** Tested that an instructor assigned to Group A cannot open or grade assignments for Group B (`test_010_unauthorized_instructor_cannot_access_foreign_assignment` => PASSED).
- **Secretary Grading Protection:** Tested that a secretary attempting to grade homework throws `AuthorizationException` (`test_011_secretary_cannot_grade_homework` => PASSED).
- **Branch / Tenant Isolation:** Ensured all student, exam, and homework queries are scoped to the active tenant.
