# 05 — CONFIRMED BUGS, ROOT CAUSES AND IMPLEMENTED FIXES

## 1. Defect Severity Classification Standard
- **P0:** Financial corruption, unauthorized financial mutation, duplicate real payments or ledger drift.
- **P1:** Broken collection, wrong installment balances, or major reporting failures.
- **P2:** Incorrect filters, counters, synchronization, or usability failures.
- **P3:** Minor non-blocking issues.

---

## 2. Comprehensive Defect Register

### BUG-01 (P1): Date Comparison String Truncation in Installment Reports
- **Affected File:** `app/Services/InstallmentDueReportService.php`, `InstallmentsDueToday.php`, `InstallmentsDueWeek.php`
- **Root Cause:** Queries used `where('due_date', $today)`. In SQLite/MySQL, due dates stored as timestamps (`YYYY-MM-DD 00:00:00`) or ISO strings failed exact equality with `'YYYY-MM-DD'`, returning 0 records.
- **Reproduction Steps:** Create an installment due today in SQLite. Navigate to `/admin/installments-due-today`. Table displays "لا توجد أقساط".
- **Expected Result:** Table displays installments due today.
- **Actual Result:** 0 installments returned.
- **Financial Impact:** Tellers unable to see or collect installments due today.
- **Implemented Fix:** Standardized all queries across daily, weekly, and monthly scopes to use Eloquent's `whereDate('installments.due_date', ...)`.
- **Regression Test:** `InstallmentDueReportIntegrationTest::test_a_today_report_displays_installments_due_today`. **PASS**

---

### BUG-02 (P0): In-Memory `Payment::$status` Null Bypassing PaymentObserver
- **Affected File:** `app/Models/Payment.php`, `app/Observers/PaymentObserver.php`
- **Root Cause:** In Eloquent, database defaults are not populated in model memory during `create()` until `refresh()` is called. `PaymentObserver::created()` evaluated `if ($payment->status === 'completed')`, which evaluated to `null === 'completed'` (false), silently skipping revenue creation and student installment allocation.
- **Reproduction Steps:** Call `Payment::create([...])` without explicitly specifying `'status' => 'completed'`.
- **Expected Result:** Payment observer records revenue and updates installment `paid_amount`.
- **Actual Result:** Revenue was never created; installment balance remained 0.00.
- **Financial Impact:** Severe. Cash collected was recorded as a payment but never recognized in revenues or deducted from student debt.
- **Implemented Fix:** Added `protected $attributes = ['status' => 'completed', 'payment_method' => 'cash'];` to `Payment.php` and explicitly set `'status' => 'completed'` in collection handlers.
- **Regression Test:** `InstallmentDueReportIntegrationTest::test_e_partial_collection_through_report_action`. **PASS**

---

### BUG-03 (P1): Overdue Status Classification Drift in PaymentObserver
- **Affected File:** `app/Observers/PaymentObserver.php`
- **Root Cause:** `PaymentObserver::recalculateStudentInstallments()` marked an installment as `pending` whenever `paid_amount < amount`, ignoring whether `due_date` had passed. This conflicted with `Installment::updateStatus()`.
- **Reproduction Steps:** Make a partial payment on an installment whose due date has passed.
- **Expected Result:** Status should be `overdue` (or partial-overdue).
- **Actual Result:** Status was overwritten to `pending`.
- **Financial Impact:** Overdue installments with partial payments silently disappeared from overdue filters and reports.
- **Implemented Fix:** Synchronized `PaymentObserver` to evaluate `Carbon::parse($installment->due_date)->isPast()`. If date has passed, status is set to `overdue`; otherwise, `partial` or `pending`.
- **Regression Test:** `InstallmentOverdueCalculationTest`. **PASS**

---

