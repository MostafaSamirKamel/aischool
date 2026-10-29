# AI SCHOOL ERP - ROLES & PERMISSIONS ARCHITECTURE

هندسة وتوثيق نظام الأدوار والصلاحيات لمنصة إدارة المدارس والمراكز التعليمية (AI School ERP).

---

## 1. High-Level Architecture & Tech Stack
- **Framework**: Laravel 11 + Filament (v4/v5)
- **RBAC Engine**: Spatie Permission via Filament Shield
- **Multi-Tenancy**: Tenant isolation using `tenant_id` (عزل بيانات كل فرع/مؤسسة)
- **Total Permissions**: 652 permissions
- **Total Resource Models**: 42 models
- **Total Roles**: 10 active roles
- **Portals / Panels**: 5 distinct Filament panels

---

## 2. Panels (بوابات النظام الـ 5)
1. `/admin` -> لوحة الإدارة العامة وإدارة الفروع والخزائن والمستخدمين.
2. `/instructor` -> بوابة المعلمين والمحاضرين.
3. `/student` -> بوابة الطلاب لمتابعة المقررات والواجبات والاختبارات.
4. `/parent` -> بوابة أولياء الأمور لمتابعة الأبناء والمدفوعات.
5. `/supervisor` -> بوابة المشرفين التربويين لمتابعة الأداء والملاحظات.

---

## 3. System Roles (الأدوار الـ 10 ومسؤولياتها)

1. **`super_admin` (مدير النظام الأعلى)**:
   - صلاحيات غير مقيدة عبر جميع المستأجرين (Bypass / Full Privileges).
   - إدارة الفروع (Tenants)، إعدادات النظام، وإدارة المستخدمين العليا.

2. **`branch_manager` (مدير الفرع)**:
   - يمتلك أعلى الصلاحيات داخل الفرع (625 صلاحية).
   - إدارة كاملة للطلاب، المجموعات، المعلمين، الخزائن، الحسابات، والتقارير داخل فرعه فقط.

3. **`admin` (مسؤول إداري)**:
   - إدارة العمليات التشغيلية اليومية داخل الفرع (المقررات، الجداول، المجموعات).

4. **`accountant` (المحاسب المالي)**:
   - الصلاحيات المالية: الخزائن (`Vault`), حركة الأموال، المصروفات (`Expense`), الإيرادات (`Revenue`), الأقساط (`Installment`), والرواتب (`SalaryPayment`).

5. **`registration` (مسؤول التسجيل وشؤون الطلاب)**:
   - تسجيل الطلاب (`Enrollment`), نقل المجموعات (`transfer_student_group`), التسويات المالية للاشتراكات (`settle_enrollments`), والإلغاء (`cancel_enrollments`).

6. **`secretary` (السكرتارية والاستقبال)**:
   - الحضور والغياب اليومي والباركود (`BarcodeAttendance`), استعلامات الطلاب وأولياء الأمور.

7. **`instructor` (المعلم / المحاضر - 238 صلاحية)**:
   - إدارة مجموعاته الخاصة فقط (`Group`), الحضور (`AttendanceSession`), الواجبات (`Assignment`), الاختبارات (`Exam`), رصد الدرجات (`Grade`), والرد على تقارير الإشراف التربوي.

8. **`supervisor` (المشرف التربوي)**:
   - مراقبة الحصص والمجموعات، تقييم المعلمين (`InstructorReview`), رفع الملاحظات الإشرافية (`SupervisionReports`)، ومتابعة الخطط الدراسية.

9. **`student` (الطالب)**:
   - وصول مقيد عبر بوابة الطالب: عرض مقرراته، واجباته، امتحاناته، درجاته، وجداوله.

10. **`parent` (ولي الأمر)**:
    - وصول مقيد عبر بوابة ولي الأمر: متابعة أبنائه المرتبطين، الحضور والغياب، الدرجات، والأقساط المستحقة.

---

## 4. Filament Shield Resource Permissions Matrix (42 Models)

صيغة صلاحيات الموارد تتبع نمط Filament Shield القياسي: `{Action}:{ModelName}`:
- **العمليات القياسية**: `ViewAny`, `View`, `Create`, `Update`, `Delete`, `Restore`, `ForceDelete`, `ForceDeleteAny`, `RestoreAny`, `Replicate`, `Reorder`.

### نماذج النظام الـ 42 المشمولة بالصلاحيات:
1. **الطلاب والشؤون الأكاديمية**:
   - `Student`, `StudentParent`, `Group`, `Course`, `Track`, `Department`, `Classroom`
   - `AttendanceSession`, `SessionSchedule`, `Grade`, `Exam`, `ExamAttempt`
   - `Assignment`, `AssignmentSubmission`, `LearningResource`
