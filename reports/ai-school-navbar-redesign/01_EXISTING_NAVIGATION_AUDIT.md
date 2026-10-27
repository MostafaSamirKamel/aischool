# AI SCHOOL — EXISTING NAVIGATION AUDIT

**Date:** October 26, 2026  
**Scope:** Pre-redesign inventory of all navigation items, groups, and destinations across AI School employee panels.

---

## 1. INVENTORY OF EMPLOYEE PANELS

The AI School system operates with 3 employee management panels:
1. **Admin Panel (`/admin`)**: Serves Super Admin, Branch Manager, Secretary, Accountant, Financial Employee, Registration.
2. **Instructor Panel (`/instructor`)**: Serves Instructors and Teachers.
3. **Supervisor Panel (`/supervisor`)**: Serves Educational Supervisors.

*(Student Panel `/student` and Parent Panel `/parent` are strictly excluded from this redesign and maintained without change).*

---

## 2. AUDIT OF REGISTERED NAVIGATION GROUPS & ITEMS

### 2.1 Admin Panel (`/admin`)
- **Direct / Root Items:**
  - لوحة التحكم (`/admin`)
- **Group: الطلاب والتسجيل:**
  - تسجيل الطلاب بالكورسات والمجموعات (`/admin/enrollments`)
  - سجلات الحضور (`/admin/attendance-sessions`)
  - المدفوعات (`/admin/payments`)
  - طباعة الباركود (`/admin/barcode-print`)
  - تسجيل حضور بالباركود (`/admin/barcode-attendance`)
  - سجل الرسائل (`/admin/sms-log-resource/sms-logs`)
- **Group: متابعة الطلاب:**
  - مركز متابعة الطلاب (`/admin/academic-follow-up-center`)
- **Group: إدارة الطلاب:**
  - متابعة الطلاب والمهام (`/admin/student-follow-up-tasks`)
- **Group: المحتوى التعليمي:**
  - المسارات التعليمية (`/admin/tracks`)
  - الكورسات (`/admin/courses`)
  - القاعات (`/admin/classrooms`)
  - المجموعات (`/admin/groups`)
- **Group: الامتحانات والتقييم:**
  - الامتحانات (`/admin/exams`)
  - الامتحانات الإلكترونية (`/admin/assignment-resource/exam-attempts`)
  - النتائج الرسمية (`/admin/grades`)
- **Group: التقارير والمتابعة:**
  - تقارير الإشراف التعليمي للفروع (`/admin/supervision-reports`)
  - اعتراضات تقييمات المهندسين (`/admin/instructor-evaluation-appeals`)
- **Group: الإدارة المالية:**
  - فئات المصروفات (`/admin/expense-categories`)
  - المصروفات (`/admin/expenses`)
  - فئات الإيرادات (`/admin/revenue-categories`)
  - المرتبات (`/admin/salary-payments`)
  - الإيرادات (`/admin/revenues`)
  - الخزائن (`/admin/vaults`)
  - تسويات الخزائن (`/admin/vault-reconciliations`)
  - التحويلات بين الخزائن (`/admin/vault-transfers`)
- **Group: إدارة الأجور:**
  - توليد أجور المدرسين (`/admin/generate-instructor-payments`)
- **Group: تقارير الأقساط:**
  - أقساط الشهر (`/admin/installments-due-month`)
  - أقساط اليوم (`/admin/installments-due-today`)
  - أقساط الأسبوع (`/admin/installments-due-week`)
- **Group: التقارير:**
  - مركز التقارير الموحد (`/admin/reporting-center-page`)
  - تقرير المستخدمين (`/admin/users-report`)
  - بورتفوليو الطالب (`/admin/student-portfolio-report`)
  - التقارير المتقدمة (`/admin/advanced-reports`)
- **Group: التقارير المالية:**
  - تقرير الخزينة (`/admin/vault-report`)
  - الأقساط المتأخرة (`/admin/overdue-installments`)
  - تقرير الأقساط المستحقة (`/admin/installments-report`)
  - تقرير جميع الأقساط (`/admin/all-installments-report`)
  - تقرير إيرادات الكورسات (`/admin/course-financial-report`)
