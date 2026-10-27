# AI SCHOOL — ROLES, PERMISSIONS & MULTI-TENANT SECURITY AUDIT
**Document ID:** `13_PERMISSIONS_AND_SECURITY.md`  
**Execution Date:** 2026-10-25  
**Audit Purpose:** Authorization Verification, Tenant Isolation & Attack Resistance  
**Testing Criteria:** Spatie Roles, Tenant Scoping, Policy Enforcements, and Forbidden Route Traversal  

---

## 1. Executive Summary

This report establishes that AI School enforces strict role-based access control (RBAC) and tenant isolation across its branch structure. Cross-tenant queries are blocked at the Eloquent global scope level, and financial actions require explicit permissions.

---

## 2. Multi-Tenant Branch Isolation Audit

```
┌─────────────────────────────────┐       ┌─────────────────────────────────┐
│     TENANT 1: الفرع الرئيسي     │       │       TENANT 2: فرع دمنهور      │
│  - Vaults: #1, #2, #3           │  ◄-X-►│  - Vaults: #4, #5, #6           │
│  - Students: Cairo Enrollments  │       │  - Students: Damanhour Roster   │
│  - Staff: Cairo Personnel       │       │  - Staff: Damanhour Personnel   │
└─────────────────────────────────┘       └─────────────────────────────────┘
          ▲                                                 ▲
          └──────────── [Super Admin: Global Scope] ────────┘
```

| Security Test ID | Attempted Cross-Tenant Operation | User Role / Account | Result / Defense Triggered | Status |
| :--- | :--- | :--- | :--- | :---: |
| **SEC-TEN-01** | Cairo accountant views Damanhour Vault #4 | Accountant (`tenant_id = 1`) | HTTP 403 / ModelNotFoundException (`TenantScope`) | **PASS** |
| **SEC-TEN-02** | Damanhour secretary views Cairo student profile | Secretary (`tenant_id = 2`) | Query returns empty; direct URL denied | **PASS** |
| **SEC-TEN-03** | Cairo instructor attempts marking Damanhour group | Instructor (`tenant_id = 1`) | Group roster unresolvable | **PASS** |
| **SEC-TEN-04** | Cross-tenant cash transfer attempt | Branch Manager | Transfer validation blocks non-owned vaults | **PASS** |

---

## 3. Financial Authorization Matrix

| User Role | Collect Installments | Record Expense | Disburse Salary | Execute Refund | Vault Transfer | View Vault Balances |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Super Admin** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Branch Manager** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Accountant** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Secretary / Registrar** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Instructor** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Educational Supervisor**| ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Student** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Parent** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 4. User Status & Session Enforcement

- **Active Users:** Permitted full access based on assigned permissions.
- **Suspended / Inactive Users:** Handled by `CheckUserStatus` middleware. Any user whose `status != 'active'` is immediately logged out and redirected to login with an account suspension notification.

---

## 5. Verification Verdict

**STATUS: ROLES, PERMISSIONS & TENANT ISOLATION FULLY VERIFIED (PASS)**
