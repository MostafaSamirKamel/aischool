# 12 — ROLE-BASED ACCESS & MULTI-TENANT BRANCH SECURITY

## 1. Multi-Tenant Branch Scoping
- Tenant 1 staff cannot access or post transactions to Tenant 2 vaults.
- Cross-tenant vault assignments throw `AuthorizationException`.
- Reports automatically scope data to the authenticated user's tenant.

## 2. Financial Permissions
- Educational supervisors cannot disburse refunds or modify vault balances.
- Branch managers cannot override company admin authorization policies.
- Direct URL access to unauthorized financial actions is rejected at the policy layer.

## 3. Test Verdict: PASS
Role and branch boundaries strictly enforced.