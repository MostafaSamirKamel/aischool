# AI School — Quality Assurance Report
# Report 12: Instructor and Staff Payroll Report

**Date:** 2026-10-20  
**Environment:** Staging / Isolated SQLite (`:memory:` & `database/database.sqlite`)  
**Test Suite Reference:** `tests/Feature/E2E/FullCompositeStudentJourneyE2E001Test.php`, `tests/Feature/InstructorSalaryTest.php`  
**Author:** Educational Operations Specialist & Financial Systems Auditor  

---

## 1. Executive Summary

This report provides the full evaluation of employee compensation mechanisms within the AI School platform, bifurcated into:
1. **Instructor Variable Compensation:** Session-based entitlements calculated strictly from delivered, completed teaching sessions.
2. **Administrative Staff Payroll:** Fixed periodic compensation for secretaries, registration officers, accountants, and branch managers.

All tested payroll operations complied with financial integrity requirements: zero duplicate entitlements, exact partial disbursements, immediate ledger reflection, and complete protection of historical instructor compensation across student transfers and enrollment cancellations.

---

## 2. Instructor Session-Based Compensation Model

### 2.1 Formula & Compensation Engine
Instructor compensation is governed by:
$$\text{Earned Compensation} = \sum_{\text{completed sessions}} (\text{Session Base Rate} \times \text{Eligibility Multiplier})$$

- Standard Synthetic Instructor Rate: **150.00 EGP** per completed teaching session.
- Postponed or canceled sessions produce **0.00 EGP** entitlement.
- Sessions marked `in_progress` or `upcoming` produce **0.00 EGP** until explicitly finalized with attendance.

### 2.2 Mandatory Payout Verification Scenario (600 EGP Test)
An instructor completed 4 qualifying sessions in Group A:
$$\text{Entitlement} = 4 \times 150.00 \text{ EGP} = 600.00 \text{ EGP}$$

| Step | Action | Amount Disbursed | Source Vault | Outstanding Balance | Vault Impact | Status |
|---|---|---|---|---|---|---|
| **0** | Finalize 4 Sessions | 0.00 EGP | N/A | 600.00 EGP | None | Entitlement Generated |
| **1** | Partial Payment #1 | 400.00 EGP | VLT-01 (Cash B1) | 200.00 EGP | -400.00 EGP | Partial Payout Recorded |
| **2** | Final Payment #2 | 200.00 EGP | VLT-01 (Cash B1) | **0.00 EGP** | -200.00 EGP | **Fully Settled** |
| **3** | Duplicate Attempt | 200.00 EGP | VLT-01 (Cash B1) | 0.00 EGP | Blocked | **Rejected (Zero Outstanding)** |

**Verification Points:**
- `InstructorSalary::where('instructor_id', $instId)->sum('paid_amount')` = 600.00 EGP.
- `InstructorSalary::where('instructor_id', $instId)->sum('remaining_amount')` = 0.00 EGP.
- Cash Vault 1 deducted by exactly 600.00 EGP (in two atomic ledger records: -400.00 and -200.00).
- Third attempt to disburse 200 EGP was rejected with: `ValidationException: Requested payout exceeds outstanding salary balance (0.00 EGP)`.

---

## 3. Compensation Invariance Under Student Lifecycle Events

### 3.1 Group Transfer Invariance (TRANSFER-007)
- **Scenario:** Student Ahmed attended 4 sessions in Python Group A (Instructor Tariq), then transferred to Python Group B (Instructor Sara) where he attended 2 sessions.
- **Verification:**
  - Instructor Tariq's delivered session count remained 4 (Entitlement: 600.00 EGP). Ahmed's transfer did NOT invalidate Tariq's session logs or recalculate his compensation.
  - Instructor Sara earned entitlement solely for the 2 sessions delivered in Group B (Entitlement: 300.00 EGP).
  - Sara did NOT receive retroactive entitlement for Ahmed's previous 4 sessions.
  - **Result: PASS. Zero double-entitlement.**

### 3.2 Cancellation Invariance (CANCEL-008 & CANCEL-014)
- **Scenario:** An enrollment is canceled after the student completed 4 sessions and the instructor was already disbursed the 600.00 EGP salary.
- **Verification:**
  - The student cancellation waived remaining tuition debt and refunded eligible fees.
  - **Zero reversal** was applied to the instructor's completed session records.
  - The instructor's paid salary was NOT deducted or reclaimed.
  - Completed session attendance logs remain historically immutable.
  - **Result: PASS. Legitimate instructor compensation remains preserved.**

---

## 4. Administrative Staff Fixed Payroll

Administrative staff salaries operate on a monthly recurring payroll structure:

| Employee Role | Staff Name | Branch | Agreed Monthly Salary | Paid This Cycle | Paying Vault | Outstanding | Status |
|---|---|---|---:|---:|---|---:|---|
| **Secretary 1** | Mona Adel | Branch 1 | 4,500.00 EGP | 4,500.00 EGP | VLT-01 (Cash B1) | 0.00 EGP | Fully Paid |
| **Secretary 2** | Nader Samir | Branch 2 | 4,500.00 EGP | 4,500.00 EGP | VLT-04 (Cash B2) | 0.00 EGP | Fully Paid |
| **Reg. Officer 1** | Karim Fathy | Branch 1 | 5,000.00 EGP | 5,000.00 EGP | VLT-01 (Cash B1) | 0.00 EGP | Fully Paid |
| **Reg. Officer 2** | Hoda Mostafa | Branch 1 | 5,000.00 EGP | 2,500.00 EGP | VLT-01 (Cash B1) | 2,500.00 EGP | Partial Payout |
| **Accountant 1** | Yasser Taha | Branch 1 | 6,500.00 EGP | 6,500.00 EGP | VLT-01 (Cash B1) | 0.00 EGP | Fully Paid |
| **Branch Manager 1**| Sherif Omar | Branch 1 | 9,000.00 EGP | 9,000.00 EGP | VLT-01 (Cash B1) | 0.00 EGP | Fully Paid |

**Audit Checks:**
- All payouts require the explicit permission `process_payroll`.
- Vault balances were verified prior to debiting; insufficient funds trigger immediate transaction cancellation.
- Payout receipts automatically link to the employee file and expense ledger under category `Payroll & Salaries`.

---

## 5. Security & Authorization Matrix

| Action | Allowed Roles | Prohibited Roles | Observed Behavior When Unauthorized |
|---|---|---|---|
| View Instructor Salary Rates | Super Admin, Accountant | Instructor, Secretary, Parent | 403 Forbidden; rate column masked |
| Calculate Session Entitlement | Automated System Service | Manual user override | System-calculated only; direct modification blocked |
| Disburse Salary Payment | Super Admin, Accountant (with `disburse_payroll`) | Secretary, Instructor, Registration Officer | 403 Forbidden; disburse action button omitted and API blocked |
| Cross-Branch Salary Payout | Super Admin Only | Branch 1 Accountant accessing Branch 2 Staff | Rejected: `Unauthorized branch vault access` |

---

## 6. Audit Conclusion

The payroll and salary engine demonstrated complete operational reliability:
- Session-based instructor earnings are accurately computed, trackable to underlying attendance logs, and strictly protected from academic churn.
- Vault disbursements match real physical money deductions with zero mathematical discrepancy.
- Administrative payroll executes with role-specific authorization and accurate accruals.
