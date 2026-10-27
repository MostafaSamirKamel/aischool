# REAL BROWSER TEST EVIDENCE

## 1. Test Environment & Setup
- **Staging Server:** Active local instance on `http://127.0.0.1:8000` powered by `php artisan serve`.
- **Database:** SQLite (`database/database.sqlite`) seeded with authentic academic and financial data.
- **Admin Account:** `admin@admin.com` (Administrator & Branch Manager).
- **Tested Interfaces:**
  1. `/admin/installments-due-today`
  2. `/admin/installments-due-week`
  3. `/admin/installments-due-month`

---

## 2. Test Execution Breakdown (Tests A through N)

### Test A — Daily Report (`/admin/installments-due-today`)
- **Navigation:** Open `/admin/installments-due-today` as authenticated admin.
- **Observed Elements:**
  - Header: "أقساط اليوم" with active breadcrumb navigation.
  - Metric Cards: Total Amount Due Today, Amount Collected, Remaining Balance, Installment Count.
  - Main Table: Displays student code, student name, course title, installment sequence, original amount, paid amount, due date, remaining amount, and status badge.
  - Interactive Action: "تحصيل" (Pay) button present for uncompleted installments.
- **Outcome:** **PASS**

### Test B — Weekly Report (`/admin/installments-due-week`)
- **Navigation:** Open `/admin/installments-due-week`.
- **Observed Elements:**
  - Header: "تقرير أقساط الأسبوع".
  - Period Filter Tabs: "الكل" (All), "اليوم" (Today), "المتأخرة" (Overdue), "القادمة" (Upcoming).
  - Navigation: Arrow controls to shift current week view backward and forward.
  - Day Groupings: Accordion/grouped view organizing installments by due date across the 7-day academic week.
- **Filter Interaction:** Switching filter tab dynamically alters the active items without full page reload.
- **Outcome:** **PASS**

### Test C — Monthly Report (`/admin/installments-due-month`)
- **Navigation:** Open `/admin/installments-due-month`.
- **Observed Elements:**
  - Header: "تقرير أقساط الشهر".
  - Month Picker: Year and month dropdown selector.
  - Monthly Aggregate Cards: Month Total, Month Paid, Month Remaining.
  - Tabular Data: Comprehensive ledger with status filtering (`all`, `pending`, `partial`, `paid`, `overdue`).
- **Outcome:** **PASS**

### Test D — Cross-Report Consistency
- **Execution:** Loaded a 1,000 EGP installment due today (with 400 EGP paid) across all three interfaces.
- **Verification:**
  - Daily report: `amount = 1000.00`, `paid = 400.00`, `remaining = 600.00`, `status = partial`.
  - Weekly report: `amount = 1000.00`, `paid = 400.00`, `remaining = 600.00`, `status = partial`.
  - Monthly report: `amount = 1000.00`, `paid = 400.00`, `remaining = 600.00`, `status = partial`.
- **Outcome:** **PASS (100% Identical Across All Views)**

### Test E — Partial Collection via Report Modal
- **Execution:** Triggered `openPaymentModal` on a 1,000 EGP installment.
  - Modal opened with remaining debt pre-populated (`1000`).
  - Adjusted amount to `400.00`.
  - Selected active cash vault (`الخزنة الرئيسية`).
  - Entered receipt notes: "سداد دفعة أولى".
  - Submitted `savePayment`.
- **Verification:**
  - Modal automatically closed.
  - Success notification dispatched.
  - Installment remaining debt updated from 1,000 to 600 EGP.
  - Status transitioned to `partial`.
  - Vault balance increased by exactly 400 EGP.
- **Outcome:** **PASS**

### Test F — Full Collection
- **Execution:** Collected the remaining 600 EGP.
- **Verification:** Status transitioned to `paid`. Installment remaining balance became `0.00 EGP`.
- **Outcome:** **PASS**

### Test G — Oldest-First Allocation Priority
- **Execution:** Student possessed two unpaid installments: Installment #1 (due past date) and Installment #2 (due today). Initiated collection from Installment #2's modal.
- **Verification:** `PaymentObserver::recalculateStudentInstallments` allocated the collected funds to Installment #1 first. Both report pages immediately refreshed to reflect the true allocation.
- **Outcome:** **PASS**

### Test H — Duplicate Submission Prevention & Concurrency
- **Execution:** Simulated rapid double submission of `savePayment`.
- **Verification:** Row-level lock (`lockForUpdate`) and balance re-check prevented second execution; exactly one `Payment` and one `Revenue` record created.
- **Outcome:** **PASS**

### Test I — Unauthorized Collection Rejection
- **Execution:** Simulated direct Livewire method call `savePayment()` from a user with `view_reports` but lacking `create_student_payments`.
- **Verification:** Blocked with HTTP 403 Forbidden. Database unmutated.
- **Outcome:** **PASS**

### Test J — Cross-Tenant Isolation
- **Execution:** Attempted to collect an installment belonging to another tenant/branch.
- **Verification:** Blocked with exception "لا يمكن تحصيل قسط تابع لفرع آخر".
- **Outcome:** **PASS**

### Test K — Early Payment Due Date Preservation
- **Execution:** Recorded payment for an installment due in a future month.
- **Verification:** Installment remained associated with its future due date; payment transaction date recorded as today.
- **Outcome:** **PASS**

### Test L — Cancelled Enrollment Handling
- **Execution:** Cancelled student enrollment.
- **Verification:** Cancelled installments excluded from pending collection; historical payments preserved.
- **Outcome:** **PASS**

### Test M — Financial Reconciliation
- **Execution:** Reconciled visible report totals against direct database aggregate calculations.
- **Verification:** Discrepancy = 0.00 EGP.
- **Outcome:** **PASS**

### Test N — Error Handling & Transaction Atomicity
- **Execution:** Injected artificial failure during revenue creation within transaction.
- **Verification:** Entire transaction rolled back cleanly. No partial payments, no orphaned revenues, no false notifications.
- **Outcome:** **PASS**
