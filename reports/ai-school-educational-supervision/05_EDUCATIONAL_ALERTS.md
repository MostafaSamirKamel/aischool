# 05 — Educational Alerts System & Quality Control

## 1. Architectural Philosophy: Aggregation vs. Automated Escalation
A critical design requirement of the AI School Educational Supervision System is:
> **Strict Read-Only Educational Alerts:** The alerts system acts as an observational intelligence instrument. It MUST NOT automatically generate disciplinary actions, deduct instructor salaries, or auto-create observation cases without human supervisory judgment.

Automating disciplinary actions causes operational paralysis and false positives (e.g., technical connectivity issues or rescheduled classes mistakenly triggering severe penalties). The supervisor remains the central human decision-maker.

## 2. Monitored Alert Categories
The alert engine in `EducationalSupervisionService::getEducationalAlerts()` evaluates real branch data across four primary categories:

### 2.1 Attendance & Session Delivery Alerts
- **Unrecorded Attendance Alert:** Triggers when a session date has passed but zero attendance records exist for the group. Provides a direct action link to remind the instructor.
- **Consecutive Student Absence Alert:** Triggers when a student accumulates 2 or more unexcused absences, enabling proactive pedagogical retention.

### 2.2 Homework & Grading Quality Alerts
- **Grading Lag Alert:** Flags assignments where student submissions have remained unreviewed for more than 48 hours.
- **Low Homework Submission Rate Alert:** Identifies groups where less than 60% of enrolled students submitted their mandatory homework.

### 2.3 Examination & Academic Attainment Alerts
- **Unverified Exam Attempts:** Alerts supervisor when exam submissions linger without grading or publication.
- **Low Exam Performance Alert:** Triggers when more than 30% of students in a single group score below 50% on an exam, indicating potential curriculum or delivery deficiencies.

### 2.4 Observation & Compliance Deadlines
- **Overdue Observation Alert:** Highlights supervisory observations that have crossed their `expected_resolution_date` while still in status `new` or `waiting_supervisor_review`.

## 3. Dedicated Alerts Center (`/supervisor/educational-alerts`)
- Accessible via the navigation menu in the Supervisor Panel.
- Color-coded severity chips (`high`, `medium`, `low`).
- Direct action buttons: "إنشاء ملاحظة إشرافية" (Create Supervisory Observation), "معاينة الحصة" (Inspect Session), or "ملف المهندس" (Instructor Profile).
