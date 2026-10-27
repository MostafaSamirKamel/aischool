# Phase 12 — Report 12: Confirmed Bugs & Implemented Fixes

**Project:** AI School Management System  
**Scope:** Root Cause Analysis, Code Corrections & Verification  
**Status:** PASS  
**Date:** 2026-10-21  

---

## 1. Summary of Repaired Defects

During the thorough audit and end-to-end integration testing, 5 confirmed backend bugs were identified, repaired, and regression-tested. Zero frontend templates or styles were altered.

---

## 2. Detailed Bug Reports

### Bug 1: Signed Negative Duration in `SessionSchedule`
- **Broken Operation:** Session duration calculation in `SessionSchedule::calculateDurationMinutes()`.
- **Reproduction Steps:**
  1. Create a session schedule starting at `10:00:00` and ending at `12:00:00`.
  2. Call `$schedule->calculateDurationMinutes()`.
- **Frontend-Backend Connection:** `SessionScheduleResource` and timetable display.
- **Root Cause:** In Carbon 3 / PHP, `$end->diffInMinutes($start)` is signed. For `$end = '12:00'` and `$start = '10:00'`, it computed `$end->diffInMinutes($start)` which returned `-120`.
- **Impact:** Negative durations corrupted instructor hourly pay calculations and schedule display widgets.
- **Implemented Fix:** Wrapped difference in `abs()` in [SessionSchedule.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Models/SessionSchedule.php):
  ```php
  // Before:
  return (int) $end->diffInMinutes($start);
  // After:
  return abs((int) $end->diffInMinutes($start));
  ```
- **Verification:** Verified via `test_session_schedule_calculates_positive_duration()`. Duration is consistently `+120`.

---

### Bug 2: Timezone String Serialization Mismatch in `InstructorPayment`
- **Broken Operation:** Accrued session payment lookup and updates (`updateOrCreateForSession`).
- **Reproduction Steps:**
  1. Instructor delivers a session on `2026-10-01`.
  2. Carbon object passed as `period_start` (`2026-10-01 00:00:00+03:00`).
  3. In SQLite / MySQL, Eloquent serialized the Carbon object as UTC timestamp string (`2026-09-30T21:00:00Z`).
  4. Subsequent `WHERE period_start = '2026-10-01'` queries failed to match the existing row, generating duplicate pending payments.
- **Root Cause:** Missing explicit string formatting mutators on `period_start` and `period_end` date attributes.
- **Impact:** Duplicate salary payment snapshots created for the same instructor and period.
- **Implemented Fix:** Added explicit mutators in [InstructorPayment.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Models/InstructorPayment.php):
  ```php
  public function setPeriodStartAttribute($value)
  {
      $this->attributes['period_start'] = $value ? Carbon::parse($value)->toDateString() : null;
  }

  public function setPeriodEndAttribute($value)
  {
      $this->attributes['period_end'] = $value ? Carbon::parse($value)->toDateString() : null;
  }
  ```
- **Verification:** Verified in `test_instructor_earnings_calculated_and_double_completion_prevented()`. Exact date matches eliminate duplicate rows.

---

### Bug 3: Global Scope Authorization Blockage in Background Observers
- **Broken Operation:** Session completion updating instructor payment records when triggered outside an admin session context.
- **Reproduction Steps:**
  1. Trigger session completion via queue or non-admin background task.
  2. `Group::find($session->group_id)` returned null due to active user tenant/branch global scope constraints.
- **Root Cause:** Calling scoped `Group` and `Instructor` queries inside the system observer.
- **Impact:** Silent failure of instructor earnings calculation on completed sessions.
- **Implemented Fix:** In [InstructorPayment.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Models/InstructorPayment.php), utilized `withoutGlobalScopes()`:
  ```php
  $group = Group::withoutGlobalScopes()->find($session->group_id);
  $instructor = Instructor::withoutGlobalScopes()->find($session->instructor_id);
  ```
- **Verification:** Earnings correctly generated in all contexts, including automated background jobs.

---

### Bug 4: Observer Call Fragmentation & Settlement Protection
- **Broken Operation:** `AttendanceSessionObserver::updateInstructorPayment` maintained duplicated, outdated logic that did not respect already-settled salary records.
- **Reproduction Steps:**
  1. Pay instructor salary for Session #1 (`salary_payment_id != null`).
  2. Re-trigger observer or edit session notes.
  3. Outdated observer logic recalculated total earnings without excluding paid records.
- **Root Cause:** Duplication of calculation logic across `AttendanceSessionObserver` and `InstructorPayment`.
- **Implemented Fix:** Refactored [AttendanceSessionObserver.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Observers/AttendanceSessionObserver.php) to delegate directly to canonical `InstructorPayment::updateOrCreateForSession($session)` which strictly filters `whereNull('salary_payment_id')`.
- **Verification:** Verified in `test_paid_sessions_are_excluded_from_new_monthly_payment_snapshot()`.

---

### Bug 5: Missing Vault Balance Validation on Salary Payout
- **Broken Operation:** Creating a salary disbursement when vault balance was lower than payment amount.
- **Reproduction Steps:**
  1. Open `/admin/salary-payments/create`.
  2. Select vault with `100.00` EGP.
  3. Enter payout amount `500.00` EGP and submit form.
- **Root Cause:** `SalaryPaymentForm` lacked a closure rule comparing vault balance against `amount`.
- **Impact:** Potential vault balance overdrafts or unhandled database exceptions.
- **Implemented Fix:** Added closure validation rule in [SalaryPaymentForm.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Filament/Admin/Resources/SalaryPayments/Schemas/SalaryPaymentForm.php):
  ```php
  fn (\Filament\Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
      $amount = (float) $get('amount');
      if ($value && $amount > 0) {
          $vault = Vault::find($value);
          if ($vault && $vault->balance < $amount) {
              $fail(__('The selected vault does not have sufficient balance for this payout.'));
          }
      }
  }
  ```
- **Verification:** Form rejects payment cleanly with user-friendly error message; tested in `test_insufficient_vault_balance_rejects_salary_payment()`.

---

## 3. Defect Resolution Summary

All 5 defects were repaired with minimal, targeted changes, preserving complete backward compatibility and maintaining 100% of approved frontend templates.
