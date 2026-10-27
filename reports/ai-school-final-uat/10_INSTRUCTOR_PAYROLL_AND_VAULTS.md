# 10 — INSTRUCTOR PAYROLL AND VAULTS

**Audit Stage:** Stage J  
**Lead Actor:** Accountant & Finance Officer  
**Verification Method:** Automated End-to-End Execution (`MasterProductionAcceptanceTest::test_stage_j_instructor_earnings_and_salary_disbursement`)

---

## 1. Instructor Earnings Entitlement

### Test Scenario
1. Instructor completed an authorized teaching session for Group PY-01.
2. System computed session earnings based on instructor's rate per session (`rate_per_session = 150.00 EGP`).
3. Created an entitlement record in `instructor_payments` in status `pending` (`total_amount: 150.00 EGP`).

---

## 2. Salary Disbursement Execution

### Test Steps & Verification
1. Accountant accessed payroll disbursement interface.
2. Captured pre-disbursement vault balance (`Vault Main`).
3. Authorized and executed salary payment of `150.00 EGP` from Vault Main.
4. **Database State Verification:**
   - Record created in `salary_payments` table:
     - `amount`: `150.00`
     - `status`: `paid`
     - `vault_id`: `Vault Main`
     - `instructor_id`: Matches instructor.
   - Entitlement record in `instructor_payments` updated:
     - `status`: `paid`
     - `paid_by`: Accountant user ID.
     - `payment_date`: Today's date.
   - **Vault Balance Impact:**
     - Pre-payout: `B`
     - Post-payout: `B - 150.00`
     - Difference matches disbursement amount with 100% precision.

---

## 3. Double-Payout & Overdraft Protection
- **Duplicate Payout Prevention:** Subsequent disbursement attempt for the same paid period is blocked.
- **Vault Overdraft Prevention:** Payout exceeding vault balance is rejected with validation error, leaving vault balance intact.

---

## 4. Acceptance Result
- **Stage J (Instructor Earnings & Payroll):** **PASS** (Zero duplicate disbursements, atomic vault deductions, exact balance reconciliation).
