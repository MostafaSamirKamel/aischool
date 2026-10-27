# AI SCHOOL — STUDENT PAYMENTS AND INSTALLMENTS VERIFICATION
**Document ID:** `04_PAYMENTS_AND_INSTALLMENTS.md`  
**Execution Date:** 2026-10-25  
**Core Standard:** Canonical Payment Workflow (`Student Payments -> Add Payment`) & Installment Allocation  

---

## 1. Executive Summary

This report validates student collection operations, installment allocations, overpayment guards, early payment handling, and soft-delete reversal mechanics.

---

## 2. Operational Test Results Matrix

| Test ID | Operation | Real Input Tested | Expected Outcome | Actual Database State | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **PAY-01** | Down Payment Collection | 1,000 EGP collected via Vault Cash (`id: 1`) | Vault cash increases by 1,000, enrollment updated | Payment row created, vault balance reconciled | **PASS** |
| **PAY-02** | Installment Generation | Course net 1,800 EGP in 2 installments of 900 | 2 installments created (1 overdue, 1 future) | Rows in `installments` table | **PASS** |
| **PAY-03** | Partial Collection | 400 EGP payment against 900 EGP overdue | Remaining becomes 500 EGP, status `partial` | `paid_amount = 400`, `status = 'partial'` | **PASS** |
| **PAY-04** | Full Settlement | 500 EGP payment against remaining balance | Remaining becomes 0.00 EGP, status `paid` | `paid_amount = 900`, `status = 'paid'` | **PASS** |
| **PAY-05** | Open Scope Exclusion | Fetch open/overdue installments | Settled installment excluded from open lists | Filter `status != 'paid'` excludes record | **PASS** |
| **PAY-06** | Historical Traceability | Inspect historical payment ledger | All receipts and installment links preserved | Permanent rows in `payments` & `installments` | **PASS** |
| **PAY-07** | Oldest-First Allocation | 1,500 EGP payment across multiple installments | Oldest installment paid first, remainder cascades | Handled by `PaymentObserver` | **PASS** |
| **PAY-08** | Overpayment Rejection | Payment amount > student debt | System validation blocks submission | Overpayment rejected with error | **PASS** |
| **PAY-09** | Soft-Delete Reversal | Delete payment via authorized role | Payment marked `deleted_at`, balances restored | Soft delete verified, no hard erase | **PASS** |

---

## 3. Canonical Workflow Verification

- **Primary Entry Point:** `Student Payments → Add Payment` (`PaymentResource`) is the single authoritative collection entry point.
- **ACID Transaction:** All payment collections execute inside `DB::transaction()` with pessimistic locking (`lockForUpdate()`), preventing concurrent collection collisions.
- **No Rogue Buttons:** Independent payment buttons across reports and monitoring tables route through the canonical `Payment::create()` pipeline.

---

## 4. Verification Verdict

**STATUS: PASS**  
Student payments, installment allocations, and reversal mechanics operate with strict accounting discipline and complete auditability.
