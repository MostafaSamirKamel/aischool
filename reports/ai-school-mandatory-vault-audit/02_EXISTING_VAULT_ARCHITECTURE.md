# 02 — EXISTING VAULT ARCHITECTURE & LEDGER RECONCILIATION

## 1. Architectural Overview
AI School maintains a multi-tenant vault ledger system (`vaults` table) representing physical cash drawers, digital wallets (Vodafone Cash, InstaPay), and corporate bank accounts.

### Core Mathematical Formula (`Vault::getCurrentBalance()`):
$$\text{Current Balance} = \text{Opening Balance} + \text{Standalone Revenues} + \text{Completed Payments} + \text{Enrollment Upfronts} + \text{Transfers In} - \text{Paid Expenses} - \text{Paid Salaries} - \text{Transfers Out}$$

## 2. Hardened Ledger Restrictions
1. **Expenses Sum Filter**: Updated from `where('status', '!=', 'cancelled')` to `whereIn('status', ['paid', 'completed'])`. Unpaid liability obligations never deduct cash prematurely.
2. **Salaries Sum Filter**: Verified `where('status', 'paid')`. Calculated entitlements without payment do not deduct cash.
3. **Negative Balance Prevention**:
   - `Vault::assertSufficientBalance(float $amount, ?float $originalAmount = 0.0)` is executed prior to every cash outflow.
   - Throws `ValidationException` in Arabic with sanitized messaging for unauthorized users.
4. **Internal Transfers Neutrality**:
   - Transfers reduce `from_vault_id` and increase `to_vault_id` atomically.
   - Internal transfers are excluded from revenue and expense totals.