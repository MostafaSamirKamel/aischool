# INSTALLMENT AND VAULT RECONCILIATION

## 1. Accounting Formula & Vault Balance Integrity
In AI School, vault balances are computed dynamically from transaction records to prevent ledger drift.

### The Authoritative Vault Formula:
```php
$balance = $vault->opening_balance
    + Payment::where('vault_id', $vault->id)->where('status', 'completed')->sum('amount')
    + Revenue::where('vault_id', $vault->id)->whereNull('payment_id')->sum('amount')
    - Expense::where('vault_id', $vault->id)->sum('amount');
```

---

## 2. Prevention of Double Counting

### The Core Risk:
When an installment payment is collected, two financial records are produced:
1. A `Payment` record representing the student's installment settlement.
2. A `Revenue` record representing the cash receipt for general accounting.

If both records were counted independently in the vault ledger, cash balances would increase by **double** the received amount (e.g., collecting 400 EGP would inflate the vault by 800 EGP).

### The Implemented Protection:
`PaymentObserver` automatically tags the generated `Revenue` record with:
```php
'payment_id' => $payment->id
```
Because `Vault::getCurrentBalance()` explicitly filters `Revenue::whereNull('payment_id')`, the revenue record serves exclusively as an audit trail line item and is **never** added a second time to vault cash totals.

### Reconciliation Proof:
- Initial Vault Opening Balance: `5,000.00 EGP`
- Installment Payment Collected: `400.00 EGP`
- Generated Payment Record: `400.00 EGP` (`status = 'completed'`, `vault_id = 1`)
- Generated Revenue Record: `400.00 EGP` (`payment_id = 123`, `vault_id = 1`)
- Computed Vault Balance: `5,000.00 + 400.00 = 5,400.00 EGP`
- Discrepancy / Drift: **0.00 EGP** (Verified in `test_e_partial_collection_through_report_action`).

---

## 3. Installment Debt vs. Enrollment Total Reconciliation

```
[Total Course Price] 
   = Σ(Installment Original Amounts)
   = 1,000.00 EGP

[Student Payments Made] 
   = Σ(Payment Records for Enrollment) 
   = 400.00 EGP

[Installment Paid Amount] 
   = Σ(Installment Paid Amounts) 
   = 400.00 EGP

[Outstanding Enrollment Debt] 
   = Total Course Price - Student Payments Made 
   = 600.00 EGP

[Remaining Installments Total] 
   = Σ(Installment Remaining Amounts) 
   = 600.00 EGP
```

There is zero discrepancy between individual installment records, enrollment-level financial summary counters, and cash vault totals.

---

## 4. Reconciliation Table Across Test Life-Cycle

| Event | Installment Paid | Installment Remaining | Enrollment Paid | Payment Record | Revenue Record | Vault Balance |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Initial State** | 0.00 | 1,000.00 | 0.00 | None | None | 5,000.00 |
| **Partial Pay (400 EGP)** | 400.00 | 600.00 | 400.00 | 400.00 | 400.00 (linked) | 5,400.00 |
| **Final Pay (600 EGP)** | 1,000.00 | 0.00 | 1,000.00 | 600.00 | 600.00 (linked) | 6,000.00 |
| **Cancelled Enrollment** | 0.00 (unpayable)| 0.00 | 0.00 | Retained | Retained | 6,000.00 |

Historical receipts and ledger entries remain intact even when course enrollments are cancelled or settled, ensuring permanent financial auditability.
