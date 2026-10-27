# 08 — REFUND ELIGIBILITY, SETTLEMENT & VAULT DISBURSEMENT

## 1. Distinction: Liability vs Executed Cash Refund
- Synthetic Student (ID: 146) enrolled in course with 2,000.00 EGP paid.
- Cancellation with approved partial refund: 800.00 EGP refundable, 1,200.00 EGP retained by school.
- Step 1 (Liability Creation):
  - Enrollment `settlement_status` becomes `pending_refund`.
  - `refundable_amount = 800.00`, `refunded_amount = 0.00`.
  - **Vault Balance: Completely unchanged (Zero cash movement).**

## 2. Authorized Refund Execution (`executeRefund`)
- Secretary selects reimbursing Vault ID 1.
- Balance before disbursement: 80,350.00 EGP.
- Disbursement executed: 800.00 EGP.
- System Actions:
  1. Creates official disbursement `Expense` (Category: استرداد رسوم اشتراك, Status: `paid`).
  2. Creates trace `Payment` record (Status: `refunded`).
  3. Updates Enrollment `refunded_amount = 800.00`, `refund_status = 'completed'`, `settlement_status = 'completed'`.
  4. Vault balance decreases to 79,550.00 EGP (-800.00 EGP).
- Step 2 (Duplicate Prevention):
  - Second attempt to disburse 800.00 EGP was immediately blocked by `ValidationException`.

## 3. Test Verdict: PASS
Refund liability separation, mandatory reimbursing vault, and duplicate prevention verified.