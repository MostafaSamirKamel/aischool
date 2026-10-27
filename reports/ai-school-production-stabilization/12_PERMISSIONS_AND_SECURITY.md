# AI SCHOOL — SECURITY, MULTI-TENANCY, AND PERMISSIONS AUDIT
**Document ID:** `12_PERMISSIONS_AND_SECURITY.md`  
**Execution Date:** 2026-10-25  
**Audit Priority:** CRITICAL  
**Scope:** Authentication, Multi-Tenant Scoping, Branch Isolation, Shield Delegation Ceilings, Web Security  

---

## 1. Executive Summary

This audit evaluates the application's multi-tenant isolation, branch boundaries, role-based access controls (Filament Shield), direct URL route protections, and vulnerability resilience.

---

## 2. Security and Permissions Test Matrix

| Test ID | Security Dimension | Test Operation | Expected Behavior | Actual Behavior | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **SEC-01** | Multi-Tenant Isolation | Tenant 1 user attempts URL access to Tenant 2 installment | Request rejected with HTTP 403 / 404 | HTTP 403 Forbidden | **PASS** |
| **SEC-02** | Branch Scoping | Branch A accountant attempts to disburse from Branch B vault | Operation blocked by model policy | Access denied | **PASS** |
| **SEC-03** | Delegation Ceilings | Branch Manager attempts to assign Super Admin role | System blocks privilege escalation | Escalation blocked | **PASS** |
| **SEC-04** | Direct URL Bypassing | Unauthenticated request to `/admin/payments` | Redirected to `/admin/login` | Redirected to login | **PASS** |
| **SEC-05** | Parent-Child Isolation | Parent A attempts to view grades of unrelated Student B | Portal returns 403 / Not Found | Access denied | **PASS** |
| **SEC-06** | Student Portal Masking | Student attempts to view unpublished exam answers | Draft questions/answers masked | Masked by query scope | **PASS** |
| **SEC-07** | SQL Injection Resilience | Inject `' OR '1'='1` in student search | Sanitized by PDO parameter binding | Escaped safely | **PASS** |
| **SEC-08** | CSRF Protection | Submit Livewire action without valid checksum/token | Livewire throws invalid signature exception | Request rejected | **PASS** |
| **SEC-09** | Mass Assignment Guard | Inject `is_admin = 1` in registration payload | Protected by `$fillable` array on User | Attribute ignored | **PASS** |

---

## 3. Defense-in-Depth Architecture

1. **Eloquent Global Scopes:** Multi-tenant models automatically bind `where('tenant_id', auth()->user()->tenant_id)`.
2. **Filament Shield Roles:** Permissions are granularly assigned at the resource level (view, create, update, delete, forceDelete, export).
3. **Backend Enforcement:** Permissions are enforced in Eloquent Observers and Model Policies, ensuring that API or Livewire calls cannot bypass authorization even if frontend UI elements are tampered with.

---

## 4. Security Audit Verdict

**STATUS: PASS**  
The application is resilient against privilege escalation, enforces rigorous multi-tenant data boundaries, and adheres to the principle of least privilege.
