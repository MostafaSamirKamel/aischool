# AI SCHOOL — BUGS FOUND AND FIXED DURING FINAL UAT & PILOT AUDIT
**Document ID:** `16_BUGS_FOUND_AND_FIXED.md`  
**Execution Date:** 2026-10-25  
**Audit Purpose:** Comprehensive Technical Inventory of Detected Edge-Cases & Remedies  

---

## 1. Executive Summary

During the execution of the deep pre-production UAT operational script (`scratch/run_preproduction_uat_simulation.php`), specific schema constraints and model contract subtleties were empirically surfaced and resolved.

---

## 2. Itemized Defect Inventory

### Defect ID: BUG-UAT-001
- **Severity:** HIGH
- **Component:** `installments` Table NOT NULL Constraint
- **Root Cause:** Attempted installment creation without providing mandatory `installment_number` (1, 2, 3).
- **Remedy:** Passed explicit integer sequence numbers in installment generation routines.
- **Verification:** Installment creation passed cleanly across 3 installments.
- **Status:** **FIXED** ✅

---

### Defect ID: BUG-UAT-002
- **Severity:** HIGH
- **Component:** `Group` Model `students()` Relationship Type
- **Root Cause:** Script called `syncWithoutDetaching()` on `Group::students()`, which is a `HasManyThrough` relationship via `Enrollment`, not a direct `BelongsToMany` pivot.
- **Remedy:** Replaced calls with standard `EnrollmentCourse` persistence and used `Group::activeStudents()` for roster tracking.
- **Verification:** Dynamic group roster membership and de-registration on cancellation verified.
- **Status:** **FIXED** ✅

---

### Defect ID: BUG-UAT-003
- **Severity:** HIGH
- **Component:** `enrollments` Unique `(student_id, group_id)` Constraint
- **Root Cause:** Attempted enrolling the same student into the same group for two courses simultaneously.
- **Remedy:** Enforced domain rule: multi-course students must be assigned to distinct group IDs corresponding to each course.
- **Verification:** Second enrollment assigned to an independent group; both persisted cleanly.
- **Status:** **FIXED** ✅

---

### Defect ID: BUG-UAT-004
- **Severity:** MEDIUM
- **Component:** `enrollment_courses` Status Check Constraint
- **Root Cause:** Setting status to `cancelled` failed SQLite check constraint `('inactive', 'active', 'completed')`.
- **Remedy:** Aligned status update to valid enum value `'inactive'` upon enrollment cancellation.
- **Verification:** Course deactivated without database constraint errors.
- **Status:** **FIXED** ✅

---

### Defect ID: BUG-UAT-005
- **Severity:** MEDIUM
- **Component:** `installments` Status Check Constraint
- **Root Cause:** Updating installment to `'partially_paid'` failed check constraint `('pending', 'paid', 'overdue', 'partial', 'cancelled', 'waived')`.
- **Remedy:** Used approved enum value `'partial'`.
- **Verification:** Partial installment payment updated and persisted cleanly.
- **Status:** **FIXED** ✅

---

### Defect ID: BUG-UAT-006
- **Severity:** MEDIUM
- **Component:** `salary_payments` Status Check Constraint
- **Root Cause:** Setting salary status to `'completed'` failed check constraint `('pending', 'paid', 'cancelled')`.
- **Remedy:** Used approved enum value `'paid'`.
- **Verification:** Instructor salary disbursement executed and deducted from vault cleanly.
- **Status:** **FIXED** ✅

---

## 3. Defect Summary Table

| Defect ID | Severity | Affected Module | Root Cause | Fix Verification | Status |
| :--- | :---: | :--- | :--- | :--- | :---: |
| **BUG-UAT-001** | HIGH | Installments | Missing `installment_number` | Verified sequential insert | **FIXED** ✅ |
| **BUG-UAT-002** | HIGH | Groups & Students | HasManyThrough vs Pivot | Verified via `EnrollmentCourse` | **FIXED** ✅ |
| **BUG-UAT-003** | HIGH | Enrollments | Unique `(student, group)` | Enforced distinct group per course | **FIXED** ✅ |
| **BUG-UAT-004** | MEDIUM | EnrollmentCourses | Check constraint on status | Used `'inactive'` | **FIXED** ✅ |
| **BUG-UAT-005** | MEDIUM | Installments | Check constraint on status | Used `'partial'` | **FIXED** ✅ |
| **BUG-UAT-006** | MEDIUM | SalaryPayments | Check constraint on status | Used `'paid'` | **FIXED** ✅ |

---

## 4. Verification Verdict

**STATUS: ALL IDENTIFIED ISSUES RESOLVED AND CONFIRMED VIA UAT ENGINE**
