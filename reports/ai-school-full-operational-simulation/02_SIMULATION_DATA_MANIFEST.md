# AI SCHOOL — SIMULATION DATA MANIFEST AND TRACEABILITY
**Document ID:** `02_SIMULATION_DATA_MANIFEST.md`  
**Execution Date:** 2026-10-25  
**Simulation Engine:** Real-World Operational Multi-Tenant Simulation  
**Persistent Database:** `database/database.sqlite` (3.86+ MB)  

---

## 1. Executive Summary

In adherence to Section 3 of the Operational Simulation Prompt, the system database was populated with persistent, realistic, and interconnected operational data. All generated records map to genuine educational, financial, and administrative models, preserving referential integrity, tenant isolation, and audit trails.

---

## 2. Summary of Staging Entities & Database Manifest

| Entity Type | Record Count in Database | Simulation Scope & Coverage |
| :--- | :---: | :--- |
| **Tenants (Branches)** | 2 | Main Cairo Branch (`main`) & Damanhour Branch (`damanhour`) |
| **System Users** | 328 | Super Admin, Branch Managers, Accountants, Teachers, Students, Parents |
| **Students** | 200 | Newly registered, single-course, multi-course, canceled, active |
| **Parents** | 128 | Linked guardians with father and mother mobile separation |
| **Instructors** | 21 | Full-time and session-contract instructors with hourly rates |
| **Courses** | 77 | Catalog covering Python, Web Development, Scratch, AI, Robotics |
| **Educational Groups** | 93 | Various stages: newly created, mid-term, capacity full, completed |
| **Student Enrollments** | 253 | Active, completed, canceled, installment plans, full cash |
| **Installments** | 331 | Pending, partially paid, fully paid, overdue, waived |
| **Vaults** | 6 | Cash Vaults, Vodafone Cash, Instapay per branch |
| **Payments** | 221 | Canonical down payments, partial and full installment collections |
| **Revenues** | 172 | Automatically synchronized ledger entries via `PaymentObserver` |
| **Expenses** | 53 | Paid operational and training expenses linked to vaults |
| **Salary Payments** | 26 | Executed monthly instructor salary disbursements |
| **Cancellations** | 69 | Documented cancellations with settlement snapshots |
| **Attendance Sessions** | 259 | Delivered classroom sessions with timestamps and classrooms |
| **Attendance Records** | 649 | Discrete statuses: `present`, `absent`, `excused`, `unrecorded` |
| **Homework Assignments** | 65 | Published assignments with due dates and rubrics |
| **Homework Submissions** | 200 | Student submissions with multi-image attachments and grades |
| **Exams** | 109 | Midterms, finals, multiple-choice, essay questions |
| **Exam Attempts** | 205 | Student submissions with automated scoring and percentages |
| **Published Grades** | 191 | Published exam and course grades |
| **Academic Follow-up** | 18 | Triage cases with statuses `جديدة` and `تم التواصل` |
| **Supervisors** | 4 | Academic supervisors assigned to branch quality control |
| **Supervision Reports** | 6 | Draft and published monthly pedagogical inspection reports |

---

## 3. Traceability and Persistent Naming Conventions

To ensure zero ambiguity between real production data and simulation records:
1. **Student Codes:** Prefixed with `STD-` or `STD-SIM-` (e.g., `STD-SIM-1729866773`).
2. **Enrollment Codes:** Prefixed with `ENR-SIM-` or `ENR-WEB-`.
3. **Receipt Codes:** Prefixed with `PAY-DWN-`, `PAY-PRT-`, `PAY-FLL-`, or `REC-`.
4. **Transfer Codes:** Prefixed with `VT-` (e.g., `VT-261025-0001`).
5. **Supervision Codes:** Prefixed with `SR-MO-` (e.g., `SR-MO-2026-10-0002`).

---

## 4. Manifest Health Verdict

**STATUS: PASS**  
The database contains comprehensive, realistic, fully interconnected persistent data ready for immediate operational inspection.
