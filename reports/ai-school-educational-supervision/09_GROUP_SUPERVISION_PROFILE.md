# 09 — Group Supervision Profile & Cohort Health

## 1. Overview
The Group Supervision Profile (`/supervisor/group-profiles`) provides supervisors with real-time academic health metrics for each student group/class cohort in the branch.

## 2. Cohort Health Dimensions

### 2.1 Group Metadata & Scope
- Group Code & Course Title.
- Assigned Instructor.
- Branch / Tenant ID.
- Assigned Supervisor or Secretary.
- Active Student Enrollment Count.

### 2.2 Academic Progression Metrics
- **Syllabus Progress:** Completed sessions vs. total planned sessions in the course curriculum.
- **Attendance Integrity:** Group attendance rate (Present / (Present + Absent)), tracking student absenteeism spikes.
- **Unrecorded Attendance Sessions:** Highlights any session dates where attendance remains unlogged by the instructor.

### 2.3 Homework & Exam Attainment
- Group average homework submission rate.
- Group average homework score.
- Group exam pass rate (students scoring $\ge$ 50%).
- Distribution of low-performing students requiring pedagogical intervention.

### 2.4 Follow-Up Cases & Student Retention
- Active student follow-up tickets for group members (from `student_follow_ups` table).
- Retention alerts (students missing 2+ consecutive sessions).

## 3. Scope Isolation
A designated branch supervisor sees all groups in their branch. A limited supervisor or secretary sees only those groups specifically assigned to them in `supervision_group_assignments`.
