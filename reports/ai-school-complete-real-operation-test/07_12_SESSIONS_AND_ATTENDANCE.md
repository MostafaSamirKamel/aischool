# Report 07: 12 Scheduled Sessions and Attendance Operations

## 1. Schedule Manifest
Exactly 4 sessions per group were generated (total 12 sessions across the 3 groups):

| Schedule ID | Group Name | Session # | Staging Date | Session Status | Attendance Status |
|---|---|---|---|---|---|
| 2646 | Group A — Python Junior | Session 1 | 2026-10-08 | completed | Recorded (ID 260) |
| 2647 | Group A — Python Junior | Session 2 | 2026-10-15 | completed | Recorded (ID 261) |
| 2648 | Group A — Python Junior | Session 3 | 2026-10-22 | completed | Recorded (ID 262) |
| 2649 | Group A — Python Junior | Session 4 | 2026-11-05 | scheduled | Unrecorded (Scheduled) |
| 2650 | Group B — HTML & CSS | Session 1 | 2026-10-08 | completed | Recorded (ID 263) |
| 2651 | Group B — HTML & CSS | Session 2 | 2026-10-15 | completed | Recorded (ID 264) |
| 2652 | Group B — HTML & CSS | Session 3 | 2026-10-22 | completed | Recorded (ID 265) |
| 2653 | Group B — HTML & CSS | Session 4 | 2026-11-05 | scheduled | Unrecorded (Scheduled) |
| 2654 | Group C — Scratch | Session 1 | 2026-10-09 | completed | Recorded (ID 266) |
| 2655 | Group C — Scratch | Session 2 | 2026-10-16 | completed | Recorded (ID 267) |
| 2656 | Group C — Scratch | Session 3 | 2026-10-23 | completed | Recorded (ID 268) |
| 2657 | Group C — Scratch | Session 4 | 2026-11-06 | scheduled | Unrecorded (Scheduled) |


---

## 2. Attendance Operation Breakdown
- **Completed Sessions:** 9 sessions (Sessions 1, 2, 3 for Groups A, B, and C).
- **Attendance Records Stored:** For each completed session, student attendance was recorded using authentic statuses (`present`, `absent`, `late`, `excused`).
- **Unrecorded Sessions:** Session 4 for each group remains in `scheduled` status with unrecorded attendance to verify that pending sessions do not falsely register as completed or trigger instructor salary entitlements.

---

## 3. Test Result Format
- **Test ID:** SES-ATT-01
- **Module:** Session Scheduling & Attendance Tracking
- **Role:** Instructor / Supervisor
- **Route:** `/instructor/groups/{id}/attendance`, `/admin/attendance-sessions`
- **Initial State:** 12 session schedules in database.
- **Actual Action Executed:** Recorded attendance for 9 completed sessions; refreshed instructor and supervisor views.
- **Expected Result:** Attendance persists; student profiles display attendance rate; scheduled sessions remain unrecorded.
- **Actual Result:** 9 attendance sessions created; hundreds of attendance records persisted in `attendance_records`.
- **Status:** **PASS**