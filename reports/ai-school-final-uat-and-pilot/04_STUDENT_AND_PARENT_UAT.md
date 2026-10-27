# AI SCHOOL — STUDENT AND PARENT PORTAL ACCEPTANCE TESTING
**Document ID:** `04_STUDENT_AND_PARENT_UAT.md`  
**Execution Date:** 2026-10-25  
**Audit Purpose:** End-to-End Validation of Student and Guardian Portals  
**Tested Portals:** `/student` and `/parent`  

---

## 1. Executive Summary

This report covers empirical User Acceptance Testing for Students and Parents, validating that enrolled learners and their guardians can interact with courses, assignments, exams, grades, attendance, and financial history without cross-account or cross-tenant data leakage.

---

## 2. Student Portal Testing (`student@aischool.test`)

| Test ID | Operational Task | Executed Action | Observed Result | Security / Data Isolation | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **STU-01** | **Authentication** | Login via `/student/login` | Dashboard opens with student name | Tenant and user session established | **PASS** |
| **STU-02** | **Course Roster** | View "My Courses" | Displays active enrollments only | Excludes canceled or unassigned courses | **PASS** |
| **STU-03** | **Class Schedule** | View Group Timetable | Shows session dates, times, rooms | Scoped strictly to student's assigned group | **PASS** |
| **STU-04** | **Homework Access** | View Pending Assignments | Homework rubric & attachments visible | Unpublished drafts remain hidden | **PASS** |
| **STU-05** | **Homework Submit** | Upload 2 project images | Submission saved with timestamps | File paths stored in `images` array | **PASS** |
| **STU-06** | **Exam Attempt** | Take online exam & submit | Attempt recorded; timer enforced | Auto-grading calculated score accurately | **PASS** |
| **STU-07** | **Grade Visibility** | View Academic Results | Displays published score (88.5%) | Unpublished exam grades remain concealed | **PASS** |
| **STU-08** | **Financial View** | View Installments & Receipts | Displays paid receipts and balances | Read-only; cannot modify accounting | **PASS** |

---

## 3. Parent Portal Testing (`parent@aischool.test`)

| Test ID | Operational Task | Executed Action | Observed Result | Security / Data Isolation | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **PAR-01** | **Authentication** | Login via `/parent/login` | Dashboard displays linked children | Authenticated into guardian scope | **PASS** |
| **PAR-02** | **Multi-Child Switch** | Switch between Child 1 & 2 | Context switches data seamlessly | Verified via `ParentPortalMultiChildTest` | **PASS** |
| **PAR-03** | **Attendance Record** | Review Attendance History | Present, absent, and excused sessions | Real attendance data; zero mock data | **PASS** |
| **PAR-04** | **Published Grades** | Review Child Exam Reports | Grade card displays score & percent | Only published grades visible | **PASS** |
| **PAR-05** | **Follow-up Notes** | View Academic Notes | Displays contact notes from staff | Internal staff notes concealed | **PASS** |
| **PAR-06** | **Payment History** | View Child Receipt History | Displays official receipt codes | Strict isolation; cannot view other parents | **PASS** |
| **PAR-07** | **Unauthorized Access** | Direct URL to stranger student | HTTP 403 Forbidden / Not Found | Strict policy enforces `student_parents` link | **PASS** |

---

## 4. Verification Verdict

**STATUS: STUDENT & PARENT USER ACCEPTANCE FULLY VALIDATED (PASS)**
