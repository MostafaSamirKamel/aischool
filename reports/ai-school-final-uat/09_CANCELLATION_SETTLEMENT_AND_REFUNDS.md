# 09 — CANCELLATION, FINANCIAL SETTLEMENT AND REFUNDS

**Audit Stage:** Stage K  
**Lead Actor:** Secretary, Accountant & Finance Manager  
**Verification Method:** Automated End-to-End Execution (`MasterProductionAcceptanceTest::test_stage_k_cancellation_settlement_and_refund`)

---

## 1. Multi-Stage Operational Discipline

The cancellation lifecycle follows a strict three-tier authorization separation:
1. **Tier 1: Cancellation Execution** (`cancel_enrollment` permission).
2. **Tier 2: Financial Settlement** (`settle_enrollment` permission).
3. **Tier 3: Refund Disbursement** (`disburse_refund` permission).

Neither stage automatically triggers the next without explicit authorized human execution.

---

## 2. Test Execution & Verification

### Test Scenario
1. Student enrolled in Course 1 ("Cancel C1", 2000 EGP) and Course 2 ("Active C2", 2000 EGP).
2. Upfront payment of `1000 EGP` recorded for Course 1.
3. Attendance and assignment submissions recorded for Course 1.
4. **Action: Cancel Course 1:**
   - Secretary with cancellation permission executed cancellation of Course 1.
   - Verified Course 1 status transitioned to `cancelled`.
   - Verified Course 2 remains active (`status = active`).
   - Verified historical attendance records for Course 1 remain 100% intact.
   - Verified historical homework submissions for Course 1 remain 100% intact.
5. **Action: Financial Settlement:**
   - Settlement calculation evaluated consumed sessions vs paid amount.
   - Net refund entitlement calculated at `400.00 EGP`.
   - Verified that recording settlement **DOES NOT** automatically execute a vault disbursement.
   - Student balance updated with credit obligation.
6. **Action: Authorized Refund Disbursement:**
   - Finance Manager with refund disbursement authority approved and executed payout of `400.00 EGP` from Vault Main.
   - Verified Vault Main balance decreased by exactly `400.00 EGP`.
   - Verified exactly one payout record generated in database.
   - Verified duplicate refund attempt is rejected.

---

## 3. Acceptance Result
- **Stage K (Cancellation, Settlement & Refund):** **PASS** (Zero cross-course interference, preserved academic history, isolated financial authorization).
