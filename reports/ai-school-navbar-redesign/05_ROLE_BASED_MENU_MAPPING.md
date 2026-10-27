# AI SCHOOL — ROLE-BASED DYNAMIC MENU MAPPING

**Date:** October 26, 2026  
**Scope:** Verification that each employee role sees only authorized navigation categories and links.

---

## 1. DYNAMIC AUTHORIZATION PRINCIPLE

The new Top Navbar never relies on hardcoded menu displays or client-side visual masking alone:
1. `EmployeeTopNavbar` consumes `$navigation = Filament::getNavigation()`.
2. Filament generates `$navigation` by evaluating Spatie/Shield roles and `canAccess()` policies on every Resource and Page.
3. If an employee lacks permission for a resource, it is absent from `$navigation`.
4. If all items in a Mega Dropdown category are absent for a user, that category button is **completely removed** from the navbar.
5. Direct unauthorized URL requests are independently intercepted by Laravel/Filament route middleware returning HTTP 403.

---

## 2. ROLE NAVIGATION PROFILES

### 2.1 Super Admin (`super_admin`)
- **Dashboard:** Unrestricted system dashboard.
- **Navbar Categories:** All 5 categories visible (الطلاب والتسجيل، الشؤون الأكاديمية، الإدارة المالية، التقارير والإحصائيات، إدارة النظام).
- **Access Level:** 100% of system modules and tenant switcher.

### 2.2 Branch Manager (`branch_manager`)
- **Dashboard:** Branch-scoped operational metrics.
- **Navbar Categories:** Students, Academic, Finance, Reports, Human Resources (within branch delegation).
- **Hidden Items:** Platform-level tenant switcher, core system role definition editing.

### 2.3 Secretary (`secretary`)
- **Dashboard:** Daily registration and student attendance.
- **Visible Categories:**
  - `الطلاب والتسجيل` (Enrollments, Attendance, Barcode scanning, Messages, Daily follow-up).
  - `الشؤون الأكاديمية` (Courses, Groups, Session schedules).
- **Hidden Categories:** Financial vaults, salaries, system settings, shield roles.

### 2.4 Accountant (`accountant`)
- **Dashboard:** Cash flow, revenues, expenses, pending installments.
- **Visible Categories:**
  - `الإدارة المالية` (Payments, Vaults, Vault transfers, Revenues, Expenses, Salaries, Installments).
  - `التقارير والإحصائيات` (Vault reports, course financial reports, overdue installments).
- **Hidden Categories:** Course creation, exam editing, employee user management.

### 2.5 Instructor (`instructor`)
- **Dashboard:** Assigned courses, groups, upcoming sessions, pending homework.
- **Visible Categories:**
  - `التدريس والدرجات` (Unified Academic Center, Homework review, Exams, Schedules).
  - `المجموعات والطلاب` (My groups, Attendance, Resources, Messaging).
  - `المتابعة والتقارير` (Instructor reports, Payments, Supervisory directives).
- **Hidden Categories:** All administrative and financial panels of other instructors.

### 2.6 Educational Supervisor (`supervisor`)
- **Dashboard:** Educational supervision metrics and alert center.
- **Visible Categories:**
  - `الإشراف والمجموعات` (Assigned groups, student rosters, attendance).
  - `الجودة التعليمية والتوجيه` (Teaching quality evaluations, directives, instructor profiles, alerts).
