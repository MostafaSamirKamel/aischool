# خطة تنفيذ نظام الصلاحيات والأدوار
## Roles & Permissions Implementation Plan

---

## الوضع الحالي (Current Status)

### ✅ موجود ومكتمل:
1. **Admin Panel** للإدارة
2. **Super Admin** - وصول كامل لكل الفروع
3. **Multi-tenancy** - عزل البيانات بين الفروع
4. **عمود وفلتر الفرع** لكل الجداول

### ⚠️ يحتاج تنظيم:
1. **صلاحيات Branch Admin** - يرى كل بيانات فرعه فقط
2. **دور Instructor** - صلاحيات محدودة للمدرب

### ❌ غير موجود:
1. **Student Panel** - لوحة تحكم الطلاب
2. **Parent Panel** - لوحة تحكم أولياء الأمور
3. **Instructor Panel** أو تحديد صلاحياته في Admin Panel

---

## المرحلة 1: تنظيم صلاحيات Admin Panel ✅

### الخطوة 1.1: تفعيل نظام الصلاحيات لـ Spatie
```bash
# الباكدج موجود بالفعل في composer.json
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### الخطوة 1.2: إنشاء الـ Permissions

**الملف:** `database/seeders/PermissionsSeeder.php`

الصلاحيات المطلوبة:
```php
// للـ Students
view_any_student, create_student, update_student, delete_student

// للـ Instructors  
view_any_instructor, create_instructor, update_instructor

// للـ Enrollments
view_any_enrollment, create_enrollment, update_enrollment, delete_enrollment

// للـ Groups
view_any_group, view_own_groups (للمدرب)

// للـ Attendance
view_any_attendance, create_attendance, update_attendance
view_own_attendance (للمدرب - مجموعاته فقط)

// للـ Grades
view_any_grade, create_grade, update_grade
view_own_grades (للمدرب - طلابه فقط)

// للـ Payments
view_any_payment, create_payment, update_payment, delete_payment

// للـ Reports
view_all_reports (super_admin)
view_branch_reports (admin)

// للـ Settings
manage_system_settings (super_admin)
manage_branch_settings (admin)
```

### الخطوة 1.3: إنشاء الـ Roles

**الأدوار المطلوبة:**
1. `super_admin` - الإدارة العليا
2. `admin` - مشرف الفرع
3. `instructor` - المدرب
4. `student` - الطالب
5. `parent` - ولي الأمر

### الخطوة 1.4: تطبيق Policies على الـ Resources

**مثال: StudentResource**
```php
public static function canViewAny(): bool
{
    return auth()->user()->can('view_any_student');
}

public static function canCreate(): bool
{
    return auth()->user()->can('create_student');
}

// في الـ Query
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    
    // Super admin يرى كل شيء
    if (auth()->user()->is_super_admin) {
        return $query;
    }
    
    // Admin يرى فرعه فقط
    return $query->where('tenant_id', auth()->user()->tenant_id);
}
```

### الخطوة 1.5: تطبيق صلاحيات Instructor

**للمجموعات:**
```php
// في GroupResource
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    
    $user = auth()->user();
    
    if ($user->is_super_admin) {
        return $query;
    }
    
    if ($user->hasRole('instructor')) {
        // المدرب يرى مجموعاته فقط
        return $query->where('instructor_id', $user->instructor?->id);
    }
    
    // Admin يرى كل مجموعات فرعه
    return $query->where('tenant_id', $user->tenant_id);
}
```

**للحضور:**
```php
// في AttendanceSessionResource
public static function canCreate(): bool
{
    return auth()->user()->can('create_attendance');
}

