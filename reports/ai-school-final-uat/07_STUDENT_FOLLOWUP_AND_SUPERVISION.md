# 07 — STUDENT FOLLOW-UP AND SUPERVISION

**Audit Stage:** Stage H  
**Lead Actor:** Academic Follow-Up Employee / Supervisor  
**Verification Method:** Automated End-to-End Execution (`MasterProductionAcceptanceTest::test_stage_h_student_follow_up_workflow_and_statuses`)

---

## 1. Academic Follow-Up Center Architecture

The system enforces strict operational discipline within the Academic Follow-Up Center:
1. **Strict Two-Status Invariant:**
   - The workflow supports **STRICTLY TWO STATUSES**:
     - Status 1: `"new"` ("جديدة") — Uncontacted or pending task.
     - Status 2: `"contacted"` ("تم التواصل") — Successfully reached student/guardian.
   - Verified that no intermediate or extraneous statuses exist in the model or UI selectors.

2. **Explicit Assignment & Group Isolation:**
   - Employees view and manage tasks strictly for students in their assigned groups (`SupervisionGroupAssignment`).
   - Cross-group tasks are hidden and inaccessible.

---

## 2. Test Execution & Workflow Transitions

### Step-by-Step Scenario
1. **Low Grade Follow-Up Generation:**
   - Student with published score `4/10` generated a task in status `new` assigned to Follow-Up Employee.
2. **Unsuccessful Contact Attempt:**
   - Employee logged an unsuccessful phone call (`outcome: 'no_answer'`).
   - *Result:* Contact attempt recorded in `student_follow_up_contacts` table.
   - *Status Check:* Task status **remains `"new"`** ("جديدة").
3. **Successful Contact Log:**
   - Employee logged a successful conversation (`outcome: 'answered'`).
   - *Result:* Second record inserted in `student_follow_up_contacts`.
   - *Status Check:* Task status transitioned to **`"contacted"`** ("تم التواصل").
   - *Grade Check:* Underlying academic score `4/10` remains completely unmodified.
4. **Task Reassignment:**
   - Supervisor reassigned a pending task to a second authorized employee.
   - *Audit Trail:* A record was created in `student_follow_up_task_reassignments` recording `previous_assigned_to`, `new_assigned_to`, and timestamp.
   - *Access Enforcement:* First employee immediately loses edit privileges; second employee gains access.

---

## 3. Acceptance Result
- **Stage H (Follow-Up):** **PASS** (Strict 2-status compliance, full contact history audit trail, tamper-proof academic grades).
