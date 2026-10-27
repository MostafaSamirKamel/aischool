# Report 16: Salary Disbursement and Cash Vault Deduction

## 1. Disbursement Transaction Details
The calculated entitlement of 1,350.00 EGP was disbursed to Lead Instructor 01091812925:

| Transaction Parameter | Recorded Value |
|---|---|
| **Salary Payment ID** | `28` |
| **Instructor Profile ID** | `337` (01091812925) |
| **Disbursing Vault** | Vault ID 1 (كاش - Abu El Matamir) |
| **Disbursed Amount** | **1,350.00 EGP** |
| **Session Count Covered** | 9 completed sessions |
| **Payment Date** | `2026-10-27 00:00:00` |
| **Posting Status** | Finalized / Posted |

---

## 2. Vault Balance Impact
- **Vault 1 Balance Prior to Salary Disbursement:** 221,008.32 EGP
- **Salary Cash Outflow:** -1,350.00 EGP
- **Vault 1 Balance Following Salary Disbursement:** 219,658.32 EGP
- **Double-Payment Prevention:** Attempting to disburse a second payment for the same session period is strictly blocked by the payroll validator.

---

## 3. Test Result Format
- **Test ID:** SAL-DIS-01
- **Module:** Treasury & Payroll Disbursement
- **Role:** Accountant / Branch Manager
- **Route:** `/admin/salary-payments/create`
- **Initial State:** Unpaid salary entitlement of 1,350.00 EGP.
- **Actual Action Executed:** Disbursed salary through Vault 1; verified ledger entries.
- **Expected Result:** Salary payment record created; Vault 1 cash decreased by 1,350.00 EGP; remaining instructor liability becomes 0.00 EGP.
- **Actual Result:** Salary payment ID 28 recorded; vault balance updated.
- **Status:** **PASS**