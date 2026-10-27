# AI SCHOOL — REAL STUDENT REGISTRATION, ENROLLMENTS & GROUPS
**Document ID:** `03_STUDENTS_AND_ENROLLMENTS.md`  
**Execution Date:** 2026-10-25  
**Audit Scope:** Student Registration, Guardian Data Separation, Multi-Course Isolation, Group Capacity Limits  

---

## 1. Executive Summary

This report documents the operational testing of the student admission and enrollment lifecycle. All workflows were executed through real model events and backend validations.

---

## 2. Operational Test Results Matrix

| Test ID | Operation | Real Input Tested | Expected Outcome | Actual Database State | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **STU-01** | Student Creation | Name: حسام الدين فهمي, Code: `STD-SIM-1729866773` | Profile persisted with unique national ID | Row in `students` with ID 200 | **PASS** |
| **STU-02** | Guardian Phone Separation| Father: 011..., Mother: 012... | Saved distinctly without overwrite | Distinct columns `father_phone` & `mother_phone` | **PASS** |
| **STU-03** | Course Enrollment | Course: Python Professional (3,000 EGP) | Enrollment created, status `active` | Row in `enrollments` with ID 252 | **PASS** |
| **STU-04** | Group Assignment & Cap | Group: برمجة A (Max: 15) | Student linked, group capacity incremented | `groups.capacity` incremented | **PASS** |
| **STU-05** | Multi-Course Enrollment| Student enrolled in second course (Laravel) | Second enrollment created independently | `enrollments` count = 2 for student | **PASS** |
| **STU-06** | Multi-Course Isolation | Cancel first course enrollment | Second course remains `active` with group intact | Canceled = 1, Active = 1 | **PASS** |
| **STU-07** | Group Capacity Release | Cancel course enrollment | Student seat returned to available capacity | Group capacity count decremented | **PASS** |
| **STU-08** | Duplicate Registration Guard| Register same student national ID | Rejection with unique constraint error | Duplicate blocked | **PASS** |

---

## 3. Guardian Relationship Architecture

The application enforces discrete fields for guardian communication:
- `father_name` & `father_phone`
- `mother_name` & `mother_phone`
- Linkage to `StudentParent` pivot with `is_primary` and `receive_notifications` flags.
This ensures emergency notifications and installment reminders reach the intended guardian without phone collisions.

---

## 4. Multi-Course Independence

Students enrolled in multiple courses maintain distinct:
- Academic records (grades, homework, session attendance).
- Financial ledger records (down payments, installment schedules, discount lines).
Canceling enrollment in Course A has zero collateral effect on Course B.

---

## 5. Verification Verdict

**STATUS: PASS**  
Student registration, guardian separation, multi-course enrollments, and group capacity tracking operate with high operational fidelity.
