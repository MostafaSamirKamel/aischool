# PAYMENT COLLECTION AND OBSERVER VERIFICATION

## 1. Architectural Overview
Payment collection initiated from any of the three installment report interfaces (`InstallmentsDueToday`, `InstallmentsDueWeek`, `InstallmentsDueMonth`) executes through the unified trait `HandlesInstallmentDuePayments`.

To ensure complete accounting integrity and prevent duplicate financial logic, this flow does **not** perform independent balance arithmetic. Instead, it delegates entirely to the authoritative domain pipeline:

```
[Report UI] 
   └── HandlesInstallmentDuePayments::savePayment()
          ├── DB::transaction() + Installment::lockForUpdate()
          ├── Payment::create(['status' => 'completed', ...])
          └── PaymentObserver::created() / PaymentObserver::recalculateStudentInstallments()
                 ├── Revenue::create(['payment_id' => $payment->id, ...])
                 ├── Oldest-First Installment Balance Re-allocation
                 ├── Enrollment Financial Synchronization
                 └── Vault Balance Dynamic Computation
```

---

## 2. Step-by-Step Execution Verification

### Step 1: Pre-Submission Validation & Locking
1. Authenticated user permission checked via `InstallmentDueReportAccess::canCollectPayment(auth()->user())`.
2. Target installment retrieved with `lockForUpdate()` inside `DB::transaction()`.
3. Tenant isolation verified: `$installment->tenant_id === $user->tenant_id`.
4. Vault verified: active and belongs to user's tenant.
5. Remaining debt verified: `$paymentAmount <= $installment->getRemainingAmount()`.
6. Negative or zero amount rejected: `numeric|min:0.01`.

### Step 2: Payment Record Creation
The payment is created with:
- `tenant_id`, `branch_id`: inherited from enrollment.
- `enrollment_id`: linked directly.
- `vault_id`: selected authorized vault.
- `amount`: validated payment sum.
- `payment_date`: transaction date.
- `payment_method`: 'cash'.
- `status`: 'completed'.
- `receipt_number`: auto-generated unique identifier (`TXN-YYYYMMDD-XXXX`).
- `received_by`: `auth()->id()`.

### Step 3: PaymentObserver Trigger & Revenue Generation
Upon `Payment::created()`, `PaymentObserver` intercepts the event:
1. Verifies `$payment->status === 'completed'`.
2. Checks for an existing revenue with matching `payment_id` to prevent duplicates.
3. Creates a `Revenue` record:
   - `payment_id`: `$payment->id`
   - `vault_id`: `$payment->vault_id`
   - `amount`: `$payment->amount`
   - `revenue_type`: `'installment'`
   - `status`: `'received'`
   - `received_by`: `$payment->received_by`

### Step 4: Oldest-First Allocation Enforcement
`PaymentObserver::recalculateStudentInstallments($studentId)` evaluates all completed payments for the student and allocates them strictly to unpaid installments ordered by:
1. `due_date ASC` (oldest installments first)
2. `installment_number ASC`
3. `id ASC`

**Key Guarantee:** If a teller clicks "Pay" on installment #3 when installment #1 is still unpaid, the financial engine allocates the payment to installment #1. The reports immediately refresh and display the **real resulting database state**, preventing false representation of settled debt.

### Step 5: Enrollment & Vault Ledger Synchronization
1. `Enrollment::paid_amount` is updated to reflect total payments made toward the course.
2. `Enrollment::remaining_balance` is recalculated.
3. `Vault::getCurrentBalance()` is evaluated dynamically. Because `getCurrentBalance()` sums `payments WHERE status = 'completed'` and `revenues WHERE payment_id IS NULL`, setting `revenues.payment_id = $payment->id` guarantees **zero double counting**.

---

## 3. Automated Test Verification

| Test Scenario | Test Method in Test Suite | Assertions Verified | Status |
| :--- | :--- | :--- | :--- |
| **Partial Collection (400 on 1000 EGP)** | `test_e_partial_collection_through_report_action` | Payment created, Revenue created, Installment paid=400 remaining=600 status=partial, Vault +400. | **PASS** |
| **Full Collection (1000 EGP)** | `test_f_full_collection_settles_installment_completely` | Installment paid=1000 remaining=0 status=paid, Enrollment updated. | **PASS** |
| **Oldest-First Priority** | `test_g_oldest_first_allocation_priority_respected` | Payment applied to older installment, report displays real allocation. | **PASS** |
| **Duplicate Prevention** | `test_h_duplicate_payment_submission_safely_handled` | Second identical submit rejected, exactly one payment/revenue created. | **PASS** |
| **Transaction Rollback on Error** | `test_n_failed_payment_rolls_back_cleanly` | Unhandled exception rolls back all tables; zero orphaned rows. | **PASS** |
