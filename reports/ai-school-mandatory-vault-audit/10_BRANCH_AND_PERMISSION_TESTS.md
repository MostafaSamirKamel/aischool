# 10 — BRANCH ISOLATION & PERMISSION SECURITY TESTS

## 1. Multi-Tenant Branch Isolation
1. **Cross-Tenant Vault Assignment**:
   - Attempted to post a payment for Tenant 1 using a Vault belonging to Tenant 2.
   - Result: Threw `AuthorizationException`. Cross-tenant leakage strictly blocked.
2. **Branch User Scoping**:
   - Cashiers and secretaries only view and select vaults belonging to their authenticated tenant.

## 2. Role Permissions
1. **Supervisor Restrictions**:
   - Educational supervisors cannot view vault balances or execute refund disbursements.
   - Tested via `EnrollmentPolicy::executeRefund($supervisor)` -> Evaluates to `false`.
2. **Company Admin Privileges**:
   - Branch managers cannot grant permissions exceeding company admin scopes.