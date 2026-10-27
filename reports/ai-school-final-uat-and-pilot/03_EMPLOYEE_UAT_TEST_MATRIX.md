# AI SCHOOL — REAL EMPLOYEE USER ACCEPTANCE TESTING (UAT) MATRIX
**Document ID:** `03_EMPLOYEE_UAT_TEST_MATRIX.md`  
**Execution Date:** 2026-10-25  
**Audit Purpose:** Role-Based Workflow Validation for School Staff  
**Tested Portals:** `/admin`, `/instructor`, `/supervisor`  

---

## 1. Executive Summary

User Acceptance Testing was performed using distinct authorized test accounts representing every operational school role. All tests were executed against authentic backend business logic and database constraints.

---

## 2. Role A: Registration Employee / Secretary (`secretary.main@admin.com`)

| Step ID | Operation Executed | Input Data / Identifiers | Expected Outcome | Actual Database State | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **SEC-01** | Student Registration | Name, Gender, Birthdate, Dual Phones | Unique `student_code` generated; `user_id` linked | `students` row created with code `STD-UAT-...` | **PASS** |
| **SEC-02** | Parent Linking | Guardian Name, Relationship: Father | `student_parents` record created | Linked with `relation = 'father'`, `is_primary = true` | **PASS** |
| **SEC-03** | Student Search | Search by Code & Name | Instant filtered record returned | Returned matching student record | **PASS** |
| **SEC-04** | Edit Permitted Info | Update phone numbers | Changes saved without side effects | `phone_2` updated in database | **PASS** |
| **SEC-05** | Course Enrollment | Select Python Course & Plan | Enrollment generated with total fee | `enrollments` row created with total amount | **PASS** |
| **SEC-06** | Group Assignment | Select Group 1 (Cairo Branch) | Group assigned; capacity checked | `enrollment_courses` row created with `group_id` | **PASS** |
| **SEC-07** | View Obligations | Inspect Installment Schedule | Correct installment rows visible | 3 installments displayed (1,000 EGP each) | **PASS** |
| **SEC-08** | Review History | View Student Profile Tabs | Complete chronological timeline | Enrollments and group rosters visible | **PASS** |
| **SEC-09** | Cancellation | Mark Enrollment Cancelled | Status moved to cancelled | `status = 'cancelled'`, student detached from active roster | **PASS** |

---

## 3. Role B: Accountant (`accountant.cairo@aischool.test`)

| Step ID | Operation Executed | Input Data / Identifiers | Expected Outcome | Actual Database State | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **ACC-01** | Open Payment Portal | Route: `/admin/payments/create` | Canonical Add Payment form loads | Form loaded with mandatory vault selector | **PASS** |
| **ACC-02** | Student Lookup | Search by Student Code | Student and pending debts populated | Active enrollments and pending installments visible | **PASS** |
| **ACC-03** | Down Payment Posting | Amount: 1,000 EGP, Vault: `كاش` | Payment receipt created, vault incremented | `payments` created; vault #1 increased by 1,000 | **PASS** |
| **ACC-04** | Installment Collection | Amount: 500 EGP on Installment 2 | Installment updated to `partial` | Installment 2 marked `partial` (500/1000) | **PASS** |
| **ACC-05** | Operational Expense | Amount: 1,200 EGP, Category: Tools | Expense recorded; vault deducted | `expenses` row created; deducted from selected vault | **PASS** |
| **ACC-06** | Salary Disbursement | Amount: 4,500 EGP, Instructor: #1 | Salary record `paid`; vault deducted | `salary_payments` row created with `status = 'paid'` | **PASS** |
| **ACC-07** | Vault Transfer | 5,000 EGP from Cash to Vodafone | Outflow/Inflow posted; zero school revenue | `vault_transfers` completed; no duplicate revenue | **PASS** |
| **ACC-08** | Approved Refund | Amount: 500 EGP via Vault #1 | Cash disbursed; duplicate blocked | `refunded_amount = 500`, status `completed` | **PASS** |
| **ACC-09** | EOD Vault Reconcile | All 6 Vaults Reconciled | Calculated matches actual (0.00 Diff) | 6/6 vaults reconciled with zero variance | **PASS** |

---

## 4. Role C: Instructor (`instructor.main@admin.com`)

| Step ID | Operation Executed | Input Data / Identifiers | Expected Outcome | Actual Database State | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **INS-01** | View Assigned Groups | Instructor Portal Dashboard | Shows authorized branch groups only | Displays Cairo Branch assigned groups | **PASS** |
| **INS-02** | Session Attendance | Session #1, Mark Students | Attendance saved with discrete statuses | `attendance_records` rows: present/absent | **PASS** |
| **INS-03** | Publish Homework | Title, Guidelines, Due Date | Assignment available to group students | `assignments` row created and published | **PASS** |
| **INS-04** | Grade Submission | Score: 95, Feedback: 'ممتاز' | Grade recorded and visible to student | `score = 95`, `instructor_feedback` updated | **PASS** |
| **INS-05** | Exam Results Publish | Grade: 88.5%, Is Published: 1 | Grade published for student and parent | `grades` row published with percentage 88.5% | **PASS** |

---

## 5. Role D: Educational Supervisor (`supervisor.main@admin.com`)

| Step ID | Operation Executed | Input Data / Identifiers | Expected Outcome | Actual Database State | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **SUP-01** | Inspect Branch Groups | Supervisor Portal | Scoped to assigned groups | Groups loaded with academic rosters | **PASS** |
| **SUP-02** | Log Observation | Field Visit & Instructor Rating | Observation logged with rubric notes | `supervisory_observations` row created | **PASS** |
| **SUP-03** | Monthly Report Draft | Period: `2026-10`, Metrics Snapshot | Report saved in draft state | `supervision_reports` created with JSON metrics | **PASS** |
| **SUP-04** | Report Publication | Supervisor Approval | Status changed to `published` | `status = 'published'`, accessible to management | **PASS** |
| **SUP-05** | Academic Follow-up | Case Triage: `جديدة` -> `تم التواصل` | Contact logged with notes | `student_follow_up_contacts` logged chronologically | **PASS** |

---

## 6. Staff Role Verification Verdict

**STATUS: ALL EMPLOYEE ROLES TESTED AND APPROVED (PASS)**
