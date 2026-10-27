# 07 — REFUNDS & CANCELLATION FINANCIAL SETTLEMENT TESTS

## 1. Important Financial Distinction
- **Cancellation without Refund**: Cancels subscription, drops remaining debt (or keeps debt), but does NOT post any cash movement. Refund vault is null.
  - Test: Canceled enrollment with 0 refund -> Zero vault change. (**PASS**)
- **Cancellation with Immediate Refund**: Mandates reimbursing vault. Decreases vault balance atomically.
  - Test: 500 EGP refund disbursed from Vault 1 -> Vault balance decreased by exactly 500 EGP. (**PASS**)
- **Deferred Refund**: Creates refund liability (`status = 'pending_refund'`). No cash movement until authorized disbursement.
- **Disbursement Execution**: Calls `executeRefund` with reimbursing vault. Disburses cash and marks status `'completed'`.