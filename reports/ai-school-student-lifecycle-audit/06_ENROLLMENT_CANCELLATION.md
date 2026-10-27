# 06 — ENROLLMENT CANCELLATION & GROUP REMOVAL AUDIT

## 1. Authorized Workflow Execution
- Cancellation initiated via `EnrollmentCancellationService::cancelSubscription`.
- Reason: Family relocation out of province.
- Status changed: `active` $\rightarrow$ `cancelled`.
- Audit record created in `enrollment_cancellations` with full financial snapshot.

## 2. Immediate Group Roster Removal
1. **Active Roster Exclusion**:
   - `Group::activeStudents()` automatically filters out cancelled enrollments.
   - Active student count decreased from 1 to 0.
   - Available group seat released immediately.
2. **Future Attendance Protection**:
   - Attendance roster (`AttendanceSessionForm`) dynamically queries `$group->activeStudents()`.
   - Cancelled student will not appear on future session attendance rosters.
3. **Academic History Preservation**:
   - Student's main school profile is **NOT** deleted.
   - All 3 past attendance records remain 100% intact in the database.
   - Exam grade (95/100, A+) remains 100% intact.

## 3. Test Verdict: PASS
Group capacity updated, student removed from active roster, academic records preserved.