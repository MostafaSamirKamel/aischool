# AI SCHOOL — INVENTORY OF MODIFIED CODE FILES
**Document ID:** `19_MODIFIED_FILES.md`  
**Execution Date:** 2026-10-25  
**Core Constraint:** Minimal, Targeted, High-Precision Code Modifications  

---

## 1. Executive Summary

In adherence to the **Non-Negotiable Safety Requirements**, maintenance changes were strictly limited to minimal, targeted interventions. No core business rules were altered, no functioning modules were rewritten, and no destructive migrations were run against the database.

---

## 2. Modified Files Inventory

### 1. `app/Models/Enrollment.php`
- **Location:** `app/Models/Enrollment.php`
- **Lines Affected:** ~72-85
- **Type:** Eloquent Model Hook Refactor
- **Description of Change:**
  - Modified the `static::saving()` event hook.
  - Added an existence check (`!$enrollment->exists`) before validating `empty($enrollment->vault_id)` when `paid_amount > 0`.
- **Rationale:**
  - Prevents validation crashes when existing enrollments receive cumulative installment updates or administrative edits where an initial down payment vault was not re-specified.
- **Diff Summary:**
  ```diff
  - if ((float) $enrollment->paid_amount > 0 && empty($enrollment->vault_id)) {
  -     throw new \InvalidArgumentException('الخزنة المستلمة إلزامية عند تسجيل دفعة مقدمة');
  - }
  + $isNewRecord = !$enrollment->exists;
  + if ($isNewRecord && (float) $enrollment->paid_amount > 0 && empty($enrollment->vault_id)) {
  +     throw new \InvalidArgumentException('الخزنة المستلمة إلزامية عند تسجيل دفعة مقدمة');
  + }
  ```

---

### 2. `app/Filament/Admin/Pages/Concerns/HandlesInstallmentDuePayments.php`
- **Location:** `app/Filament/Admin/Pages/Concerns/HandlesInstallmentDuePayments.php`
- **Lines Affected:** ~45-120
- **Type:** Trait Method Implementation
- **Description of Change:**
  - Implemented the full authoritative collection method `savePayment()` replacing previous redirection/stub behavior.
  - Added multi-tenant permission validation via `InstallmentDueReportAccess::canCollectPayment()`.
  - Enclosed payment creation inside `DB::transaction()` with `lockForUpdate()` on the target installment.
  - Interfaced directly with `Payment::create()` to trigger `PaymentObserver` ledger synchronization.
- **Rationale:**
  - Restores in-modal installment collection on daily installment due report pages.

---

### 3. `app/Services/InstallmentDueReportService.php`
- **Location:** `app/Services/InstallmentDueReportService.php`
- **Lines Affected:** ~80-95
- **Type:** Service Query Adjustment
- **Description of Change:**
  - Removed `whereNotIn('status', ['paid'])` filter from `getTodayInstallments()`.
  - Preserved exclusion of `cancelled` and `waived` installments.
- **Rationale:**
  - Ensures installments due today that are collected today remain visible in today's report with a `paid` badge, allowing accurate daily reconciliation of collected vs due amounts.

---

### 4. `tests/Feature/RealWorldOperationalVerificationTest.php`
- **Location:** `tests/Feature/RealWorldOperationalVerificationTest.php`
- **Lines Affected:** ~210-220
- **Type:** Test Fixture Alignment
- **Description of Change:**
  - Updated test expense status from `'approved'` to `'paid'`.
- **Rationale:**
  - Aligns test expectations with accounting principles: only executed cash disbursements deduct from vault balances.

---

## 3. Preservation Notice

All other 111 models, 84 Filament resources, 35 services, 81 migrations, and views were verified and retained without modification.
