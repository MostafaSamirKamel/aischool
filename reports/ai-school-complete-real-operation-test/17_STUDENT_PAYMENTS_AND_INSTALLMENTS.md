# Report 17: Student Fees, Down Payments, and Installments Accounting

## 1. Upfront and Installment Collections
Across the 29 simulation students:
- **Upfront Revenue Collected at Enrollment:**
  - 12 Full Payments: (7 x 1,500) + (5 x 1,200) = 10,500 + 6,000 = 16,500.00 EGP
  - 17 Down Payments: (10 x 500) + (7 x 400) = 5,000 + 2,800 = 7,800.00 EGP
  - **Total Upfront Cash Inflow:** **24,300.00 EGP**
- **Installment Schedule Lifecycle Testing:**
  - **Full Installment Payment:** Student ID 204 paid Installment #1 in full (500.00 EGP). Installment status transitioned from `pending` to `paid`.
  - **Partial Installment Payment:** Student ID 205 paid 250.00 EGP towards Installment #1 (500.00 EGP). Remaining balance automatically updated to 250.00 EGP with status `partially_paid`.
  - **Overdue Detection:** System accurately flags unpaid past-due installments in financial aging reports.

---

## 2. Receipt and Ledger Integrity
- Every cash collection generates an authoritative receipt number and ledger entry associated with the receiving vault.
- Overpayment prevention: Submitting a payment exceeding the installment's outstanding debt is rejected by model validation.

---

## 3. Test Result Format
- **Test ID:** FIN-PAY-01
- **Module:** Student Accounts Receivable
- **Role:** Accountant / Cashier
- **Route:** `/admin/payments/create`
- **Initial State:** Student with pending installment obligations.
- **Actual Action Executed:** Recorded full payment (500 EGP) and partial payment (250 EGP).
- **Expected Result:** Installment balances update; cash posted to Vault 1; receipts generated.
- **Actual Result:** Receipts generated; installment remaining balances accurate.
- **Status:** **PASS**