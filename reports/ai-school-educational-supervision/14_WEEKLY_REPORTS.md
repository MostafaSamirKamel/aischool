# 14 — Weekly Supervision Reports System

## 1. Overview & Objective
Weekly Supervision Reports (`report_type = 'weekly'`) provide high-frequency operational oversight over weekly teaching delivery, attendance discipline, homework correction speed, and emergent issues across all groups in a branch.

## 2. Code Generation & Scope
- **Report Code Convention:** `SR-WK-{YEAR}-{WEEK_NUMBER}-{RANDOM}` (e.g., `SR-WK-2026-42-A81B`).
- **Period Calculation:** Automatically snaps to Monday–Sunday or Saturday–Thursday depending on school operating calendar.
- **Idempotency Guarantee:** Calling `generateWeeklyReportDraft($user, $year, $weekNumber)` multiple times returns the existing draft if already initiated, preventing duplicate reports.

## 3. Real Snapshot Metrics Captured
Each weekly report freezes a comprehensive JSON snapshot of branch operations:
- Scheduled vs. completed sessions.
- Sessions missing attendance.
- Group-by-group attendance averages.
- Homework submitted vs. graded.
- Average grading turnaround time.
- Open observations created during the week.
- Resolved observations during the week.

## 4. Supervisor Narrative Sections
- **Executive Summary (`executive_summary`):** High-level operational overview.
- **Achievements (`achievements`):** Groups with perfect attendance or standout student engagement.
- **Challenges & Obstacles (`challenges`):** Syllabus delays, technical difficulties, or student absenteeism patterns.
- **Corrective Actions Taken (`recommendations`):** Interventions initiated with instructors or parents.

## 5. Workflow: Draft ➔ Published
The supervisor reviews the auto-populated metrics, edits the analytical narrative, and clicks **"نشر التقرير الأسبوعي"** (Publish Weekly Report). Once published, it becomes permanently accessible to Branch Management and Executive Directors in the Admin Panel.
