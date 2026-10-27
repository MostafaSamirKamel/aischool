# AI School — Complete Feature & Architecture Inventory

**Project**: AI School Management System  
**Framework**: Laravel 11.45.2 | PHP 8.3  
**Admin Engine**: Filament v3.2.140 (Filament Panels, Tables, Forms, Actions, Notifications, Widgets)  
**Real-time Layer**: Livewire v3.6.4  
**Date**: October 2026  
**Auditor**: Principal QA Automation & Architecture Engineer  
**Status**: Completely Audited & Validated

---

## 1. Executive System Overview

AI School is an enterprise-grade multi-tenant educational and financial management system designed for STEM, coding, robotics, and artificial intelligence academies. The platform operates across 5 discrete, role-scoped web panels, complemented by a RESTful mobile API and automated background cron workers.

```
                          ┌─────────────────────────┐
                          │    AI SCHOOL SYSTEM     │
                          └────────────┬────────────┘
                                       │
     ┌──────────────┬──────────────────┼─────────────────┬──────────────┐
     │              │                  │                 │              │
┌────┴─────┐  ┌─────┴──────┐    ┌──────┴──────┐    ┌─────┴────┐   ┌─────┴────┐
│  ADMIN   │  │ INSTRUCTOR │    │  SUPERVISOR │    │ STUDENT  │   │  PARENT  │
│  PANEL   │  │   PANEL    │    │    PANEL    │    │  PANEL   │   │  PANEL   │
│  /admin  │  │/instructor │    │ /supervisor │    │ /student │   │  /parent │
└──────────┘  └────────────┘    └─────────────┘    └──────────┘   └──────────┘
```

---

## 2. Panel Inventory

### 2.1 Admin Panel (`/admin`)
- **Access Guard**: `web` (Spatie Permission: `super_admin`, `admin`, `branch_manager`, `secretary`, `accountant`)
- **Multi-Tenancy**: Scoped via `tenant_id` session filter and `BelongsToBranch` global query scopes. Super Admin possesses cross-tenant override capability (`Gate::before`).
- **Core Resources**:
  - `UserResource`: Full CRUD, role assignment, password resets, active/inactive status enforcement.
  - `StudentResource`: Multi-step registration, parent linking, national ID validation, academic history.
  - `ParentResource`: Family profiles, linked children management, contact details.
  - `InstructorResource`: Instructor onboarding, hourly rate, assigned tracks, salary calculation logs.
  - `SupervisorResource`: Academic oversight staff, employee records, quality tracking.
  - `TrackResource` & `CourseResource`: Curriculum hierarchies, track dependencies, session counts, pricing defaults.
  - `GroupResource`: Class batches, scheduling days/times, capacity limits, instructor assignments.
  - `EnrollmentResource`: Subscriptions, multi-course enrollment, discount allocation, installment scheduling, vault assignment.
  - `PaymentResource`: Financial receipting, installment collection, vault association, automated balance recalculation.
  - `InstallmentResource`: Due date scheduling, overdue tracking, payment allocations.
  - `VaultResource`: Cash safe and bank account registers, opening balance, transfers, real-time ledger balance.
  - `RevenueResource` & `ExpenseResource`: Categorized operating cash inflows and outflows.
  - `StudentFollowUpResource`: Academic risk detection, repeated absence cases, declining grade tickets.
  - `LearningResourceResource`: Centralized educational material directory.
  - `ExamResource`: Exam scheduling, question banks, grading configurations.
  - `CertificateResource`: Course completion certificates, eligibility checks, Arabic PDF generation.
  - `BranchResource`: Multi-branch tenant configuration, branding, domains.
  - `SettingResource`: System flags, currency settings, SMS/WhatsApp gateway configurations.

### 2.2 Instructor Panel (`/instructor`)
- **Access Guard**: `web` (Spatie role: `instructor`)
- **Assigned Resources & Pages**:
  - `InstructorDashboard`: Upcoming sessions today, active groups count, student count, pending submissions counter.
  - `InstructorGroupsResource`: Assigned groups only (strictly isolated by `instructor_id`).
  - `AttendanceSessionResource`: Create sessions, mark attendance (present, absent, late, excused), evaluation scores (commitment, focus, participation).
  - `LearningResources`: Upload PDF guides, project starter files, presentation slides, YouTube/external links scoped by course and group.
  - `Assignments`: Create homework tasks, set deadlines, attach assignment sheets, configure max marks.
  - `AssignmentSubmissions`: Grade student homework submissions, preview multi-image uploads, award marks, write qualitative feedback, toggle grade publication.
  - `StudentMessages`: Direct one-on-one private messaging to enrolled students in assigned groups.
  - `GroupMessageResource`: Broadcast announcements to entire groups.
  - `InstructorSalaries`: View earned wages, session payment logs, settlement history.

### 2.3 Supervisor Panel (`/supervisor`)
- **Access Guard**: `web` (Spatie role: `supervisor`)
- **Assigned Resources & Pages**:
  - `SupervisorDashboard`: Academic KPIs, attendance compliance percentages, flagged students.
  - `SupervisorGroups`: Overview of all active cohorts across assigned curriculum tracks.
  - `AttendanceMonitoring`: Audit instructor session timing, attendance punctuality, absence spikes.
  - `StudentFollowUpTasks`: Triage automated alerts for students with >=3 absences or low grades; assign action items.
  - `CurriculumProgress`: Track group session pacing vs scheduled course length.

