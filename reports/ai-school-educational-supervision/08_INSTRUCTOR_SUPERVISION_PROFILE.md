# 08 — Instructor Supervision Profile & Academic Dossier

## 1. Overview
The Instructor Supervision Profile (`/supervisor/instructor-profiles`) aggregates all educational, supervisory, and operational records of an instructor into a unified 360-degree academic dossier.

## 2. Core Architectural Constraint: Zero Financial/Salary Exposure
> **Strict Separation Rule:** The Educational Supervision Profile must NEVER display, query, or expose instructor financial data, bank account numbers, base salary, hourly rates, calculated earnings, or payroll disbursements.

Educational supervisors evaluate pedagogical quality, syllabus adherence, student engagement, and compliance. Financial compensation and payroll are strictly restricted to the Finance, HR, and Administrative departments.

## 3. Profile Data Aggregation
The profile queries and displays:
1. **Assigned Groups & Courses:** List of groups currently taught by the instructor within the supervisor's branch.
2. **Attendance Reliability:**
   - Total scheduled sessions.
   - Completed sessions with attendance recorded on time (< 24 hrs).
   - Sessions with late attendance submission or missing attendance.
3. **Homework Management:**
   - Number of assignments posted.
   - Average submission rate.
   - Grading turnaround time (percentage reviewed within 48 hours).
4. **Examination Standards:**
   - Exams scheduled and conducted.
   - Student pass rate and grade distribution.
5. **Supervisory Observation Record:**
   - Active, waiting review, and completed observations.
   - Frequency of recurring issues.
6. **Formal Evaluation History:**
   - Published evaluations over previous months.
   - Overall rating trend line (1.0 to 5.0).
   - Documented strengths, areas for improvement, and management recommendations.
