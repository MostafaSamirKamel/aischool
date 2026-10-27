# 02 — Complete Inventory of Modules, Resources & Options

## 1. Discovered Filament Resources (57 Resources)
1. **Academic Management:**
   - Courses (`CourseResource`)
   - Course Categories (`CourseCategoryResource`)
   - Groups (`GroupResource`)
   - Classrooms (`ClassroomResource`)
   - Educational Sessions / Attendance (`AttendanceSessionResource`)
   - Attendance Records (`AttendanceRecordResource`)
   - Schedules (`ScheduleResource`)

2. **Student & Parent Lifecycle:**
   - Students (`StudentResource`)
   - Parents (`StudentParentResource` / `ParentResource`)
   - Enrollments (`EnrollmentResource`)
   - Student Transfers (`StudentTransferResource`)
   - Student Certificates (`CertificateResource`)
   - Student ID Cards (`StudentCardResource`)

3. **Academic Evaluation & Examinations:**
   - Homework Assignments (`AssignmentResource`)
   - Assignment Submissions (`AssignmentSubmissionResource`)
   - Examinations (`ExamResource`)
   - Exam Questions (`ExamQuestionResource`)
   - Exam Attempts (`ExamAttemptResource`)
   - Grades & Bulk Grade Entry (`GradeResource` & `BulkGradeEntry`)

4. **Financial Workflows & Treasury:**
   - Vaults / Treasuries (`VaultResource`)
   - Payments & Collections (`PaymentResource`)
   - Installments (`InstallmentResource`)
   - Expenses (`ExpenseResource`)
   - Expense Categories (`ExpenseCategoryResource`)
   - Revenues (`RevenueResource`)
   - Vault Transfers (`VaultTransferResource`)
   - Instructor Compensation / Salaries (`SalaryPaymentResource` / `TeacherSalaryResource`)
   - Subscription Cancellations & Settlements (`EnrollmentCancellationResource`)
   - Discounts & Offers (`DiscountResource`)

5. **Educational Supervision & Quality Assurance:**
   - Supervisory Observations (`SupervisionObservationResource`)
   - Teacher Evaluations (`TeacherEvaluationResource`)
   - Evaluation Criteria (`EvaluationCriterionResource`)
   - Supervisory Appeals (`EvaluationAppealResource`)
   - Periodic Supervision Reports (`SupervisionReportResource`)

6. **Student Follow-Up & Student Affairs:**
   - Follow-Up Tasks (`StudentFollowUpTaskResource`)
   - Follow-Up Call Logs (`StudentFollowUpCallLogResource`)
   - Academic Alerts (`AcademicAlertResource`)

7. **System Administration & Settings:**
   - Branches / Tenants (`TenantResource`)
   - Users (`UserResource`)
   - Roles & Permissions (`RoleResource`)
   - Activity Logs / Audit Trail (`ActivityLogResource`)
   - System Settings (`SettingResource`)
   - Reporting Center (`ReportingCenterPage`)

## 2. Core Actions & Modals Inventory
- **Header Actions:** Create, Export (CSV, PDF, Excel), Filter, Bulk Grade Entry, Recalculate Totals
- **Table Record Actions:** View, Edit, Delete, Reorder, Change Status, Print Receipt, Trigger Follow-up
- **Bulk Actions:** Bulk Delete, Bulk Restore, Bulk Status Update, Bulk Export
- **Custom Modals:** Subscription Cancellation & Refund Modal, Grade Entry Modal, Observation Corrective Action Modal, Evaluation Appeal Modal
