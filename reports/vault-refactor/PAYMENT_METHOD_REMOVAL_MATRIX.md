# PAYMENT METHOD REMOVAL MATRIX
## System-Wide Field Replacement, Schema Scoping & Canonical Vault Routing

---

### Overview

As mandated by the primary business requirement:
> **"THE SELECTED VAULT IS THE ONLY USER-SELECTED SOURCE OR DESTINATION OF MONEY."**

Every user-facing "Payment Method" selector across all administrative, operational, and Livewire financial workflows was removed and replaced with a dynamic, branch-scoped, permission-aware Vault selector.

---

### Detailed Screen-by-Screen Replacement Matrix

| Screen / Component | Former Payment Method Field | Replacement Vault Selector | Arabic Field Label | Scoping & Behavior |
|---|---|---|---|---|
| **Enrollment Registration** (`EnrollmentForm.php`) | `payment_method` (Select) | `vault_id` (Select) | `الخزنة المستلمة` | Dynamic DB choices; scoped to tenant; displays balance only if user has `view_vault_balances`. |
| **Payment Creation & Edit** (`PaymentForm.php`) | `payment_method` (Select) | `vault_id` (Select) | `الخزنة المستلمة` | Required for new collections; searchable; scoped to user's branch. |
| **Expense Creation & Edit** (`ExpenseForm.php`) | `payment_method` (Select) | `vault_id` (Select) | `الخزنة المصروف منها` | Mandatory; checks sufficient balance on saving; prevents negative balances. |
| **Salary Payment Form** (`SalaryPaymentForm.php`) | `payment_method` (Select) | `vault_id` (Select) | `الخزنة المصروف منها` | Required when `status === 'paid'`; asserts sufficient vault balance. |
| **Salary Mark as Paid Modal** (`SalaryPaymentsTable.php`) | `payment_method` (Hidden/Static) | `vault_id` (Searchable Select) | `الخزنة المصروف منها` | Allows user to select paying vault dynamically upon disubrsement. |
| **Revenue Creation & Edit** (`RevenueForm.php`) | `payment_method` (Select) | `vault_id` (Select) | `الخزنة المستلمة` | Dynamic active vaults; model automatically defaults legacy column to `'vault'`. |
| **Enrollment Cancellation** (`CancelSubscriptionAction.php`)| `refund_payment_method` (Select) | `refund_vault_id` (Select) | `الخزنة المصروف منها` | Visible and required only when `refund_amount > 0`. |
| **Execute Refund Action** (`ExecuteRefundAction.php`) | `payment_method` (Select) | `refund_vault_id` (Select) | `الخزنة المصروف منها` | Dynamically loads active branch vaults; verifies `executeRefund` permission. |
| **Vault Transfers** (`VaultTransferForm.php`) | Redundant fields | `from_vault_id` & `to_vault_id` | `من خزنة` & `إلى خزنة` | Validates source != destination and sufficient source balance. |
| **Livewire Due Today** (`installments-due-today.blade.php`)| `paymentMethod` (HTML Select) | `vaultId` (Searchable Select) | `الخزنة المستلمة *` | Bound to `$vaultId`; loads active vaults with balance if permitted. |
| **Livewire Due Week** (`installments-due-week.blade.php`) | `paymentMethod` (HTML Select) | `vaultId` (Searchable Select) | `الخزنة المستلمة *` | Bound to `$vaultId`; loads active vaults with balance if permitted. |
| **Livewire Due Month** (`installments-due-month.blade.php`)| `paymentMethod` (HTML Select) | `vaultId` (Searchable Select) | `الخزنة المستلمة *` | Bound to `$vaultId`; loads active vaults with balance if permitted. |

---

### Backend Data Integrity & Legacy Strategy

1. **Database Schema Non-Destructive Invariant:**
   - Existing database columns `payment_method` in `payments`, `expenses`, `revenues`, and `salary_payments` were **NOT** dropped.
   - Dropping historical columns would destroy audit trails for past transactions.
2. **Handling Legacy vs New Transactions:**
   - Historical records with existing payment methods (e.g. `'cash'`, `'mobile_wallet'`) remain unchanged.
   - For new operations, user interfaces only collect `vault_id`.
   - In `Revenue.php` creating hook, `payment_method` automatically falls back to `'vault'` to satisfy non-null constraints while avoiding false channel assumptions.
3. **Table Column Visibility:**
   - Historical payment methods remain viewable in tables via toggleable columns (defaulting to hidden): `طريقة الدفع (تاريخي)`.
