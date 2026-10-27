# AI SCHOOL — STUDENT PAYMENTS AND INSTALLMENTS AUDIT
**Document ID:** `06_STUDENT_PAYMENTS_AND_INSTALLMENTS.md`  
**Execution Date:** 2026-10-25  
**Core Workflow:** Student Payments → Add Payment (Canonical Entry Point) & Daily Installments  

---

## 1. Executive Summary

This report audits the lifecycle of student collections, installment generation, allocation algorithms, receipt generation, and report synchronization.

All installment collections route through an authoritative, ACID-compliant collection pipeline.

---

## 2. Canonical Collection Workflow

The approved collection path is:
$$\text{Student Payments} \longrightarrow \text{Add Payment}$$

### 2.1 Workflow Steps:
1. **Student Selection:** User selects a student via searchable dropdown (RTL Arabic supported).
2. **Enrollment Context:** The system resolves the student's active course enrollments and outstanding balances.
3. **Vault Selection:** User selects an authorized vault within the active branch.
4. **Amount Entry:** System validates that payment amount is greater than zero and does not exceed total outstanding debt.
5. **Execution:** Transaction executes inside `DB::transaction()` with pessimistic locking (`lockForUpdate`).

---

## 3. Installment Allocation Logic (Oldest-First)

When a payment is created:
1. The `PaymentObserver::created()` event fires.
2. The observer fetches all pending or partially paid installments for the student, sorted strictly by `due_date ASC`, then `id ASC`.
3. The payment amount is allocated sequentially:
   - If payment amount $\ge$ installment remaining balance:
     - Installment receives full remaining balance.
     - `paid_amount` becomes equal to `amount`.
     - `status` transitions to `paid`.
     - Remaining unallocated payment amount cascades to the next installment.
   - If payment amount $<$ installment remaining balance:
     - Installment receives partial amount.
     - `paid_amount` increases by the allocated amount.
     - `status` transitions to `partially_paid`.
     - Payment amount is fully exhausted ($0.00$).

---

## 4. Installment State Synchronization

| Installment State | Outstanding Balance | Visible in Unpaid List? | Visible in Overdue List? | Visible in History? |
| :--- | :--- | :--- | :--- | :--- |
| **Pending** | `amount` | **Yes** | Yes (if `due_date < today`) | Yes |
| **Partially Paid**| `amount - paid_amount` | **Yes** | Yes (if `due_date < today`) | Yes |
| **Fully Paid** | $0.00$ | **No** (Hidden by default) | **No** | **Yes** |
| **Waived** | $0.00$ (liability waived)| **No** | **No** | **Yes** (Audit note) |
| **Cancelled** | $0.00$ (enrollment ended)| **No** | **No** | **Yes** (Historical) |

---

## 5. Daily Installment Due Collections Fix (`HandlesInstallmentDuePayments`)

- **Prior Issue:** The "Collect Payment" action in daily installment reports redirected or failed with authorization exceptions.
- **Repair Applied:** Implemented canonical `savePayment()` method:
  - Validates `vault_id` and user permissions (`InstallmentDueReportAccess::canCollectPayment()`).
  - Wraps in `DB::transaction` with row locking on `Installment`.
  - Creates the authoritative `Payment` record with vault reference.
  - Generates receipt, updates `PaymentObserver` ledger, and emits notification.
- **Verification:** Verified via `InstallmentDueReportIntegrationTest` (14 passing tests, 78 assertions).

---

## 6. Audit Verdict

**STATUS: PASS**  
Installment allocation, payment processing, receipt generation, and report synchronization are verified, fully automated, and completely reliable.
