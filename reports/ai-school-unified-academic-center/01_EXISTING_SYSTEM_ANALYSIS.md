# 01 — EXISTING SYSTEM ANALYSIS & ARCHITECTURE REVIEW

## 1. Executive Overview
This report provides a comprehensive architectural and operational analysis of the AI School Management System's academic modules prior to and during the unification of the **Group Academic Center**.

The AI School system operates on:
- **Framework:** Laravel 12.x
- **Admin & Instructor Panels:** Filament 5.x with custom Livewire 3 components
- **Frontend / Student Portal:** Responsive PWA (`AI_School_Student_Frontend`) communicating with Laravel REST APIs
- **Database Architecture:** Multi-tenant relational schema (Branch/Tenant scoping via `tenant_id`)

## 2. Pre-Existing State vs. Required Unified Architecture

| Domain Concept | Pre-Existing Implementation | Identified Deficiencies | Unified Academic Center Solution |
| :--- | :--- | :--- | :--- |
| **Academic Interface** | Standalone homework review page, separate exam screens, disconnected submission views | Instructors had to switch between multiple decoupled pages to follow up on a single group's academic lifecycle | **Single Group Academic Center** with 4 organized tabs: الواجبات (Homework), الامتحانات (Exams), التسليمات (Submissions), النتائج (Results) |
| **Homework-Session Link** | Loose link via `session_schedule_id` or non-persisted numbering | Changes in session scheduling could break assignment links; unlinked homework lacked structured representation | Normalized `attendance_session_id` and `session_schedule_id` foreign keys with fallback to unlinked historical assignment section |
| **Homework Deadlines** | Soft expiration without student-specific exceptions | Inflexible deadline handling; inability to grant medical or emergency extensions to an individual student without changing group due date | Strict server-side closure on expiration, supported by `HomeworkDeadlineExtension` for group-wide or single-student reopening |
| **Homework Resubmission** | Directly overwrote original submissions or lost historical attempts | Inability to audit previous grades or trace student progress across multiple iterations | Independent `HomeworkResubmissionGrant` system; one-time atomic consumption; preserves all submission attempts and attachments |
| **Official Grade Rule** | Last submitted score was frequently treated as official | A student resubmitting for improvement could receive a lower score that downgraded their grade | **Highest Valid Score Rule:** Official grade is the highest among corrected eligible attempts (e.g., 6/10 -> 9/10 -> 7/10 => Official is 9/10) |
| **Homework Publication** | Manual secondary publishing step or inconsistent draft state | Delays in student and parent visibility | **Instant Publication on "تم التصحيح":** Corrected score publishes immediately, updating student and parent panels atomically |
| **Exam-Session Link** | Required sessions or inconsistent relations | Comprehensive exams covering entire courses were forced to invent a fake session | **Optional Session Association:** Exams can be linked to a specific session or remain comprehensive for the entire group |
| **Exam Question Protection** | Modifiable even during active student attempts | Editing a question or answer key while students were testing compromised attempt integrity | Strict immutability once attempts commence; assessment corrections require audit trails |
| **Exam Retake Grants** | Merged with homework permissions or unmonitored retakes | Uncontrolled exam retakes; overlapping active attempts | Independent `ExamRetakeGrant` governed by Branch Manager ceiling; atomic single-use grant consumption |
| **Official Exam Score** | Ambiguous aggregation when multiple attempts existed | Overlapping attempts confused student report cards | Highest score among completed eligible attempts is official; historical attempts preserved |
| **Exam Publication Rule** | Some modules auto-published exam scores prematurely | Students saw unmoderated scores before instructor review | **Exam Publication Decoupled:** Exam results obey independent `published_at` policy; grading an attempt does not prematurely release the exam |
| **Low-Grade Alerts** | Arbitrary or inconsistent thresholds | False alerts on passing grades (e.g. at 50%) | **Strict `< 50%` Threshold:** Exactly 50.0% is passing and does not alert; only `< 50.0%` triggers low-grade indicators |

## 3. Data Integrity & Authorization Boundaries
1. **Tenant & Branch Isolation:** All academic queries strictly enforce `tenant_id` scoping.
2. **Instructor Group Scope:** Instructors can only view and manage assignments, exams, and grades for groups where they are assigned (`instructor_id`).
3. **Audit History:** Sensitive modifications (grade corrections, retake grants, deadline extensions) record authorized employee ID, timestamp, and mandatory reason.
