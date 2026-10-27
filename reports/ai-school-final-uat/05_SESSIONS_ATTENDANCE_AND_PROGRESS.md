# 05 — SESSIONS, ATTENDANCE AND ACADEMIC PROGRESS

**Audit Stage:** Stage D & Stage E  
**Lead Actor:** Educational Supervisor & Course Instructor  
**Verification Method:** Automated End-to-End Execution (`MasterProductionAcceptanceTest::test_stage_d_and_e_sessions_and_attendance_integrity`)

---

## 1. Stage D — Academic Scheduling and Session Lifecycle

### Test Steps & Scenario
1. Scheduled regular session for Group PY-01 (4 enrolled students: S1, S2, S3, S4).
2. Rescheduled an upcoming session with updated date/time.
3. Created an approved makeup session (`is_makeup = true`).
4. Monitored session state transitions across instructor, supervisor, and student dashboards.

### Verification Results
- **Schedule Integrity:** Date, start time, end time, and instructor association stored accurately.
- **Rescheduling Audit:** Rescheduled sessions maintain original history without creating orphan records.
- **Makeup Sessions:** Makeup session correctly tagged without triggering double-entitlement in instructor salary calculations.

---

## 2. Stage E — Attendance Recording & The "Unrecorded" Invariant

### Critical Rule Verified
> **"Attendance not recorded must NOT count as confirmed absence."**

### Test Steps & Verification
1. **Recorded Session:**
   - Instructor submitted attendance for Session 1:
     - Student 1: `present`
     - Student 2: `present`
     - Student 3: `present`
     - Student 4: `absent`
   - Verified 4 explicit `AttendanceRecord` rows inserted.
   - Student 4 absence rate incremented; Students 1-3 presence rate incremented.
2. **Unrecorded Session:**
   - Created Session 2 for the same group.
   - Left attendance completely unrecorded (zero `AttendanceRecord` entries created).
   - Re-evaluated Student 1, 2, 3, and 4 absence counts across backend queries and dashboard metrics.
   - **Result:** Student 1, 2, 3 registered **0 additional absences**. Student 4 registered **0 additional absences**.
   - Verified that total absent sessions equals strictly `COUNT(attendance_records WHERE status = 'absent')`.

---

## 3. Acceptance Result
- **Stage D (Scheduling):** **PASS**
- **Stage E (Attendance):** **PASS** (Zero false absences from unrecorded sessions).
