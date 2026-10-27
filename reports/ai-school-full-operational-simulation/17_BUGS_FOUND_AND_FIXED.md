# AI SCHOOL — BUGS FOUND AND FIXED DURING OPERATIONAL TESTING
**Document ID:** `17_BUGS_FOUND_AND_FIXED.md`  
**Execution Date:** 2026-10-25  
**Audit Purpose:** Comprehensive Inventory of Identified Defects, Root Causes, and Applied Remedies  

---

## 1. Executive Summary

During the execution of the full-scale operational simulation and automated test runs, specific edge-case defects and seeder schema misalignments were detected and repaired immediately.

---

## 2. Defect Repair Inventory

### Defect ID: BUG-SIM-001
- **Severity:** HIGH
- **Component:** Seeder Database Idempotency & AttendanceSession Global Scope
- **Affected File:** `database/seeders/MasterFullSchoolQaSeeder.php` & `app/Models/AttendanceSession.php`
- **Root Cause:** In CLI seeder execution without an authenticated user, `firstOrCreate` applied tenant global scopes that hid existing sessions, causing the model `creating` hook to throw a duplicate session `ValidationException`.
- **Applied Fix:** Updated seeder to check `AttendanceSession::withoutGlobalScopes()->where('group_id', $grp->id)->whereDate(...)` before creation, making the seeder fully idempotent across repeated runs.
- **Verification:** Seeder completed successfully without exceptions.
- **Status:** **PASS / FIXED**

---

### Defect ID: BUG-SIM-002
- **Severity:** MEDIUM
- **Component:** Assignment Model Schema & Seeder
- **Affected File:** `database/seeders/MasterFullSchoolQaSeeder.php` (Line ~445)
- **Root Cause:** Seeder attempted to insert `course_id` into the `assignments` table, which only contains `group_id` (the course is resolved via the group relationship).
- **Applied Fix:** Removed the non-existent `course_id` column from the assignment payload.
- **Verification:** Assignments seeded cleanly across all 16 groups.
- **Status:** **PASS / FIXED**

---

### Defect ID: BUG-SIM-003
- **Severity:** MEDIUM
- **Component:** Assignment Submission Feedback Column
- **Affected File:** `database/seeders/MasterFullSchoolQaSeeder.php` (Line ~469)
- **Root Cause:** Seeder passed `feedback` instead of the database column name `instructor_feedback`.
- **Applied Fix:** Renamed payload key to `instructor_feedback`.
- **Verification:** 197 submissions seeded with instructor comments.
- **Status:** **PASS / FIXED**

---

### Defect ID: BUG-SIM-004
- **Severity:** MEDIUM
- **Component:** Vault Transfer & Expense Seeder Foreign Keys
- **Affected File:** `database/seeders/MasterFullSchoolQaSeeder.php` (Lines ~491 & ~520)
- **Root Cause:** Seeder used `created_by` on `vault_transfers` (which expects `transferred_by`) and `recipient_name` on `expenses` (which expects `title` and `expense_category_id`).
- **Applied Fix:** Corrected column names to match table schema definitions and added `created_by` on expenses.
- **Verification:** 12 vault transfers and 26 salary expenses seeded cleanly.
- **Status:** **PASS / FIXED**

---

## 3. Summary of Fixes

| Defect ID | Severity | File Affected | Verification | Status |
| :--- | :--- | :--- | :--- | :---: |
| **BUG-SIM-001** | HIGH | `MasterFullSchoolQaSeeder.php` | Idempotent Run | **FIXED** |
| **BUG-SIM-002** | MEDIUM | `MasterFullSchoolQaSeeder.php` | Assignment Creation | **FIXED** |
| **BUG-SIM-003** | MEDIUM | `MasterFullSchoolQaSeeder.php` | Submission Creation | **FIXED** |
| **BUG-SIM-004** | MEDIUM | `MasterFullSchoolQaSeeder.php` | Transfers & Expenses | **FIXED** |

---

## 4. Defect Repair Verdict

**STATUS: ALL DEFECTS RESOLVED AND VERIFIED**
