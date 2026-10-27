# 03 — INITIAL DOWN PAYMENT & VAULT ALLOCATION TESTS

## 1. Requirement & Rule
- Contracted Amount: 3,000.00 EGP.
- Down payment of 1,000.00 EGP collected upon registration with mandatory receiving vault.
- Down payment must reduce the contract balance to 2,000.00 EGP.
- Down payment must NOT consume or be counted twice in the remaining installment schedule.

## 2. Execution Evidence
- Receiving Vault Selected: Vault ID 1 (كاش - الفرع الرئيسي).
- Vault balance before collection: 76,350.00 EGP.
- Vault balance after collection: 77,350.00 EGP (+1,000.00 EGP).
- Enrollment `paid_amount` updated to 1,000.00 EGP.
- Enrollment `remaining_balance` updated to 2,000.00 EGP.
- No phantom or duplicate receipts posted.

## 3. Test Verdict: PASS
Vault cash inflow and contract balance reduction confirmed with zero double-counting.