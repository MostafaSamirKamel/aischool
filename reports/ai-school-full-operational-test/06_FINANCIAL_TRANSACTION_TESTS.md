# 06 — Financial Transactions & Invoicing Testing

## 1. Accounting Workflow Overview
All financial transactions in AI School follow strict accounting rules: every collected payment must link to an active enrollment, update student ledger balances, post directly to a specified vault, and generate a printable receipt.

## 2. Live Payment Execution Log
- **Enrollment Reference:** `ENR-LIVE-5317` (Total Due: 2,800.00 EGP)
- **Payment Transaction 1:**
  - Amount: 1,400.00 EGP (Initial Installment)
  - Method: `cash`
  - Target Vault: `كاش الرئيسي` (Vault ID: 1)
  - Balance before transaction: 30,640.00 EGP
  - Balance after transaction: 32,040.00 EGP
  - Database Table: `payments` (ID: 25, `status: completed`, `tenant_id: 1`)
- **Payment Transaction 2:**
  - Amount: 1,400.00 EGP (Final Installment)
  - Method: `cash`
  - Balance before transaction: 32,040.00 EGP
  - Balance after transaction: 33,440.00 EGP
  - Enrollment Paid Status: 2,800.00 / 2,800.00 EGP (100% Fully Paid)

## 3. Audit & Ledger Reconciliation
| Metric | Expected Value | Database Value | Discrepancy |
|---|---|---|---|
| Total Contracted | 2,800.00 EGP | 2,800.00 EGP | 0.00 EGP |
| Total Collected | 2,800.00 EGP | 2,800.00 EGP | 0.00 EGP |
| Outstanding Debt | 0.00 EGP | 0.00 EGP | 0.00 EGP |
| Vault Inflow | +2,800.00 EGP | +2,800.00 EGP | 0.00 EGP |
| Receipt Generated | Yes (REC-LIVE-001/002) | Verified in DB | None |
