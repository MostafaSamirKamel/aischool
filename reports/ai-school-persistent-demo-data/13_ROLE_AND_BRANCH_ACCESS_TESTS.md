# 13 — Role & Branch Access Tests & Multi-Portal Verification

## Overview
This document specifies the authorization tests, multi-panel accessibility, and branch isolation boundaries enforced across all user personas in AI School.

---

## 1. User Panel Verification Matrix

The populated demonstration data was tested across all primary user roles to confirm that each persona sees appropriate, permission-filtered operational information:

| User Role | Tested Account | Accessible Modules & Views | Data Filter Restrictions |
|---|---|---|---|
| **Super Admin / General Manager** | `admin@aischool.test` | All screens, all branches, financial treasury, reporting center, audit logs, system settings | Unrestricted full enterprise access |
| **Branch Registration Employee** | `registration@aischool.test` | Student profiles, enrollments, group assignments, installment generation | Restricted to assigned branch students |
| **School Accountant** | `accountant@aischool.test` | Vault receipts, installments, aging debts, operating expenses, revenues | Restricted to assigned branch vaults |
| **Course Instructor** | `instructor1@aischool.test` | Assigned groups, session logs, student attendance marking, homework grading, exam question pools | Sees only groups assigned to the instructor |
| **Educational Supervisor** | `supervisor@aischool.test` | Classroom observations, instructor evaluations, evaluation appeals, periodic supervision reports | Quality assurance and pedagogical supervision |
| **Enrolled Student** | `student1@aischool.test` | Enrolled courses, upcoming sessions, personal attendance, submitted assignments, published exam results | Strict isolation: sees only own academic records |
| **Authorized Parent** | `parent1@aischool.test` | Linked children's academic timeline, attendance percentage, grade transcripts, outstanding tuition balance | Strict isolation: sees only verified linked children |

---

## 2. Branch Isolation & Multi-Campus Separation

AI School operates with strict branch partitioning between **Branch 1 (Main Campus - Cairo/Nasr City)** and **Branch 2 (Satellite Campus - Giza)**:

1. **Vault Isolation**:
   - Cashiers at Branch 1 can only collect payments into `Vault 1` (الخزينة الرئيسية).
   - Cashiers at Branch 2 can only collect payments into `Vault 2` (خزينة فرع الجيزة).
2. **Group & Session Isolation**:
   - Instructors and students assigned to Branch 1 groups cannot view or access Branch 2 schedules.
3. **Cross-Branch Executive Access**:
   - The Branch Comparison Report in the Reporting Center provides aggregated side-by-side KPI analysis exclusively to users possessing the `super_admin` or `executive_director` role.
