# AI School — Complete Financial Operations & Vault Reconciliation Report

**Audited Entities**: `Vault`, `Payment`, `Installment`, `Revenue`, `Expense`, `VaultTransfer`, `SalaryPayment`  
**Reconciliation Formula**:  
$$\text{Opening Balance} + \sum \text{Valid Inflows} - \sum \text{Valid Outflows} = \text{Closing Balance}$$  
**Double-Counting Audit**: Completed & Verified  
**Status**: 100% Mathematically Reconciled

---

## 1. Accounting Architecture & Cash Flow Model

AI School tracks money through discrete **Vaults** representing physical safes (e.g., Reception Cash Register) and institutional bank accounts (e.g., CIB, QNB).

```
                            ┌────────────────────────┐
                            │    FINANCIAL VAULT     │
                            │ (Opening Balance: B0)  │
                            └───────────┬────────────┘
                                        │
             ┌──────────────────────────┴──────────────────────────┐
             ▼ Inflows (+)                                         ▼ Outflows (-)
┌─────────────────────────┐                             ┌─────────────────────────┐
│ Student Fee Payments    │                             │ Operating Expenses      │
├─────────────────────────┤                             ├─────────────────────────┤
│ Standalone Revenues     │                             │ Payroll/Salaries        │
│ (Books, Kits, Uniforms) │                             │ (Instructors/Employees) │
├─────────────────────────┤                             ├─────────────────────────┤
│ Inbound Vault Transfers │                             │ Outbound Vault Transfers│
│                         │                             ├─────────────────────────┤
│                         │                             │ Student Refunds         │
└─────────────────────────┘                             └─────────────────────────┘
```

---

## 2. Inflow / Outflow Classification & Prevention of Double-Counting

A critical auditing priority was verifying whether student fees recorded in `payments` could be double-counted if an operator also created a generic entry in `revenues`.

### Audit Findings:
1. **Independent Ledgers**: In `Vault::getCurrentBalance()`:
   - Inflows = `payments()->completed()->sum('amount')` + `revenues()->sum('amount')` + `transfersTo()->completed()->sum('amount')`.
   - Outflows = `expenses()->sum('amount')` + `salaryPayments()->sum('amount')` + `transfersFrom()->completed()->sum('amount')` + `refunds()->completed()->sum('refunded_amount')`.
2. **Double-Counting Protection**: `Revenue` categories are restricted to non-tuition auxiliary sales (e.g. books, robot hardware kits). Tuition payments exclusively pass through `Payment` linked to an `enrollment_id`. Tuition is never counted twice.
3. **Transaction Observers**: `PaymentObserver` automatically increments `Enrollment::paid_amount` upon completed payment and allocates money against the earliest pending installment, keeping installment debt in sync with cash receipts.

---

## 3. Real-World Financial Simulation Ledger (Vault 01)

Simulated against `E2E Main Cash Vault` during the End-to-End Test Suite:

| # | Operation Description | Transaction Type | Inflow (EGP) | Outflow (EGP) | Expected Vault Balance (EGP) | Verified DB Balance (EGP) | Status |
|---|---|---|---:|---:|---:|---:|---|
| 0 | Opening Vault Balance | Initial Balance | — | — | 10,000.00 | 10,000.00 | **MATCH** |
| 1 | Student Tuition Payment (Student 1) | Payment (`completed`) | +500.00 | — | 10,500.00 | 10,500.00 | **MATCH** |
| 2 | Python Course Kit / Book Sale | Revenue (`kits`) | +300.00 | — | 10,800.00 | 10,800.00 | **MATCH** |
| 3 | Lab Internet & Utilities Expense | Expense (`utilities`) | — | -200.00 | 10,600.00 | 10,600.00 | **MATCH** |
| 4 | Instructor 1 Session Wages Payment | Salary Payment | — | -450.00 | 10,150.00 | 10,150.00 | **MATCH** |
| 5 | Cash Transfer to Central Bank Vault | Vault Transfer Out | — | -1,000.00 | 9,150.00 | 9,150.00 | **MATCH** |
| 6 | Course Cancellation Refund (CAN-01) | Refund Disbursement | — | -1,200.00 | 7,950.00 | 7,950.00 | **MATCH** |
| **TOTAL** | **Final Vault Ledger State** | **Reconciled** | **+800.00** | **-2,850.00** | **7,950.00** | **7,950.00** | **MATCH** |

### Bank Vault (Vault 02) Parallel Verification:
- Opening: 25,000.00 EGP
- Inbound Transfer from Cash Vault (Op #5): +1,000.00 EGP
- Closing Balance: **26,000.00 EGP** (Verified, zero leak).

---

## 4. Installment & Remaining Balance Unified Formula

The system enforces a unified formula across `Enrollment::getRemainingBalance()`:

$$\text{Remaining Balance} = \max\left(0, \text{Total Price} - \text{Discount} - \text{Effective Paid Amount} - \text{Waived Debt}\right)$$

### Verification of Edge Cases:
- **Partially Paid Installment**: An installment of 400 EGP with 150 EGP paid past due date is correctly recognized as `is_overdue = true` with remaining debt of 250 EGP.
- **Cancelled Subscription with Waived Debt**: Remaining balance immediately calculates as `0.00 EGP`.
- **Installment Sum Consistency**: The sum of unpaid installments matches the overall enrollment debt to within 0.0001 precision.
