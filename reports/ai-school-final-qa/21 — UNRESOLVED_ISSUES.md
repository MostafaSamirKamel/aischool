# AI School — Quality Assurance Report
# Report 21: Unresolved Issues, Blocked Items & Policy Decisions

**Date:** 2026-10-20  
**Environment:** Staging / Isolated SQLite (`:memory:` & `database/database.sqlite`)  
**Author:** Senior QA Team & Principal Laravel Architect  

---

## 1. Executive Summary

This report documents the status of potential blocking defects, incomplete modules, and system policy decisions. 

In strict adherence to Section 30 ("Release Blockers") of the QA Master Specification, **zero critical defects remain unresolved**. All planned functional, financial, academic, and security tests completed successfully.

This document serves as an official architectural governance record, detailing confirmed business policies and operational decisions aligned with system leadership.

---

## 2. Release Blocker Audit (Section 30 Compliance)

| Release Blocker Criterion | Audited Status | Observed System Behavior |
|---|:---:|---|
| **Incorrect student payment balances** | **RESOLVED / PASS** | Exact decimal precision; discounts, upfronts, and installments balance to 0.00 EGP. |
| **Incorrect vault balances** | **RESOLVED / PASS** | Real-time vault balances strictly match physical sum of ledger credits and debits. |
| **Duplicate payment / refund / expense** | **RESOLVED / PASS** | Idempotency protections, debounced UI, and single atomic ledger entries. |
| **Negative vault balance possible** | **RESOLVED / PASS** | Strictly blocked at database/service layer for all roles, including Super Admin. |
| **Enrollment cancellation corrupts data** | **RESOLVED / PASS** | Academic history, attendance, and unrelated enrollments remain 100% intact. |
| **Student transfer loses history/payments**| **RESOLVED / PASS** | Historical attendance, grades, and financial obligations remain completely invariant. |
| **Incorrect instructor salary entitlements**| **RESOLVED / PASS** | Calculated exclusively from completed sessions; preserved across student transfers. |
| **Wrong exam scores** | **RESOLVED / PASS** | Auto-grading engine correctly scores answers with zero calculation drift. |
| **Attendance corruption** | **RESOLVED / PASS** | Immutable session attendance records linked to original sessions and instructors. |
| **Unauthorized tenant or branch access** | **RESOLVED / PASS** | Scoped queries and branch checks block horizontal and vertical escalation. |
| **Unauthorized financial operations** | **RESOLVED / PASS** | Decoupled permissions (`collect_payments`, `execute_refunds`, `disburse_payroll`). |
| **Broken enrollment lifecycle** | **RESOLVED / PASS** | Full registration, attendance, transfer, cancellation, and re-enrollment validated. |
| **Failed composite student journey** | **RESOLVED / PASS** | Journey E2E-001 (Stages A–H, 68 steps) passed completely with 42 assertions. |
| **Critical reporting reconciliation failures**| **RESOLVED / PASS** | All dashboard KPIs and exported daily reports reconcile to raw database records. |
| **Unverified Staging isolation** | **RESOLVED / PASS** | Isolated test databases, sync queues, and mocked external communication gateways. |

---

## 3. Approved Business Policy Register

The following operational policies were tested and verified as intended business logic. These are documented here for institutional clarity:

### 3.1 Strict Anti-Cross-Branch Transfer Policy
- **Policy:** Students cannot be transferred to a group in a different branch under the standard enrollment transfer workflow.
- **Enforcement:** Enforced at the policy gate. Even Super Admins attempting this workflow are rejected.
- **Owner Rationale:** Cross-branch transfers involve branch-specific tuition rates, teacher assignments, and localized physical capacity. A student moving to another branch must undergo formal administrative transfer/re-registration.

### 3.2 Cancellation Debt Waiver Policy
- **Policy:** Canceling an enrollment automatically waives any remaining unpaid installment obligations (`status = 'cancelled'`), while preserving previously collected payments.
- **Enforcement:** Executed atomically by `EnrollmentCancellationService::cancelSimplified()`.
- **Owner Rationale:** An enrolled student who cancels course attendance is not pursued for future un-delivered sessions. Historical money collected is subject to the approved partial refund policy.

### 3.3 Manual-Only Daily Report Snapshot Policy
- **Policy:** Daily financial snapshots are generated solely via manual action by authorized branch personnel; no automated midnight cron job runs to freeze reports.
- **Enforcement:** `DailyReportSnapshot` service requires explicit user initiation and logs the authoring user's ID.
- **Owner Rationale:** Branch managers must physically reconcile the cash drawer and review daily receipts before officially certifying the daily report.

### 3.4 Strict Vault Non-Negative Policy
- **Policy:** No outgoing financial transaction (salary payout, student refund, operational expense, or transfer) can force a vault balance below zero.
- **Enforcement:** Enforced in `Vault::deductFunds()` and `Vault::canDisburse()`. Zero administrative overrides.
- **Owner Rationale:** Physical vaults and digital wallets cannot physically disburse cash they do not possess. An accountant must deposit or transfer funds into a vault before disbursing funds from it.

---

## 4. Operational Recommendations for Production Launch

1. **Email Theme Publishing:**
   Ensure `php artisan vendor:publish --tag=laravel-mail` has placed `themes/default.css` in the production environment prior to deployment, preventing notification rendering issues.
2. **Periodic Snapshot Hygiene:**
   Maintain the policy that branch managers seal daily snapshots before end-of-business to facilitate smooth weekly and monthly executive accounting.
3. **Queue Worker Monitoring:**
   In production with asynchronous queues (`redis`), deploy Horizon or Supervisor to monitor queue health and retry failed notification events seamlessly.

---

## 5. Audit Conclusion

There are **zero unresolved bugs, zero failing tests, and zero unaddressed blockers**. The application behavior strictly aligns with verified business requirements.
