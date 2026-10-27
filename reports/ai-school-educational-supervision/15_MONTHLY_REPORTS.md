# 15 — Monthly Supervision Reports & Strategic Quality Review

## 1. Overview & Objective
Monthly Supervision Reports (`report_type = 'monthly'`) provide executive management with macro-level insights into branch academic health, curriculum pacing, instructor retention, exam attainment, and pedagogical quality.

## 2. Code Generation & Scope
- **Report Code Convention:** `SR-MO-{YEAR}-{MONTH_NUMBER}-{RANDOM}` (e.g., `SR-MO-2026-10-82C4`).
- **Period Calculation:** Bounded by the first day and last day of the calendar month (e.g., `2026-10-01` to `2026-10-31`).
- **Idempotency Guarantee:** Multiple requests for the same month return the existing report draft.

## 3. Comprehensive Monthly Analytics Snapshot
The monthly report aggregates deep metrics:
1. **Total Sessions Conducted:** Full monthly throughput across all courses.
2. **Attendance Reliability:** Branch-wide monthly attendance percentage.
3. **Homework Compliance:** Overall submission and grading compliance rates.
4. **Exam Results Distribution:** Student distribution across performance bands (A $\ge 90\%$, B $75-89\%$, C $50-74\%$, Fail $< 50\%$).
5. **Supervisory Observations Summary:** Total logged, addressed, resolved, and recurring.
6. **Instructor Monthly Evaluations Summary:** Distribution of instructor ratings across the 6 core criteria.

## 4. Strategic Narrative & Action Plan
Supervisors provide detailed qualitative sections:
- `curriculum_pacing_review`: Analysis of courses ahead or behind schedule.
- `instructor_performance_highlights`: Commendations for top educators.
- `targeted_remedial_plans`: Remedial classes or supplementary workshops planned for struggling cohorts.
- `strategic_recommendations`: Staffing suggestions, pedagogical resources, or schedule optimizations.
