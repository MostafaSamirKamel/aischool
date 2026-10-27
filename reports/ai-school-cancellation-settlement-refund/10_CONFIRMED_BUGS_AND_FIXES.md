# 10 — CONFIRMED BUGS AND FIXES
## Root Cause Analysis, Impact & Solutions Implemented

**Project:** AI School Management System  
**Audit Period:** October 2026  
**Status:** ALL ISSUES RESOLVED & VERIFIED WITH REGRESSION TESTS  

---

### Bug #1: Database Enum Violation on `payment_method` (`'vault'`)

- **Location:**
  - `App\Filament\Admin\Resources\Enrollments\Actions\CancelSubscriptionAction`
  - `App\Filament\Admin\Resources\Enrollments\Actions\ExecuteRefundAction`
  - `App\Services\Enrollment\EnrollmentCancellationService`
- **Root Cause:**
  When executing a simplified cancellation with refund or a standalone refund, the actions were passing `'payment_method' => 'vault'`. However, the MySQL schema for `payments` and `expenses` tables restricts `payment_method` to:
  `enum('cash', 'card', 'bank_transfer', 'mobile_wallet', 'other')`.
- **Financial / Operational Impact:**
  Under strict SQL mode on staging/production MySQL databases, executing any refund resulted in a fatal SQL violation:
  `SQLSTATE[01000]: Data truncated for column 'payment_method' at row 1`.
  The refund failed, and the modal errored out.
- **Solution Applied:**
  1. Updated `CancelSubscriptionAction` and `ExecuteRefundAction` to pass `'payment_method' => 'cash'` (or valid selected payment method).
  2. Enhanced `EnrollmentCancellationService` to sanitize and normalize any incoming payment method:
     ```php
     $allowedMethods = ['cash', 'card', 'bank_transfer', 'mobile_wallet', 'other'];
     $paymentMethod = in_array(strtolower($paymentMethod), $allowedMethods, true) ? strtolower($paymentMethod) : 'cash';
     ```
- **Regression Coverage:**
  `Tests\Feature\ExecuteRefundActionLivewireTest::test_two_step_cancel_and_execute_refund_action` (PASS).

---

### Bug #2: Missing Form-Level Vault Sufficiency Validation

- **Location:**
  - `App\Filament\Admin\Resources\Enrollments\Actions\CancelSubscriptionAction`
  - `App\Filament\Admin\Resources\Enrollments\Actions\ExecuteRefundAction`
- **Root Cause:**
  The `vault_id` field in both modals did not have a custom form rule to verify that the selected vault possessed sufficient funds *before* attempting the transaction. When an underfunded vault was chosen, the backend exception was caught in a general `catch` block and emitted a notification, leaving the modal in an ambiguous state without specific field highlighting.
- **Financial / Operational Impact:**
  User confusion when selecting a vault with zero or low balance; lack of instant form-level feedback in Arabic.
- **Solution Applied:**
  Added custom validation rules on `vault_id` / `refund_vault_id` in both actions:
  ```php
  ->rules([
      fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
          $amount = (float) $get('amount'); // or $get('refund_amount')
          if ($value && $amount > 0) {
              $vault = Vault::find($value);
              if ($vault && (float) $vault->getCurrentBalance() < $amount) {
                  $fail('رصيد الخزنة المحددة غير كافٍ لإتمام عملية الصرف.');
              }
          }
      },
  ])
  ```
- **Regression Coverage:**
  `Tests\Feature\ExecuteRefundActionLivewireTest::test_insufficient_vault_balance_blocks_refund` (PASS).

---

### Bug #3: Installment Widget Arithmetic Double Counting

- **Location:**
  - `App\Filament\Admin\Resources\Enrollments\Widgets\EnrollmentInstallmentsWidget`
  - `App\Filament\Admin\Resources\Enrollments\Pages\ViewEnrollment`
- **Root Cause:**
  The widget was computing total paid by summing payments AND installments:
  `$totalPaid = $record->payments()->sum('amount') + $record->installments()->sum('paid_amount');`
  Because payments are recorded when installments are paid, this resulted in paid amounts being counted twice. Furthermore, an eager load `->with(['payments'])` was requested on `Installment`, which had no direct `payments` relationship.
- **Financial / Operational Impact:**
  The widget displayed distorted figures (e.g., student showed 1,000 EGP paid on a 500 EGP payment), causing financial confusion.
- **Solution Applied:**
  Streamlined calculation to use authoritative single-source `enrollment->paid_amount`, and removed the non-existent eager load.
- **Regression Coverage:**
  `Tests\Feature\FinancialIntegrationCrossModuleTest` (PASS).
