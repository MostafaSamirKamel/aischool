# 21 — Bugs Identified, Fixed & Retested

## 1. Defect Catalog
### Bug ID: BUG-REP-001 — Ambiguous SQL Column in Collections Reporting
- **File:** [ReportingCenterPage.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Filament/Admin/Pages/ReportingCenterPage.php)
- **Severity:** High (Crashed Reporting Center upon accessing collections report)
- **Root Cause:** Raw join query omitted table prefix for `tenant_id` and `vault_id`.
- **Fix:** Qualified all joined fields (`payments.tenant_id`, `payments.vault_id`, `payments.status`, `payments.payment_date`).
- **Retest Result:** **PASS**. Collections report renders in < 15ms.

### Bug ID: BUG-UI-002 — Exam Grade Form Responsive Overflow
- **File:** [GradeForm.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Filament/Admin/Resources/Grades/Schemas/GradeForm.php)
- **Severity:** Medium (Text clipping and boundary overflow with long Arabic student & exam names on mobile/modals)
- **Root Cause:** Rigid `Grid::make(2)` and `Grid::make(3)` without responsive breakpoints, squishing labels.
- **Fix:** Upgraded grids to `Grid::make(['default' => 1])` with `columnSpanFull()` for exam selection, and responsive `['default' => 1, 'sm' => 3]` for score metrics.
- **Retest Result:** **PASS**. Clean presentation on all viewport widths (320px to 1920px).

## 2. Regression Testing Summary
Executed full automated test suites post-fix:
- `ReportingCenterIntegrationTest`: 11 / 11 Passed.
- `MasterProductionAcceptanceTest`: 11 / 11 Passed.
- `EducationalSupervisionFullSystemTest`: 12 / 12 Passed.
- `DailyStudentFollowUpCenterTest`: 22 / 22 Passed.
- `CancelSubscriptionLivewireActionTest`: 42 / 42 Passed.
