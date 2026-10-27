# VAULT PERMISSION MATRIX
## Independent Permissions Architecture, Authorization Boundaries & Leakage Prevention

---

### Overview

In strict accordance with Section 7 and Section 17 of the refactoring prompt:
- **Operational permissions** (collecting, paying expenses, paying salaries, executing refunds, transferring) are strictly separated from **visibility permissions**.
- Any employee permitted to execute a financial operation can select **any active vault in their authorized branch**.
- **Two new independent visibility permissions** were introduced and integrated into Spatie Laravel-Permission and Filament Shield:
  1. `view_vault_balances`: Controls seeing monetary balances and figures.
  2. `view_vault_transactions`: Controls seeing ledger transaction statements and reports.

---

### 1. Independent Permission Matrix

| Capability / Screen | Required Permission | Employee without Balance Visibility (`view_vault_balances` = NO) | Employee without Statement Visibility (`view_vault_transactions` = NO) | Authorized Auditor / Admin (Both = YES) |
|---|---|---|---|---|
| **Collect Student Payment** | `create_student_payments` | Allowed. Chooses vault by name. Balance NOT shown. | Allowed. Chooses vault by name. | Allowed. Chooses vault; sees balance in dropdown. |
| **Record Operational Expense** | `create_expenses` | Allowed if funds sufficient. Balance NOT shown. | Allowed. | Allowed. Sees balance in dropdown. |
| **Disburse Salary Payment** | `create_salary_payments` | Allowed if funds sufficient. Balance NOT shown. | Allowed. | Allowed. Sees balance in dropdown. |
| **Execute Student Refund** | `refund_enrollments` | Allowed if funds sufficient. Balance NOT shown. | Allowed. | Allowed. Sees balance in dropdown. |
| **Execute Vault Transfer** | `create_vault_transfers` | Allowed if source balance sufficient. | Allowed. | Allowed. Sees balances. |
| **Vault List Table Balances** | `view_vault_balances` | Column Hidden (Opening Balance & Current Balance). | Visible if `view_vault_balances` = YES. | Fully Visible. |
| **Dropdown Label with Balance** | `view_vault_balances` | Displays only vault name (e.g. `"Vodafone Cash"`). | Displays only vault name if balance perm is NO. | Displays `"Vodafone Cash (الرصيد: 5,000.00 ج.م)"`. |
| **Dashboard Balance KPI Card** | `view_vault_balances` | Stat card completely omitted from `StatsOverview`. | Card visible if balance perm is YES. | Card visible with real total balance. |
| **Payment Methods / Vaults Chart**| `view_vault_balances` | Widget returns `canView() = false`. | Widget visible if balance perm is YES. | Displays real chart of collections. |
| **Vault Daily Movement Widget** | `view_vault_transactions` | Widget visible if statement perm is YES. | Widget returns `canView() = false`. | Fully Visible. |
| **Vault Statement Page** (`VaultTransactions`) | `view_vault_transactions` | Access Denied (`403 Forbidden` / `canAccess() = false`). | Access Denied. | Access Granted. Full chronological ledger. |
| **Comprehensive Vault Report** (`VaultReport`) | `view_vault_transactions` | Access Denied (`403 Forbidden` / `canAccess() = false`). | Access Denied. | Access Granted. Summary & statements. |

---

### 2. Information Leakage Prevention Architecture

When an employee attempts to disburse more than the available vault balance:
- If the employee has `view_vault_balances` (or Super Admin):
  $$\text{Detailed Message: } \text{"لا يمكن إتمام العملية: الرصيد المتاح 500.00 ج.م والمطلوب 800.00 ج.م"}$$
- If the employee does **NOT** have `view_vault_balances`:
  $$\text{Generic Arabic Message: } \text{"لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية."}$$
- Exact balance amounts are strictly scrubbed from:
  - Error messages and validation bags
  - Tooltips and placeholder text
  - HTML DOM attributes and Livewire JSON state payloads
  - Network API / autocomplete responses

---

### 3. Tenant & Branch Isolation Safeguards

- Non-super-admin users are strictly scoped to their `tenant_id` via global scopes and `BelongsToBranch`.
- In `VaultForm`, `PaymentForm`, `ExpenseForm`, and `VaultTransferForm`, all queries filter `where('tenant_id', auth()->user()->tenant_id)`.
- Cross-branch access attempts are immediately rejected by both Eloquent tenant scopes and authorization policies.
