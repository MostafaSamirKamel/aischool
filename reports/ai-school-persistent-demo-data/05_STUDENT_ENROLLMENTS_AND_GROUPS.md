# 05 — Student Enrollments & Group Assignments

## Overview
This document specifies how the existing 111 students were mapped into valid academic structures, courses, and group cohorts without violating database constraints or group capacities.

---

## 1. Group Structure & Capacity Overview

The AI School system operates with structured groups linked to specific courses, instructors, and branches:

| Group Name | Course | Instructor | Branch | Capacity | Active Enrollments | Status |
|---|---|---|---|---|---|---|
| **Physics Group A** | Physics Secondary 1 | د. عصام المنشاوي | Branch 1 | 35 | 32 | Active |
| **English Advanced B** | English Level 3 | أ. سارة عبد الرحمن | Branch 1 | 30 | 28 | Active |
| **Math Prep C** | Mathematics Secondary 1 | م. أحمد فوزي | Branch 2 | 35 | 31 | Active |
| **Science Alpha D** | General Science Prep 3 | أ. هاني عبد العظيم | Branch 2 | 30 | 26 | Active |

---

## 2. Enrollment Archetypes & Scenarios

A total of **34 new enrollments** were generated and attached to existing students:

### A. Dual Course Enrollments (10 Students)
- **Students**: ID 1 to 10
- **Configuration**: Each student was registered in a secondary group belonging to a different course (e.g. Student 1 is enrolled in both Physics Group A and English Advanced B).
- **Business Rule Verification**:
  - The student is tracked as **one single human identity** in student growth and distinct student counts.
  - The student generates **two independent enrollment tracks**, separate attendance records, and isolated academic performance evaluations.

### B. Course Renewals & Next-Term Registrations (10 Students)
- **Students**: ID 11 to 20
- **Configuration**: Students who completed or were enrolled in Term 1 were enrolled in the subsequent group/level.
- **Constraint Compliance**:
  - Respected SQLite table constraint: `UNIQUE(student_id, group_id)`.
  - Renewal enrollments were assigned to new groups where `whereNotIn('group_id', $existingGroupIds)` applied.

### C. Standard Active Enrollments (2 Students)
- **Students**: ID 21 and 22
- **Configuration**: Regular single-course active enrollments with ongoing installment schedules.

### D. Enrollment Cancellations & Settlements (12 Students)
- **Students**: ID 23 to 34
- **Configuration**: Marked with `status = 'cancelled'`.
- **Linked Records**:
  - Each cancelled enrollment has a corresponding entry in `enrollment_cancellations` detailing cancellation reason, waived fees, retained registration fees, and refundable balances.

---

## 3. Database Integrity & Validation Checks

- **Compound Key Uniqueness**: Verified zero violations of `(student_id, group_id)`.
- **Branch Matching**: Verified that all student enrollments match the authorized branch of the corresponding group.
- **Reporting Sync**: Verified that the Enrollment Report correctly combines initial enrollments and renewals into the aggregate enrollment metric while maintaining accurate unique student counts.
