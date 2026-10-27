# AI SCHOOL — ENROLLMENT CANCELLATION, SETTLEMENT, AND REFUND AUDIT
**Document ID:** `08_ENROLLMENT_CANCELLATION_AND_REFUNDS.md`  
**Execution Date:** 2026-10-25  
**Service Under Audit:** `app/Services/EnrollmentCancellationService.php`  
**Core Principle:** Strict separation between 1) Cancellation, 2) Financial Settlement, and 3) Actual Refund.  

---

## 1. Executive Summary

This audit evaluates the 3-stage enrollment termination architecture in AI School. The system decouples administrative cancellation from financial settlement and refund execution, ensuring that canceling a student's course registration does not automatically discharge debt or create unauthorized cash outflows.

---

## 2. Three-Stage Lifecycle Verification

```
[ Stage 1: Cancellation ]
      │  - Enrollment status -> 'cancelled'
      │  - Active group membership ends (roster removed)
      │  - Group capacity freed (+1 seat)
      │  - Historical grades & attendance PRESERVED
      ▼
[ Stage 2: Financial Settlement ]
      │  - Authorized decision: Waive or Retain balance
      │  - If waived: installments marked 'waived' (removed from overdue)
      │  - If refund due: Refund liability record created (status: 'approved')
      ▼
[ Stage 3: Refund Execution ]
      │  - Requires authorized disbursement vault
      │  - Deducts cash from vault
      │  - Deducts once (prevents double disbursement)
      │  - Status -> 'executed'
```

---

## 3. Detailed Stage Breakdown

### 3.1 Stage 1: Administrative Cancellation
- **Enrollment Status:** Transitions to `cancelled`.
- **Group Membership:** The student is removed from the active group roster. Future session attendance sheets no longer list the student.
- **Group Capacity:** Recalculated dynamically; the seat is returned to available capacity.
- **Academic History Preservation:** Historical attendance sessions, submitted homework, exam scores, and teacher notes for the canceled enrollment remain completely intact in the database.
- **Independent Enrollments:** If the student has an active enrollment in another course (e.g., Mathematics vs. Physics), the cancellation of Physics does not affect the Mathematics enrollment.

### 3.2 Stage 2: Financial Settlement
The system provides 3 distinct settlement options:
1. **Full Settlement (Waive Remaining Balances):**
   - Unpaid installments linked to the enrollment are marked as `waived`.
   - Waived installments are excluded from overdue reports and debt collection lists.
   - Historical records are preserved with a waiver note.
2. **Retain Balances (Student Remains Obligated):**
   - The student attended part of the term and owes past-due installments.
   - Remaining installments stay `pending` or `partially_paid` and remain in collection queues.
3. **Refund Approval (Credit Balance):**
   - If the student overpaid relative to attended sessions, a `Refund` liability is recorded with status `approved`.
   - **Crucial Rule:** Recording an approved refund does **NOT** disburse cash or deduct from any vault.

### 3.3 Stage 3: Refund Execution
- **Vault Requirement:** Executing a refund mandates selecting an authorized disbursing vault.
- **Balance Check:** Ensures `vault->getCurrentBalance() >= refund_amount`.
- **Anti-Duplication:** Once marked `executed` / `completed`, repeated execution attempts throw an exception, preventing double payout.
- **Audit Logging:** Logs the acting accountant/manager, execution timestamp, and source vault.

---

## 4. Test Verification Evidence

The 3-stage lifecycle is verified by the automated test suite:
- `Tests\Feature\MasterProductionAcceptanceTest::test_stage_k_cancellation_settlement_and_refund` (**PASSED**)
- `Tests\Feature\SubscriptionCancellationTest` (**PASSED**)

---

## 5. Audit Verdict

**STATUS: PASS**  
The enrollment cancellation, settlement, and refund lifecycle strictly enforces role separation, prevents double disbursements, and protects academic and financial history.
