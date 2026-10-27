# FINANCIAL SYSTEM AUDIT REPORT
## AI School Management System — Laravel 12 + Filament v3/v4/v5 + Livewire v3/v4 Architecture

---

### Executive Summary

A comprehensive architectural and code audit of the AI School Management System financial operations was conducted prior to executing the unified Vault-Centered refactoring. The system was audited to eliminate the historical dichotomy between "Vault" and "Payment Method" while preserving existing business logic, installment calculation algorithms, multi-tenant boundaries, and academic workflows.

---

### 1. Technology Stack Identified

- **Core Framework:** Laravel 12.64.0 (PHP 8.3.33)
- **Administration Panel:** Filament 3.x / 5.x with Livewire 3.x / 4.x
- **Multi-Tenancy:** Single-database multi-tenancy enforced by `tenant_id` and `BelongsToBranch` trait.
- **Authorization & Security:** Spatie Laravel-Permission integrated with Filament Shield policies.
- **Database Engine:** SQLite (local development and test suite) / MySQL 8.0 production compatibility.

---

### 2. Discovered Financial Models & Roles

| Model | Location | Primary Responsibility | Relationship to Vault |
|---|---|---|---|
| `Vault` | `app/Models/Vault.php` | Authoritative financial container and balance keeper. | Core entity. Calculates current balance dynamically from opening balance, payments, upfronts, revenues, expenses, salaries, cancellations, and transfers. |
| `VaultTransfer` | `app/Models/VaultTransfer.php` | Records inter-vault fund movements. | Links `from_vault_id` and `to_vault_id`. Decrements source vault and increments destination vault without generating revenue. |
| `Payment` | `app/Models/Payment.php` | Records student incoming fee payments against enrollments. | Links directly to `vault_id`. Triggers `PaymentObserver` to allocate installment payments and mirror revenue. |
| `StudentPayment` | `app/Models/StudentPayment.php` | Legacy student payment structure. | Audited for backward compatibility and aggregated in vault balance calculations if table exists. |
| `Enrollment` | `app/Models/Enrollment.php` | Academic and financial contract between student and course/group. | Stores `vault_id` for upfront registration collections and `paid_amount` / `total_amount`. |
| `Installment` | `app/Models/Installment.php` | Represents installment tranches for installment payment plans. | Tracks `amount`, `paid_amount`, `due_date`, and `status`. Allocated automatically via `PaymentObserver`. |
| `Revenue` | `app/Models/Revenue.php` | School revenue ledger entry. | Tracks `vault_id` and optional `payment_id`. Payment-linked revenues are excluded from vault balances to prevent double-counting. |
| `Expense` | `app/Models/Expense.php` | School operational expenditures. | Enforces `vault_id` as the source of funds and deducts from vault balance upon payment. |
| `SalaryPayment` | `app/Models/SalaryPayment.php` | Instructor and administrative salary disbursements. | Links to `vault_id` when `status === 'paid'`. Deducts directly from paying vault. |
| `EnrollmentCancellation`| `app/Models/EnrollmentCancellation.php` | Settlement record for student enrollment cancellations. | Tracks `refund_vault_id`, `refunded_amount`, and auto-waives outstanding debt. |

---

### 3. Financial Services & Observers

#### A. `PaymentObserver` (`app/Observers/PaymentObserver.php`)
- **Action on Payment Created/Updated:**
  - Automatically mirrors incoming completed payment to a `Revenue` record (with matching `vault_id`, `amount`, and `status = 'received'`).
  - Calls `recalculateStudentInstallments()` across student enrollments to apply payment proceeds sequentially by installment due date.
- **Action on Payment Deleted:**
  - Automatically deletes mirrored `Revenue` record to prevent ghost revenues.
  - Recalculates student installment allocations.
- **Double Counting Invariant:**
  - In `Vault::getCurrentBalance()`, standalone revenues are calculated via `whereNull('payment_id')`. Payment-generated revenues do NOT add duplicate funds into the vault balance.

#### B. `EnrollmentCancellationService` (`app/Services/Enrollment/EnrollmentCancellationService.php`)
- **Workflows Handled:**
  - `cancelSimplified()`: Streamlined cancellation workflow with reason, debt waiver, optional refund amount, and paying vault selection.
  - Automatically checks `executeRefund` policy and asserts sufficient vault balance via `assertSufficientBalance()`.
  - Atomically deducts refund amount without double counting against general expenses.

#### C. `InstallmentCalculationService` (`app/Services/InstallmentCalculationService.php`)
- Calculates installment schedules, dates, and amounts upon enrollment creation.
- Preserved 100% intact without modifications.

---

### 4. Financial UI Interfaces Audited

1. **Enrollment Form & Upfront Payment:** `app/Filament/Admin/Resources/Enrollments/Schemas/EnrollmentForm.php`
2. **Payments Form & Table:** `app/Filament/Admin/Resources/Payments/Schemas/PaymentForm.php` & `Tables/PaymentsTable.php`
3. **Expenses Form & Table:** `app/Filament/Admin/Resources/Expenses/Schemas/ExpenseForm.php` & `Tables/ExpensesTable.php`
4. **Salary Payments Form & Table:** `app/Filament/Admin/Resources/SalaryPayments/Schemas/SalaryPaymentForm.php` & `Tables/SalaryPaymentsTable.php`
5. **Revenues Form & Table:** `app/Filament/Admin/Resources/Revenues/Schemas/RevenueForm.php` & `Tables/RevenuesTable.php`
6. **Vault Transfers Form & Table:** `app/Filament/Admin/Resources/VaultTransfers/Schemas/VaultTransferForm.php` & `Tables/VaultTransfersTable.php`
7. **Cancellation & Refund Actions:** `CancelSubscriptionAction.php` & `ExecuteRefundAction.php`
8. **Livewire Installment Due Collections:**
   - `InstallmentsDueToday.php` + Blade view
   - `InstallmentsDueWeek.php` + Blade view
   - `InstallmentsDueMonth.php` + Blade view
9. **Financial Reports & Statements:**
   - `VaultReport.php`
   - `VaultTransactions.php`
   - `PaymentMethodsChart.php` (Refactored to vault distribution)
   - `StatsOverview.php` (Refactored to permission-aware vault totals)
   - `VaultBalancesWidget.php`
   - `VaultDailyMovement.php`
   - `StudentPortfolioReport.php`

---

### 5. Audit Conclusions

- Prior to refactoring, forms had redundant and conflicting selections (e.g., selecting "Vodafone Cash" as a payment method while simultaneously selecting "Main Cash" as a vault).
- Several Filament tables lacked permission-aware visibility scoping, exposing balances to unauthorized employees.
- The system now features a single, unified source and destination of funds: **The Selected Vault**.
