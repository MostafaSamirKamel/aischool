# AI School — Test Accounts & Deterministic Dataset

**Test Suite Namespace**: `E2E_AI_SCHOOL_2026`  
**Execution Environment**: Dedicated Testing Database (In-Memory SQLite with Complete Foreign Key Schema)  
**Production Isolation**: Verified (Zero modifications to `database.sqlite`, zero real SMS/Email/Payment disbursements)

---

## 1. Multi-Tenant Fixtures

To ensure strict tenant isolation and verify cross-tenant access denial, two isolated tenant environments were established:

| Tenant Code | Tenant Name | Domain | Purpose |
|---|---|---|---|
| `TENANT-01` | `E2E_AI_SCHOOL_2026_PRIMARY` | `primary.aischool.test` | Primary operations, courses, enrollments, financials |
| `TENANT-02` | `E2E_AI_SCHOOL_2026_ISOLATION` | `secondary.aischool.test` | Negative cross-tenant security and authorization tests |

---

## 2. Administrative & Staff Accounts

All test accounts use standardized deterministic naming conventions under `@aischool.test`. Real passwords are never logged or exposed.

| Role | Name | Test Identifier / Email | Assigned Permissions |
|---|---|---|---|
| **Super Admin** | E2E Super Admin | `superadmin.e2e@aischool.test` | Full bypass via `Gate::before`, cross-tenant access |
| **Branch Manager** | E2E Branch Manager | `branchmanager.e2e@aischool.test` | Branch oversight, `cancel_subscription`, `settle_subscription`, `execute_refunds` |
| **Registration Staff 1** | E2E Registration Staff 1 | `secretary1.e2e@aischool.test` | Student registration, enrollment creation, `cancel_subscription` (no refund rights) |
| **Registration Staff 2** | E2E Registration Staff 2 | `secretary2.e2e@aischool.test` | Student registration, group assignment |
| **Accountant** | E2E Financial Officer | `accountant.e2e@aischool.test` | Vault management, collections, `execute_refunds`, expense logging |
| **Supervisor 1** | E2E Supervisor 1 | `supervisor1.e2e@aischool.test` | Group supervision, attendance auditing, follow-up triage |
| **Supervisor 2** | E2E Supervisor 2 | `supervisor2.e2e@aischool.test` | Group supervision, academic performance oversight |

---

## 3. Instructors Roster

Four specialized instructors assigned to cohorts with dedicated compensation agreements:

| ID | Name | Test Identifier | Assigned Curriculum | Hourly Rate |
|---|---|---|---|---|
| `INST-01` | E2E Instructor 1 (Python) | `instructor1.e2e@aischool.test` | Python Fundamentals | 150.00 EGP |
| `INST-02` | E2E Instructor 2 (Scratch) | `instructor2.e2e@aischool.test` | Scratch Game Design | 130.00 EGP |
| `INST-03` | E2E Instructor 3 (App Inventor) | `instructor3.e2e@aischool.test` | Mobile App Inventor | 140.00 EGP |
| `INST-04` | E2E Instructor 4 (Front-End) | `instructor4.e2e@aischool.test` | Front-End Web (HTML/CSS/JS) | 160.00 EGP |

---

## 4. Student & Parent Matrix (16 Students, 8 Parents)

Students are paired deterministically under parents (2 children per parent) to rigorously test multi-child switching in the Parent Portal:

| Student Code | Student Name | Linked Parent | Family Link Type | Initial Cohort Assignment |
|---|---|---|---|---|
| `STU-001` | E2E Student 1 | E2E Parent 1 (`parent1.e2e@...`) | Father | E2E-GROUP-A (Python) |
| `STU-002` | E2E Student 2 | E2E Parent 1 (`parent1.e2e@...`) | Father | E2E-GROUP-A (Python) |
| `STU-003` | E2E Student 3 | E2E Parent 2 (`parent2.e2e@...`) | Mother | E2E-GROUP-A (Python) |
| `STU-004` | E2E Student 4 | E2E Parent 2 (`parent2.e2e@...`) | Mother | E2E-GROUP-A (Python) |
| `STU-005` | E2E Student 5 | E2E Parent 3 (`parent3.e2e@...`) | Father | E2E-GROUP-B (Scratch) |
| `STU-006` | E2E Student 6 | E2E Parent 3 (`parent3.e2e@...`) | Father | E2E-GROUP-B (Scratch) |
| `STU-007` | E2E Student 7 | E2E Parent 4 (`parent4.e2e@...`) | Mother | E2E-GROUP-B (Scratch) |
| `STU-008` | E2E Student 8 | E2E Parent 4 (`parent4.e2e@...`) | Mother | E2E-GROUP-B (Scratch) |
| `STU-009` | E2E Student 9 | E2E Parent 5 (`parent5.e2e@...`) | Father | E2E-GROUP-C (App Inventor) |
| `STU-010` | E2E Student 10 | E2E Parent 5 (`parent5.e2e@...`) | Father | E2E-GROUP-C (App Inventor) |
| `STU-011` | E2E Student 11 | E2E Parent 6 (`parent6.e2e@...`) | Mother | E2E-GROUP-C (App Inventor) |
| `STU-012` | E2E Student 12 | E2E Parent 6 (`parent6.e2e@...`) | Mother | E2E-GROUP-C (App Inventor) |
| `STU-013` | E2E Student 13 | E2E Parent 7 (`parent7.e2e@...`) | Father | E2E-GROUP-D (Front-End) |
| `STU-014` | E2E Student 14 | E2E Parent 7 (`parent7.e2e@...`) | Father | E2E-GROUP-D (Front-End) |
| `STU-015` | E2E Student 15 | E2E Parent 8 (`parent8.e2e@...`) | Mother | E2E-GROUP-D (Front-End) |
| `STU-016` | E2E Student 16 | E2E Parent 8 (`parent8.e2e@...`) | Mother | E2E-GROUP-D (Front-End) |

---

## 5. Curriculum, Cohorts & Vaults

### Courses & Groups
- **Course 1**: Python Fundamentals | 12 Sessions | 1,200.00 EGP (100.00 EGP / session) | Group: `E2E-GROUP-A`
- **Course 2**: Scratch Game Design | 12 Sessions | 1,200.00 EGP (100.00 EGP / session) | Group: `E2E-GROUP-B`
- **Course 3**: App Inventor Mobile | 12 Sessions | 1,200.00 EGP (100.00 EGP / session) | Group: `E2E-GROUP-C`
- **Course 4**: Front-End Web Development | 12 Sessions | 1,200.00 EGP (100.00 EGP / session) | Group: `E2E-GROUP-D`

### Financial Vaults
- `VAULT-01`: **E2E Main Cash Vault** | Type: Cash Safe | Opening Balance: 10,000.00 EGP
- `VAULT-02`: **E2E Central Bank Vault** | Type: Bank Account | Opening Balance: 25,000.00 EGP
