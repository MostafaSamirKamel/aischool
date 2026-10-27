# AI SCHOOL — EXPENSES, REVENUES & INSTRUCTOR SALARIES AUDIT
**Document ID:** `08_SALARIES_AND_EXPENSES.md`  
**Execution Date:** 2026-10-25  
**Audit Purpose:** Integrity of Cash Outflows, Operational Overhead & Payroll  

---

## 1. Executive Summary

This report audits the financial mechanisms governing school operating expenses and instructor salary disbursements. Strict separation is maintained between entitlement calculation and cash payout, with mandatory disbursing vault selection on every completed payment.

---

## 2. Operational Expenses Lifecycle

| Step / Workflow | Executed Action | Observed Business Rule | Ledger Impact | Status |
| :--- | :--- | :--- | :--- | :---: |
| **Create Expense Draft** | Record utility bill (1,500 EGP) | Status initialized as `pending` | **0.00 EGP** vault deduction | **PASS** |
| **Disburse Expense** | Select Vault #1 & submit | Status updated to `paid` | Vault #1 deducted by 1,500 EGP | **PASS** |
| **Categorization** | Assign `expense_category_id` | Categorized for reporting | Placed under Operating Expenses | **PASS** |
| **Expense Correction** | Reverse erroneous entry | Soft-delete / reversal record | Vault credited; audit note saved | **PASS** |

---

## 3. Instructor Salary Workflow & Payroll Safeguards

### A. Separation of Calculation from Disbursement
1. **Delivered Sessions Tracking:** Instructors earn compensation strictly based on verified delivered sessions linked in `attendance_sessions`.
2. **Entitlement vs. Payout:** Calculating an instructor's earned monthly entitlement creates an accrual view in reporting; it **does NOT automatically withdraw cash** from any vault.
3. **Authorized Disbursement:** Only an authorized accountant or manager can execute a `SalaryPayment` by explicitly selecting a disbursing vault with sufficient liquidity.

### B. Salary Payout Audit Log

| Payout ID | Instructor Name | Period | Sessions Count | Amount (EGP) | Disbursing Vault | Status | Verification |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **SAL-001** | أحمد محمد - مدرب رئيسي | 2026-10 | 12 | 4,500.00 | كاش (Vault #1) | `paid` | **PASS** |
| **SAL-002** | المدرب 1 - فرع القاهرة | 2026-10 | 10 | 3,800.00 | كاش (Vault #1) | `paid` | **PASS** |
| **SAL-003** | مدرب فرع دمنهور 1 | 2026-10 | 8 | 3,200.00 | الخزينة النقدية (Vault #4) | `paid` | **PASS** |

### C. Overpayment Prevention
Attempting to disburse more than the verified earned entitlement is blocked by payroll approval rules, ensuring compensation remains mathematically aligned with course attendance hours.

---

## 4. Verification Verdict

**STATUS: EXPENSES AND SALARIES AUDIT FULLY VERIFIED (PASS)**
