# 03 — REAL COLLECTION TESTS & WORKFLOW VERIFICATION

## 1. Test Methodology
Payment collection workflows were executed against synthetic staging records to verify real database persistence, observer triggers, enrollment balance recalculation, and vault ledger effects.

---

## 2. Practical Test Scenarios & Results

### Test 1: Core Practical Test (400 EGP on 1,000 EGP Installment)
- **Inputs:** Synthetic installment ID #1 ($1,000.00 EGP). Action: Collect $400.00 EGP to Vault ID #1.
- **Backend Execution:** `HandlesInstallmentDuePayments::savePayment()` -> `Payment::create()` -> `PaymentObserver::created()`.
- **Database Verification:**
  - `payments` table: Exactly 1 record inserted (`amount = 400.00`, `status = completed`, `received_by = auth()->id()`).
  - `revenues` table: Exactly 1 record inserted (`payment_id = payments.id`, `amount = 400.00`, `status = received`).
  - `installments` table: `paid_amount = 400.00`, `remaining_amount = 600.00`, `status = partial`.
  - `enrollments` table: `paid_amount = 400.00`.
  - `vaults` balance: Increased by exactly 400.00 EGP.
- **Result:** **PASS**

### Test 2: Full Payment (Settlement)
- **Inputs:** Collect remaining 600.00 EGP on the installment.
- **Database Verification:**
  - `installments` table: `paid_amount = 1000.00`, `remaining_amount = 0.00`, `status = paid`.
  - `enrollments` table: `paid_amount = 1000.00`.
  - `vaults` balance: Increased by exactly 600.00 EGP.
- **Result:** **PASS**

### Test 3: Multiple Installments & Oldest-First Allocation Rule
- **Setup:** Student has Installment #1 (1,000 EGP, due 30 days ago) and Installment #2 (1,000 EGP, due today).
- **Action:** Cashier opens payment modal on Installment #2 and submits 1,000 EGP.
- **Approved Business Rule:** School accounting requires **Oldest-First Allocation**.
- **Database Verification:**
  - `PaymentObserver::recalculateStudentInstallments` allocated the 1,000 EGP to Installment #1 (oldest debt).
  - Installment #1 transitioned to `paid`.
  - Installment #2 remained `pending` (0.00 paid, 1,000 remaining).
  - Report UI refreshed immediately to reflect the real post-allocation state, preventing false representation of debt.
- **Result:** **PASS**

### Test 4: Concurrency & Duplicate Submission Prevention
- **Action:** Replay Livewire `savePayment` request or simulate simultaneous double-click.
- **Safeguard:** `Installment::lockForUpdate()` inside `DB::transaction()` locks the row and re-evaluates remaining debt under lock.
- **Result:** Second execution rejected; zero duplicate payment or revenue rows created. **PASS**

### Test 5: Role & Permission Boundary Enforcement
- **Action:** Attempt collection using credentials of a view-only reporting employee.
- **Result:** Livewire action blocked with HTTP 403 Forbidden. Zero database mutations. **PASS**
