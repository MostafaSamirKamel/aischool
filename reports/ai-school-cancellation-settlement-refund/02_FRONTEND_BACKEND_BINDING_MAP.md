# 02 — FRONTEND-BACKEND BINDING MAP
## Complete Binding Verification: UI Controls → Livewire → Services → DB

**Project:** AI School Management System  
**Audit Scope:** Filament 3 Resources, Livewire Actions, Modals, Services & Database Transactions  
**Status:** PASS  

---

### 1. Verification of Frontend Control Mappings

| UI Component | Location | Trigger Event / Action | Connected Backend Method | Service Invoked | Database Transaction Effects |
|---|---|---|---|---|---|
| **Cancel Subscription Button** | `EnrollmentsTable` & `ViewEnrollment` (Header) | `Action::make('cancel_subscription')` | `CancelSubscriptionAction::action()` | `EnrollmentCancellationService::cancelSimplified()` | - `enrollments.status` → `cancelled`<br>- `enrollments.cancellation_reason`<br>- `enrollments.cancelled_by`, `cancelled_at`<br>- Unpaid installments marked `cancelled`<br>- If refund specified: `expenses` row created, `payments` row created (`refunded`), vault balance reduced |
| **Execute Refund Button** | `ViewEnrollment` (Header) | `Action::make('execute_refund')` | `ExecuteRefundAction::action()` | `EnrollmentCancellationService::executeRefund()` | - `expenses` row created (`category: student_refund`, `status: paid`)<br>- `payments` row created (`status: refunded`)<br>- `vaults.current_balance` decreased<br>- `enrollments.refunded_amount` += amount<br>- `enrollments.refund_status` → `completed` or `partial` |
| **Installments Due Table Action** | `/admin/installments-due-*` | `Action::make('collect')` | `InstallmentDueReport::collectPayment()` | `PaymentObserver` & `EnrollmentCancellationService` | - `payments` row created<br>- `installments.paid_amount` updated<br>- Vault balance increased |
| **Student Financial Profile Widget** | `ViewStudent` & `ViewEnrollment` | Mount Component | `EnrollmentInstallmentsWidget` | Query: `Installment::where('enrollment_id', ...)` | - Computed outstanding debt<br>- Accurate paid amounts (no double counting) |

---

### 2. Action Lifecycle & Validation Flow

#### A. Cancel Subscription Modal (`CancelSubscriptionAction`)
1. **Visibility Check:**
   - `$record->status !== 'cancelled'`
   - `auth()->user()->can('cancel', $record)`
2. **Form Rendering:**
   - Section 1: Financial Snapshot (Course Price, Total Paid, Remaining Debt, informative debt waiver notice).
   - Section 2: Cancellation Reason (`Textarea`, `required`, max 500 chars).
   - Section 3: Refund Amount (`TextInput`, numeric, min 0, max `paid_amount - refunded_amount`). Visible to all, but displays permission warning helper text if user lacks `executeRefund` permission.
   - Section 4: Vault Selection (`Select`, active branch vaults). Visible and required ONLY if `refund_amount > 0`. Includes form-level rule validating `vault.balance >= refund_amount`.
3. **Submission & Execution:**
   - Database transaction begins.
   - Pessimistic lock acquired on enrollment and vault.
   - Enrollment status updated to `cancelled`.
   - Remaining installments updated with `status = 'cancelled'` and `waived_amount`.
   - If refund given: Expense recorded, Payment recorded, Vault deducted.
   - Notifications dispatched (`Notification::make()->success()`).

#### B. Standalone Refund Execution Modal (`ExecuteRefundAction`)
1. **Visibility Check:**
   - `$record->status === 'cancelled'`
   - `($record->refundable_amount - $record->refunded_amount) > 0`
   - `auth()->user()->can('executeRefund', $record)`
2. **Form Defaults:**
   - Amount pre-filled with remaining refundable balance.
   - Vault dropdown showing active branch vaults with real-time balances.
3. **Form-Level Validation:**
   - Amount: `numeric`, `required`, `minValue: 0.01`, `maxValue: pendingAmount`.
   - Vault ID: `required`, custom closure ensuring `vault.balance >= amount`.
4. **Backend Concurrency Defense:**
   - Service locks vault and enrollment rows (`FOR UPDATE`).
   - Asserts available balance (`$vault->assertSufficientBalance($amount)`).
   - Prevents duplicate execution.

---

### 3. Frontend Visual Fidelity Guarantee

- **Zero visual modifications:** All blade structures, CSS tokens, RTL orientations, Arabic typography, heroicons, and badges remained 100% intact.
- **Enhanced Data Integrity:** Form validation rules were added within existing Filament action definitions to give instantaneous user feedback without altering HTML or layout structures.
