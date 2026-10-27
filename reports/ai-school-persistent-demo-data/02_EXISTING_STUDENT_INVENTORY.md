# 02 — Existing Student Inventory & Cohort Distribution

## Overview
This document catalogs the pre-existing student cohort discovered in the AI School relational database prior to population, verifying that **zero artificial student duplicates** were introduced and that all demonstration operational data was anchored directly to these authentic existing student records.

---

## 1. Student Population Inventory

| Metric | Pre-Population Count | Post-Population Count | Variance |
|---|---|---|---|
| **Total Registered Students** | **111** | **111** | **0 (No Duplicates Created)** |
| **Active Students** | 111 | 111 | 0 |
| **Branch 1 (Nasr City) Affiliation** | 74 | 74 | 0 |
| **Branch 2 (Giza) Affiliation** | 37 | 37 | 0 |
| **Students with Parent Linkage** | 87 (78.4%) | 111 (100.0%) | +24 (26 new links created) |
| **Total Student-Parent Links** | 87 | 113 | +26 |

> [!NOTE]
> 100% of the 111 pre-existing students now have active, authorized parent portal linkage, enabling full end-to-end verification of the Parent Dashboard.

---

## 2. Sample Student Profiles from Existing Cohort

The following sample illustrates representative existing students who were selected and enriched with diverse academic, operational, and financial histories:

| Student ID | Full Name | Phone | Branch | Initial Status | Enriched Scenarios |
|---|---|---|---|---|---|
| **1** | عمر خالد محمد | `+201011112222` | Branch 1 | Enrolled | Dual Course, A+ Grades, Full Settlement |
| **2** | فاطمة الزهراء علي | `+201022223333` | Branch 1 | Enrolled | Renewal, Good Attendance, Scheduled Payments |
| **3** | يوسف إبراهيم السيد | `+201033334444` | Branch 1 | Enrolled | Late Homework, Low Exam Score (<50%), Follow-up |
| **4** | مريم أحمد عبد الله | `+201044445555` | Branch 2 | Enrolled | Attendance Warnings, Pending Installment |
| **5** | علي حسن مصطفى | `+201055556666` | Branch 1 | Enrolled | Cancellation Scenario, Refund & Settlement |
| **6** | نورهان محمود بدر | `+201066667777` | Branch 2 | Enrolled | Dual Course, Excellent Attendance, Paid Receipts |
| **7** | كريم حسام الدين | `+201077778888` | Branch 1 | Enrolled | Makeup Sessions, Partial Installment Paid |
| **8** | سارة محمد طارق | `+201088889999` | Branch 2 | Enrolled | Renewal Term, Completed Submissions |
| **9** | زياد أيمن رضوان | `+201099990000` | Branch 1 | Enrolled | Absentee Alert, Unanswered Call Log |
| **10** | ليلى شريف كامل | `+201012345678` | Branch 2 | Enrolled | Cancellation, Retained Fee, Authorized Settlement |
| **11 – 111** | *(Cohort of 101 Students)* | *(Assorted)* | Branches 1 & 2 | Enrolled | Active Groups, Sessions, Attendance & Portals |

---

## 3. Cohort Segmentation by Academic Archetype

To ensure realistic, non-uniform data across the entire school:

1. **Archetype A — Honor Students (Top Performers)**:
   - High attendance (95%+), on-time homework, grades 90-100%, fully settled accounts.
2. **Archetype B — Average / Steady Students**:
   - Regular attendance (80-90%), mostly on-time submissions, grades 70-85%, regular monthly installments.
3. **Archetype C — At-Risk / Follow-Up Cases**:
   - Multiple absences, late or missing homework, exam score < 50%, triggered academic follow-up tasks.
4. **Archetype D — Dual-Enrollment / Multi-Course**:
   - Enrolled in 2 concurrent subjects (e.g. Physics + Advanced English), separate grades and attendance streams.
5. **Archetype E — Renewals & Returning Students**:
   - Completed Level 1 in previous term, registered renewal for Level 2 with fresh installment schedules.
6. **Archetype F — Course Cancellations & Settlements**:
   - Valid cancellation with authorized settlement rules: waived amounts, retained administrative fees, and recorded refund obligations.