public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();
    
    if ($user->hasRole('instructor')) {
        // المدرب يرى حضور مجموعاته فقط
        return $query->whereHas('group', function($q) use ($user) {
            $q->where('instructor_id', $user->instructor?->id);
        });
    }
    
    return $query->where('tenant_id', $user->tenant_id);
}
```

---

## المرحلة 2: إنشاء Student Panel 📱

### الخطوة 2.1: إنشاء الـ Panel
```bash
php artisan make:filament-panel student
```

### الخطوة 2.2: تكوين الـ Panel

**الملف:** `app/Providers/Filament/StudentPanelProvider.php`

```php
->authGuard('web')
->path('student')
->login()
->colors([...])
->navigationGroups([
    'الدراسة',
    'الحضور والدرجات',
    'الحسابات',
])
```

### الخطوة 2.3: الموارد المطلوبة

1. **Profile** - الملف الشخصي
2. **MyCourses** - كورساتي
3. **MySchedule** - جدولي الدراسي
4. **MyAttendance** - حضوري
5. **MyGrades** - درجاتي
6. **MyPayments** - مدفوعاتي

**كل الموارد read-only للطالب**

---

## المرحلة 3: إنشاء Parent Panel 👨‍👩‍👧

### الخطوة 3.1: إنشاء الـ Panel
```bash
php artisan make:filament-panel parent
```

### الخطوة 3.2: الموارد المطلوبة

1. **Profile** - ملفي الشخصي
2. **MyChildren** - أبنائي
3. **ChildrenGrades** - درجات الأبناء
4. **ChildrenAttendance** - حضور الأبناء
5. **ChildrenPayments** - مدفوعات الأبناء
6. **ChildrenSchedule** - جداول الأبناء
7. **Messages** - التواصل مع الإدارة

---

## المرحلة 4: التقارير المتقدمة 📊

### الخطوة 4.1: إنشاء صفحات التقارير

**في:** `app/Filament/Pages/Reports/`

1. **StudentsReport** - تقرير الطلاب المسجلين
   - فلاتر: المسار، الكورس، المجموعة، التاريخ، اسم الطالب
   - تصدير: Excel, PDF
   - طباعة

2. **EnrollmentsReport** - تقرير التسجيلات
   - فلاتر ذكية حسب الفترة والحالة

3. **AttendanceReport** - تقرير الحضور والغياب
   - حسب الطالب، المجموعة، الفترة

4. **RevenuesReport** - تقرير الإيرادات
   - حسب الفترة، الفرع، نوع الإيراد

5. **ExpensesReport** - تقرير المصروفات
   - حسب الفترة، الفرع، نوع المصروف

6. **FinancialReport** - تقرير مالي شامل
   - الإيرادات vs المصروفات
   - الأرباح والخسائر

### الخطوة 4.2: تفعيل التصدير

**استخدام:**
- `maatwebsite/excel` للـ Excel
- `barryvdh/laravel-dompdf` للـ PDF
- Filament Actions للطباعة

---

## الأولويات والتنفيذ

### 🔴 عالي الأهمية (الأسبوع الأول):
1. ✅ تنظيم صلاحيات Admin Panel
2. ✅ تطبيق Policies على Resources
3. ✅ صلاحيات Instructor المحدودة

### 🟡 متوسط الأهمية (الأسبوع الثاني):
1. 📱 إنشاء Student Panel
2. 📱 إنشاء Parent Panel
3. 📊 صفحات التقارير الأساسية

### 🟢 منخفض الأهمية (الأسبوع الثالث):
1. 📊 تقارير متقدمة مع فلاتر ذكية
2. 📥 التصدير PDF/Excel
3. 🖨️ الطباعة
4. 💬 نظام الرسائل

---

## هل تريد البدء في التنفيذ؟

اختر واحدة من الخيارات:

### الخيار 1: تنظيم صلاحيات المدرب (Instructor) 👨‍🏫
- إنشاء الـ Permissions و Roles
- تطبيق Policies على الموارد
- تحديد ما يراه المدرب فقط

### الخيار 2: إنشاء Student Panel 📱
- Panel جديد للطلاب
- الموارد المطلوبة (read-only)

### الخيار 3: إنشاء Parent Panel 👨‍👩‍👧
- Panel جديد لأولياء الأمور
- متابعة الأبناء

### الخيار 4: صفحات التقارير المتقدمة 📊
- تقارير الطلاب والتسجيلات
- تقارير الحضور
- تقارير المصروفات والإيرادات
- التصدير والطباعة

**أيهم تريد البدء به؟**
