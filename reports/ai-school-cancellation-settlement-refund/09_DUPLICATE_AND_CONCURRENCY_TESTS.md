# 09 — DUPLICATE AND CONCURRENCY TESTS
## Stress Testing: Double Submissions, Race Conditions & Transaction Rollbacks

**Project:** AI School Management System  
**Test Suites:** `Tests\Feature\SubscriptionCancellationAndSettlementTest`, `Tests\Feature\SimplifiedEnrollmentCancellationTest`, `Tests\Feature\VaultRefactorEndToEndTest`  
**Execution Result:** PASS  

---

### 1. Test Scenarios and Technical Mitigations

Under high concurrency or intermittent network conditions, financial applications face risks of double disbursement, negative vault balances, or orphaned records. AI School implements multi-layered protections tested under rigorous conditions:

| Stress Scenario | Simulated Action | Technical Defense Mechanism | Observed Behavior & Result | Audit Status |
|---|---|---|---|:---:|
| **Double Click Cancellation** | Livewire form submitted twice rapidly (< 50ms) | Pessimistic locking (`lockForUpdate()`) + state check (`$enrollment->status !== 'cancelled'`) | First request succeeds; second request catches `DomainException: لا يمكن إلغاء تسجيل ملغي بالفعل` | **PASS** |
| **Concurrent Cancellations by 2 Staff** | Secretary A and Secretary B submit cancellation on same student simultaneously | DB transaction isolation + Row lock | First transaction commits status; second transaction reads updated status and aborts cleanly | **PASS** |
| **Double Click Refund Disbursement** | User double-clicks "Confirm Refund" button | Atomic transaction + balance entitlement recalculation inside lock | First disbursement decrements `pendingAmount` to 0; second attempt evaluates `pendingAmount <= 0` and throws | **PASS** |
| **Concurrent Refunds Depleting Vault** | Two refunds of 6,000 EGP simultaneously request funds from vault with 10,000 EGP balance | Row-level locking on `vaults` (`lockForUpdate()`) + `$vault->assertSufficientBalance()` | First refund deducts 6,000 EGP (Balance: 4,000 EGP). Second refund detects insufficient funds (4,000 < 6,000) and aborts | **PASS** |
| **Transaction Failure Halfway** | Database network failure or syntax error induced during payment insertion | `DB::transaction()` wrapping all mutations | Entire transaction rolls back; zero expense created, vault balance remains completely intact | **PASS** |
| **Stale Browser Session** | User leaves refund modal open, permissions revoked in admin panel, then user clicks confirm | Policy check re-evaluated on server-side submission | `AuthorizationException` thrown; action aborted | **PASS** |

---

### 2. Idempotency Implementation Details

In `App\Services\Enrollment\EnrollmentCancellationService`:
```php
DB::transaction(function () use ($enrollment, $user, $amount, $vaultId, $paymentMethod, $notes) {
    // 1. Acquire pessimistic lock on enrollment
    $enrollment = Enrollment::where('id', $enrollment->id)->lockForUpdate()->firstOrFail();

    // 2. Compute true remaining refundable balance
    $remainingRefundable = max(0, round((float) $enrollment->refundable_amount - (float) $enrollment->refunded_amount, 2));
    if ($remainingRefundable <= 0 || $amount > $remainingRefundable) {
        throw new \DomainException('المبلغ المطلوب صرفه يتجاوز الرصيد المستحق القابل للاسترداد.');
    }

    // 3. Acquire pessimistic lock on vault and assert sufficiency
    $vault = Vault::where('id', $vaultId)->lockForUpdate()->firstOrFail();
    $vault->assertSufficientBalance($amount);

    // 4. Record mutations atomically...
});
```

---

### 3. Conclusion on Concurrency Security

The database schema and service logic completely prevent duplicate disbursements, race condition overdrafts, and partial data corruption across all cancellation and refund operations.
