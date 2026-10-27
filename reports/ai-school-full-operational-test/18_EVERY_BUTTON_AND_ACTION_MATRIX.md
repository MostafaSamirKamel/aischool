# 18 — Action & Button Coverage Matrix

| Module | Screen | Discovered Action / Button | Actor | Verified DB Impact | Status |
|---|---|---|---|---|---|
| **Students** | ListStudents | "إنشاء طالب جديد" (Create Student) | Registration | Inserts into `users` & `students` | **PASS** |
| **Students** | EditStudent | "حفظ التغييرات" (Save Changes) | Registration | Updates `students` record | **PASS** |
| **Students** | ListStudents | "تصدير Excel / CSV" | Admin | Streams sanitized CSV file | **PASS** |
| **Enrollments** | CreateEnrollment | "حفظ الاشتراك" (Save Enrollment) | Registration | Inserts `enrollments` & `installments` | **PASS** |
| **Enrollments** | ViewEnrollment | "إلغاء الاشتراك" (Cancel Subscription Modal) | Manager | Updates status, computes settlement | **PASS** |
| **Payments** | ListPayments | "تسجيل دفعة" (Record Payment) | Accountant | Inserts `payments`, updates `vaults` | **PASS** |
| **Payments** | ListPayments | "طباعة إيصال" (Print Receipt) | Accountant | Generates printable voucher | **PASS** |
| **Sessions** | ListSessions | "جدولة سيشن" (Schedule Session) | Supervisor | Inserts `attendance_sessions` | **PASS** |
| **Attendance** | SessionAttendance | "حفظ الحضور والغياب" | Instructor | Inserts/Updates `attendance_records` | **PASS** |
| **Assignments** | CreateAssignment | "نشر الواجب للطلاب" | Instructor | Inserts `assignments` (`active`) | **PASS** |
| **Assignments** | GradeAssignment | "اعتماد ونشر الدرجات" | Instructor | Updates `assignment_submissions` | **PASS** |
| **Exams** | CreateExam | "إنشاء امتحان جديد" | Instructor | Inserts `exams` with `passing_marks` | **PASS** |
| **Grades** | GradeForm | "حفظ الدرجة" (Save Grade) | Instructor | Inserts `grades`, triggers alerts | **PASS (FIXED)** |
| **Grades** | BulkGradeEntry | "حفظ الدرجات الجماعي" | Instructor | Bulk inserts multiple `grades` | **PASS** |
| **Follow-Up** | FollowUpTask | "تسجيل محاولة اتصال" | Follow-Up | Inserts `student_follow_up_call_logs` | **PASS** |
| **Follow-Up** | FollowUpTask | "تغيير الحالة إلى تم التواصل" | Follow-Up | Updates task status to `contacted` | **PASS** |
| **Supervision**| Observations | "إنشاء ملاحظة توجيهية" | Supervisor | Inserts `supervision_observations` | **PASS** |
| **Supervision**| Observations | "إغلاق واعتماد المعالجة" | Supervisor | Updates status to `completed` | **PASS** |
| **Supervision**| Evaluations | "نشر التقييم الشهري" | Supervisor | Sets evaluation `is_published: true` | **PASS** |
| **Supervision**| Appeals | "البت في الاعتراض" | Manager | Resolves appeal & updates record | **PASS** |
| **Reports** | ReportingCenter | "تصدير CSV للتقرير" | Admin | Streams CSV with UTF-8 BOM | **PASS** |
| **Reports** | ReportingCenter | "تصدير PDF للتقرير" | Admin | Renders 45KB PDF with Tajawal font | **PASS** |
