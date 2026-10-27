# AI SCHOOL — MEGA DROPDOWN STRUCTURE

**Date:** October 26, 2026  
**Scope:** Architectural specification of the multi-column Mega Dropdown menus across panels.

---

## 1. MEGA DROPDOWN INTERACTION MODEL
- **Open / Close Logic:** Clicking any category button opens its dropdown. Clicking outside or pressing `Escape` immediately closes it.
- **Mutual Exclusivity:** Opening Category B automatically closes Category A.
- **Maximum Two Levels:** Main Category ➔ Destination Page.
- **Multi-Column Presentation:** Instead of nested submenus, sub-groups are presented side-by-side in 1 to 4 clean vertical columns.
- **Touch & Mobile Support:** Touch targets exceed 44px; on viewports < 1024px, the menu transforms into an accordion drawer.

---

## 2. ADMIN PANEL MEGA DROPDOWN SCHEMAS

### Category 1: الطلاب والتسجيل (Students & Registration)
- **Column 1: تسجيل وحضور الطلاب**
  - تسجيل الطلاب بالكورسات والمجموعات (`/admin/enrollments`)
  - سجلات الحضور (`/admin/attendance-sessions`)
  - المدفوعات (`/admin/payments`)
  - طباعة الباركود (`/admin/barcode-print`)
  - تسجيل حضور بالباركود (`/admin/barcode-attendance`)
  - سجل الرسائل (`/admin/sms-log-resource/sms-logs`)
- **Column 2: المتابعة اليومية**
  - مركز متابعة الطلاب (`/admin/academic-follow-up-center`)
  - متابعة الطلاب والمهام (`/admin/student-follow-up-tasks`)

### Category 2: الشؤون الأكاديمية (Academic)
- **Column 1: المناهج والمجموعات**
  - المسارات التعليمية (`/admin/tracks`)
  - الكورسات (`/admin/courses`)
  - القاعات (`/admin/classrooms`)
  - المجموعات (`/admin/groups`)
- **Column 2: الاختبارات والنتائج**
  - الامتحانات (`/admin/exams`)
  - الامتحانات الإلكترونية (`/admin/assignment-resource/exam-attempts`)
  - النتائج الرسمية (`/admin/grades`)
- **Column 3: الإشراف والاعتراضات**
  - تقارير الإشراف التعليمي للفروع (`/admin/supervision-reports`)
  - اعتراضات تقييمات المهندسين (`/admin/instructor-evaluation-appeals`)

### Category 3: الإدارة المالية (Finance)
- **Column 1: الخزائن والمصروفات**
  - فئات المصروفات (`/admin/expense-categories`)
  - المصروفات (`/admin/expenses`)
  - فئات الإيرادات (`/admin/revenue-categories`)
  - الإيرادات (`/admin/revenues`)
  - الخزائن (`/admin/vaults`)
  - تسويات الخزائن (`/admin/vault-reconciliations`)
  - التحويلات بين الخزائن (`/admin/vault-transfers`)
- **Column 2: مرتبات المدرسين**
  - المرتبات (`/admin/salary-payments`)
  - توليد أجور المدرسين (`/admin/generate-instructor-payments`)
- **Column 3: متابعة التحصيل**
  - أقساط اليوم (`/admin/installments-due-today`)
  - أقساط الأسبوع (`/admin/installments-due-week`)
  - أقساط الشهر (`/admin/installments-due-month`)

### Category 4: التقارير والإحصائيات (Reports)
- **Column 1: التقارير العامة والشاملة**
  - مركز التقارير الموحد (`/admin/reporting-center-page`)
  - تقرير المستخدمين (`/admin/users-report`)
  - بورتفوليو الطالب (`/admin/student-portfolio-report`)
  - التقارير المتقدمة (`/admin/advanced-reports`)
- **Column 2: تقارير الإيرادات والخزائن**
  - تقرير الخزينة (`/admin/vault-report`)
  - الأقساط المتأخرة (`/admin/overdue-installments`)
  - تقرير الأقساط المستحقة (`/admin/installments-report`)
  - تقرير جميع الأقساط (`/admin/all-installments-report`)
  - تقرير إيرادات الكورسات (`/admin/course-financial-report`)

### Category 5: إدارة النظام (Administration)
- **Column 1: فريق العمل والموظفين**
  - المشرفون (`/admin/supervisors`)
  - الإداريين (`/admin/admins`)
  - الطلاب (`/admin/students`)
  - أولياء الأمور (`/admin/student-parents`)
  - المدرسين (`/admin/instructors`)
  - حضور السكرتيرة (`/admin/secretary-attendance`)
- **Column 2: الأدوار والمؤسسات**
  - الأدوار والصلاحيات (`/admin/shield/roles`)
  - المؤسسات والفروع (`/admin/tenants`)
  - المستخدمين (`/admin/users`)
- **Column 3: التعريفات والإعدادات**
  - أقسام المؤسسة (`/admin/departments`)
  - الجنسيات (`/admin/nationalities`)
  - المهن (`/admin/occupations`)
  - الإعدادات العامة (`/admin/settings`)
  - إعدادات الرسائل (`/admin/sms-configs`)
  - المهام والجدولة (`/admin/scheduled-tasks`)

---

## 3. INSTRUCTOR PANEL MEGA DROPDOWN SCHEMAS

- **Category: التدريس والدرجات (Teaching & Grades)**
  - Column 1: المركز الأكاديمي الشامل، نتائج الطلاب
  - Column 2: الواجبات، تسليمات الواجبات، الامتحانات، جدول السيشنات
- **Category: المجموعات والطلاب (Groups & Students)**
  - Column 1: مجموعاتي، جلسات الحضور، المصادر التعليمية
  - Column 2: رسائل المجموعات، رسائل الطلاب
- **Category: المتابعة والتقارير (Reports & Supervision)**
  - Column 1: توجيهاتي الإشرافية، تقييماتي التعليمية الرسمية
  - Column 2: أجوري، تقاريري، تقييماتي

---

## 4. SUPERVISOR PANEL MEGA DROPDOWN SCHEMAS

- **Category: الإشراف والمجموعات (Supervision & Groups)**
  - Column 1: المجموعات، الطلاب، جلسات الحضور، الملفات الإشرافية للمجموعات
- **Category: الجودة التعليمية والتوجيه (Quality & Directives)**
  - Column 1: الملاحظات والتوجيهات الإشرافية، تقييمات جودة التدريس، الملفات الإشرافية للمهندسين، التنبيهات التعليمية الذكية، التقارير الإشرافية والإدارية
