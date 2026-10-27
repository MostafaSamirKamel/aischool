# 19 — CONFIRMED BUGS AND APPLIED FIXES

**Audit Stage:** Master Production Acceptance Testing  
**Audit Scope:** Defect discovery, severity classification, minimal scope repair, and regression proof.

---

## 1. Defect Classification Standard
- **P0 (Critical):** Financial corruption, unauthorized cross-tenant data access, unrecoverable data loss.
- **P1 (High):** Broken school workflow, incorrect financial balance, broken authorization gate.
- **P2 (Medium):** Inaccurate filters, query edge cases, report inconsistencies.
- **P3 (Low):** Minor usability or nonblocking edge cases.

---

## 2. Confirmed & Repaired Defects

### Defect BUG-01: Null-Safe Auth in AttendanceSession Creation
- **Severity:** P2 (Operational Robustness)
- **Reproduction:** During automated cron jobs, queue workers, or CLI seeds where `auth()->user()` is null, invoking `AttendanceSession::create()` threw a fatal PHP exception: `Attempt to read property "tenant_id" on null`.
- **Root Cause:** In `app/Models/AttendanceSession.php::boot()`, `static::creating` unconditionally dereferenced `auth()->user()->tenant_id` without checking if `auth()->user()` was null.
- **Minimal Safe Fix:**
  ```php
  static::creating(function ($session) {
      if (!$session->tenant_id) {
          $session->tenant_id = auth()->user()?->tenant_id ?? $session->group?->tenant_id;
      }
  });
  ```
- **Regression Verification:** Added test coverage in `MasterProductionAcceptanceTest::test_stage_d_and_e_sessions_and_attendance_integrity`. Verified passing in 100% of CLI and HTTP runs.

---

### Defect BUG-02: Missing Dynamic Balance Accessor on Vault Model
- **Severity:** P2 (Financial API Consistency)
- **Reproduction:** Attempting to read `$vault->balance` on a retrieved `Vault` instance returned `null` because the `vaults` table stores `opening_balance` and computes real-time funds dynamically via `getCurrentBalance()`.
- **Root Cause:** In `app/Models/Vault.php`, there was no getter accessor linking the property `$vault->balance` to the method `$vault->getCurrentBalance()`.
- **Minimal Safe Fix:**
  ```php
  public function getBalanceAttribute(): float
  {
      return (float) $this->getCurrentBalance();
  }
  ```
- **Regression Verification:** Tested in `MasterProductionAcceptanceTest` across all financial collection, payroll, and settlement tests. `$vault->balance` returns the exact real-time mathematical balance.

---

## 3. Unresolved Critical Defects
**NONE.** All P0, P1, and P2 defects identified during pre-flight and acceptance testing have been repaired and verified.
