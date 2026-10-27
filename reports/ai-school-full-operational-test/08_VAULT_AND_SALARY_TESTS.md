# 08 — Vault Management & Instructor Salary Disbursement

## 1. Vault Dynamic Balance Architecture
In the AI School Management System, vaults do not rely on an error-prone static balance column. Instead, `Vault::getCurrentBalance()` dynamically computes the real-time balance via double-entry principles:
$$\text{Current Balance} = \text{Opening Balance} + \sum \text{Payments} + \sum \text{Revenues} + \sum \text{Transfers In} - \sum \text{Expenses} - \sum \text{Transfers Out} - \sum \text{Salaries}$$

## 2. Instructor Compensation Workflow
- **Instructor:** أحمد محمد - مدرب (`User ID: 14`)
- **Completed Sessions:** 2 eligible educational sessions completed.
- **Contract Formula:** Standard session rate (120.00 EGP / session) $\times$ 2 sessions = 240.00 EGP earned compensation.
- **Disbursement Transaction:**
  - Payout Amount: 240.00 EGP
  - Source Vault: `كاش الرئيسي` (Vault ID: 1)
  - Balance before payout: 33,440.00 EGP
  - Balance after payout: 33,200.00 EGP
  - Recorded in: `salary_payments` table (`status: paid`, `payment_method: cash`)

## 3. Vault Transfer & Expense Validation
- Expense of 500.00 EGP (Educational Supplies) logged against Vault 1: Balance reduced to 32,700.00 EGP.
- Cash movement log confirmed that every transaction has a traceable actor, timestamp, and voucher ID.
