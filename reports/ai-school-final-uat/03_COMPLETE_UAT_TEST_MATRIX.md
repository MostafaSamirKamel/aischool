# 03 — COMPLETE UAT TEST MATRIX

**Project:** AI School Management System  
**Test Suite:** Master Production Acceptance Testing  
**Execution Mode:** Real Automated & Browser-Level Integration Simulation  

---

## 1. Master Acceptance Test Matrix

| Test ID | Stage / Module | Test Description | Role | Method | Status | Assertions |
|:---|:---|:---|:---|:---|:---:|:---:|
| **UAT-STG-A** | Registration | Student registration & guardian phone separation | Registration Officer | `MasterProductionAcceptanceTest::test_stage_a` | **PASS** | 5 |
| **UAT-STG-B** | Enrollment | Multi-course enrollment & group independence | Secretary | `MasterProductionAcceptanceTest::test_stage_b` | **PASS** | 6 |
| **UAT-STG-C** | Financial Reg | Upfront payment, installments & vault entry | Accountant | `MasterProductionAcceptanceTest::test_stage_c` | **PASS** | 5 |
| **UAT-STG-D** | Scheduling | Session scheduling, makeup sessions, no duplicate salary | Supervisor | `MasterProductionAcceptanceTest::test_stage_d_and_e` | **PASS** | 6 |
| **UAT-STG-E** | Attendance | Attendance recording, unrecorded session is not absence | Instructor | `MasterProductionAcceptanceTest::test_stage_d_and_e` | **PASS** | 5 |
| **UAT-STG-F** | Homework | Submission, draft score hidden, low-score alert trigger | Instructor / Student | `MasterProductionAcceptanceTest::test_stage_f` | **PASS** | 5 |
| **UAT-STG-G** | Exams | Examination attempt, draft vs published, follow-up threshold | Instructor / Student | `MasterProductionAcceptanceTest::test_stage_g` | **PASS** | 4 |
| **UAT-STG-H** | Follow-Up | Academic Follow-Up Center: strictly 2 statuses, reassign | Follow-Up Officer | `MasterProductionAcceptanceTest::test_stage_h` | **PASS** | 6 |
| **UAT-STG-I** | Installments | Collection allocation, partial payment, due date filters | Accountant | `MasterProductionAcceptanceTest::test_stage_i` | **PASS** | 6 |
| **UAT-STG-J** | Payroll | Session completion earnings calculation & payout | Accountant | `MasterProductionAcceptanceTest::test_stage_j` | **PASS** | 3 |
| **UAT-STG-K** | Cancellation | Single course cancel, history intact, settlement & refund | Finance Manager | `MasterProductionAcceptanceTest::test_stage_k` | **PASS** | 5 |
| **UAT-SEC-01** | Multi-Tenant | Cross-branch tenant isolation & unauthorized vault access | Super Admin / Branch Mgr | `MasterProductionAcceptanceTest::test_cross_tenant` | **PASS** | 3 |
| **UAT-SEC-02** | Group Isolation | Explicit assigned group filtering in follow-up center | Follow-Up Officer | `FollowUpSecurityAndIsolationTest` | **PASS** | 12 |
| **UAT-SEC-03** | Phone Privacy | Phone number masking without `view_student_phone` | Secretary / Follow-Up | `FollowUpSecurityAndIsolationTest` | **PASS** | 4 |
| **UAT-SEC-04** | Parent Isolation| Parent can view only own children, cross-parent blocked | Parent | `RealWorldEndToEndWorkflowTest` | **PASS** | 8 |
| **UAT-BAK-01** | Backup & Restore| DB export, restore into clean environment, PRAGMA check | System Admin | `scratch/test_backup_restore.php` | **PASS** | 10 |
| **UAT-PRF-01** | Performance | Query latency, memory usage & HTTP load timings | QA Engineer | `scratch/benchmark_performance.php` | **PASS** | 12 |

---

## 2. Summary Statistics
- **Total Master Test Cases:** 17 Comprehensive High-Level Workflows.
- **Passed:** 17 (100%).
- **Failed:** 0 (0%).
- **Blocked:** 0 (0%).
- **Total Fine-Grained Assertions Evaluated in Master Suite:** 102 assertions.
