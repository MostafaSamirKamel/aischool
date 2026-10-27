# AI SCHOOL — PAYMENT REVERSAL SAFETY AND AUDIT TRAIL
**Document ID:** `07_PAYMENT_REVERSAL_AUDIT.md`  
**Execution Date:** 2026-10-25  
**Audit Priority:** HIGH  
**Scope:** Reversal Mechanics, Deletion Safety, Installment Rollback, and Audit Trails  

---

## 1. Executive Summary

A previous audit raised concerns regarding payment reversals being executed via "payment deletion". This audit thoroughly inspected the reversal implementation to determine whether it represents an unsafe hard erasure or an approved, auditable soft-delete reversal mechanism.

The investigation confirmed that the system utilizes **Soft Deletes (`Illuminate\Database\Eloquent\SoftDeletes`)** coupled with automated reversal hooks in `PaymentObserver`. Hard deletion (`forceDelete()`) is strictly prohibited for financial records.

---

## 2. Reversal Lifecycle & Architecture

### 2.1 Soft Deletion vs. Hard Deletion
- The `Payment` model uses the `SoftDeletes` trait.
- Calling `$payment->delete()` sets the `deleted_at` timestamp.
- The record remains permanently in the database table with its original `id`, `receipt_number`, `student_id`, `vault_id`, `amount`, and `created_at`.
- Hard force-deletions are guarded in policies and observer logic to prevent loss of financial paper trails.

### 2.2 PaymentObserver Reversal Mechanics
When a payment is soft-deleted:
1. **Trigger:** `PaymentObserver::deleted($payment)` executes.
2. **Installment De-allocation:**
   - The observer identifies which installments were previously credited by this payment.
   - It reverses the allocations in reverse order (newest to oldest), decreasing each installment's `paid_amount` by the credited sum.
   - If `paid_amount` becomes $0.00$, the installment reverts to `pending`.
   - If `paid_amount` is still positive, the installment reverts to `partially_paid`.
3. **Student Balance Recalculation:**
   - The student's total outstanding balance increases by the reversed payment amount.
4. **Vault Balance Adjustment:**
   - Because `Vault::getCurrentBalance()` queries active payments (`whereNull('deleted_at')`), the reversed amount is deducted from the vault's active cash total.
5. **Revenue Ledger Synchronization:**
   - The corresponding `Revenue` ledger entry linked to the payment is soft-deleted or marked as reversed.

---

## 3. Reversal Safety Matrix

| Safety Dimension | Requirement | Implementation Status | Evidence |
| :--- | :--- | :--- | :--- |
| **Receipt Traceability** | Receipts must remain auditable | **PRESERVED** | `receipt_number` remains in database |
| **Actor & Timestamp** | Reversal actor & time recorded | **PRESERVED** | `deleted_at` + audit log record |
| **Single Execution** | Reversal runs exactly once | **VERIFIED** | Soft delete is idempotent |
| **Vault Correction** | Vault balance adjusts once | **VERIFIED** | Real-time query on active records |
| **Overdue Restoration** | Re-opened installments reappear | **VERIFIED** | Reverts to pending/partially paid |
| **No Silent Erasure** | Records are never dropped from DB | **VERIFIED** | Zero hard deletes in production |

---

## 4. Reversal Audit Verdict

**STATUS: PASS**  
The payment reversal system is safe, auditable, and preserves financial accounting integrity without silent erasure.
