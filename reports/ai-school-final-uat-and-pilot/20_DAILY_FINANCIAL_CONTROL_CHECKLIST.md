# AI SCHOOL — DAILY FINANCIAL CONTROL & AUDIT CHECKLIST
**Document ID:** `20_DAILY_FINANCIAL_CONTROL_CHECKLIST.md`  
**Execution Date:** 2026-10-25  
**Audit Purpose:** Standard Operating Procedure (SOP) for Daily Accounting Control  
**Applicability:** Daily School Operations (Pilot & General Production)  

---

## 1. Executive Summary

This checklist establishes the daily financial control protocol that the branch accountant and branch director must execute at the start, middle, and close of each operational school day.

> [!WARNING]
> **STRICT AUDIT RULE:**  
> **Never silently auto-correct financial differences.** Any mismatch between physical vault cash and calculated system ledger balance must be documented immediately in an incident report.

---

## 2. Daily Three-Stage Financial Control Protocol

### Stage 1: Morning Opening Checklist (08:30 AM)
- [ ] Log in as Branch Accountant (`/admin`).
- [ ] Open `/admin/vaults` and verify the opening balance for each active branch vault:
  - [ ] Cash Vault (`كاش`)
  - [ ] Vodafone Cash (`فودافون كاش`)
  - [ ] Instapay (`إنستاباي`)
- [ ] Count physical cash drawer to ensure physical bills equal the recorded opening balance.
- [ ] Sign opening cash register ledger.

---

### Stage 2: Mid-Day Operational Audits (02:00 PM)
- [ ] **Canonical Collection Check:** Confirm all student installment payments were entered strictly through **Student Payments → Add Payment** (`/admin/payments/create`).
- [ ] **Vault Allocation Verification:** Confirm each recorded payment receipt specifies the correct physical or digital vault.
- [ ] **Pending Expense Review:** Review `/admin/expenses`. Ensure pending drafts are reviewed and approved before cash disbursement.
- [ ] **Vault Transfer Verification:** Ensure inter-vault transfers (e.g. moving funds from Cash Drawer to Vodafone Cash) are recorded via `/admin/vault-transfers` and do NOT inflate school revenues.

---

### Stage 3: End-of-Day Balancing & Sign-Off (08:00 PM)
- [ ] Navigate to `/admin/vaults` to generate the Daily Vault Reconciliation Statement.
- [ ] Execute mathematical balancing for EACH vault:
  $$\text{Expected Closing Balance} = \text{Opening Balance} + \text{Collections} + \text{Transfers In} - \text{Expenses} - \text{Salaries} - \text{Refunds} - \text{Transfers Out}$$
- [ ] Physically count cash drawer notes and verify electronic wallet app balances.
- [ ] Calculate Variance:
  $$\text{Variance} = \text{Physical Count} - \text{Calculated System Balance}$$
- [ ] If $\text{Variance} == 0.00\text{ EGP}$: Complete daily sign-off.
- [ ] If $\text{Variance} \neq 0.00\text{ EGP}$: Escalate to Branch Manager immediately.

---

## 3. Daily End-of-Day Sign-Off Ledger Template

| Date | Branch Vault | Opening (EGP) | Inflows (EGP) | Outflows (EGP) | System Balance | Physical Count | Variance | Accountant Signature | Manager Signature |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| ___ / ___ / 2026 | كاش (Cash) | | | | | | 0.00 | __________________ | __________________ |
| ___ / ___ / 2026 | فودافون كاش | | | | | | 0.00 | __________________ | __________________ |
| ___ / ___ / 2026 | إنستاباي | | | | | | 0.00 | __________________ | __________________ |

---

## 4. Verification Verdict

**STATUS: DAILY FINANCIAL CONTROL CHECKLIST APPROVED FOR OPERATIONAL DEPLOYMENT**