### BUG-04 (P0): Conflated Report Viewing vs Payment Collection Authorization
- **Affected File:** `app/Services/InstallmentDueReportAccess.php`, `HandlesInstallmentDuePayments.php`
- **Root Cause:** Method-level guards were missing inside Livewire payment actions. Any employee with permission to view reports could trigger `savePayment()` via console or direct Livewire requests.
- **Reproduction Steps:** Authenticate as an employee with `view_reports` but lacking `create_student_payments`. Invoke `savePayment()`.
- **Expected Result:** HTTP 403 Forbidden.
- **Actual Result:** Payment was saved and vault was mutated.
- **Financial Impact:** Unauthorized staff could record cash collections and alter school financial ledgers.
- **Implemented Fix:** Implemented `InstallmentDueReportAccess::canCollectPayment($user)` and added strict `abort_unless(..., 403)` at entry points.
- **Regression Test:** `InstallmentDueReportIntegrationTest::test_i_reporting_only_user_cannot_collect_payments`. **PASS**

---

### BUG-05 (P0): Double-Counting of Payments in Enrollment Installments Widget
- **Affected File:** `app/Filament/Admin/Resources/Enrollments/Widgets/EnrollmentInstallmentsWidget.php`, `ViewEnrollment.php`
- **Root Cause:** Code computed `$totalPaid = $record->payments()->sum('amount') + $record->installments()->sum('paid_amount')`. Because payments are allocated into `installments.paid_amount`, adding both summed the paid amount twice.
- **Reproduction Steps:** Collect 400 EGP on a 1,000 EGP installment. Open Enrollment view.
- **Expected Result:** Widget displays Paid: 400 EGP, Remaining: 600 EGP.
- **Actual Result:** Widget displayed Paid: 800 EGP, Remaining: 200 EGP.
- **Financial Impact:** Critical visual misinformation on student financial profile, showing students as having paid double their actual payments.
- **Implemented Fix:** Removed erroneous addition. Each installment directly uses its authoritative database `paid_amount` and `status` maintained by `PaymentObserver`.
- **Regression Test:** `FinancialIntegrationCrossModuleTest::test_complete_cross_module_financial_workflow`. **PASS**

---

### BUG-06 (P1): Undefined `payments` Relation Exception on Installment Model
- **Affected File:** `app/Filament/Admin/Resources/Enrollments/Widgets/EnrollmentInstallmentsWidget.php`, `ViewEnrollment.php`
- **Root Cause:** Code called `$record->installments()->with(['payments'])`. `Installment` has no `payments` relationship (payments belong to `Enrollment`), throwing `RelationNotFoundException`.
- **Reproduction Steps:** Load `/admin/enrollments/{record}`.
- **Expected Result:** Page renders installment list.
- **Actual Result:** Crash: `Call to undefined relationship [payments] on model [App\Models\Installment]`.
- **Financial Impact:** Prevents administrative staff from viewing student enrollment financial details.
- **Implemented Fix:** Removed `->with(['payments'])` from the query.
- **Regression Test:** `FinancialIntegrationCrossModuleTest`. **PASS**

---

### BUG-07 (P0): Concurrency Race Condition & Double Submit Vulnerability
- **Affected File:** `app/Filament/Admin/Pages/Concerns/HandlesInstallmentDuePayments.php`
- **Root Cause:** Absence of row-level pessimistic locking (`lockForUpdate()`) allowed rapid double-clicks to insert multiple payment and revenue records for the same installment.
- **Reproduction Steps:** Submit payment modal twice rapidly before first transaction commits.
- **Expected Result:** Second request rejected.
- **Actual Result:** Multiple payments inserted.
- **Financial Impact:** Vault balance overstated; student credited with duplicate unbacked payments.
- **Implemented Fix:** Wrapped payment execution in `DB::transaction()` with `Installment::lockForUpdate()` and re-checked debt balance under lock.
- **Regression Test:** `InstallmentDueReportIntegrationTest::test_h_duplicate_payment_submission_safely_handled`. **PASS**
