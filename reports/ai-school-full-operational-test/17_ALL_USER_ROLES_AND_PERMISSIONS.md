# 17 — User Roles, Permissions & Security Boundary Testing

## 1. Discovered System Roles (10 Roles)
1. `super_admin`: Global system owner, bypasses tenant isolation.
2. `branch_manager`: Complete administrative authority scoped to branch.
3. `accountant`: Financial access (vaults, payments, installments, expenses, salaries).
4. `registration`: Student admission, parent records, course enrollment.
5. `secretary`: Front desk, schedules, inquiry follow-up.
6. `instructor`: Teaching schedule, attendance, assignments, exam grading.
7. `supervisor`: Educational quality, teacher evaluations, observations.
8. `student`: Enrolled student portal access.
9. `parent`: Family portal, child academic and financial tracking.
10. `admin`: Legacy/General administrator.

## 2. Security & Isolation Matrix
| Role | Can Access Admin Panel? | Can Access Vaults? | Can Grade Exams? | Multi-Tenant Isolated? | Tested Result |
|---|---|---|---|---|---|
| **Super Admin** | Yes | Yes | Yes | Global (All Branches) | **PASS** |
| **Branch Manager** | Yes | Yes | Read-Only | Scoped to Assigned Branch | **PASS** |
| **Accountant** | Yes | Yes | No | Scoped to Assigned Branch | **PASS** |
| **Registration** | Yes | No | No | Scoped to Assigned Branch | **PASS** |
| **Instructor** | No (Instructor Panel) | No | Yes (Assigned Groups Only) | Scoped to Assigned Groups | **PASS** |
| **Supervisor** | No (Supervisor Panel) | No | Read-Only | Scoped to Branch Teachers | **PASS** |
| **Student** | No (Student Panel) | No | No | Scoped to Self Record | **PASS** |
| **Parent** | No (Parent Panel) | No | No | Scoped to Linked Children | **PASS** |

## 3. Direct URL Tampering & Privilege Escalation Checks
- Attempted to access `/admin/vaults` as Registration Staff: Returned HTTP 403 Forbidden.
- Attempted to access Branch 2 data with Branch 1 Accountant account: Query returned empty dataset (Tenant scope enforced).
- Parent cannot access unlinked student profiles: Strictly constrained by `student_parents` relation.
