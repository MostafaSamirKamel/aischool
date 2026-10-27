# تقرير الملفات المعدلة والرقع البرمجية
## AI SCHOOL — MODIFIED FILES & CODE PATCHES
**التاريخ والوقت:** 2026-10-20T23:26:00+03:00  
**المسؤول:** Senior Laravel Architect & Code Auditor  
**نوع التعديلات:** Minimal, Targeted, High-Security Maintenance Patches

---

## 1. قائمة الملفات المعدلة

1. [app/Filament/Admin/Pages/AcademicFollowUpCenter.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Filament/Admin/Pages/AcademicFollowUpCenter.php)
2. [app/Services/AcademicFollowUpService.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Services/AcademicFollowUpService.php)
3. [tests/Feature/AcademicFollowUpCenterTest.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/tests/Feature/AcademicFollowUpCenterTest.php)
4. [tests/Feature/LivewireFollowUpCenterSecurityTest.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/tests/Feature/LivewireFollowUpCenterSecurityTest.php) *(ملف جديد لاختبارات الأمان المباشرة)*

---

## 2. تفاصيل التعديلات والرقع البرمجية

### أولاً: `app/Services/AcademicFollowUpService.php`

#### 1. إزالة تجاوز الدور في دالة `canViewPhone`:
```diff
     public function canViewPhone(?User $user): bool
     {
         if (!$user) return false;
         return $user->is_super_admin
             || $user->hasRole('admin')
             || $user->hasRole('branch_manager')
-            || $user->hasRole('secretary')
             || $user->can('view_student_contact_numbers')
             || $user->can('view_student_phone');
     }
```
*السبب:* منع أي مستخدم يملك دور `secretary` من رؤية أرقام الهواتف ما لم يحصل صراحة على الصلاحية المستقلة `view_student_contact_numbers`.

#### 2. إضافة دوال التحقق من المجموعات والطلاب والمهام:
- `isGroupAuthorized(User $user, int $groupId, ?int $branchId = null): bool`
- `isStudentAuthorized(User $user, Student|int $student, ?int $branchId = null): bool`
- `isTaskAuthorized(User $user, StudentFollowUpTask|int $task): bool`
*السبب:* توفير محرك أمني مركزي وموحد لفحص مطابقة المجموعة للطالب والمهمة والمستخدم المصرح له.

#### 3. تقييد استعلامات ملف الطالب `getStudentProfile`:
- التحقق من تسجيل الطالب في مجموعة واحدة على الأقل مصرح بها للموظف المشاهد، وإرجاع `null` إذا كان غير مصرح له.
- تصفية الكورسات والمجموعات `enrolledCourses` لتشمل فقط المجموعات المصرح بها للمشاهد.
- تصفية سجل الحضور والغياب بشرط:
  `whereHas('session', fn($q) => $q->whereIn('group_id', $authorizedGroupIds))`
- تصفية تسليمات الواجبات بشرط:
  `whereHas('assignment', fn($q) => $q->whereIn('group_id', $authorizedGroupIds))`
- تصفية محاولات الامتحانات بشرط:
  `whereHas('exam', fn($q) => $q->whereIn('group_id', $authorizedGroupIds))`
- تصفية مهام المتابعة السابقة بشرط:
  `whereIn('group_id', $authorizedGroupIds)`
- تحويل السجلات إلى مصفوفات وهياكل آمنة بدلاً من إرسال كائنات Eloquent غير مقيدة للواجهة.

#### 4. توضيح عدادات التقرير اليومي `getDailyReportData`:
- إضافة `created_today_count` ليمثل إجمالي ما تم إنشاؤه اليوم.
- الحفاظ على `new_cases_count` ليمثل الحالات التي أُنشئت اليوم وما زالت بانتظار التواصل.

---

### ثانياً: `app/Filament/Admin/Pages/AcademicFollowUpCenter.php`

#### 1. دمج دالة الحماية المركزية `authorizeAction`:
```php
protected function authorizeAction(?string $permission = null): void
{
    $user = Auth::user();
    abort_unless(
        $user && $user->status === 'active' && (
            $user->is_super_admin
            || $user->hasRole('admin')
            || $user->hasRole('branch_manager')
            || ($permission ? ($user->can($permission) || $user->can('view_daily_follow_up_dashboard')) : $user->can('view_daily_follow_up_dashboard'))
        ),
        403,
        'غير مصرح لك بتنفيذ هذا الإجراء'
    );
}
```

#### 2. إصلاح `shouldRegisterNavigation` و `mount`:
إزالة الأدوار `supervisor`, `secretary`, `registration` من شرط التجاوز التلقائي، وإلزامية الصلاحية المستقلة `view_daily_follow_up_dashboard`.

#### 3. تأمين دوال الـ Modals والإجراءات الحساسة:
- `openStudentProfile`: فحص `isStudentAuthorized`. وفي حال عدم التصريح إغلاق النافذة وعدم جلب أي بيانات للـ Payload.
- `openFollowUpModal`: فحص `isStudentAuthorized` و `isGroupAuthorized`، وحجب أرقام الهواتف في الخواص العامة بـ `— (محجوب)` عند غياب الصلاحية.
- `saveFollowUpTask`: فحص الصلاحية فورياً قبل الحفظ، والتحقق من أهلية الموظف المكلف بالمهمة في نفس الفرع، وحماية التكرار الزمني بـ `DB::transaction`.
- `openContactLogModal`: فحص `isTaskAuthorized`، وحجب الأرقام إذا لم تكن مصرحة.
- `saveContactLog`: فحص `isTaskAuthorized` ومنع تكرار النقرات المزدوجة بفاصل 5 ثوانٍ، والتنفيذ داخل Transaction.
- `openReassignModal`: فحص `isTaskAuthorized`.
- `saveReassignment`: فحص `isTaskAuthorized`، والتحقق من أن الموظف البديل يتبع نفس الفرع ونشط، ومنع إعادة التكليف لنفس الموظف الحالي.
- `openHistoryModal`: فحص `isTaskAuthorized`، وحجب أرقام الهواتف في سجل الاتصالات الزمني عند عدم وجود صلاحية.
- `getContactLogsProperty` & `getStudentsListProperty`: حجب الهواتف في القوائم العامة لغير المصرح لهم.
- `getExamsProperty`, `getAssignmentsProperty`, `getRosterProperty`: تقييد القوائم بـ `in_array($this->selectedGroupId, $authorizedGroupIds)`.

---

### ثالثاً: `tests/Feature/AcademicFollowUpCenterTest.php`
- تحديث `setUp()` لمنح السكرتيرة الصلاحيات المستقلة الفعلية وتعيين المجموعة (أ) لها صراحة عبر `SupervisionGroupAssignment` ليتطابق الاختبار مع السياسة المعتمدة للنظام.

---

### رابعاً: `tests/Feature/LivewireFollowUpCenterSecurityTest.php`
- ملف اختبارات ميزة جديد تماماً يحتوي على 12 اختباراً أمنياً شاملاً يغطي:
  - عزل المجموعات داخل نفس الفرع.
  - محاولات التلاعب المباشر بمعرفات المهام والطلاب.
  - فحص حجب أرقام الهواتف في الخواص العامة.
  - فحص حجب كورس سكراتش عن سكرتيرة بايثون لطالب مشترك.
  - محاولات إعادة التكليف لموظف من فرع آخر.
  - منع التكرار العرضي لإنشاء المهام.
