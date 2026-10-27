# 04 — PAYMENT AND VAULT RECONCILIATION

## 1. Accounting Formula & Vault Ledger Reconciliation
In AI School, vault balances are evaluated dynamically across authoritative transaction logs to guarantee zero ledger drift.

### The Authoritative Dynamic Balance Formula:
```php
$balance = $vault->opening_balance
    + Payment::where('vault_id', $vault->id)->where('status', 'completed')->sum('amount')
    + Revenue::where('vault_id', $vault->id)->whereNull('payment_id')->sum('amount')
    - Expense::where('vault_id', $vault->id)->sum('amount');
```

---

## 2. Prevention of Double-Counted Revenue

### The Mathematical Problem:
When an installment is paid, the system records:
1. A `Payment` record in `payments` (representing student installment settlement).
2. A `Revenue` record in `revenues` (representing general income recognition).

If both rows were added to vault cash totals, vault balances would increase by **double** the cash received (e.g., collecting 400 EGP would increase the vault by 800 EGP).

### The Implemented Mathematical Safeguard:
`PaymentObserver` automatically attaches `'payment_id' => $payment->id` to the generated revenue.
Because `Vault::getCurrentBalance()` explicitly filters `Revenue::whereNull('payment_id')`, the revenue row serves exclusively as an audit line item and is **never added a second time to vault cash totals**.

### Reconciliation Proof:
- Initial Opening Balance: `10,000.00 EGP`
- Installment Payment: `400.00 EGP`
- Generated Payment: `400.00 EGP` (`status = 'completed'`, `vault_id = 1`)
- Generated Revenue: `400.00 EGP` (`payment_id = 123`, `vault_id = 1`)
- Dynamic Balance: `10,000.00 + 400.00 = 10,400.00 EGP`
- Double Count Discrepancy: **0.00 EGP (Zero Drift)**

---

## 3. Financial Reconciliation Across Student Lifecycle

| Lifecycle Stage | Installment Original | Installment Paid | Installment Remaining | Enrollment Paid | Enrollment Remaining | Vault Total |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **1. Schedule Created** | 2,000.00 EGP | 0.00 EGP | 2,000.00 EGP | 0.00 EGP | 2,000.00 EGP | 10,000.00 EGP |
| **2. Report Payment (400 EGP)** | 2,000.00 EGP | 400.00 EGP | 1,600.00 EGP | 400.00 EGP | 1,600.00 EGP | 10,400.00 EGP |
| **3. Primary Screen (600 EGP)**| 2,000.00 EGP | 1,000.00 EGP | 1,000.00 EGP | 1,000.00 EGP | 1,000.00 EGP | 11,000.00 EGP |
| **4. Final Settlement (1,000 EGP)**| 2,000.00 EGP | 2,000.00 EGP | 0.00 EGP | 2,000.00 EGP | 0.00 EGP | 12,000.00 EGP |

**Reconciliation Rule Verified:**
`Effective Installment Amount = Allocated Paid Amount + Outstanding Amount` (Holds true at every step).
