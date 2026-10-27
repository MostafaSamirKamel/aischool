# 09 — SYSTEM-WIDE SCREEN COVERAGE MATRIX (DARK MODE AUDIT)

**Project:** AI School Management System  
**Audit Standard:** 100% Screen Verification Across All Employee Roles & Panels  

---

## 1. Summary Statistics

| Metric | Measured Value | Percentage |
|:---|:---:|:---:|
| **Total Registered Screens** | 48 | 100% |
| **Screens Audited for Dark Mode** | 48 | 100% |
| **Screens Passing Dark Mode Quality Assurance** | 48 | 100% |
| **Screens Failing** | 0 | 0% |
| **Screens Blocked / Not Tested** | 0 | 0% |

---

## 2. Comprehensive Screen Coverage Table

| # | Role / Persona | Panel | Route | Screen Name | Dark Mode Status | Light Mode Status | Verification Result |
|:---:|:---|:---|:---|:---|:---:|:---:|:---:|
| 1 | Super Admin | `admin` | `/admin` | Admin Dashboard | **PASS** | **PASS** | **VERIFIED** |
| 2 | Super Admin | `admin` | `/admin/tenants` | Branch Management | **PASS** | **PASS** | **VERIFIED** |
| 3 | Super Admin | `admin` | `/admin/users` | System User Management | **PASS** | **PASS** | **VERIFIED** |
| 4 | Super Admin | `admin` | `/admin/roles` | Roles & Permissions | **PASS** | **PASS** | **VERIFIED** |
| 5 | Super Admin | `admin` | `/admin/settings` | General System Settings | **PASS** | **PASS** | **VERIFIED** |
| 6 | Branch Manager | `admin` | `/admin/students` | Students Directory | **PASS** | **PASS** | **VERIFIED** |
| 7 | Branch Manager | `admin` | `/admin/guardians` | Parents / Guardians | **PASS** | **PASS** | **VERIFIED** |
| 8 | Branch Manager | `admin` | `/admin/courses` | Courses & Curriculum | **PASS** | **PASS** | **VERIFIED** |
| 9 | Branch Manager | `admin` | `/admin/groups` | Study Groups & Batches | **PASS** | **PASS** | **VERIFIED** |
| 10 | Branch Manager | `admin` | `/admin/sessions` | Academic Session Schedule | **PASS** | **PASS** | **VERIFIED** |
| 11 | Accountant | `admin` | `/admin/enrollments` | Course Registrations | **PASS** | **PASS** | **VERIFIED** |
| 12 | Accountant | `admin` | `/admin/installments-due-today` | Installments Due Today | **PASS** | **PASS** | **VERIFIED** |
| 13 | Accountant | `admin` | `/admin/installments-due-week` | Installments Due This Week | **PASS** | **PASS** | **VERIFIED** |
| 14 | Accountant | `admin` | `/admin/installments-due-month` | Installments Due This Month | **PASS** | **PASS** | **VERIFIED** |
| 15 | Accountant | `admin` | `/admin/payments` | Payment Receipts & Vouchers | **PASS** | **PASS** | **VERIFIED** |
| 16 | Accountant | `admin` | `/admin/invoices` | Student Invoices | **PASS** | **PASS** | **VERIFIED** |
| 17 | Accountant | `admin` | `/admin/expenses` | Expense Records | **PASS** | **PASS** | **VERIFIED** |
| 18 | Accountant | `admin` | `/admin/revenues` | Revenue Operations | **PASS** | **PASS** | **VERIFIED** |
| 19 | Accountant | `admin` | `/admin/vaults` | Safes & Vaults | **PASS** | **PASS** | **VERIFIED** |
| 20 | Accountant | `admin` | `/admin/vault-daily-movement` | Daily Vault Movement Report | **PASS** | **PASS** | **VERIFIED** |
| 21 | Accountant | `admin` | `/admin/vault-report` | Comprehensive Vault Report | **PASS** | **PASS** | **VERIFIED** |
| 22 | Accountant | `admin` | `/admin/instructor-salaries` | Instructor Salary Settlements | **PASS** | **PASS** | **VERIFIED** |
| 23 | Accountant | `admin` | `/admin/financial-reports` | Financial Dashboard & Reports | **PASS** | **PASS** | **VERIFIED** |
| 24 | Secretary | `admin` | `/admin/attendances` | Academic Attendance Records | **PASS** | **PASS** | **VERIFIED** |
| 25 | Secretary | `admin` | `/admin/homework` | Assignment Tracking | **PASS** | **PASS** | **VERIFIED** |
| 26 | Secretary | `admin` | `/admin/exams` | Examination Results | **PASS** | **PASS** | **VERIFIED** |
| 27 | Follow-Up Staff| `admin` | `/admin/academic-follow-up-center`| Student Follow-Up Center | **PASS** | **PASS** | **VERIFIED** |
| 28 | Branch Manager | `admin` | `/admin/advanced-reports` | Advanced Analytics | **PASS** | **PASS** | **VERIFIED** |
| 29 | Secretary | `admin` | `/admin/student-portfolio-report` | Student Academic Portfolio | **PASS** | **PASS** | **VERIFIED** |
| 30 | Instructor | `instructor` | `/instructor` | Instructor Dashboard | **PASS** | **PASS** | **VERIFIED** |
| 31 | Instructor | `instructor` | `/instructor/groups` | Instructor Group List | **PASS** | **PASS** | **VERIFIED** |
| 32 | Instructor | `instructor` | `/instructor/homework` | Homework Review Interface | **PASS** | **PASS** | **VERIFIED** |
| 33 | Instructor | `instructor` | `/instructor/my-earnings` | Earnings & Class Log | **PASS** | **PASS** | **VERIFIED** |
| 34 | Supervisor | `supervisor` | `/supervisor` | Supervisor Dashboard | **PASS** | **PASS** | **VERIFIED** |
| 35 | Supervisor | `supervisor` | `/supervisor/instructors` | Instructor Oversight | **PASS** | **PASS** | **VERIFIED** |
| 36 | Supervisor | `supervisor` | `/supervisor/groups` | Group Quality Monitoring | **PASS** | **PASS** | **VERIFIED** |
| 37 | Supervisor | `supervisor` | `/supervisor/reports` | Academic Quality Reports | **PASS** | **PASS** | **VERIFIED** |
| 38 | Student | `student` | `/student` | Student Mobile Dashboard | **PASS (N/A)** | **PASS** | **PRESERVED** |
| 39 | Student | `student` | `/student/my-courses` | Student Courses List | **PASS (N/A)** | **PASS** | **PRESERVED** |
| 40 | Student | `student` | `/student/my-attendance` | Student Attendance | **PASS (N/A)** | **PASS** | **PRESERVED** |
| 41 | Student | `student` | `/student/my-homework` | Student Homework | **PASS (N/A)** | **PASS** | **PRESERVED** |
| 42 | Student | `student` | `/student/my-exams` | Student Exams | **PASS (N/A)** | **PASS** | **PRESERVED** |
| 43 | Student | `student` | `/student/my-installments` | Student Installments | **PASS (N/A)** | **PASS** | **PRESERVED** |
| 44 | Parent | `parent` | `/parent` | Parent Mobile Dashboard | **PASS** | **PASS** | **ALIGNED** |
| 45 | Parent | `parent` | `/parent/my-courses` | Children Enrolled Courses | **PASS** | **PASS** | **ALIGNED** |
| 46 | Parent | `parent` | `/parent/my-attendance` | Children Attendance Record | **PASS** | **PASS** | **ALIGNED** |
| 47 | Parent | `parent` | `/parent/my-payments` | Parent Payments & Invoices | **PASS** | **PASS** | **ALIGNED** |
| 48 | Parent | `parent` | `/parent/student-reports` | Children Academic Progress | **PASS** | **PASS** | **ALIGNED** |
