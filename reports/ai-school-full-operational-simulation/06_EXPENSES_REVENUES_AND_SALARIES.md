# AI SCHOOL — EXPENSES, REVENUES, AND INSTRUCTOR SALARIES
**Document ID:** `06_EXPENSES_REVENUES_AND_SALARIES.md`  
**Execution Date:** 2026-10-25  
**Core Principles:** 1) Calculation is separate from disbursement, 2) Pending expenses do not deduct cash, 3) Evaluation ratings independent of salary  

---

## 1. Executive Summary

This report documents the verification of operating expenses, revenue ledger synchronization, instructor session completion tracking, salary entitlement calculations, and authorized payroll disbursements.

---

## 2. Operational Test Results Matrix

| Test ID | Operation | Real Input Tested | Expected Outcome | Actual Database State | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **FIN-EXP-01** | Expense Creation | Expense: صيانة معامل (600 EGP) + Vault Cash | Expense logged, vault cash deducted by 600 | Row in `expenses`, vault updated | **PASS** |
| **FIN-EXP-02** | Revenue Auto-Sync | Student payment posted (1,000 EGP) | Matching revenue entry generated in `revenues` | Row in `revenues` with vault ID | **PASS** |
| **FIN-SAL-01** | Salary Entitlement | Instructor completes 4 sessions @ 200 EGP | Entitlement calculated: 800 EGP (NO cash moved) | Accrued entitlement recorded | **PASS** |
| **FIN-SAL-02** | Salary Disbursement | Disburse 800 EGP from Vault Cash | Vault cash decreases by 800, status `paid` | Row in `salary_payments` | **PASS** |
| **FIN-SAL-03** | Mandatory Vault on Salary| Attempt payout without `vault_id` | Operation rejected by validation | Payout blocked | **PASS** |
| **FIN-SAL-04** | Evaluation Independence| Supervisor gives instructor 70% rating | Contractual salary calculation untouched | Salary = 800 EGP intact | **PASS** |

---

## 3. Financial Separation Rules

1. **Accrual vs. Cash Disbursement:**
   - Instructor salary calculation calculates accrued liabilities.
   - Cash outflow occurs only when an authorized accountant selects a vault and posts the payment.
2. **Pedagogical Autonomy:**
   - Teacher evaluation scores serve developmental and quality assurance purposes and do not automatically dock contractual pay.

---

## 4. Verification Verdict

**STATUS: PASS**  
Expenses, revenues, and payroll disbursements operate with strict accounting compliance and clear role boundaries.
