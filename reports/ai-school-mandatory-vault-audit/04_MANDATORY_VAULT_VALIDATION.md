# 04 — MANDATORY VAULT VALIDATION & BACKEND GUARDRAILS

## 1. Seven-Layer Defense Model
Validation is not left to frontend HTML/JS forms. Backend Eloquent model hooks (`static::saving`) enforce absolute compliance:

1. **Explicit Selection**: `vault_id` must be present and non-null on all completed/paid transactions.
2. **Existence Check**: Vault record must exist in the database.
3. **Active Status**: Vault must have `is_active = true`.
4. **Tenant Isolation**: Vault `tenant_id` must match transaction `tenant_id`. Cross-tenant posting throws `AuthorizationException`.
5. **Direction Validation**: Inflows require receiving vault; outflows require disbursing vault; transfers require both.
6. **Balance Sufficiency**: Outflows call `assertSufficientBalance()` to prevent overdraft.
7. **Amount Validity**: Amount must be positive ($> 0$). Overpayments exceeding remaining debt are rejected.

## 2. Protected Models
- `App\Models\Payment`: Completed payments strictly require active tenant-matching vault.
- `App\Models\Expense`: Paid expenses require active tenant-matching vault with sufficient funds.
- `App\Models\SalaryPayment`: Paid salaries require active tenant-matching vault with sufficient funds.
- `App\Models\Enrollment`: `paid_amount > 0` requires active vault; `paid_amount == 0` requires null vault.
- `App\Models\Revenue`: Received revenues require active vault.
- `App\Models\VaultTransfer`: Requires two distinct active vaults from the same tenant with sufficient source funds.