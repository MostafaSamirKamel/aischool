# CONFIRMED PROBLEMS AND ROOT CAUSES

## 1. Problem Identification & Diagnosis Summary

During rigorous analysis and integration testing of the three installment reports and connected financial mechanisms, six critical backend defects were uncovered and diagnosed.

---

## 2. Detailed Defect Registry

### Defect 1: Date String Comparison Failure on Datetime Columns
- **Affected File:** `app/Services/InstallmentDueReportService.php`, `InstallmentsDueToday.php`, `InstallmentsDueWeek.php`
- **Actual Problem:** Queries comparing `where('due_date', $today)` returned 0 records even when installments existed with dates formatted as `YYYY-MM-DD 00:00:00` or ISO strings in SQLite/MySQL.
- **Root Cause:** Direct string equality (`=`) fails when database timestamps contain time components or differing timezone representations.
- **Applied Correction:** Refactored all queries to use Eloquent's `whereDate('due_date', $date)` across daily, weekly, and monthly query builders.
- **Database Impact:** Consistent, dialect-agnostic date matching across SQLite, MySQL, and PostgreSQL.
- **Tests Executed:** `test_a_today_report_displays_installments_due_today`, `test_b_weekly_report_structure_and_stats`, `test_d_cross_report_consistency`.
- **Result:** **PASS**

---

### Defect 2: In-Memory `Payment::$status` Null Bypassing PaymentObserver
- **Affected File:** `app/Models/Payment.php`, `app/Observers/PaymentObserver.php`
- **Actual Problem:** When creating payments via `Payment::create()`, the in-memory model instance did not evaluate database column defaults (`DEFAULT 'completed'`). Consequently, inside `PaymentObserver::created()`, `$payment->status` was `null`, causing the observer to skip revenue creation and student installment recalculation.
- **Root Cause:** Eloquent models in Laravel do not automatically hydrate database defaults in memory until `refresh()` is called unless explicitly defined in `$attributes`.
- **Applied Correction:** Added `$attributes = ['status' => 'completed', 'payment_method' => 'cash']` to `Payment.php` and explicitly specified `'status' => 'completed'` in `HandlesInstallmentDuePayments::savePayment()`.
- **Database Impact:** Guaranteed that every completed payment immediately triggers revenue creation and installment allocation upon creation.
- **Tests Executed:** `test_e_partial_collection_through_report_action`, `test_f_full_collection_settles_installment_completely`.
- **Result:** **PASS**

---

### Defect 3: Overdue Status Logic Discrepancy in PaymentObserver
- **Affected File:** `app/Observers/PaymentObserver.php`
- **Actual Problem:** `PaymentObserver::recalculateStudentInstallments()` marked an installment as `pending` whenever `paid_amount < amount`, ignoring the fact that if `due_date < today`, the installment must be marked `overdue` (or `partial` if partially paid). This conflicted with `Installment::updateStatus()`.
- **Root Cause:** Divergent business logic between `Installment::updateStatus()` (which checked `due_date < now()`) and `PaymentObserver` (which only checked `paid_amount > 0`).
- **Applied Correction:** Aligned `PaymentObserver` to evaluate `Carbon::parse($installment->due_date)->isPast()`: if balance remains and date has passed, the status is set to `overdue`; if current/future, it is set to `partial` (if partially paid) or `pending`.
- **Database Impact:** Eliminates status drift and ensures overdue installments are accurately tallied across all report filters.
- **Tests Executed:** `InstallmentOverdueCalculationTest`, `test_e_partial_collection_through_report_action`.
- **Result:** **PASS**

---

### Defect 4: Reporting Permission Conflated with Payment Collection Permission
- **Affected File:** `app/Services/InstallmentDueReportAccess.php`, `HandlesInstallmentDuePayments.php`
- **Actual Problem:** Secretarial and follow-up staff with permission to view financial reports could trigger the payment modal and submit payments via direct Livewire method calls, even without `create_student_payments` authorization.
- **Root Cause:** Lack of method-level authorization checks inside Livewire action handlers; reliance on route-level UI guards alone.
- **Applied Correction:** Implemented strict backend permission validation in `InstallmentDueReportAccess::canCollectPayment($user)`. Livewire action handlers verify `abort_unless(InstallmentDueReportAccess::canCollectPayment($user), 403)`.
- **Database Impact:** Prevents unauthorized financial postings, fraudulent receipts, and unauthorized vault interactions.
- **Tests Executed:** `test_i_reporting_only_user_cannot_collect_payments`.
- **Result:** **PASS**

---

### Defect 5: Absence of Row-Level Concurrency Locking on Payment Collection
- **Affected File:** `app/Filament/Admin/Pages/Concerns/HandlesInstallmentDuePayments.php`
- **Actual Problem:** If an employee double-clicked "حفظ السداد" or two tellers submitted payments for the same installment concurrently, duplicate `Payment` and `Revenue` records could be inserted, driving `paid_amount` above the total installment debt.
- **Root Cause:** Absence of database transactions with row-level locks (`lockForUpdate()`) and lack of unique transaction references.
- **Applied Correction:** Wrapped payment creation inside `DB::transaction()`, locked the target installment with `Installment::lockForUpdate()->findOrFail($this->paymentInstallmentId)`, re-validated `remaining_amount >= $amount`, and auto-generated a unique `TXN-` receipt number.
- **Database Impact:** Complete ACID transaction safety; impossible to overpay or generate unallocated duplicate payments.
- **Tests Executed:** `test_h_duplicate_payment_submission_safely_handled`.
- **Result:** **PASS**

---

### Defect 6: Cross-Tenant Vault and Installment Bleed
- **Affected File:** `app/Services/InstallmentDueReportService.php`, `InstallmentDueReportAccess.php`
- **Actual Problem:** Payment modal vault dropdown loaded all active vaults globally, allowing staff from Branch A to deposit tuition fees into Branch B's vault.
- **Root Cause:** Vault queries were not filtered by the authenticated user's `tenant_id`.
- **Applied Correction:** Scoped `InstallmentDueReportService::getActiveVaults(?int $tenantId)` strictly to the user's tenant (unless super admin). Scoped target installment verification to tenant.
- **Database Impact:** Zero cross-tenant data leakage; strict vault ledger segregation.
- **Tests Executed:** `test_j_cross_tenant_installment_access_denied`.
- **Result:** **PASS**
