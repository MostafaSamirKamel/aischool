# 01 — INSTALLMENT REPORTS AUDIT (DAILY, WEEKLY, MONTHLY)

## 1. Scope & Objective
This audit analyzes the three installment report interfaces in AI School:
- **Daily Report:** `/admin/installments-due-today` (`app/Filament/Admin/Pages/InstallmentsDueToday.php`)
- **Weekly Report:** `/admin/installments-due-week` (`app/Filament/Admin/Pages/InstallmentsDueWeek.php`)
- **Monthly Report:** `/admin/installments-due-month` (`app/Filament/Admin/Pages/InstallmentsDueMonth.php`)

The investigation focused on auditing the existing frontend controls, Livewire bindings, data source consistency, date boundaries, and backend permission enforcement.

---

## 2. Page-by-Page Audit Findings

### A. Daily Report (`/admin/installments-due-today`)
- **View Template:** `resources/views/filament/admin/pages/installments-due-today.blade.php`
- **Frontend Controls:**
  - Metric summary cards: Total Due Today, Total Collected, Remaining Balance, Installment Count.
  - Data table: Student code, student name, course, installment number, original amount, paid amount, remaining balance, due date, status badge.
  - Payment Action Button ("تحصيل"): Triggers `wire:click="openPaymentModal({{ $installment->id }})"`.
  - Payment Modal: Amount input, vault select, payment date, notes, receipt reference, submit button.
- **Pre-Fix Defect:**
  - Date query used `where('due_date', $today)` which evaluated string equality against timestamps (`YYYY-MM-DD 00:00:00`), returning 0 rows in SQLite.
  - In-memory `Payment::$status` was null upon creation, bypassing `PaymentObserver`.
  - Payment collection was accessible to users with only `view_reports` permission.

### B. Weekly Report (`/admin/installments-due-week`)
- **View Template:** `resources/views/filament/admin/pages/installments-due-week.blade.php`
- **Frontend Controls:**
  - Period Filter Tabs: "الكل" (All), "اليوم" (Today), "المتأخرة" (Overdue), "القادمة" (Upcoming).
  - Week Navigation: Previous week / next week controls.
  - Daily Accordions: Grouping installments by active day of the week.
  - Payment Action Button ("تحصيل") on each row.
- **Pre-Fix Defect:**
  - Weekly statistics duplicated calculation logic independently from daily/monthly reports.
  - Missing branch scoping on vault selection in the modal.

### C. Monthly Report (`/admin/installments-due-month`)
- **View Template:** `resources/views/filament/admin/pages/installments-due-month.blade.php`
- **Frontend Controls:**
  - Month/Year selector dropdowns.
  - Monthly Aggregate Cards: Month Total, Month Paid, Month Remaining.
  - Data table with status filters (`all`, `pending`, `partial`, `paid`, `overdue`).
  - Row collection action.
- **Pre-Fix Defect:**
  - Status classification logic in observer diverged from `Installment::updateStatus()`, causing overdue installments with partial payments to be falsely classified as pending.

---

## 3. Strict UI Preservation Audit
- **Layouts & Blade Structure:** Unchanged. All div hierarchies, hero sections, stat cards, and table headers preserved 100%.
- **Colors, Fonts, Badges:** Unchanged (`inst-badge--paid`, `inst-badge--partial`, `inst-badge--pending`).
- **Filament Navigation & URLs:** Unchanged.
