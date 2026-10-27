# AI SCHOOL — STUDENT GROUP MEMBERSHIP AND ATTENDANCE AUDIT
**Document ID:** `09_STUDENT_GROUP_AND_ATTENDANCE.md`  
**Execution Date:** 2026-10-25  
**Audit Scope:** Group Synchronization, Multi-Course Independence, Capacity Recalculation, Attendance Integrity  

---

## 1. Executive Summary

This audit examines the synchronization between student course enrollments, educational group memberships, classroom capacity enforcement, and attendance logging.

The system enforces strict multi-course independence and preserves all historical attendance logs even when students transfer groups or cancel subscriptions.

---

## 2. Group Membership and Multi-Course Independence

### 2.1 Multi-Course Independence Architecture
- A student may be simultaneously enrolled in multiple courses (e.g., Arabic, English, Mathematics).
- Each enrollment references a distinct `course_id` and assigned `group_id`.
- Modifying, transferring, or canceling enrollment in Course A has zero side-effects on Course B.

### 2.2 Group Capacity Synchronization
- Each `Group` maintains a `max_students` limit.
- Current active membership is computed by:
  ```php
  $activeCount = $group->enrollments()->where('status', 'active')->count();
  ```
- **Capacity Overflow Prevention:** When assigning a student to a group, the system verifies `activeCount < max_students`.
- When an enrollment is canceled or transferred, the seat is immediately freed for new enrollments.

### 2.3 Student Group Transfer
- Transfers are supported between groups within the same course and branch.
- Upon transfer, the existing group enrollment record is closed, and a new group assignment is attached.
- Historical attendance records linked to the previous group remain intact.

---

## 3. Attendance Recording and Status Fidelity

### 3.1 Four Discrete Attendance States
The system strictly distinguishes between unrecorded sessions and confirmed absences:
1. `present` (حاضر): Student confirmed present in the session.
2. `absent` (غائب): Student confirmed absent without authorized excuse. Triggers absence tracking.
3. `excused` (معذور): Student absence authorized with documented medical or personal excuse.
4. `unrecorded` (لم تسجل): Session scheduled but teacher has not yet submitted attendance. **Crucial Rule:** Unrecorded sessions are never counted as confirmed absences in student or parent reports.

### 3.2 Roster Exclusion for Canceled Enrollments
- Once an enrollment is marked `cancelled`, queries for active attendance rosters filter them out (`where('status', 'active')`).
- Canceled students no longer appear on daily teacher attendance sheets.

---

## 4. Test Verification Evidence

- `Tests\Feature\MasterProductionAcceptanceTest::test_stage_d_and_e_sessions_and_attendance_integrity` (**PASSED**)
- `Tests\Feature\StudentGroupTransferTest` (**PASSED**)

---

## 5. Audit Verdict

**STATUS: PASS**  
Group membership synchronization, capacity limits, multi-course independence, and attendance record preservation are fully verified and operating cleanly.
