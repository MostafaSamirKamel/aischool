# 06 — Sessions & Attendance Records

## Overview
This document details the generation of academic sessions and granular student attendance logs across historical and upcoming schedules, verifying realistic operational distributions and compliance with AI School attendance policies.

---

## 1. Attendance Sessions Master Summary

A total of **156 new attendance sessions** were generated across the 4 primary academic groups:

| Session Category | Past / Future Window | Status | Count | Operational Purpose |
|---|---|---|---|---|
| **Completed Sessions** | Past 6 Weeks (T-42 to T-1) | `completed` | 136 | Provides rich historical attendance and payroll baseline |
| **Upcoming Scheduled Sessions** | Next 2 Weeks (T+1 to T+14) | `scheduled` | 16 | Appears in student/parent calendar and instructor agenda |
| **Postponed Sessions** | Historical Window | `postponed` | 2 | Demonstrates rescheduling and session notices |
| **Cancelled Sessions** | Historical Window | `cancelled` | 2 | Demonstrates emergency cancellation logs |
| **Total New Sessions** | — | — | **156** | Total in Database: **177 sessions** |

---

## 2. Granular Attendance Records Distribution

A total of **375 attendance records** were generated for eligible enrolled students attending the completed sessions:

| Attendance Status | Database Value | Record Count | Percentage | Real-World Scenario |
|---|---|---|---|---|
| **Present (حاضر)** | `present` | **293** | 69.6% | Dedicated students with steady attendance |
| **Late (متأخر)** | `late` | **83** | 19.7% | Recorded arrival delay (5–20 minutes) |
| **Absent (غائب)** | `absent` | **45** | 10.7% | Confirmed absence triggering follow-up alerts |
| **Total In Database** | — | **421** | 100.0% | *(Includes 46 baseline records)* |

> [!IMPORTANT]
> **Zero False Absences**:
> Upcoming scheduled sessions (16 sessions) have **no attendance records created yet**, adhering strictly to the principle: *Never interpret missing or upcoming attendance as confirmed absence.*

---

## 3. Cross-Portal Visibility Verification

The persisted session and attendance data was verified across all system interfaces:
1. **Instructor Panel**:
   - Instructors can view past sessions, mark/adjust attendance, and view upcoming class schedules.
2. **Student Portal**:
   - Students see an accurate timeline of attended classes, late penalties, and absence records.
3. **Parent Portal**:
   - Parents receive a breakdown of their child's attendance rate, with immediate alerts on consecutive absences.
4. **Attendance & Absence Reports**:
   - Aggregate statistics calculate dynamically from the `attendance_records` table, correctly differentiating between excused, late, and unexcused absences.
