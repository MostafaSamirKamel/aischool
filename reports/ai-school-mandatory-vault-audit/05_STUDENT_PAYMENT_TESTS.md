# 05 — STUDENT PAYMENT & INSTALLMENT ALLOCATION TESTS

## 1. Test Summary
All student payment workflows were tested using synthetic records in staging.

| Test Case | Inputs | Expected Result | Actual DB Result | Status |
|---|---|---|---|---|
| Valid payment with vault | Student ID: 140, Vault ID: 1, Amount: 1,000 EGP | Receipt created, vault +1,000 EGP, Inst #1 paid | Payment ID: 173 created, Vault balance updated | **PASS** |
| Payment without vault | Student ID: 140, Vault ID: null, Amount: 500 EGP | Rejected by `Payment::saving` hook | ValidationException thrown | **PASS** |
| Invalid vault ID | Student ID: 140, Vault ID: 999999, Amount: 500 EGP | Rejected as non-existent vault | ValidationException thrown | **PASS** |
| Cross-tenant vault | Tenant 1 payment with Tenant 2 vault | Rejected by tenant isolation check | AuthorizationException thrown | **PASS** |
| Overpayment prevention | Contract balance: 2,000 EGP, Payment: 5,000 EGP | Rejected by overpayment validator | ValidationException thrown | **PASS** |
| Full installment settlement | Inst #2 remaining: 1,000 EGP, Payment: 1,000 EGP | Status becomes 'paid', disappears from open list | Inst #2 status: 'paid', paid_amount: 1000.00 | **PASS** |
| Partial settlement | Inst #3 amount: 1,000 EGP, Payment: 400 EGP | Status becomes 'partial', remaining 600 EGP | Inst #3 status: 'partial', paid_amount: 400.00 | **PASS** |
| Payment reversal | Delete payment of 200 EGP | Vault balance reversed, installment status updated | Balance restored, inst recalculated | **PASS** |