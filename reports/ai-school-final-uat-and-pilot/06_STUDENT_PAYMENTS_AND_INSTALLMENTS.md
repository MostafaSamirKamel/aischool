# AI SCHOOL — STUDENT PAYMENTS, INSTALLMENT COLLECTION & ALLOCATION ENGINE
**Document ID:** `06_STUDENT_PAYMENTS_AND_INSTALLMENTS.md`  
**Execution Date:** 2026-10-25  
**Canonical Workflow:** `Student Payments → Add Payment` (`/admin/payments/create`)  
**Audit Purpose:** Financial Integrity of Cash Inflows and Installment Fulfillment  

---

## 1. Executive Summary

This report establishes that student installment collection is consolidated into a single, authoritative frontend workflow: **Student Payments → Add Payment**. No competing rogue payment buttons bypass vault posting, and all cash inflows update the ledger synchronously.

---

## 2. Canonical Workflow Verification

```
[Accountant Opens /admin/payments/create]
               │
               ▼
   [Select Student / Enrollment]
               │
               ▼
[System Loads Overdue & Due Installments]
               │
               ▼
     [Enter Amount & Select Vault] ─── (Mandatory Vault Check)
               │
               ▼
 [Authoritative Payment Allocation Engine]
   ├─► Overdue Installments First
   ├─► Currently Due Next
   ├─► Partial Installment (Status: 'partial')
   └─► Fully Paid (Status: 'paid')
               │
               ▼
[Atomic Database Transaction]
   ├─► Create Payment Receipt (PAY-...)
   ├─► Create Revenue Record (REV-...)
   ├─► Increment Selected Vault Balance
   └─► Update Student Outstanding Debt
```

---

## 3. Installment Status & Allocation Rules

| Installment State | Table Column Value | Frontend Display in "Installments Due" | Collectible Balance Impact |
| :--- | :---: | :---: | :---: |
| **Pending / Overdue** | `status = 'pending'` or `'overdue'` | **Visible** in Unpaid Lists | Counted in active student debt |
| **Partially Paid** | `status = 'partial'` | **Visible** with remaining balance | Only unpaid remainder counted |
| **Fully Paid** | `status = 'paid'` | **Disappears** from Unpaid Lists | Debt completely cleared |
| **Waived via Settlement** | `status = 'waived'` | **Excluded** from Unpaid Lists | Excluded from collectible debt |
| **Cancelled Enrollment** | `status = 'cancelled'` | **Excluded** from Unpaid Lists | Excluded from collectible debt |

---

## 4. Key Financial Edge-Cases Tested

| Test ID | Edge Case Tested | Trigger / Condition | Observed Behavior | Status |
| :--- | :--- | :--- | :--- | :---: |
| **PAY-01** | **Overpayment Rejection** | Paying 4,000 EGP on a 3,000 EGP course | Domain validation rejects payment exceeding total debt | **PASS** |
| **PAY-02** | **Duplicate Code Constraint** | Resubmitting exact same receipt code | SQLite UNIQUE constraint rejects duplicate insert | **PASS** |
| **PAY-03** | **Missing Receiving Vault** | Attempting collection without selecting vault | Form blocks submission with mandatory validation error | **PASS** |
| **PAY-04** | **Payment Soft-Delete Reversal**| Canceling erroneous payment via manager | Record soft-deleted; student debt restored; audit intact | **PASS** |
| **PAY-05** | **Multi-Installment Split** | Paying 2,000 EGP covering Installments 1 & 2 | Installment 1 -> `paid`, Installment 2 -> `paid` | **PASS** |

**VERDICT: PAYMENT ENTRY & INSTALLMENT ALLOCATION 100% VERIFIED (PASS)**
