# 01 — Project Architecture & System Audit

## 1. Executive Summary
The **AI School Management System** is an enterprise-grade school and educational center management platform built on modern Laravel, Filament V3/V4, Livewire, Blade, and Tailwind CSS. The system enforces strict multi-tenancy (multi-branch isolation), role-based access control (RBAC), end-to-end academic tracking, integrated double-entry style financial vaults, and a consolidated 14-section Reporting Center.

## 2. Technology Stack & Framework Inventory
- **Backend Framework:** Laravel 11.x / PHP 8.2+
- **Admin & Employee Panels:** Filament Admin Panel, Filament Instructor Panel, Filament Supervisor Panel, Filament Student Panel, Filament Parent Panel
- **Frontend / Dynamic Reactive Components:** Livewire 3.x, Alpine.js, Tailwind CSS with custom RTL and Dark/Light mode support
- **PDF Engine:** mPDF with custom Arabic typography (`Tajawal` / `Amiri`)
- **Database Engine:** SQLite (Staging & Testing) / MySQL / PostgreSQL ready
- **Authentication & RBAC:** Laravel Sanctum, Spatie Laravel-Permission with custom branch scoping
- **Safety Safeguards:** `MAIL_MAILER=log`, WhatsApp/SMS gateways disabled for staging execution

## 3. Panels & Routing Architecture
| Panel | Route Prefix | Primary Target Roles | Core Responsibilities |
|---|---|---|---|
| **Admin Panel** | `/admin` | Super Admin, Branch Manager, Accountant, Registration, Secretary | Full school operations, student enrollment, finance, payroll, settings |
| **Instructor Panel** | `/instructor` | Instructors | Session management, attendance recording, homework grading, exam management |
| **Supervisor Panel** | `/supervisor` | Educational Supervisors | Academic oversight, teacher evaluation, classroom observations, appeals |
| **Student Panel** | `/student` | Enrolled Students | Course schedule, attendance view, homework submissions, exam taking, results |
| **Parent Panel** | `/parent` | Parents / Guardians | Child academic tracking, attendance review, fee schedules, teacher notes |

## 4. Multi-Branch Isolation & Data Integrity
Every core entity (`students`, `enrollments`, `payments`, `vaults`, `courses`, `groups`, `attendance_sessions`, `exams`, `assignments`, `student_follow_up_tasks`, `supervision_reports`) implements the `BelongsToBranch` / tenant scoping trait. Queries automatically filter by `tenant_id` unless executed by a tenant-exempt Super Administrator.

## 5. System Health & Environment Status
- **Staging Database Snapshot:** Taken before testing (`database/database.sqlite.pre_full_ops_backup`)
- **Active Execution Run ID:** `[E2E-LIVE-74b6b]`
- **Background Dev Server:** Active on `http://127.0.0.1:8000`
- **Audit Result:** Architecture is robust, fully compliant with enterprise Laravel standards, and ready for end-to-end operational validation.
