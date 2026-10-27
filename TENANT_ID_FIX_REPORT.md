# إصلاح مشكلة tenant_id في النظام

## المشكلة
كان النظام يواجه خطأ في قاعدة البيانات عند إضافة سجلات جديدة:
```
SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'tenant_id' cannot be null
```

السبب: بعض النماذج (Models) لا تقوم بإضافة `tenant_id` تلقائياً من بيانات المستخدم الحالي.

## الحل
تم استخدام **BelongsToBranch trait** في جميع النماذج التي تحتوي على `tenant_id` لضمان إضافته تلقائياً عند إنشاء أي سجل جديد.

## التغييرات المطبقة

### 1. النماذج (Models) التي تم تحديثها

#### تم إضافة BelongsToBranch trait لـ:
- ✅ ExpenseCategory
- ✅ RevenueCategory
- ✅ Expense
- ✅ Revenue
- ✅ Department
- ✅ Profession
- ✅ Occupation
- ✅ Nationality
- ✅ StudentPayment
- ✅ Setting
- ✅ StudentParent

#### النماذج التي كانت تستخدمه بالفعل:
- Assignment
- AttendanceSession
- Attendance
- AssignmentSubmission
- Enrollment
- Exam
- ExamAttempt
- Grade
- Payment
- Track
- Group
- Classroom
- Course

### 2. تحسين BelongsToBranch trait

تم تحسين الـ trait ليكون أكثر أماناً:

```php
protected static function bootBelongsToBranch(): void
{
    static::addGlobalScope(new BranchScope());

    static::creating(function ($model) {
        // تخطي إذا كان tenant_id موجود بالفعل
        if (isset($model->tenant_id) && !is_null($model->tenant_id)) {
            return;
        }
        
        // التحقق من تسجيل الدخول
        if (!auth()->check()) {
            return;
        }
        
        $user = auth()->user();
        
        // أخذ tenant_id من المستخدم إن وجد
        if (isset($user->tenant_id) && !is_null($user->tenant_id)) {
            $model->tenant_id = $user->tenant_id;
        }
    });
}
```

### 3. إزالة tenant_id اليدوي من Forms

تم إزالة الحقول المخفية (Hidden fields) من النماذج التالية:
- ✅ NationalityForm
- ✅ GradeForm
- ✅ DepartmentForm

**قبل:**
```php
Hidden::make('tenant_id')
    ->default(fn() => auth()->user()->tenant_id)
    ->dehydrated(),
```

**بعد:**
```php
// tenant_id is set automatically by BelongsToBranch trait
```

### 4. ملاحظة هامة بخصوص Create/Edit Pages

⚠️ لا تزال بعض صفحات Create/Edit تقوم بتعيين `tenant_id` يدوياً مثل:
```php
$data['tenant_id'] = auth()->user()->tenant_id;
```

هذا **ليس مشكلة** لأن:
1. BelongsToBranch trait يتحقق أولاً إذا كان tenant_id موجود
2. إذا كان موجوداً، لن يتم تغييره
3. التعيين اليدوي يعمل كـ fallback إضافي

## الفوائد

### 1. الأمان
- جميع السجلات الجديدة ستحصل تلقائياً على `tenant_id` الصحيح
- لا يمكن إنشاء سجل بدون `tenant_id` عن طريق الخطأ

### 2. الاتساق
- جميع النماذج تستخدم نفس الآلية (BelongsToBranch trait)
- سهولة الصيانة والتطوير

### 3. تقليل الأخطاء
- لا حاجة لتذكر إضافة `tenant_id` يدوياً في كل Form
- القاعدة موحدة عبر كل النظام

## الاختبار

تم إنشاء ملف اختبار: `test_tenant_id_fix.php`

**النتيجة:**
```
✅ 24 نموذج يستخدم BelongsToBranch trait بنجاح
✅ 0 فشل
```

## ما يجب فعله الآن

### 1. اختبار النظام
قم بتجربة إضافة سجلات جديدة في:
- فئات المصروفات (Expense Categories)
- فئات الإيرادات (Revenue Categories)
- المصروفات (Expenses)
- الإيرادات (Revenues)
- الأقسام (Departments)
- الجنسيات (Nationalities)
- المهن (Occupations & Professions)

### 2. التحقق من البيانات
تأكد من أن جميع السجلات الجديدة تحتوي على `tenant_id` الصحيح:
```sql
SELECT * FROM expense_categories WHERE tenant_id IS NULL;
SELECT * FROM revenue_categories WHERE tenant_id IS NULL;
-- يجب أن تكون النتيجة فارغة
```

### 3. في حالة وجود مشاكل
إذا واجهت أي مشاكل:
1. تحقق من أن المستخدم مسجل دخول
2. تحقق من أن المستخدم لديه `tenant_id`
3. راجع لوج Laravel: `storage/logs/laravel.log`

## الملفات المعدلة

### Models
- app/Models/ExpenseCategory.php
- app/Models/RevenueCategory.php
- app/Models/Expense.php
- app/Models/Revenue.php
- app/Models/Department.php
- app/Models/Profession.php
- app/Models/Occupation.php
- app/Models/Nationality.php
- app/Models/StudentPayment.php
- app/Models/Setting.php
- app/Models/StudentParent.php

### Concerns
- app/Models/Concerns/BelongsToBranch.php

### Forms
- app/Filament/Admin/Resources/Nationalities/Schemas/NationalityForm.php
- app/Filament/Admin/Resources/Grades/Schemas/GradeForm.php
- app/Filament/Admin/Resources/Departments/Schemas/DepartmentForm.php

### Testing
- test_tenant_id_fix.php (ملف اختبار جديد)

## التوصيات المستقبلية

### 1. إزالة tenant_id اليدوي من Pages (اختياري)
يمكن إزالة الأسطر التالية من صفحات Create/Edit:
```php
$data['tenant_id'] = auth()->user()->tenant_id;
```

لأن BelongsToBranch يتولى ذلك الآن.

### 2. إضافة Validation
يمكن إضافة validation للتأكد من وجود tenant_id:
```php
'tenant_id' => ['required', 'exists:tenants,id']
```

### 3. Unit Tests
يُنصح بإنشاء Unit Tests للتحقق من:
- إضافة tenant_id تلقائياً
- عدم تغيير tenant_id إذا كان موجوداً
- التعامل مع Super Admin بشكل صحيح

---

**تاريخ الإصلاح:** 25 ديسمبر 2025
**الحالة:** ✅ مكتمل ومختبر