### 2.4 Student Panel (`/student`)
- **Access Guard**: `web` (Spatie role: `student`)
- **Assigned Pages & Components**:
  - `StudentDashboard`: Enrolled courses carousel, overall progress bar, next scheduled session, recent grades.
  - `MyCourses`: Detailed syllabus, session roadmap, instructor profile.
  - `StudentLearningResources`: Downloadable lecture notes, worksheets, tutorial links (strictly scoped to enrolled groups).
  - `MyAssignments`: View active assignments, upload homework solutions (up to 5 images), review feedback, view grades (masked while in draft).
  - `MyAttendance`: Personal session attendance history, presence rate, punctuality records.
  - `GroupMessages`: Real-time announcements from instructors and school administration.
  - `MyFinancials`: Subscription pricing, paid receipts, upcoming installment deadlines.

### 2.5 Parent Portal (`/parent`)
- **Access Guard**: `web` (Spatie role: `parent`)
- **Assigned Pages & Components**:
  - `ParentDashboard`: Child selector widget, overview of children's academic performance.
  - `ParentStudentAccessService`: Strict authorization engine ensuring parents can only query their legally linked children.
  - `ChildSwitching`: Dynamic session-backed student switching with tampering prevention.
  - `ChildAttendance`: Live attendance updates, absence notifications.
  - `ChildGrades & Homework`: Published exam marks, assignment feedback.
  - `ChildFinancials`: Outstanding balance, installment due dates, paid receipt ledger.

---

## 3. Database Architecture & Model Map

The system relies on 42 relational tables with foreign keys and cascade protections:

| Model | Database Table | Key Responsibilities |
|---|---|---|
| `Tenant` | `tenants` | Multi-tenant organization boundaries and domain mapping |
| `User` | `users` | Unified authentication, spatie roles/permissions, status flag |
| `Student` | `students` | Student demographic data, national ID, academic code |
| `StudentParent` | `student_parents` | Parent profile, emergency contacts, relationship types |
| `Instructor` | `instructors` | Instructor credentials, hourly compensation rate |
| `Supervisor` | `supervisors` | Quality supervisor employee profile |
| `Track` | `tracks` | Educational tracks (e.g. AI, Robotics, Web Development) |
| `Course` | `courses` | Specific courses with defined total sessions and default pricing |
| `Group` | `groups` | Class batches assigned to course, instructor, room, time |
| `Enrollment` | `enrollments` | Subscriptions, financial commitments, payment plans |
| `EnrollmentCourse`| `enrollment_courses` | Pivot tracking individual course progress within an enrollment |
| `EnrollmentCancellation` | `enrollment_cancellations` | Permanent audit record of subscription cancellation & settlement |
| `Payment` | `payments` | Cash/bank transaction records linked to enrollments |
| `Installment` | `installments` | Scheduled future payments with status and overdue tracking |
| `Vault` | `vaults` | Financial safes and bank accounts tracking current balance |
| `VaultTransfer` | `vault_transfers` | Inter-vault funds transfers with double-entry safety |
| `Revenue` | `revenues` | Miscellaneous operating income (books, kits, fees) |
| `Expense` | `expenses` | Operating expenditures (rent, utilities, hardware) |
| `SalaryPayment` | `salary_payments` | Instructor and employee payroll disbursements |
| `AttendanceSession`| `attendance_sessions` | Physical/virtual session log for a group |
| `AttendanceRecord` | `attendance_records` | Individual student presence, late status, and rubrics |
| `LearningResource` | `learning_resources` | Course documents, attachments, external video URLs |
| `Assignment` | `assignments` | Homework briefs, instructions, deadlines, max score |
| `AssignmentSubmission` | `assignment_submissions` | Multi-image submissions, grade, draft/published status |
| `Exam` | `exams` | Formal assessments, question sets, time limits |
| `Grade` | `grades` | Numerical score, letter grade, percentage calculation |
| `StudentMessage` | `student_messages` | 1-to-1 secure instructor-student messaging |
| `GroupMessage` | `group_messages` | Cohort-wide broadcast announcements |
| `StudentFollowUpTask` | `student_follow_up_tasks` | Automated academic intervention workflows |
| `Certificate` | `certificates` | Generated graduation certificates with verification codes |

---

## 4. API Endpoints Inventory

- **Base URL**: `/api/v1`
- **Documentation**: Generated via Scramble at `/docs/api`
- **Key Routes**:
  - `POST /api/v1/auth/login`: Student/Parent Sanctum token authentication.
  - `GET /api/v1/student/portal`: Full student payload (enrolled courses, attendance percentage, pending homework).
  - `GET /api/v1/student/courses`: Enrolled course roster with completed session counters.
  - `GET /api/v1/student/resources`: Access-controlled downloadable files.
  - `GET /api/v1/student/assignments`: Group-scoped homework list.
  - `POST /api/v1/student/assignments/{id}/submit`: Multi-image solution upload endpoint.
  - `GET /api/v1/student/exams`: Scheduled and active exams.
  - `POST /api/v1/student/exams/{id}/submit`: Automated answer processing.
  - `GET /api/v1/student/financials`: Transparent installment schedule and payment history.

---

## 5. Security & Isolation Matrix

1. **Multi-Tenancy**: All queries for non-super-admin users are strictly constrained by `tenant_id`. Cross-tenant record queries return HTTP 403 or empty sets.
2. **Role Gate Policy**: `Gate::before` allows super admins full access while ordinary staff, branch managers, and instructors must possess explicit Spatie permissions.
3. **Draft Evaluation Masking**: Homework grades marked as `is_published = false` are never disclosed to students via API or UI until formally released.
4. **Group-Level Data Scoping**: Audited and fixed query leaks in `MyAssignments.php`, ensuring students only see materials and assignments belonging to their active groups.
