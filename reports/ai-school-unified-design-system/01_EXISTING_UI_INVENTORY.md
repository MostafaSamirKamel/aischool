# 01 — EXISTING UI INVENTORY AND STRUCTURE

**Project:** AI School Management System  
**Design System Audit:** UI/UX Inventory Across All Panels  
**Date:** October 21, 2026  
**Auditor:** Senior Product Designer & Enterprise SaaS UI/UX Architect  

---

## 1. Panels Discovered

The application operates five distinct Filament panels:

| Panel ID | Route Path | Target Audience | Primary Visual Identity | Layout Engine |
|:---|:---|:---|:---|:---|
| **`admin`** | `/admin` | Super Admin, Admin, Branch Managers, Secretary, Accountant, Follow-Up Officers | Unified Enterprise SaaS Design System (Dark Navy Sidebar + Tajawal + Light Canvas) | Filament v3 Multi-Resource |
| **`instructor`** | `/instructor` | Course Instructors, Teachers | Unified Enterprise SaaS Design System | Filament v3 Multi-Resource |
| **`supervisor`** | `/supervisor` | Educational Supervisors | Unified Enterprise SaaS Design System | Filament v3 Multi-Resource |
| **`student`** | `/student` | Enrolled Students | Approved Mobile-App Visual Language (Fixed Bottom Bar, Cards, Left Drawer) | Custom Blade + Livewire + Filament |
| **`parent`** | `/parent` | Guardians and Parents | Aligned Mobile-App Visual Language (Child Switcher, Bottom Bar, Cards) | Custom Blade + Livewire + Filament |

---

## 2. Inventory of Employee-Facing Pages & Resources

### Admin Panel (`/admin`)
- **Core Administration:**
  - Dashboard (`/admin`)
  - Tenants / Branches Resource (`/admin/tenants`)
  - Users & Roles Resource (`/admin/users`, `/admin/shield/roles`)
  - Settings Page (`/admin/settings`)
  - Activity Logs (`/admin/activity-logs`)
- **Student & Enrollment Operations:**
  - Students Resource (`/admin/students`)
  - Parents Resource (`/admin/parents`)
  - Enrollments Resource (`/admin/enrollments`)
  - Courses Resource (`/admin/courses`)
  - Groups Resource (`/admin/groups`)
- **Financial Operations:**
  - Installments Resource (`/admin/installments`)
  - Installments Due Today (`/admin/installments-due-today`)
  - Installments Due This Week (`/admin/installments-due-week`)
  - Installments Due This Month (`/admin/installments-due-month`)
  - Payments Resource (`/admin/payments`)
  - Vaults Resource (`/admin/vaults`)
  - Vault Transfers (`/admin/vault-transfers`)
  - Revenues (`/admin/revenues`)
  - Expenses (`/admin/expenses`)
  - Salary Payments (`/admin/salary-payments`)
- **Academic Supervision & Follow-Up:**
  - Academic Follow-Up Center (`/admin/academic-follow-up-center`)
  - Attendance Sessions (`/admin/attendance-sessions`)
  - Instructors (`/admin/instructors`)
  - Supervisors (`/admin/supervisors`)

### Instructor Panel (`/instructor`)
- Instructor Dashboard (`/instructor`)
- Attendance Sessions Management (`/instructor/attendance-sessions`)
- Groups Management (`/instructor/groups`)
- Homework & Assignments (`/instructor/assignments`)
- Homework Review Center (`/instructor/homework-review-center`)
- Examinations Management (`/instructor/exams`)
- Exam Results Review (`/instructor/exam-results`)
- Instructor Reviews (`/instructor/instructor-reviews`)

### Supervisor Panel (`/supervisor`)
- Supervisor Dashboard & Calendar Widget (`/supervisor`)
- Supervision Groups Resource (`/supervisor/groups`)
- Supervised Students Resource (`/supervisor/students`)
- Attendance Sessions Review (`/supervisor/attendance-sessions`)
- Supervision Notes & Follow-Up (`/supervisor/instructor-supervision-notes`)

---

## 3. Inventory of Student & Parent Portals

### Student Portal (`/student`)
- Dashboard (`/student`) with Progress Cards & Quick Stats
- My Courses (`/student/my-courses`)
- My Assignments (`/student/my-assignments`)
- My Exams & Take Exam (`/student/my-exams`, `/student/take-exam`)
- My Attendance (`/student/my-attendance`)
- My Payments & Receipts (`/student/my-payments`)
- Learning Resources (`/student/student-learning-resources`)
- Academic Reports & Portfolio (`/student/student-reports`)
- Group Announcements (`/student/group-messages`)

### Parent Portal (`/parent`)
- Parent Dashboard (`/parent`) with ChildrenSelectorWidget
- Children Courses (`/parent/my-courses`)
- Child Attendance Tracking (`/parent/my-attendance`)
- Child Installments & Payments (`/parent/my-payments`)
- Comprehensive Academic Reports (`/parent/student-reports`)
- Learning Resources (`/parent/view-learning-resource`)
- Group Announcements & Messages (`/parent/group-messages`)
