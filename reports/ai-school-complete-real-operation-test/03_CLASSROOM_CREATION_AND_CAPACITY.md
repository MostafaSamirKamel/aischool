# Report 03: Classroom Creation and Capacity Enforcement

## 1. Classrooms Specification
In strict compliance with the simulation instructions, exactly two physical classrooms were verified and configured under the **أبو المطامير** branch:

| Classroom ID | Name | Tenant ID | Capacity (Max Students) | Status | Group Assignment |
|---|---|---|---|---|---|
| **1** | قاعة 1 | 1 (أبو المطامير) | **17** | Active | Group A (12) & Group B (5) |
| **2** | قاعة 2 | 1 (أبو المطامير) | **12** | Active | Group C (12) |

```
Total Active Classrooms in Abu El Matamir: 2
Physical Room 1 Capacity: 17 Students
Physical Room 2 Capacity: 12 Students
```

---

## 2. Capacity & Policy Verification
1. **Physical Room Capacity vs. Group Academic Limit:**
   - Room 1 has a physical capacity of 17 seats.
   - Group A occupies 12 students (within 17 limit).
   - Group B occupies 5 students (within 17 limit).
   - Room 2 has a physical capacity of 12 seats.
   - Group C occupies 12 students (100% capacity utilization).
2. **Timetable & Collision Rules:**
   - Group A and Group B share Room 1.
   - Non-overlapping time slot scheduling is enforced so both groups never occupy Room 1 simultaneously.

---

## 3. Test Result Format
- **Test ID:** CLS-CAP-01
- **Module:** Academic Logistics / Classrooms
- **Role:** Branch Manager
- **Route:** `/admin/classrooms`
- **Initial State:** Verification of classroom master data.
- **Actual Action Executed:** Inspected `classrooms` table; checked room capacity rules against timetable.
- **Expected Result:** Exactly 2 active classrooms in Abu El Matamir with capacities 17 and 12.
- **Actual Result:** Classroom 1 (ID 1, cap 17), Classroom 2 (ID 2, cap 12).
- **Database Evidence:** `SELECT id, name, capacity, status FROM classrooms WHERE tenant_id = 1;`
- **Status:** **PASS**