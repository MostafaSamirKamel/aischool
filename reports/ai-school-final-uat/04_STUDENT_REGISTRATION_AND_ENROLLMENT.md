# 04 — STUDENT REGISTRATION AND ENROLLMENT

**Test Stage:** Stage A & Stage B  
**Lead Actor:** Registration Employee / Secretary  
**Verification Method:** Automated End-to-End Execution (`MasterProductionAcceptanceTest::test_stage_a`, `test_stage_b`)

---

## 1. Stage A — Student & Guardian Registration Verification

### Test Steps & Execution
1. A synthetic guardian was registered in Branch A with contact details (`phone: 01011112222`).
2. Synthetic Student A (`STU-UAT-001`) was registered with distinct phone number (`phone: 01033334444`).
3. Student was linked to guardian via `student_parents` table with relationship `father`.
4. Registered Students B (`STU-UAT-002`) and C (`STU-UAT-003`).

### Assertions & Database Verification
- **Unique Student Code:** Auto-generated and verified unique across the branch.
- **Phone Number Separation:**
  - `Student::phone` = `01033334444`
  - `User(Parent)::phone` = `01011112222`
  - Zero overwriting or cross-contamination between student and guardian telephone fields.
- **Administrative Counts:** Database correctly reflects total active students incremented by 3 without ghost records.

---

## 2. Stage B — Course Enrollment & Group Assignment Verification

### Test Steps & Execution
1. Created Course "Python Programming" and Course "Scratch Game Dev".
2. Created Group "PY-01" (for Python) and Group "SC-01" (for Scratch).
3. Enrolled Student A into **both** Python and Scratch.
4. Enrolled Student B into Python only.
5. Enrolled Student C into Scratch only.

### Multi-Course Identity Integrity
- **Single Identity / Multiple Enrollments:**
  - Student A retains a single unique `student_id` (`STU-UAT-001`).
  - Two distinct records created in `enrollments` table:
    - Enrollment 1: `course_id: Python`, `group_id: PY-01`, `status: active`
    - Enrollment 2: `course_id: Scratch`, `group_id: SC-01`, `status: active`
- **Group Isolation:**
  - Group PY-01 has 2 students (A, B).
  - Group SC-01 has 2 students (A, C).
- **Fees & Financial Obligations:**
  - Fees calculated separately per enrollment without accidental fee merging.

---

## 3. Acceptance Result
- **Stage A:** **PASS** (Zero phone cross-contamination, correct guardian relationship).
- **Stage B:** **PASS** (Single student identity across multiple concurrent course enrollments).
