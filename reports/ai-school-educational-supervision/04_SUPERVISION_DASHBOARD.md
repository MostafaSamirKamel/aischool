# 04 — Educational Supervision Dashboard & Priority Queue

## 1. Overview
The Educational Supervision Dashboard located at `/supervisor` serves as the daily mission control center for the Branch Educational Supervisor. It replaces static administrative summaries with dynamic, action-oriented intelligence and live priority queues.

## 2. Real Operational Metrics (13 Live Metrics)
All metrics are computed on-the-fly in `EducationalSupervisionService::getDashboardMetrics()` without hardcoded mocks:

1. **Today's Scheduled Sessions (`today_scheduled_sessions`):** Academic sessions slated for the current calendar day in the supervisor's branch.
2. **Completed Sessions (`completed_sessions`):** Sessions where teaching concluded and attendance was recorded.
3. **Postponed Sessions (`postponed_sessions`):** Sessions rescheduled due to emergencies or holiday notices.
4. **Canceled Sessions (`canceled_sessions`):** Officially canceled sessions.
5. **Sessions Missing Attendance (`sessions_missing_attendance`):** High priority! Conducted sessions where the instructor has failed to submit attendance records within 24 hours.
6. **Confirmed Student Absences (`confirmed_student_absences`):** Authenticated absence count derived strictly from submitted attendance records.
7. **Homework Awaiting Grading (`homework_awaiting_grading`):** Total submitted student homework awaiting instructor review.
8. **Homework Overdue Grading (`homework_overdue_grading`):** Submissions unreviewed after 48 hours.
9. **Exams Awaiting Actions (`exams_awaiting_actions`):** Submitted exam attempts awaiting grading or verification.
10. **Published Low Academic Scores (`published_low_scores`):** Homework or exam grades published below 50% threshold.
11. **Open Supervisory Observations (`open_observations`):** Active observations in status `new`.
12. **Observations Waiting Review (`observations_waiting_review`):** Corrective actions completed by instructors awaiting supervisor approval.
13. **Evaluations Due This Month (`evaluations_due_this_month`):** Instructors requiring formal monthly evaluation.

## 3. Daily Priority Queue Widget (`DailyPriorityQueueWidget`)
The Daily Priority Queue algorithm evaluates all pending tasks across the branch and orders them into 4 urgency tiers:
- **Critical (Red):** Sessions missing attendance > 24 hours, overdue observation deadlines, repeated instructor non-responsiveness.
- **Urgent (Orange):** Homework uncorrected > 48 hours, student absence streaks (2+ sessions).
- **Attention (Yellow):** Published low exam scores (< 50%), observations in `waiting_supervisor_review`.
- **Informational (Blue):** Monthly evaluations approaching due date, upcoming exam milestones.

## 4. UI / UX Design Adherence
- Arabic RTL layout with native Tajawal typography.
- Filament stat cards with contextual badge colors (`danger`, `warning`, `success`, `info`).
- Responsive cards designed for desktop, tablet, and mobile views.
- Deep-links from priority items directly to the affected group, session, or observation for one-click intervention.
