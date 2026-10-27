# 15 — Subscription Cancellation, Financial Settlement & Refund Workflow

## 1. Three-Stage Separation Rule
> **Domain Constraint:** Enrollment cancellation, financial debt settlement, and cash refund payment are strictly separate operations governed by distinct permissions and service methods.

## 2. Test Execution Details
- **Test Enrollment:** `ENR-LIVE-5317`
- **Initial Contracted Fee:** 2,800.00 EGP
- **Total Paid:** 1,400.00 EGP
- **Service Invoked:** `EnrollmentCancellationService::cancelSimplified()`

## 3. Financial Settlement Breakdown
- **Unpaid Debt Waived:** 1,000.00 EGP (Uncollected future installment forgiven).
- **Administrative / Session Fees Retained:** 400.00 EGP (Retained for attended sessions).
- **Approved Refund Liability:** 1,000.00 EGP (Calculated as Paid 1,400 EGP - Retained 400 EGP).

## 4. Refund Payment & Vault Movement
- Executed synthetic cash refund via `كاش الرئيسي` (Vault ID: 1).
- Outflow recorded in vault movements: -1,000.00 EGP.
- Cancellation status: `cancelled`.
- Settlement status: `settled`.
- Reporting Center updated: Cancellations Waived (+1,000 EGP), Retained Fees (+400 EGP), Refunds Disbursed (+1,000 EGP).
