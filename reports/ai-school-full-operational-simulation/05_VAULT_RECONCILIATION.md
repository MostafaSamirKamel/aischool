# AI SCHOOL — VAULT RECONCILIATION AND MANDATORY SELECTION
**Document ID:** `05_VAULT_RECONCILIATION.md`  
**Execution Date:** 2026-10-25  
**Core Rule:** Every operation that actually receives or disburses money MUST require selecting an existing authorized vault.  

---

## 1. Executive Summary

This audit evaluates the physical and electronic vault reconciliation, mandatory vault enforcement, negative-balance protection, and inter-vault transfers.

---

## 2. Vault Operations & Verification Matrix

| Test ID | Operation | Real Input Tested | Expected Outcome | Actual Database State | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **VLT-01** | Mandatory Vault on Inflow | Create student payment without `vault_id` | Operation rejected with exception | `InvalidArgumentException` thrown | **PASS** |
| **VLT-02** | Mandatory Vault on Expense | Create expense without `vault_id` | Operation rejected by validation | Submission blocked | **PASS** |
| **VLT-03** | Mandatory Vault on Salary | Pay instructor salary without `vault_id` | Operation rejected by validation | Payout blocked | **PASS** |
| **VLT-04** | Mandatory Vault on Refund | Execute refund without `vault_id` | Operation rejected by validation | Payout blocked | **PASS** |
| **VLT-05** | Overdraft Protection | Expense > current vault balance | Rejected with insufficient balance error | Transaction aborted | **PASS** |
| **VLT-06** | Vault Inter-Transfer | Transfer 1,500 EGP: Cash Vault -> Vodafone Cash | Cash decreases by 1,500, Vodafone increases by 1,500 | Balances conserved exactly | **PASS** |
| **VLT-07** | Transfer Zero-Revenue Rule| Audit school revenue after vault transfer | Total school revenue remains UNCHANGED | Transfers excluded from revenues | **PASS** |
| **VLT-08** | Pending Expense Audit | Inspect vault deduction on pending expense | Pending expense does NOT reduce cash | Excluded from cash balance formula | **PASS** |

---

## 3. Mathematical Formula for Vault Cash Balance

$$\text{Current Balance} = \text{Opening Balance} + \sum \text{Collections} + \sum \text{Transfers In} - \sum \text{Paid Expenses} - \sum \text{Paid Salaries} - \sum \text{Executed Refunds} - \sum \text{Transfers Out}$$

Every component is queried in real time from active transactional rows, eliminating stale caches while maintaining high response speeds.

---

## 4. Verification Verdict

**STATUS: PASS**  
The vault subsystem operates with complete accounting accuracy. No incoming or outgoing cash can bypass vault selection.
