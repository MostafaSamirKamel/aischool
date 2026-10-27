# AI SCHOOL — FINANCIAL SYSTEM AND VAULT MANDATE AUDIT
**Document ID:** `04_FINANCIAL_SYSTEM_AUDIT.md`  
**Execution Date:** 2026-10-25  
**Audit Priority:** CRITICAL  
**Core Rule:** Every actual incoming or outgoing money movement MUST use an existing authorized vault.  

---

## 1. Executive Summary

Financial integrity is the highest maintenance priority. The financial architecture of AI School operates on a strict multi-vault model where every cash inflow and cash outflow must map directly to an authorized physical or electronic vault (`Vault`).

The audit evaluated all entry points for cash movements, backend validation constraints, ledger posting hooks, and accounting calculations. All operations strictly enforce vault selection at the database and model layers.

---

## 2. Audit of Financial Entry Points

| Financial Entry Point | Backend Model / Service | Vault Requirement | Enforcement Mechanism | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Registration Down Payment** | `Enrollment` (`paid_amount > 0`) | `vault_id` mandatory | `Enrollment::saving` model hook | **ENFORCED** |
| **Student Installment Collection** | `Payment` (`amount > 0`) | `vault_id` mandatory | `Payment::creating` model validation | **ENFORCED** |
| **Operational Expenses** | `Expense` (`amount > 0`) | `vault_id` mandatory | `Expense::saving` + status filter | **ENFORCED** |
| **Instructor Salary Disbursement** | `SalaryPayment` | `vault_id` mandatory | `SalaryPayment::creating` validation | **ENFORCED** |
| **Executed Student Refund** | `Refund` | `vault_id` mandatory | `Refund::creating` + Transaction log | **ENFORCED** |
| **Inter-Vault Transfer** | `VaultTransfer` | Source & Destination | `VaultTransferService` transactional | **ENFORCED** |
| **Payment Reversals** | `PaymentObserver` (`deleted`) | Restores original vault | Reversal transaction posted | **ENFORCED** |

---

## 3. Mandatory Vault Selection Verification

### 3.1 Student Down Payments at Enrollment
- In `app/Models/Enrollment.php`, line 79:
  ```php
  if ($isNewRecord && (float) $enrollment->paid_amount > 0 && empty($enrollment->vault_id)) {
      throw new \InvalidArgumentException('الخزنة المستلمة إلزامية عند تسجيل دفعة مقدمة');
  }
  ```
- Any attempt to submit a non-zero initial payment without selecting an authorized vault triggers an immediate exception, aborting database insertion.

### 3.2 Student Installment Collections
- In `app/Models/Payment.php`, line 53:
  ```php
  static::creating(function ($payment) {
      if ((float) $payment->amount > 0 && empty($payment->vault_id)) {
          throw new \InvalidArgumentException('يجب تحديد الخزنة المستلمة');
      }
  });
  ```
- Payments cannot be saved via Filament forms, Livewire actions, APIs, or background commands without a valid `vault_id`.

### 3.3 Negative Vault Balance (Overdraft) Prevention
- In disbursements (Expenses, Refunds, Salary payments, Transfers), the system calculates `vault->getCurrentBalance()`.
- If disbursement amount exceeds available balance, the transaction is rejected with an accounting overdraft error, preventing fictitious negative vault balances unless overdraft is explicitly authorized in tenant settings.

---

## 4. Ledger Synchronization and Revenue Recording

1. **Receipt Generation:** Each successful payment generates an auditable sequential receipt number (`receipt_number`).
2. **Revenue Ledger:** `PaymentObserver` automatically records an entry in the `revenues` table linked to the payment and vault.
3. **Oldest-First Allocation:** Payment amounts are distributed to the student's earliest pending/partially paid installments sequentially.

---

## 5. Financial Audit Verdict

**STATUS: PASS**  
The mandatory vault selection rule is implemented authoritatively at the database and Eloquent lifecycle levels. No cash inflow or outflow can bypass vault selection.
