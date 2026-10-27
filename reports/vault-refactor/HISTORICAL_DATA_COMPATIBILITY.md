# HISTORICAL DATA COMPATIBILITY & MIGRATION STRATEGY
## Preservation of Legacy Financial Records, Non-Destructive Migrations & Fallback Integrity

---

### 1. Mandatory Data Preservation Principle

In strict compliance with Section 11 of the refactoring prompt:
> **"DO NOT DELETE HISTORICAL PAYMENT METHOD VALUES OR REASSIGN OLD TRANSACTIONS TO ARBITRARY VAULTS."**

Historical financial entries represent audited tax and financial records. The refactoring design explicitly forbids any destructive migration or retroactive mutation of past transaction logs.

---

### 2. Pre-Implementation Safety Backup

Prior to schema and code adjustments:
- A snapshot backup of the application database was executed to:
  `database/database.sqlite.bak_pre_vault_refactor`
- Integrity of backup tables and row counts was verified.

---

### 3. Database Schema Strategy

1. **Non-Destructive Column Preservation:**
   - Database columns `payment_method` across tables `payments`, `expenses`, `salary_payments`, and `revenues` remain present in the schema.
   - A dedicated migration (`2026_10_20_000001_add_vault_visibility_permissions.php`) was introduced purely to register new permissions (`view_vault_balances`, `view_vault_transactions`, `view_any_vault`, `view_vault`) without dropping existing columns.
2. **Nullable Legacy Compatibility:**
   - For all new operations, `vault_id` is the canonical foreign key.
   - For new incoming payments, `payment_method` is nullable or defaults cleanly to `'vault'`.
   - Existing records retaining values such as `'cash'`, `'bank_transfer'`, `'mobile_wallet'` remain 100% intact.
3. **Table Column Toggling:**
   - In administrative index tables (`PaymentsTable.php`, `ExpensesTable.php`, `RevenuesTable.php`), historical payment methods are viewable as optional toggleable columns labeled:
     `طريقة الدفع (تاريخي)` (Hidden by default, can be toggled on by administrators during historical audits).

---

### 4. Handling Unlinked Legacy Transactions

For historical transactions created before vault enforcement:
- If a historical record has an existing valid `vault_id`, it is included in that vault's chronological statement.
- If a historical record lacks a `vault_id`, it is **NOT** arbitrarily reassigned to a modern vault. Such records are preserved in their respective tables with `vault_id = NULL` and isolated as legacy records requiring manual bookkeeping reconciliation.
- Vault statements and reports display warning badges or flags for legacy unlinked items rather than falsifying destination vaults.

---

### 5. Automated Regression Verification

- **TEST 16 in `VaultRefactorEndToEndTest`:**
  Verified that an existing historical payment record with `payment_method = 'mobile_wallet'` retains its exact value after repeated model updates, while vault balances reconcile accurately.
