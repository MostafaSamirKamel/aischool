# AI SCHOOL — REPORTING CENTER AND FINANCIAL STATEMENT AUDIT
**Document ID:** `13_REPORTING_CENTER.md`  
**Execution Date:** 2026-10-25  
**Core Standard:** Authoritative Source Reconciliations, Cash Flow vs. P&L Separation, Multi-Format Exports  

---

## 1. Executive Summary

This report inspects the Reporting Center of AI School. The reporting engine aggregates academic, administrative, and financial indicators across branches and academic terms.

A critical requirement verified in this audit is that every summary card and aggregate figure reconciles mathematically against underlying database transactions, with strict separation between cash flow and profit & loss.

---

## 2. Reporting Center Verification Matrix

| Test ID | Report / Metric | Data Source | Verification Method | Reconciled Result | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **REP-01** | Total Cash Collections | Sum of active `payments.amount` + down payments | Reconcile against vault inflows | Exact match | **PASS** |
| **REP-02** | Installments Due vs Overdue| Sum of pending/partial installments by `due_date` | Reconcile against calendar dates | Correctly partitioned | **PASS** |
| **REP-03** | Waived Installments | Sum of installments where `status = 'waived'` | Excluded from collectible receivables | Excluded from active debt | **PASS** |
| **REP-04** | Cash Flow vs P&L | Unpaid installments recognized in P&L vs Cash Flow | Cash Flow = strictly cash in vaults | Respected boundary | **PASS** |
| **REP-05** | Branch Comparison | Group aggregations by `branch_id` | Check for cross-branch data bleeding | Strict branch boundaries | **PASS** |
| **REP-06** | CSV Export | Export collections report to CSV | UTF-8 with BOM; Arabic text legible | Cleanly exported | **PASS** |
| **REP-07** | XLSX Export | Export installment aging report to Excel | Formatted currency, dates, and headers | Cleanly exported | **PASS** |
| **REP-08** | PDF Export | Export official monthly supervision report | Printable A4 layout with school branding | Cleanly exported | **PASS** |

---

## 3. Granular Reporting Permissions

The Reporting Center enforces 4 independent permission tiers:
1. `view`: View high-level KPI cards and summary statistics.
2. `view_details`: Drill down into itemized student rosters and individual transaction lines.
3. `export`: Export reports to Excel, CSV, or PDF.
4. `manage_settings`: Configure automated report distribution schedules.

---

## 4. Reporting Center Verdict

**STATUS: PASS**  
The Reporting Center is mathematically accurate, fully reconciled with transactional ledgers, and secured with granular role permissions.
