# FRONTEND-BACKEND BINDING AUDIT

## 1. Audit Scope & Objective
This audit examines every interactive frontend element across the three installment report interfaces:
1. **Daily Report:** `/admin/installments-due-today` (`InstallmentsDueToday.php`)
2. **Weekly Report:** `/admin/installments-due-week` (`InstallmentsDueWeek.php`)
3. **Monthly Report:** `/admin/installments-due-month` (`InstallmentsDueMonth.php`)

The goal is to map every frontend control (button, input, modal trigger, filter) to its backend handler, services, observers, database mutations, and resulting UI state.

---

## 2. Interactive Control Mapping Table

### A. Daily Report (`/admin/installments-due-today`)

| Frontend Control | Wire / Event Binding | Backend Method | Service / Observer Invoked | Database Operation | Expected Result |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Header Refresh Button** | `wire:click="$refresh"` | Livewire component render cycle | `InstallmentDueReportService::getTodayInstallments()` | `SELECT * FROM installments WHERE date(due_date) = date('now')` | Refreshes installment records and metrics without full page reload. |
| **Total Due Metric Card** | Blade expression `$todayStats['total_amount']` | `getStatsProperty()` / `mount()` | `InstallmentDueReportService::getTodayStats()` | Aggregates `SUM(amount)` | Displays total currency sum due today. |
| **Collected Metric Card** | Blade expression `$todayStats['paid_amount']` | `getStatsProperty()` / `mount()` | `InstallmentDueReportService::getTodayStats()` | Aggregates `SUM(paid_amount)` | Displays total currency sum collected on today's installments. |
| **Remaining Metric Card** | Blade expression `$todayStats['remaining_amount']` | `getStatsProperty()` / `mount()` | `InstallmentDueReportService::getTodayStats()` | Aggregates `SUM(remaining_amount)` | Displays true outstanding balance for today's due dates. |
| **Count Metric Card** | Blade expression `$todayStats['count']` | `getStatsProperty()` / `mount()` | `InstallmentDueReportService::getTodayStats()` | `COUNT(*)` | Displays integer count of installments due today. |
| **Pay Action Button (تحصيل)** | `wire:click="openPaymentModal({{ $installment->id }})"` | `openPaymentModal($installmentId)` | `HandlesInstallmentDuePayments::openPaymentModal` | Reads installment with relations (`enrollment`, `student`) | Opens payment modal, populates default amount with remaining debt, fetches active vaults. |
| **Modal Amount Input** | `wire:model.defer="paymentAmount"` | Property `$paymentAmount` | Validation in `savePayment()` | None (in-memory state) | Holds user payment amount; validated against numeric, positive, and remaining balance. |
| **Modal Vault Dropdown** | `wire:model.defer="vaultId"` | Property `$vaultId` | `InstallmentDueReportService::getActiveVaults()` | Validates vault exists, belongs to tenant, and is active | Selects destination cash vault. |
| **Modal Date Picker** | `wire:model.defer="paymentDate"` | Property `$paymentDate` | Validation: `required\|date` | Sets transaction date on Payment record | Records payment date (defaults to current date). |
| **Modal Notes Textarea** | `wire:model.defer="paymentNotes"` | Property `$paymentNotes` | Validation: `nullable\|string\|max:500` | Stored in `payments.notes` | Optional remarks for financial auditor. |
| **Modal Submit (حفظ السداد)** | `wire:click="savePayment"` | `savePayment()` | `HandlesInstallmentDuePayments` -> `PaymentObserver` | `INSERT INTO payments`, `INSERT INTO revenues`, `UPDATE installments`, `UPDATE enrollments` | Commits collection, closes modal, displays success notification, refreshes table. |
| **Modal Cancel Button** | `wire:click="closePaymentModal"` | `closePaymentModal()` | `HandlesInstallmentDuePayments` | Clears modal properties | Closes modal without committing changes. |

---

### B. Weekly Report (`/admin/installments-due-week`)

| Frontend Control | Wire / Event Binding | Backend Method | Service / Observer Invoked | Database Operation | Expected Result |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Period Filter Tabs** (`all`, `today`, `overdue`, `upcoming`) | `wire:click="setFilter('{{ $key }}')"` | `setFilter($filter)` / `updatedInstallmentFilter()` | `InstallmentDueReportService::getWeekInstallmentsQuery()` | Dynamic query filter (`whereDate`, `where('status')`) | Swaps view without page refresh to filter by selected status within current week. |
| **Week Navigation Arrows** | `wire:click="previousWeek"` / `nextWeek"` | `changeWeek($offset)` | `Carbon::parse($selectedDate)->addWeeks()` | Recalculates start/end of week | Moves reporting window 7 days backward or forward. |
| **Day Accordion / Group Header** | Blade loop `@foreach($installmentsByDay as $day => $items)` | `getInstallmentsByDayProperty()` | `InstallmentDueReportService::getWeekInstallmentsByDay()` | Groups installments by `due_date` | Clean chronological display of installments day-by-day. |
| **Pay Action Button (تحصيل)** | `wire:click="openPaymentModal({{ $installment->id }})"` | `openPaymentModal($installmentId)` | `HandlesInstallmentDuePayments` | Same authoritative flow as Daily Report | Allows immediate payment collection directly from weekly calendar view. |

---

### C. Monthly Report (`/admin/installments-due-month`)

| Frontend Control | Wire / Event Binding | Backend Method | Service / Observer Invoked | Database Operation | Expected Result |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Month / Year Selector** | `wire:model.live="selectedMonth"` | `updatedSelectedMonth()` | `InstallmentDueReportService::getMonthInstallmentsQuery()` | Queries date range `[startOfMonth, endOfMonth]` | Dynamically updates table for the chosen calendar month. |
| **Status Filter Dropdown** | `wire:model.live="installmentFilter"` | `updatedInstallmentFilter()` | Filter applied to query builder | Filters by `all`, `pending`, `partial`, `paid`, `overdue` | Displays only installments matching criteria. |
| **Monthly Summary Cards** | Blade expressions for Month Total, Month Paid, Month Remaining | `getMonthStatsProperty()` | `InstallmentDueReportService::getMonthStats()` | Aggregates month totals | Displays unified month financial totals. |
| **Pay Action Button (تحصيل)** | `wire:click="openPaymentModal({{ $installment->id }})"` | `openPaymentModal($installmentId)` | `HandlesInstallmentDuePayments` | Same authoritative flow | Immediate collection capability within monthly ledger. |

---

## 3. UI/UX Preservation Guarantee
- **Layouts & Blade Templates:** 100% preserved. No HTML tags, divs, grids, flex containers, or blade components were moved or deleted.
- **Styling & CSS:** No Tailwind or custom CSS classes were altered.
- **Colors, Fonts, & Icons:** All heroicons, Arabic fonts, badge color schemes (`warning`, `success`, `danger`, `primary`) remain identical to production.
