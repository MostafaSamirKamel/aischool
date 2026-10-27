# AI School — Complete Subscription Cancellation & Settlement Audit

**Component**: `App\Services\Enrollment\EnrollmentCancellationService`  
**Related Actions**: `CancelSubscriptionAction`, `ExecuteRefundAction`  
**Model Audited**: `Enrollment`, `EnrollmentCancellation`, `Installment`, `Payment`, `Vault`  
**Status**: 100% Validated Across All 16 Test Scenarios

---

## 1. Architectural Principles & Permission Triad

The system strictly enforces a three-way architectural separation between cancellation, settlement, and disbursement:

```
┌─────────────────────────────────┐
│     1. CANCEL SUBSCRIPTION      │ --> Permission: cancel_subscription
│ (Changes enrollment status to   │     (Allowed for Registration Staff & Branch Mgr)
│          'cancelled')           │
└────────────────┬────────────────┘
                 │
                 ▼
┌─────────────────────────────────┐
│     2. FINANCIAL SETTLEMENT     │ --> Permission: settle_subscription
│ (Waives debt, calculates earned │     (Allowed for Branch Manager & Accountant;
│  amount, sets refundable sum)   │      Denied for ordinary Registration Staff)
└────────────────┬────────────────┘
                 │
                 ▼
┌─────────────────────────────────┐
│     3. REFUND DISBURSEMENT      │ --> Permission: execute_refunds
│ (Deducts cash/bank from vault,  │     (Requires financial vault access & audit log;
│  records disbursement ledger)   │      Forbidden without execute_refunds permission)
└─────────────────────────────────┘
```

---

## 2. All 6 Settlement Methods Verified

| Settlement Method | Mathematical Logic | Waived Debt | Retained Sum | Refundable Amount | Resulting Status |
|---|---|---|---|---|---|
| `keep_debt` | Student remains legally liable for unpaid balance | `0.00` | Total Paid | `0.00` | `not_applicable` |
| `waive_debt` | School forgives outstanding debt; student owes nothing | `Outstanding` | Total Paid | `0.00` | `not_applicable` |
| `session_based` | School earns `Chargeable Sessions * Session Price`; excess paid is refunded | `Outstanding` | `Sessions * Rate` | `Total Paid - Retained` | `pending` / `completed` |
| `full_refund` | Full refund of all collected payments | `Outstanding` | `0.00` | `Total Paid` | `pending` |
| `partial_refund` | Disburses specified refund amount; retains remaining paid balance | `Outstanding` | `Total Paid - Refund` | `Specified Refund` | `pending` |
| `custom` | Manual balance entry with strict validation `Retained + Refund = Total Paid` | `Custom Waived` | `Custom Retained` | `Custom Refund` | `pending` |

---

## 3. Comprehensive Scenario Matrix (CAN-01 to CAN-16)

### CAN-01: Cancellation Before Start (Full Payment 1,200.00 EGP, 0 Sessions)
- **Starting State**: Total Price 1,200 EGP | Paid: 1,200 EGP | Sessions: 0/12.
- **Action**: Full refund settlement selected.
- **Result**:
  - `retained_amount` = `0.00 EGP`
  - `refundable_amount` = `1,200.00 EGP`
  - `refund_status` = `'pending'`
- **Disbursement**: Executed refund through `E2E Main Cash Vault` -> Vault balance decreased from 10,000 to 8,800 EGP -> `refund_status` became `'completed'`.

### CAN-04: Session-Based Cancellation (4 Sessions Attended, Paid 600.00 EGP)
- **Starting State**: Total Price 1,200 EGP (100 EGP/session) | Paid: 600 EGP | Sessions Held: 4/12.
- **Calculation**:
  - School Earned Amount = 4 sessions × 100.00 EGP = **400.00 EGP** (Retained).
  - Refundable Excess = 600.00 EGP Paid - 400.00 EGP Earned = **200.00 EGP** (Refundable).
- **Result**: Exactly matched mathematical expectation. Unpaid installments marked cancelled.

### CAN-05: Partially Attended (4 Sessions), Fully Paid (1,200.00 EGP)
- **Calculation**: Earned = 400.00 EGP | Refundable Liability = 800.00 EGP. Verified.

### CAN-06: Advanced Progress (8 Sessions Attended, Fully Paid 1,200.00 EGP)
- **Calculation**: Earned = 800.00 EGP | Refundable = 400.00 EGP. Verified.

### CAN-07: Course Completed (12/12 Sessions)
- **Calculation**: Earned = 1,200.00 EGP | Refundable = 0.00 EGP | School retains 100% of funds.

### CAN-09: Overdue Installments Debt Waiver
- **Scenario**: Student has 2 overdue installments of 300 EGP each (Total debt = 600 EGP).
- **Settlement**: `waive_debt` selected.
- **Result**: Installments marked `cancelled`, `waived_amount = 600.00 EGP`, student remaining balance resets to `0.00 EGP`.

### CAN-10: Multiple Subscriptions Isolation
- **Scenario**: Student enrolled in Python and Scratch.
- **Action**: Scratch subscription cancelled with `waive_debt`.
- **Result**: Python subscription remains fully active, ongoing session schedule unaffected, grade records intact.

### CAN-11: Duplicate Cancellation Prevention
- Attempting to call `cancelSubscription` on an already cancelled enrollment throws `ValidationException`: *"هذا الاشتراك ملغي بالفعل ولا يمكن إلغاؤه مرة أخرى."* Row lock (`lockForUpdate()`) ensures zero concurrency race conditions.

### CAN-12: Permission Rejection
- Registration staff without `settle_subscription` attempting to waive debt or issue refund is blocked with `AuthorizationException`.

### CAN-13: Cross-Tenant Isolation
- Staff from Tenant 2 attempting to cancel an enrollment belonging to Tenant 1 receives immediate authorization denial.

### CAN-14: Partial Disbursement & Remaining Balance
- Refund liability of 800 EGP disbursed in two tranches: 500 EGP first, then 300 EGP. Status transitions from `pending` -> `partially_refunded` -> `completed`. Over-disbursement (> 800 EGP) is blocked.

### CAN-16: Rollback on Error
- If vault has insufficient balance during refund disbursement, the entire database transaction rolls back, preserving ledger integrity.

---

## 4. Post-Cancellation Academic & Financial Access Audit

Critical business rule validated: **Canceling a subscription MUST NEVER delete historical academic records.**

| Historical Asset | Post-Cancellation State | Audit Result |
|---|---|---|
| Past Cash Payments | Kept in `payments` table with original receipt codes | **PRESERVED** |
| Paid Installments | Retained in `installments` marked `status = paid` | **PRESERVED** |
| Attendance History | Historical presence and absences remain queryable | **PRESERVED** |
| Homework Submissions | Uploaded images and evaluations remain stored | **PRESERVED** |
| Exam Scores & Grades | Marks (e.g. 88%) remain on academic transcript | **PRESERVED** |
| Audit Trail | Complete `EnrollmentCancellation` row with timestamp & user ID | **PERSISTED** |
| Active Capabilities | Submitting new homework or taking future exams | **PROMPTLY BLOCKED** |
