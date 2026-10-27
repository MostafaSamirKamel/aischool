# 05 — REFUND AND VAULT INTEGRITY
## Verification of Refund Disbursement, Accounting Ledger & Vault Movements

**Project:** AI School Management System  
**Test Suites:** `Tests\Feature\ExecuteRefundActionLivewireTest`, `Tests\Feature\SubscriptionCancellationAndSettlementTest`, `Tests\Feature\SimplifiedEnrollmentCancellationTest`  
**Execution Result:** PASS  

---

### 1. Separation of Concerns: Entitlement vs Disbursement

A core architectural principle of AI School is the strict decoupling of **Refund Entitlement** and **Refund Disbursement**:

```
[Student Enrollment] 
       │
       ▼ (cancelSubscription with settlement)
[Refund Entitlement Recorded] ──> refundable_amount = 300 EGP, refund_status = 'pending'
       │                          (Vault Balance: 10,000 EGP - NO MONEY LEAVES VAULT)
       │
       ▼ (ExecuteRefundAction - Authorized Employee Only)
[Actual Disbursement] ─────────> Vault: 10,000 - 300 = 9,700 EGP
                                  Expense Created (status: paid)
                                  Payment Recorded (status: refunded)
                                  Enrollment: refunded_amount = 300, refund_status = 'completed'
```

---

### 2. Vault Movement & Accounting Ledger Impact

When an authorized refund is executed, the following atomic changes take place inside a database transaction with pessimistic row-locking:

1. **Vault Balance Reduction:**
   - Pre-condition: `$vault->getCurrentBalance() >= $amount`. If false, `assertSufficientBalance()` aborts the transaction with `DomainException`.
   - Post-condition: Vault balance decreases by exactly `$amount`.
2. **Expense Audit Record:**
   - Table: `expenses`.
   - Attributes:
     - `tenant_id`: matches enrollment tenant.
     - `vault_id`: selected authorized vault.
     - `amount`: refund amount.
     - `category`: `'student_refund'`.
     - `status`: `'paid'`.
     - `payment_method`: `'cash'` (or selected valid enum).
3. **Payment Audit Record:**
   - Table: `payments`.
   - Attributes:
     - `tenant_id`: matches enrollment tenant.
     - `enrollment_id`: matches target enrollment.
     - `amount`: refund amount.
     - `status`: `'refunded'`.
     - Note: `PaymentObserver` only computes revenue from `'completed'` payments, ensuring that refunded payments do NOT inflate revenue figures.
4. **Enrollment Ledger Update:**
   - `refunded_amount` += `$amount`.
   - `refund_status` updated to `'completed'` (if fully refunded) or `'partial'`.

---

### 3. Insufficient Vault Balance & Overdraft Defense

| Test Scenario | Action Performed | Result | Verification |
|---|---|---|---|
| **Underfunded Vault** | Attempt refund of 500 EGP from vault with 50 EGP opening balance | Form validation rejected (`assertHasActionErrors(['vault_id'])`) + backend exception if bypassed | Zero funds deducted; Vault balance remains 50.00 EGP; `refunded_amount` remains 0.00 EGP |
| **Simultaneous Exhaustion** | Two concurrent refunds compete for remaining vault liquidity | Pessimistic locking forces sequential processing; second request receives `DomainException` | Vault never drops below 0.00 EGP; zero overdraft |

---

### 4. Idempotency & Duplicate Refund Prevention

1. **Livewire Double-Click:** When a user double-clicks the modal submit button, the first request acquires `lockForUpdate()` on the `Enrollment`. The second request reads the updated `refunded_amount` and aborts if `pendingAmount <= 0`.
2. **UI Dynamic Hiding:** Once `refundable_amount - refunded_amount <= 0`, the `ExecuteRefundAction` button dynamically vanishes from `ViewEnrollment` (`visible()` evaluates to `false`).
3. **Database Audit Match:** Tested executing refund against synthetic enrollment. Database contains exactly ONE `Expense` and ONE `Payment` record.
