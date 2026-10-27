# VAULT LEDGER RECONCILIATION REPORT
## Mathematical Proofs, Balance Formula, and Double-Counting Prevention

---

### 1. Fundamental Ledger Equation

The single canonical balance of any vault in the system is governed by:

$$\text{Vault Balance} = \text{Opening Balance} + \sum \text{Inflows} - \sum \text{Outflows}$$

Where:

#### Inflows ($\sum \text{Inflows}$):
1. **Direct Completed Payments:**
   $$\sum \text{Payment.amount} \quad (\text{status} = \text{'completed'})$$
2. **Standalone Revenues:**
   $$\sum \text{Revenue.amount} \quad (\text{payment\_id} \text{ IS NULL and status} \in \{\text{'received'}, \text{'completed'}\})$$
3. **Upfront Enrollment Cash/Down-payments:**
   $$\sum \text{Enrollment.paid\_amount} \quad (\text{where enrollment\_id NOT in payments, to avoid double counting})$$
4. **Completed Inward Vault Transfers:**
   $$\sum \text{VaultTransfer.amount} \quad (\text{to\_vault\_id} = \text{vault.id and status} = \text{'completed'})$$
5. **Legacy Student Payments:**
   $$\sum \text{StudentPayment.net\_amount} \quad (\text{if table exists, status} = \text{'completed'})$$

#### Outflows ($\sum \text{Outflows}$):
1. **Paid/Approved Expenses:**
   $$\sum \text{Expense.amount} \quad (\text{status} \neq \text{'cancelled'})$$
2. **Disbursed Salary Payments:**
   $$\sum \text{SalaryPayment.amount} \quad (\text{status} = \text{'paid'})$$
3. **Refund Disbursements:**
   $$\sum \text{EnrollmentCancellation.refunded\_amount} \quad (\text{refund\_status} = \text{'completed'})$$
   *(or `Expense` where category is Refund, excluding duplicates)*
4. **Completed Outward Vault Transfers:**
   $$\sum \text{VaultTransfer.amount} \quad (\text{from\_vault\_id} = \text{vault.id and status} = \text{'completed'})$$

---

### 2. Double-Counting Prevention Audit

#### Invariant A: Payment vs Mirrored Revenue
- **Scenario:** When a student pays 500 EGP, `PaymentObserver` creates a `Revenue` record with `payment_id = $payment->id`.
- **Ledger Protection:**
  In `app/Models/Vault.php` line 143:
  ```php
  $revenues = (float) $this->revenues()
      ->whereIn('status', ['received', 'completed'])
      ->whereNull('payment_id') // CRITICAL: Exclude payments mirrored as revenues
      ->sum('amount');
  ```
  **Result:** The 500 EGP is counted exactly once from `payments`, and never duplicated from `revenues`.

#### Invariant B: Refund vs Cancellation Expense
- **Scenario:** A student enrollment cancellation with refund may log both an `EnrollmentCancellation` record and a refund `Expense`.
- **Ledger Protection:**
  `EnrollmentCancellationService` processes the deduction through the authoritative refund disbursement and sets the linked expense status accordingly. In `Vault.php`, cancelled expenses are excluded (`status != 'cancelled'`), guaranteeing zero double deduction.

#### Invariant C: Vault Transfers vs School Revenue
- **Scenario:** Moving 1,000 EGP from "Main Cash" to "Vodafone Cash".
- **Ledger Protection:**
  A transfer modifies only `from_vault_id` (-1,000) and `to_vault_id` (+1,000). It creates **zero** records in `revenues` or `expenses`. Total school net funds remain unchanged ($\Delta = 0$).

---

### 3. Empirical Verification Summary (Automated Test Proofs)

- **Test 01:** Cash Collection of 500 EGP on opening 1,000 EGP $\rightarrow$ Balance = 1,500.00 EGP. (Passed)
- **Test 03:** Split installment 500 EGP in Main Cash + 700 EGP in Vodafone Cash $\rightarrow$ Balances = 500.00 EGP & 700.00 EGP. (Passed)
- **Test 04:** Expense of 300 EGP on opening 1,000 EGP $\rightarrow$ Balance = 700.00 EGP. (Passed)
- **Test 06:** Refund of 200 EGP on balance 1,500 EGP $\rightarrow$ Balance = 1,300.00 EGP. (Passed)
- **Test 07:** Salary payment of 1,500 EGP on opening 5,000 EGP $\rightarrow$ Balance = 3,500.00 EGP. (Passed)
- **Test 08:** Transfer 1,000 EGP between vaults $\rightarrow$ Net school revenue = 0.00 EGP. (Passed)
- **Test 21:** Refund double counting verification $\rightarrow$ Balance exactly 1,350.00 EGP, no double-hit. (Passed)
