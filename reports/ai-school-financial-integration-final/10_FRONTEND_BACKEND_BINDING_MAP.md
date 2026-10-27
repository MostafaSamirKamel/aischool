# 10 — COMPLETE FRONTEND-TO-BACKEND BINDING MAP

## 1. Overview
This document registers the exhaustive mapping between user interface controls, Livewire bindings, backend service handlers, observers, database mutations, and resulting UI states across all audited financial interfaces.

---

## 2. Comprehensive Binding Registry

### Section A: Installment Reports (`/admin/installments-due-*`)

| Screen & Control | UI Binding | Backend Method | Service / Observer | DB Operation | Resulting UI State |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Today Refresh** | `wire:click="$refresh"` | Component render | `InstallmentDueReportService::getTodayInstallments()` | `SELECT ... whereDate('due_date', now())` | Refreshes table and metric counters |
| **Week Filter Tab** | `wire:click="setFilter('today')"` | `setFilter('today')` | `InstallmentDueReportService::getWeekInstallmentsQuery()` | Filter query builder by date | Swaps active week view dynamically |
| **Month Selector** | `wire:model.live="selectedMonth"` | `updatedSelectedMonth()` | `InstallmentDueReportService::getMonthInstallments()` | Filter query builder by month/year | Table updates to selected calendar month |
| **Pay Button** | `wire:click="openPaymentModal({{ $id }})"` | `openPaymentModal($id)` | `HandlesInstallmentDuePayments` | Reads installment with lock | Opens modal, defaults amount to remaining debt |
| **Modal Amount** | `wire:model.defer="paymentAmount"` | Property `$paymentAmount` | Validated in `savePayment()` | None (in-memory) | Stores entered payment amount |
| **Modal Vault** | `wire:model.defer="vaultId"` | Property `$vaultId` | Scoped by tenant | None (in-memory) | Destination vault selection |
| **Modal Submit** | `wire:click="savePayment"` | `savePayment()` | `HandlesInstallmentDuePayments` -> `PaymentObserver` | `INSERT payments`, `INSERT revenues`, `UPDATE installments`, `UPDATE enrollments` | Commits collection, closes modal, dispatches success notification, updates table |
| **Modal Cancel** | `wire:click="closePaymentModal"` | `closePaymentModal()` | Resets state | None | Modal dismissed safely |

---

### Section B: Primary Payment Collection Screen (`/admin/payments/create`)

| Screen & Control | UI Binding | Backend Method | Service / Observer | DB Operation | Resulting UI State |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Barcode Scan** | `->live()->afterStateUpdated(...)` | `hydrateEnrollmentData()` | `Student::where('student_code', $code)` | `SELECT student` | Auto-selects student and loads debts |
| **Student Select**| `Select::make('student_id')` | `hydrateEnrollmentData()` | `Enrollment::where('student_id', ...)` | Eager loads courses, groups, installments | Populates course breakdown, discount, paid, remaining balance |
| **Payment Amount**| `TextInput::make('amount')` | Form validation | Validated against debt cap | `payments.amount` | Stores payment sum |
| **Vault Select** | `Select::make('vault_id')` | Form select | Scoped to active branch vaults | `payments.vault_id` | Selects target cash vault |
| **Save Payment** | Form Submit (Filament) | `CreatePayment::create()` | `CreatePayment` -> `PaymentObserver` | Full ACID transaction commit | Inserts `payments`, triggers `PaymentObserver` for oldest-first installment allocation, redirects to index |

---

### Section C: Student Financial Profile (`/admin/enrollments/{record}`)

| Screen & Control | UI Binding | Backend Method | Service / Observer | DB Operation | Resulting UI State |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Installments View** | Blade render | `EnrollmentInstallmentsWidget::getInstallments()` | Authoritative installment records | Reads `installments` | Displays clean list of installments with real `paid_amount` and `remaining` |
| **Stats Summary** | Blade render | `EnrollmentInstallmentsWidget::getStats()` | Aggregate totals | `SUM(amount)`, `SUM(paid_amount)` | Shows Total Count, Total Amount, Total Paid, Total Remaining |
| **Progress Bar** | Blade style width | `$paidPercentage` | Derived from stats | In-memory percentage | Animated green fill bar representing true paid ratio |
