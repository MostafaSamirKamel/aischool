# 13 — ACADEMIC ALERTS & EDUCATIONAL SUPERVISION REPORTS

## 1. Low-Grade Threshold Policy
- **Strict Rule:** An academic warning is triggered **strictly when a published score is below 50.0%** of the maximum possible score (`(score * 2) < max_score`).
- **Boundary Verification:**
  - `4.9 / 10.0` (49.0%): **TRIGGERS ALERT** (`⚠️ تحت 50%`).
  - `5.0 / 10.0` (50.0%): **NO ALERT** (Passing grade, alert is FALSE).
  - `5.1 / 10.0` (51.0%): **NO ALERT** (Passing grade, alert is FALSE).
- **Recalculation:** When a published score is corrected or an authorized re-submission yields a higher official score, the alert is immediately re-evaluated. If the new official score is `>= 50%`, the warning badge clears automatically.

## 2. No Automatic Task Generation Rule
- Low-grade alerts do **NOT** spawn fake or unwanted automated manual follow-up tasks in `student_follow_up_tasks`.
- Educational supervisors view accurate academic indicators on the dashboard and initiate follow-up manually when pedagogically warranted.
- Follow-up statuses remain authoritative: `جديدة` (New) and `تم التواصل` (Contacted).

## 3. Educational Supervision Integration
- Supervision reports at `/admin/supervision-reports` query authoritative live counts of:
  - Attendance rates per session.
  - Group-wide homework submission rates.
  - Exam participation and average scores.
  - Students requiring academic support.
