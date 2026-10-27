# 01 — فحص ومراجعة التقارير القائمة (Existing Reports Audit)

## مراجعة شاملة لتقارير ومصادر بيانات منصة AI School

تاريخ التدقيق: أكتوبر 2026  
المنصة: AI School Management System  
الموقع: `reports/ai-school-reporting-center/01_EXISTING_REPORTS_AUDIT.md`

---

### 1. ملخص الفحص الميداني للكود

تم إجراء تدقيق هندسي ومحاسبي شامل لكافة شاشات ومصادر التقارير والخدمات المحاسبية والأكاديمية القائمة داخل مجلدات المشروع:
- `app/Filament/Admin/Pages/`
- `app/Filament/Admin/Pages/Reports/`
- `app/Services/`
- `app/Models/`

### 2. جدول حصر وتصنيف التقارير القائمة (Inventory & Classification)

| التقرير القائم | مصدر البيانات | الحسابات الحالية | الصلاحيات الحالية | الفجوات والمشاكل المكتشفة | الإجراء المتخذ |
|---|---|---|---|---|---|
| **AdvancedReports** (`AdvancedReports.php`) | نماذج متعددة (`Student`, `Group`, `Attendance`, `StudentPayment`, `Exam`, `Classroom`) | إحصائيات عامة للطلاب والإيرادات والحضور بتواريخ متغيرة | مقتصر على `super_admin` و `branch_manager` | يخلط بين النقدية والمبيعات، لا يقسم المديونيات إلى متأخرة وقادمة | **ENHANCE & INTEGRATE**: تم الحفاظ عليه وربطه بالمركز الموحد |
| **VaultReport** (`VaultReport.php`) | `Vault`, `Payment`, `Expense`, `SalaryPayment`, `VaultTransfer` | حساب الرصيد الافتتاحي وتتبع حركات الإيداع والصرف والتحويل | صلاحية `view_vault_transactions` مع عزل الفروع | تقرير خزينة منفرد فقط، يفتقر إلى الرؤية الموحدة لكافة الخزائن بالمدرسة | **REUSE & ENHANCE**: استيعاب معادلة الرصيد الافتتاحي ودمجها بمركز التقارير |
| **InstallmentsReport** (`InstallmentsReport.php`) | `Installment` | جلب الأقساط المتأخرة والقريبة وحساب أيام التأخير | عامة للموظفين المصرح لهم | لم تكن تفصل بدقة بين حالة السداد وحالة الاستحقاق (تمت معالجتها بالنموذج) | **REUSE & INTEGRATE**: تم ربط بياناته بالمركز الموحد |
| **AllInstallmentsReport** (`AllInstallmentsReport.php`) | `Installment`, `Enrollment`, `Course` | استعراض الأقساط بمحددات الكورس والمجموعة | عامة للموظفين المصرح لهم | افتقار إعمار الديون (Aging buckets) المقسمة على فترات 1-7 و 8-30 | **ENHANCE**: تمت إضافة أوعية إعمار الديون بالكامل بالمركز الموحد |
| **InstallmentsDueToday / Week / Month** | `InstallmentDueReportService` | جداول أقساط اليوم والأسبوع والشهر | `InstallmentDueReportAccess` | تقارير قائمة ثابتة ممتازة للتحصيل اليومي | **REUSE & INTEGRATE**: ربطها المباشر من خلال فلاتر المركز الموحد |
| **AcademicFollowUpCenter** (`AcademicFollowUpCenter.php`) | `AcademicFollowUpService`, `StudentFollowUpTask`, `StudentFollowUpContact` | متابعة حالات الغياب، تكرار الغياب، الواجبات المتأخرة | مخصصة لمسؤولي المتابعة والمشرفين | ممتازة تشغيلياً لحالات التواصل ولا يجب تحويلها لإجراءات تلقائية | **REUSE**: الحفاظ الكامل على استقلالية مهام المتابعة |
| **SupervisionReports** (`AdminSupervisionReportResource.php` و `EducationalSupervisionService`) | `SupervisionReport`, `SupervisoryObservation`, `InstructorEvaluation` | حساب مؤشرات الجلسات والحضور والتقييمات | للمشرفين والإدارة العامة | تتطلب اعتماد المشرف التربوي ولا تنشر تلقائياً | **REUSE & STRICT PROTECTION**: حماية آلية المسودة والاعتماد |

### 3. قرارات التصنيف المعتمدة
1. **عدم هدم أو تكرار أي كود تشغيلي سليم**: تم الاحتفاظ بجميع نماذج وخدمات الإلغاء (`EnrollmentCancellationService`)، التسويات، التحصيلات، وتوليد مسودات الإشراف.
2. **المركزية في العرض والربط**: تم إنشاء مركز التقارير الموحد (`ReportingCenterPage`) ليكون المرجع الشامل لمتخذي القرار دون تشتت، مع توفير اختصارات سياقية من الشاشات الأصلية.