- **Group: الموارد البشرية:**
  - المشرفون (`/admin/supervisors`)
  - الإداريين (`/admin/admins`)
  - الطلاب (`/admin/students`)
  - أولياء الأمور (`/admin/student-parents`)
  - المدرسين (`/admin/instructors`)
  - حضور السكرتيرة (`/admin/secretary-attendance`)
- **Group: الإدارة والمستخدمين:**
  - الأدوار والصلاحيات (`/admin/shield/roles`)
  - المؤسسات والفروع (`/admin/tenants`)
  - المستخدمين (`/admin/users`)
- **Group: البيانات الأساسية:**
  - أقسام المؤسسة (`/admin/departments`)
  - الجنسيات (`/admin/nationalities`)
  - المهن (`/admin/occupations`)
  - الإعدادات (`/admin/settings`)
  - إعدادات الرسائل (`/admin/sms-configs`)
- **Group: إعدادات النظام:**
  - المهام المجدولة (`/admin/scheduled-tasks`)

### 2.2 Instructor Panel (`/instructor`)
- **Group: Direct:**
  - لوحة تحكم المدرس (`/instructor`)
- **Group: التدريس:**
  - المركز الأكاديمي الشامل (`/instructor/homework-review-center`)
  - نتائج الطلاب (`/instructor/instructor-exam-attempt-resource/instructor-exam-attempts`)
- **Group: إدارة المحتوى:**
  - الواجبات (`/instructor/assignments`)
  - تسليمات الواجبات (`/instructor/assignment-submissions`)
  - الامتحانات (`/instructor/exams`)
  - جدول السيشنات (`/instructor/session-schedules`)
- **Group: إدارة المجموعات:**
  - مجموعاتي (`/instructor/my-groups`)
  - جلسات الحضور (`/instructor/attendance-sessions`)
  - المصادر التعليمية (`/instructor/learning-resources`)
- **Group: الإدارة:**
  - أجوري (`/instructor/instructor-payments`)
  - تقاريري (`/instructor/instructor-reports`)
  - تقييماتي (`/instructor/instructor-reviews`)
- **Group: التواصل:**
  - رسائل المجموعات (`/instructor/group-message-resource/group-messages`)
  - رسائل الطلاب (`/instructor/student-messages`)
- **Group: المتابعة الإشرافية:**
  - توجيهاتي الإشرافية (`/instructor/supervisory-observations`)
  - تقييماتي التعليمية الرسمية (`/instructor/my-evaluations`)

### 2.3 Supervisor Panel (`/supervisor`)
- **Group: Direct:**
  - لوحة التحكم (`/supervisor`)
- **Group: الإشراف:**
  - المجموعات (`/supervisor/groups`)
  - الطلاب (`/supervisor/students`)
  - جلسات الحضور (`/supervisor/attendance-sessions`)
  - الملفات الإشرافية للمجموعات (`/supervisor/group-profiles`)
- **Group: الإشراف التعليمي:**
  - الملاحظات والتوجيهات الإشرافية (`/supervisor/supervisory-observations`)
  - تقييمات جودة التدريس (`/supervisor/instructor-evaluations`)
  - التقارير الإشرافية والإدارية (`/supervisor/supervision-reports`)
  - التنبيهات التعليمية الذكية (`/supervisor/educational-alerts`)
  - الملفات الإشرافية للمهندسين (`/supervisor/instructor-profiles`)

---

## 3. SUMMARY OF DEFICIENCIES WITH PREVIOUS SIDEBAR
1. **Lost Horizontal Space:** The sidebar consumed 260px - 280px of screen real-estate, causing severe table wrapping and horizontal scrolling on financial reports, student rosters, and homework submissions.
2. **Excessive Vertical Scrolling:** With 16 navigation groups, users had to scroll repeatedly inside the narrow sidebar to find administrative settings or financial reports.
3. **Cluttered Mobile Experience:** On mobile devices, the administrative sidebar was awkward to navigate compared to structured top accordions.
