# Report 06: Courses Catalog, Pricing, and Student Enrollments

## 1. Course Master Catalog
| Course ID | Course Name | Category | Standard Price | Payment Structure |
|---|---|---|---|---|
| **78** | Python Junior | Programming & AI | **1,500.00 EGP** | Full (1,500) or Down Payment (500) + 2 Installments (500 x 2) |
| **1** | HTML & CSS | Web Development | **1,500.00 EGP** | Full (1,500) or Down Payment (500) + 2 Installments (500 x 2) |
| **79** | Scratch | Logic & Creative Coding | **1,200.00 EGP** | Full (1,200) or Down Payment (400) + 2 Installments (400 x 2) |

---

## 2. Enrollment Breakdown by Plan
Across the 29 students, enrollments tested both upfront settlement and installment payment schedules:

| Group | Total Students | Full Upfront Paid | Down Payment + Installments Plan |
|---|---|---|---|
| **Group A (Python Junior)** | 12 | 5 Students (1,500 EGP each) | 7 Students (500 EGP down payment + 2 x 500 EGP) |
| **Group B (HTML & CSS)** | 5 | 2 Students (1,500 EGP each) | 3 Students (500 EGP down payment + 2 x 500 EGP) |
| **Group C (Scratch)** | 12 | 5 Students (1,200 EGP each) | 7 Students (400 EGP down payment + 2 x 400 EGP) |
| **Total** | **29** | **12 Full Paid** | **17 Installment Plans** |

---

## 3. Test Result Format
- **Test ID:** CRS-ENR-01
- **Module:** Academic Registration & Billing
- **Role:** Registrar / Branch Manager
- **Route:** `/admin/enrollments`
- **Initial State:** 3 approved courses in catalog.
- **Actual Action Executed:** Enrolled 29 students; generated tuition obligations and installment schedules.
- **Expected Result:** 29 enrollment records with accurate course association and fee calculations.
- **Actual Result:** 29 active/settled enrollments created in `enrollments` table.
- **Status:** **PASS**