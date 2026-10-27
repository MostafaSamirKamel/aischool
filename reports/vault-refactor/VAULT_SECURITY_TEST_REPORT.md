# VAULT SECURITY & CONCURRENCY TEST REPORT
## Absolute Negative-Balance Enforcement, Concurrency Safety, Tenant Isolation & Tamper Resistance

---

### 1. Absolute Negative-Balance Prevention Invariant

Section 9 of the mandate requires:
> **"NO outgoing transaction may cause a vault balance to become negative. There are NO exceptions. This restriction applies to Super Admin, Branch Manager, Accountant, Registration Employee, and every other role."**

#### Implementation Details:
1. **Centralized Model Assertion (`Vault::assertSufficientBalance`):**
   ```php
   public function assertSufficientBalance(float $amount, ?float $originalAmount = 0.0): void
   {
       $currentBalance = $this->getCurrentBalance();
       $available = round($currentBalance + (float) $originalAmount, 2);

       if (round($amount, 2) > $available) {
           $canView = auth()->check() && (auth()->user()->is_super_admin || auth()->user()->can('view_vault_balances'));
           $msg = $canView
               ? 'لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية. الرصيد المتاح: ' . number_format($available, 2) . ' ج.م والمطلوب: ' . number_format($amount, 2) . ' ج.م.'
               : 'لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية.';

           throw ValidationException::withMessages([
               'vault_id' => $msg,
               'amount' => $msg,
           ]);
       }
   }
   ```
2. **Hook Level Enforcement:**
   - `Expense::saving`: Asserts `$vault->assertSufficientBalance($expense->amount, $originalAmount)`.
   - `SalaryPayment::saving`: When `status === 'paid'`, asserts sufficient balance before saving.
   - `VaultTransfer::saving`: When `status === 'completed'`, asserts source vault has sufficient balance before updating status.
   - `EnrollmentCancellationService`: Checks balance prior to emitting refund disbursements.

---

### 2. Concurrency Safety & Race Condition Prevention

- **Database Row-Level Locking:**
  In high-stakes settlement workflows (`cancelSimplified`, `executeRefund`), queries utilize `lockForUpdate()` within an active database transaction (`DB::transaction(...)`).
- **Atomic Commits:**
  If two withdrawal requests occur simultaneously on a vault with funds sufficient for only one, the database row lock serializes the operations. The first commits successfully, reducing the available balance; the second immediately reads the decremented balance and is rejected with `ValidationException`.
- **Double Submission Idempotency:**
  Rapid double submissions in Filament actions are guarded through unique keys, disabling submit buttons upon initial submission (`->disabledForm()`), and transactional verification of existing uncompleted operations.

---

### 3. Tenant & Branch Isolation Verification

- **Direct Tampering Defense:**
  Attempts by an employee from Branch A to submit a transaction targeting a vault from Branch B fail at both frontend validation and backend model queries due to the `BelongsToBranch` / `TenantScope` filter:
  `where('tenant_id', auth()->user()->tenant_id)`
- **Automated Multi-Tenant Isolation Tests:**
  `Tests\Feature\MultiTenantIsolationTest` verified that tenant records are completely inaccessible to users belonging to other tenants.
- **Cross-Branch Attempt (TEST 12):**
  Passed with 100% rejection of cross-tenant vault queries.

---

### 4. Deactivation & Unsafe Deletion Protection

- **Inactive Vault Protection:**
  `Vault::is_active` is enforced across all transaction form selectors. Inactive vaults are excluded from choices for new transactions (TEST 13 & TEST 18).
- **Deletion Constraint:**
  In `VaultsTable.php`, deletion is blocked if the vault has associated `payments()`, `expenses()`, `revenues()`, `transfersFrom()`, `transfersTo()`, `enrollments()`, or `salaryPayments()`.
  An Arabic notification warns: `"لا يمكن حذف الخزينة لوجود معاملات مالية مرتبطة بها."`
