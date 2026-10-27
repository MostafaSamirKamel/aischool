# 03 — TEST DATA MANIFEST & SYNTHETIC DATASET AUDIT
**Project:** AI School Management System  
**Dataset Type:** Synthetic Staged Production-Like Fixtures  
**Audit Date:** 2026-10-20  

---

## 1. Synthetic Dataset Target Compliance

The test school dataset was constructed to satisfy all minimum targets defined in Section 5 of the QA Master Specification without artificial overfilling or constraint violations.

| Entity / Scenario Category | Minimum Required Target | Actual Tested Count | Target Status |
|---|---:|---:|:---:|
| **Branches (Tenants)** | 2 | 2 | **MET** |
| **Students** | 60 | 682 | **EXCEEDED** |
| **Parents / Guardians** | 40 | 372 | **EXCEEDED** |
| **Instructors** | 6 | 19 | **EXCEEDED** |
| **Instructor Supervisors** | 2 | 4 | **EXCEEDED** |
| **Registration Employees** | 4 | 6 | **EXCEEDED** |
| **Accountants** | 2 | 3 | **EXCEEDED** |
| **Secretaries** | 2 | 4 | **EXCEEDED** |
| **Branch Managers** | 2 | 3 | **EXCEEDED** |
| **Super Admin** | 1 | 2 | **EXCEEDED** |
| **Courses** | 8 | 32 | **EXCEEDED** |
| **Groups** | 16 | 35 | **EXCEEDED** |
| **Classrooms** | 6 | 18 | **EXCEEDED** |
| **Vaults** | 6 | 6 | **MET** |
| **Session Events** | 60+ | 148 | **EXCEEDED** |
| **Attendance Events** | 150+ | 2,490+ | **EXCEEDED** |
| **Homework / Submission Events** | 60+ | 608 | **EXCEEDED** |
| **Exam Attempts** | 50+ | 603 | **EXCEEDED** |
| **Student Financial Transactions** | 100+ | 689 | **EXCEEDED** |
| **Salary / Expense Events** | 25+ | 42 | **EXCEEDED** |
| **Vault Transfers** | 12+ | 18 | **EXCEEDED** |
| **Group Transfer Scenarios** | 15+ | 40 (TR-001 to TR-040) | **EXCEEDED** |
| **Cancellation / Refund Scenarios** | 15+ | 21 (CAN-01 to CAN-21) | **EXCEEDED** |
| **Messaging Scenarios** | 30+ | 34 | **EXCEEDED** |
| **Negative Authorization Tests** | 40+ | 48 | **EXCEEDED** |

---

## 2. Branch & Facility Structure

### Branch 1: Main Nasr City Branch (`tenant_id = 1`)
- **Domain:** `nasrcity.aischool.test`
- **Classrooms:**
  - Lab 1 - Turing Room (Capacity: 15)
  - Lab 2 - Lovelace Room (Capacity: 15)
  - Hall A - Shannon Lecture Room (Capacity: 30)
- **Vaults:**
  - `Cash Vault - Nasr City` (Type: cash, Opening: 50,000.00 EGP)
  - `Vodafone Cash Safe - Nasr City` (Type: vodafone, Opening: 25,000.00 EGP)
  - `Instapay Safe - Nasr City` (Type: instapay, Opening: 75,000.00 EGP)

### Branch 2: Isolation & Load Testing Branch (`tenant_id = 2`)
- **Domain:** `west.aischool.test`
- **Classrooms:**
  - Lab 3 - Von Neumann Lab (Capacity: 20)
  - Lab 4 - Dijkstra Lab (Capacity: 20)
  - Hall B - Hopper Room (Capacity: 30)
- **Vaults:**
  - `Cash Vault - West` (Type: cash, Opening: 40,000.00 EGP)
  - `Vodafone Cash Safe - West` (Type: vodafone, Opening: 20,000.00 EGP)
  - `Instapay Safe - West` (Type: instapay, Opening: 60,000.00 EGP)

---

## 3. Course Catalog Baseline

| Course Code | Name | Track / Level | Price (EGP) | Sessions Count | Status |
|---|---|---|---:|---:|:---:|
| `CRS-PY-101` | Python Fundamentals | Computer Science L1 | 2,000.00 | 12 | Active |
| `CRS-PY-201` | Python Advanced & Algorithms | Computer Science L2 | 2,500.00 | 12 | Active |
| `CRS-SC-101` | Scratch Creative Coding | Junior Coding L1 | 1,200.00 | 12 | Active |
| `CRS-AI-101` | App Inventor Mobile Apps | Mobile Dev L1 | 1,500.00 | 12 | Active |
| `CRS-FE-101` | Web Front-End (HTML/CSS/JS) | Web Track L1 | 1,800.00 | 12 | Active |
| `CRS-ROB-101` | Robotics & Arduino Basics | Embedded L1 | 2,200.00 | 12 | Active |
| `CRS-DS-101` | Data Science for Teens | AI & Data L1 | 2,800.00 | 12 | Active |
| `CRS-ML-101` | Machine Learning Concepts | AI & Data L2 | 3,000.00 | 12 | Active |

---

## 4. Key Synthetic Test Accounts

| Role | Name | Email | Password | Assigned Branch |
|---|---|---|---|---|
| **Super Admin** | System Super Admin | `superadmin@aischool.test` | `Secret123!` | Global (Cross-branch bypass via Gate) |
| **Branch Manager** | Tarek Branch Manager | `bm.nasrcity@aischool.test` | `Secret123!` | Nasr City (`tenant_id=1`) |
| **Registration Staff** | Sarah Registration Officer | `sarah.reg@aischool.test` | `Secret123!` | Nasr City (`tenant_id=1`) |
| **Accountant** | Khaled Financial Auditor | `khaled.acc@aischool.test` | `Secret123!` | Nasr City (`tenant_id=1`) |
| **Instructor** | Eng. Amr Tarek | `amr.instructor@aischool.test` | `Secret123!` | Nasr City (`tenant_id=1`) |
| **Instructor** | Eng. Mona Salem | `mona.instructor@aischool.test` | `Secret123!` | Nasr City (`tenant_id=1`) |
| **Student** | Ahmed Mohamed | `ahmed.mohamed@aischool.test` | `Secret123!` | Nasr City (`tenant_id=1`) |
| **Parent** | Mohamed Hassan | `parent.mohamed@aischool.test` | `Secret123!` | Nasr City (`tenant_id=1`) |
