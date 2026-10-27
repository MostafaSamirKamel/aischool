# 04 — INSTALLMENT GENERATION & COLLECTION AUDIT

## 1. Schedule Generation
- Remaining Contract Obligation: 2,000.00 EGP.
- Schedule Configuration: 2 installments of 1,000.00 EGP each.
  - Installment #1: Amount 1,000.00 EGP, Due Date: +15 days, Status: `pending`.
  - Installment #2: Amount 1,000.00 EGP, Due Date: +45 days, Status: `pending`.
- Verified: Total scheduled obligations (2,000.00 EGP) reconcile exactly with outstanding contract debt.

## 2. Collection Engine: Student Payments -> Add Payment
All installment payments use the canonical single entry point:

### Step 1: Partial Collection of 400.00 EGP
- Entry Form: `CreatePayment` with Vault ID 1.
- Allocation: Applied to Installment #1 by seniority.
- Installment #1 State: `paid_amount = 400.00`, `remaining = 600.00`, `status = 'partial'`.
- Open Installment Count: 2 (both remain open).
- Vault Balance: Increased by 400.00 EGP.

### Step 2: Full Settlement of 600.00 EGP
- Entry Form: `CreatePayment` with Vault ID 1.
- Allocation: Applied to Installment #1.
- Installment #1 State: `paid_amount = 1000.00`, `remaining = 0.00`, `status = 'paid'`.
- Open Installment List: Installment #1 disappears from open/overdue lists (`scopeOpen()`).
- Historical Paid List: Preserved in historical records (`scopePaid()`).
- Vault Balance: Increased by 600.00 EGP.

### Step 3: Payment Reversal
- Deletion of 200.00 EGP test payment.
- Result: Vault balance reversed; installment paid amounts recalculated; balances restored safely.

## 3. Test Verdict: PASS
All installment states, partial allocations, full settlements, and payment reversals verified.