# 06 — EXPENSES & INSTRUCTOR SALARIES AUDIT TESTS

## 1. Expenses Workflow
- **Paid Expense**: Requires active vault. Decreases vault balance by exact expense amount.
  - Test: 250 EGP expense paid from Vault 1 -> Vault balance decreased by 250 EGP. (**PASS**)
- **Unpaid / Pending Expense**: Creates liability obligation without moving cash.
  - Test: 700 EGP pending expense with null vault -> Vault balance remained unchanged. (**PASS**)

## 2. Instructor Salaries Workflow
- **Paid Salary**: Requires active vault and sufficient balance.
  - Test: 350 EGP salary disbursement paid from Vault 1 -> Vault balance decreased by 350 EGP. (**PASS**)
- **Calculated Entitlement**: Calculating hours or entitlement does not post cash movements.