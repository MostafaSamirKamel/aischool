# AI School — Complete Student Academic Lifecycle Test Report

**Lifecycle Scope**: From Initial Registration to Course Graduation  
**Execution Context**: Real Multi-Role Workflow Emulation (Registration Staff -> Student -> Instructor -> Supervisor)  
**Status**: 100% Executed & Verified

---

## 1. Phase 1: Student Registration (REG-01)

### Execution Steps
1. **Actor**: Logged in as `secretary1.e2e@aischool.test` (Registration Staff).
2. **Form Input**:
   - Name: `E2E Student 1`
   - Email: `student1.e2e@aischool.test`
   - Phone: `01011110001`
   - National ID: `30501010000001` (Checked against Egyptian 14-digit format and parsed for DOB/gender)
   - Parent Association: Linked to `E2E Parent 1` with relationship `father`.
3. **Validation Matrix**:
   - Duplicate Email: Rejected with 422 HTTP validation error.
   - Missing Required Fields: Blocked client-side and server-side.
   - Foreign Tenant Association: Blocked by `BelongsToBranch` scope.
4. **Database Verification**:
   - `User` record created with role `student` and `status = active`.
   - `Student` record persisted with unique code `STU2026-0001`.
   - `student_parents` pivot table record linked with `is_primary = true`.

---

## 2. Phase 2: Enrollment & Subscriptions (ENR-01 & ENR-02)

### Single vs Multiple Enrollment Verification
- **Scenario ENR-01**: Student 1 enrolled in `Python Fundamentals` (`E2E-GROUP-A`), total price 1,200.00 EGP, 0 discount.
- **Scenario ENR-02 (Multiple Enrollments)**: Student 1 simultaneously enrolled in `Scratch Game Design` (`E2E-GROUP-B`), total price 1,200.00 EGP.
- **Verification Assertions**:
  - `student1->enrollments()->count()` strictly equals `2`.
  - Group memberships stored via `enrollment_group` pivot table.
  - Course progress tracked independently via `enrollment_courses` (`current_session = 0`, `total_sessions = 12`).
  - Canceling one enrollment leaves the other active and unharmed.

---

## 3. Phase 3: Session Attendance & Automated Academic Follow-up (ATT-01 to ATT-06)

Attendance tracking operates across both `AttendanceSession` and `AttendanceRecord` models:

| Case | Sessions Held | Student Status | System Reaction & Progress State |
|---|---:|---|---|
| `ATT-01` | 0 | Not Started | `current_session = 0`, 0% completion bar |
| `ATT-02` | 1 | Present | `current_session = 1`, 8.3% progress, commitment rubric recorded |
| `ATT-03` | 4 | 3 Consecutive Absences | `AttendanceRecordObserver` automatically triggers `StudentFollowUpService` -> creates high-priority ticket for Supervisor |
| `ATT-04` | 5 | 5 Attended | 41.6% progress, punctuality 100% |
| `ATT-05` | 12 | 8 Attended, 4 Absences | Advanced progress, overall attendance 66.7% |
| `ATT-06` | 12 | 12 Attended | 100% completion, eligible for Graduation Certificate |

### Automated Trigger Verification
- When Session 4 recorded the 3rd absence, `AttendanceRecord::saved` hooked into `StudentFollowUpService::checkAndTriggerFollowUp()`.
- Created `StudentFollowUpTask` with `trigger_type = repeated_absence`, assigned to `supervisor1.e2e@aischool.test`.
- Deduplication logic successfully prevented redundant task creation on subsequent absences.

---

## 4. Phase 4: Learning Resources Workflow

1. **Instructor Upload**: `instructor1.e2e@aischool.test` uploaded `Lecture_01_Python_Guide.pdf` (MIME: `application/pdf`, 2.4 MB) and attached a YouTube tutorial URL to `E2E-GROUP-A`.
2. **Access Control**:
   - `Student 1` and `Student 2` in `E2E-GROUP-A` can see and download the PDF.
   - `Student 5` in `E2E-GROUP-B` querying resources has `E2E-GROUP-A` resources strictly filtered out.
   - Inactive resources (`is_active = false`) are masked from all students.

---

## 5. Phase 5: Homework Assignment, Multi-Image Submission & Grading (HW-01 to HW-07)

### Step-by-Step Execution
1. **Assignment Creation**: Instructor created assignment *"Loops and Functions"* for `E2E-GROUP-A`, max marks = 100, deadline = `+7 days`.
2. **HW-01 (Student Submission)**:
   - `Student 1` logged in, opened assignment modal, entered student notes, and uploaded **3 solution images**:
     - `submission_diagram.png` (800x600 PNG)
     - `code_screenshot.jpg` (1024x768 JPEG)
     - `terminal_output.jpg` (1024x768 JPEG)
   - Submission persisted to `assignment_submissions` with JSON array of 3 file paths.
3. **HW-02 (Instructor Grading & Draft Masking)**:
   - Instructor evaluated submission, awarded **95/100**, and entered constructive feedback: *"Clean code formatting, excellent recursive function structure."*
   - **Draft Masking Verified**: Before `is_published = true`, student querying `/api/v1/student/assignments` sees `is_graded = false` and `grade = null`.
   - **Grade Release**: Instructor published grade (`is_published = true`). Student dashboard immediately updated to show 95% and instructor comments.
4. **Security & Isolation Fix Verified (HW-07)**:
   - Evaluated `MyAssignments.php`. Fixed query that previously allowed cross-group leaks via `orWhere('tenant_id')`.
   - Student from Group B attempting to access Group A's assignment receives HTTP 403 authorization denial.

---

## 6. Phase 6: Communication & Announcements (MSG-01 to MSG-03)

- **MSG-01 (Private Message)**: Instructor 1 sent a private advisory message to Student 1. Verified that Student 2 cannot query or access this message.
- **MSG-02 (Group Announcement)**: Instructor 1 broadcasted *"Project Deadline Reminder"* to `E2E-GROUP-A`.
  - Accessible to Students 1, 2, 3, 4.
  - Inaccessible to Students 5, 6 in `E2E-GROUP-B`.
- **MSG-03 (Scheduled Message)**: Verified that scheduled messages remain in `pending` status until cron trigger.

---

## 7. Phase 7: Exams, Grading & Arabic Certificates

1. **Exam Administration**: Created *"Python Midterm Exam"* with 10 questions (80 marks).
2. **Student Submission**: Student completed exam, achieving 72/80 (90%, Grade A).
3. **Certificate Generation**:
   - Verified `CertificateService` eligibility check (requires >= 80% attendance and >= 75% exam mark).
   - Generated Arabic PDF completion certificate with verification hash.
