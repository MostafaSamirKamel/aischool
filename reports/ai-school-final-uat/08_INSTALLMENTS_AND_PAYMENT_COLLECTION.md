# 08 — INSTALLMENTS AND PAYMENT COLLECTION

**Audit Stage:** Stage C & Stage I  
**Lead Actor:** Accountant / Cashier  
**Verification Method:** Automated End-to-End Execution (`MasterProductionAcceptanceTest::test_stage_c`, `test_stage_i`)

---

## 1. Stage C — Financial Registration & Upfront Payment

### Test Scenario
1. Student enrolled with total course tuition = `3,000 EGP`.
2. Upfront payment of `500 EGP` recorded into Vault Main.
3. Remaining `2,500 EGP` split into synthetic installment schedule:
   - Installment 1: `1,000 EGP` (Due today)
   - Installment 2: `1,500 EGP` (Due next month)

### Verification
- **Payment Record:** Exactly one `Payment` record created with amount = `500.00`.
- **Vault Inflow:** Vault Main balance increased by exactly `+500.00`.
- **Enrollment Balances:** `paid_amount = 500.00`, `remaining_amount = 2500.00`.
- **Revenue Accounting:** No phantom duplicates created in revenue aggregation.

---

## 2. Stage I — Installment Collection & Partial Allocation

### Partial Payment Test
1. Student made a partial payment of `400 EGP` towards Installment 1 (`amount: 1,000 EGP`).
2. Payment recorded into Vault Main.

### Verification Results
- **Installment Record State:**
  - `amount`: `1,000.00`
  - `paid_amount`: `400.00`
  - `remaining_amount`: `600.00`
  - `status`: `partially_paid`
- **Vault State:** Vault balance increased by exactly `+400.00`.
- **Payment Record:** Exactly one official `Payment` record inserted.
- **Reporting Consistency:**
  - `/admin/installments-due-today`: Displays remaining due `600.00 EGP`.
  - `/admin/installments-due-week`: Consistent aggregated remaining due.
  - `/admin/installments-due-month`: Accurately incorporates Installment 1 (`600`) and Installment 2 (`1,500`).
3. **Full Settlement of Remaining Balance:**
   - Remaining `600 EGP` collected.
   - Installment 1 status transitioned to `paid`.
   - Remaining installment balance dropped to `0.00`.

---

## 3. Financial Integrity & Concurrency Safeguards
- **Negative Vault Prevention:** Verified that payouts cannot exceed vault balance.
- **Zero Double-Counting:** Upfront payments and installment receipts are separated cleanly in database queries without double revenue crediting.

---

## 4. Acceptance Result
- **Stage C (Financial Registration):** **PASS**
- **Stage I (Installment Collection):** **PASS** (Zero discrepancies in partial payments, atomic vault updates).
