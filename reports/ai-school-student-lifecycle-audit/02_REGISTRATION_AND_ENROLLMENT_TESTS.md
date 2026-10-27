# 02 — STUDENT REGISTRATION & ENROLLMENT VERIFICATION

## 1. Scenario Specification
- **Student**: Ahmed Samir Ali (Synthetic ID: 144)
- **Parent Account**: Samir Ali Mahmoud (User ID: 259)
- **Course**: Python Junior (Course ID: 33, Price: 3,000 EGP)
- **Group**: Python Group A (Group ID: 44, Capacity: 10)
- **Contracted Value**: 3,000.00 EGP

## 2. Database State & Linkages
1. **User Account Created**: `User::create(['user_type' => 'student', 'tenant_id' => 1])`.
2. **Student Profile Created**: `Student::create(['student_code' => 'ST-XXXXX', 'status' => 'active'])`.
3. **Parent Account Linked**: `StudentParent::create(['relation' => 'father', 'is_primary' => true])`.
   - Verified: `$student->parents()->exists()` evaluates to `true`.
4. **Enrollment Established**: `Enrollment::create(['group_id' => 44, 'total_amount' => 3000.00, 'status' => 'active'])`.
5. **Group Roster Updated**:
   - `Group::activeStudents()` automatically identifies the enrolled student.
   - Initial group active count: 0 $\rightarrow$ 1.
   - Available seats: 10 $\rightarrow$ 9.

## 3. Test Verdict: PASS
Student, parent relationship, academic enrollment, and classroom group membership saved with 100% integrity.