2. **الكادر التعليمي والإشرافي**:
   - `Instructor`, `Supervisor`, `Admin`, `User`, `InstructorPayment`, `InstructorReview`, `StudentFollowUpTask`
3. **المالية والخزائن والاشتراكات**:
   - `Enrollment`, `Installment`, `Payment`, `Expense`, `ExpenseCategory`
   - `Revenue`, `RevenueCategory`, `SalaryPayment`
   - `Vault`, `VaultReconciliation`, `VaultTransfer`
4. **التواصل والإعدادات العامة**:
   - `GroupMessage`, `StudentMessage`, `SmsConfig`, `SmsLog`
   - `Nationality`, `Occupation`, `Tenant`, `Role`, `Setting`

---

## 5. Custom Business Logic Permissions (الصلاحيات المخصصة)

تتبع تسمية `snake_case` وتُستخدم للتحكم في العمليات والوظائف الحساسة:

### أ) الخزائن والعمليات النقدية (Vaults & Cash):
- `view_vault_balance`, `view_vault_balances`, `view_vault_transactions`, `view_vault_reports`
- `transfer_vault_funds` (تحويل رصيد بين الخزائن)
- `manage_expenses`, `manage_salaries`, `pay_salaries`

### ب) الاشتراكات والتسويات والإلغاء (Enrollments & Settlements):
- `cancel_enrollments`, `cancel_subscription` (إلغاء اشتراك طالب)
- `settle_enrollments`, `settle_subscription` (إجراء مخالصة/تسوية مالية)
- `refund_enrollments`, `refund_subscription`, `execute_refunds` (استرداد مبالغ مالية)
- `transfer_student_group` (نقل طالب من مجموعة إلى أخرى)

### ج) الإشراف التربوي والتقييمات (Educational Supervision):
- `create_supervisory_observations`, `approve_supervisory_observations`, `return_supervisory_observations`, `respond_supervisory_observations`
- `prepare_supervision_reports`, `publish_supervision_reports`, `review_management_supervision_reports`
- `create_instructor_evaluations`, `publish_instructor_evaluations`, `appeal_instructor_evaluations`, `comment_instructor_evaluations`
- `view_educational_supervision`, `view_supervision_dashboard`, `view_supervised_groups`, `view_supervised_instructors`

### د) المتابعة اليومية وشؤون الطلاب (Student Follow-Up & Tasks):
- `create_student_followup`, `complete_student_followup`, `create_student_followup_task`, `complete_student_followup_task`
- `record_parent_contact`, `record_followup_contact`, `reassign_follow_up_case`
- `view_student_contact_numbers`, `view_student_academic_monitoring`, `view_student_financial_follow_up`

### هـ) التقارير ومراكز البيانات (Reporting & Analytics):
- `view_reports`, `export_reports`, `schedule_reports`, `save_report_configurations`
- `view_financial_reports`, `view_academic_reports`, `view_cancellation_settlement_reports`
- `view_daily_follow_up_dashboard`, `view_daily_follow_up_reports`, `save_daily_snapshot`
- `view_reporting_center`, `export_reporting_center`

---

## 6. Page & Widget Permissions (الصفحات واللوحات)

- **Pages (`View:{PageName}`)**:
  `AdvancedReports`, `CourseFinancialReport`, `BarcodeAttendance`, `StudentPortfolioReport`, 
  `AllInstallmentsReport`, `InstallmentsReport`, `SecretaryAttendance`, `VaultReport`, `UsersReport`...
- **Widgets (`View:{WidgetName}`)**:
  `StatsOverviewWidget`, `AttendanceRateChartWidget`, `GradesDistributionWidget`, 
  `OverdueInstallmentsWidget`, `PaymentsWidget`, `UpcomingExamsWidget`...

---

## 7. Developer Coding Guidelines for Authorization

Whenever writing or modifying code in this system, adhere strictly to these rules:

### 1. Blade Directives:
```blade
@can('Create:Student')
    <x-filament::button>إضافة طالب</x-filament::button>
@endcan

@can('transfer_vault_funds')
    <!-- زر تحويل الخزينة -->
@endcan
```

### 2. Policies:
All models must have policy methods bound to standard Filament Shield permissions (`viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete`) along with multi-tenancy `tenant_id` verification unless user is `super_admin`.

### 3. Filament Resources, Pages, and Actions:
- Resource actions must utilize `->authorize('permission_name')` or `->visible(fn () => auth()->user()->can('...'))`.
- Pages and Widgets must implement `canAccess()` or `canView()` verifying respective view permissions.
