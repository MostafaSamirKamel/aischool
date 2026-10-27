# 08 — VAULT TRANSFERS AUDIT TESTS

## 1. Test Scenarios
1. **Valid Transfer**: 300 EGP from Vault 1 to Vault 2.
   - Result: Vault 1 balance decreased by 300 EGP; Vault 2 balance increased by 300 EGP atomically. (**PASS**)
2. **Missing Destination**: `to_vault_id = null`.
   - Result: Rejected by `VaultTransfer::saving` hook. (**PASS**)
3. **Same-Vault Transfer**: `from_vault_id === to_vault_id`.
   - Result: Rejected with validation exception. (**PASS**)
4. **Insufficient Balance**: Transfer amount exceeding available balance.
   - Result: Rejected by `assertSufficientBalance`. (**PASS**)