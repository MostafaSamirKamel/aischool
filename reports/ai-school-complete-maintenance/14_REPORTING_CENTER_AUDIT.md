# AI SCHOOL — REPORTING CENTER AND FINANCIAL STATEMENT AUDIT
**Document ID:** `14_REPORTING_CENTER_AUDIT.md`  
**Execution Date:** 2026-10-25  
**Audit Scope:** Reporting Center, Financial Reconciliation, Cash Flow vs. P&L, Export Formats, Permission Tiers  

---

## 1. Executive Summary

This audit inspects the centralized reporting engine of AI School. The Reporting Center aggregates academic, administrative, and financial metrics across branches and tenants.

A critical requirement of this audit is verifying that every displayed aggregate total is mathematically derived from authoritative transactional records, preserving strict separation between **Cash Flow** and **Profit & Loss**.

---

## 2. Cash Flow vs. Profit & Loss Distinction

| Dimension | Cash Flow Accounting (حركة النقدية) | Profit & Loss Statement (الأرباح والخسائر) |
| :--- | :--- | :--- |
| **Revenue Recognition** | Recognized strictly when cash enters a vault (`Payment` / `paid_amount`) | Recognized based on delivered terms and earned fees |
| **Expense Recognition** | Recognized strictly upon cash disbursement from vault | Recognized when approved/incurred regardless of disbursement date |
| **Installments Owed** | Excluded from cash flow until actually collected | Tracked as Accounts Receivable (أصول متداولة) |
| **Refunds** | Deducted only upon executed vault disbursement | Deducted from gross earned revenues |
| **Vault Balances** | Directly reconciles with physical and electronic vaults | Independent of vault liquid positions |

The reporting services strictly maintain this boundary, preventing misleading profit numbers when underlying collections remain unrealized.

---

## 3. Metric Reconciliations Audited

1. **Student Registration Counts:**
   - Active Students: Matches `Student::where('status', 'active')->count()`.
   - New Enrollments: Distinguishes between new students and renewals.
2. **Installment Metrics:**
   - Total Collectible: Exact sum of remaining balances on pending/partially paid installments.
   - Overdue Balances: Exact sum where `due_date < CURRENT_DATE` and `status IN ('pending', 'partially_paid')`.
   - Waived Balances: Strictly tracked under historical settlements; excluded from collectible receivables.
3. **Vault Metrics:**
   - Individual vault balance cards match `vault->getCurrentBalance()` to the penny.
4. **Attendance Metrics:**
   - Reconciles total scheduled sessions vs. recorded attendance percentages, preserving the unrecorded session distinction.

---

## 4. Multi-Tier Permission Hierarchy

The Reporting Center enforces 4 granular permission levels via Filament Shield:
- `view`: Allows viewing summary cards and aggregate totals.
- `view_details`: Allows drill-down into individual student and transaction lists.
- `export`: Authorizes exporting data to CSV, XLSX, and PDF formats.
- `manage_settings`: Authorizes report template customization and scheduled automated reports.

---

## 5. Export Engine Verification

- **CSV Export:** Fast, UTF-8 with BOM for flawless Arabic character rendering in Microsoft Excel.
- **XLSX Export:** Generates clean multi-column spreadsheets with formatted currency amounts and dates.
- **PDF Export:** Generates printable A4 reports with headers, school branding, and official signature blocks.

---

## 6. Audit Verdict

**STATUS: PASS**  
The Reporting Center is mathematically reconciled with the database, respects accounting principles, and enforces rigorous access controls.
