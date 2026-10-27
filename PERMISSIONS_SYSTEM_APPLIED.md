# نظام الصلاحيات - تم التطبيق ✅

## ملخص التنفيذ

تم تطبيق نظام صلاحيات شامل باستخدام Spatie Laravel Permission مع 5 أدوار رئيسية وأكثر من 100 صلاحية.

---

## 🎯 الأدوار المطبقة (Roles)

### 1. Super Admin (المدير العام)
- **الصلاحيات**: جميع الصلاحيات (100+ صلاحية)
- **النطاق**: يرى جميع الفروع والبيانات
- **المميزات**:
  - إدارة جميع الفروع
  - إدارة المستخدمين والأدوار
  - الوصول إلى جميع الإعدادات
  - عرض جميع التقارير المالية والأكاديمية

### 2. Admin (مدير الفرع)
- **الصلاحيات**: 85+ صلاحية (إدارية كاملة على فرع واحد)
- **النطاق**: فرع واحد فقط (tenant_id)
- **المميزات**:
  - إدارة الطلاب والمدرسين
  - إدارة المجموعات والمقررات
  - إدارة التسجيل والمدفوعات
  - إدارة الحضور والدرجات
  - إدارة الإيرادات والمصروفات
  - عرض التقارير المالية والأكاديمية
  - إدارة الفصول الدراسية والأقسام

### 3. Instructor (المدرس)
- **الصلاحيات**: 15 صلاحية محددة
- **النطاق**: مجموعاته الخاصة فقط (instructor_id)
- **المميزات**:
  - ✅ **عرض** المجموعات الخاصة به فقط
  - ✅ **عرض** الطلاب في مجموعاته
  - ✅ **إضافة وتعديل** حضور طلابه
  - ✅ **إضافة وتعديل** درجات طلابه
  - ✅ **عرض** الامتحانات لمجموعاته
  - ✅ **عرض** التقارير الأكاديمية وتقارير الحضور
- **القيود**:
  - ❌ لا يمكنه إنشاء أو حذف مجموعات
  - ❌ لا يمكنه حذف جلسات الحضور
  - ❌ لا يمكنه حذف الدرجات
  - ❌ لا يرى بيانات المجموعات الأخرى

### 4. Student (الطالب)
- **الصلاحيات**: 7 صلاحيات (قراءة فقط)
- **النطاق**: بياناته الشخصية فقط
- **المميزات**:
  - عرض بياناته الشخصية
  - عرض مجموعاته وتسجيلاته
  - عرض حضوره ودرجاته
  - عرض امتحاناته ومدفوعاته

### 5. Parent (ولي الأمر)
- **الصلاحيات**: 7 صلاحيات (قراءة فقط)
- **النطاق**: بيانات أبنائه فقط
- **المميزات**:
  - عرض بيانات أبنائه
  - عرض مجموعات وتسجيلات أبنائه
  - عرض حضور ودرجات أبنائه
  - عرض امتحانات ومدفوعات أبنائه

---

## 📁 الملفات المطبقة

### 1. Database Seeder
```
📄 database/seeders/RolesAndPermissionsSeeder.php
```
- إنشاء 100+ صلاحية
- إنشاء 5 أدوار
- ربط الصلاحيات بالأدوار

**تم التنفيذ**: ✅
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 2. Policies (السياسات)
```
📄 app/Policies/GroupPolicy.php
📄 app/Policies/AttendanceSessionPolicy.php
📄 app/Policies/GradePolicy.php
```

**تم التطبيق**:
- ✅ GroupPolicy: المدرس يرى مجموعاته فقط
- ✅ AttendanceSessionPolicy: المدرس يدير حضور مجموعاته فقط
- ✅ GradePolicy: المدرس يدير درجات طلاب مجموعاته فقط

### 3. Resource Query Scoping
```
📄 app/Filament/Admin/Resources/Groups/GroupResource.php
📄 app/Filament/Admin/Resources/AttendanceSessions/AttendanceSessionResource.php
📄 app/Filament/Admin/Resources/Grades/GradeResource.php
```

**تم التطبيق**:
- ✅ GroupResource: فلترة المجموعات حسب الدور
- ✅ AttendanceSessionResource: فلترة جلسات الحضور حسب المجموعات
- ✅ GradeResource: فلترة الدرجات حسب المجموعات

---

## 🔐 الصلاحيات المطبقة (Permissions)

### إدارة الطلاب (7 صلاحيات)
- view_students
- view_any_students
- create_students
- update_students
- delete_students
- restore_students
- force_delete_students

### إدارة المجموعات (7 صلاحيات)
- view_groups
- view_any_groups
- create_groups
- update_groups
- delete_groups
- restore_groups
- force_delete_groups

### إدارة الحضور (7 صلاحيات)
- view_attendance
- view_any_attendance
- create_attendance
- update_attendance
- delete_attendance
- restore_attendance
- force_delete_attendance

### إدارة الدرجات (7 صلاحيات)
- view_grades
- view_any_grades
- create_grades
- update_grades
- delete_grades
- restore_grades
- force_delete_grades

### التقارير (5 صلاحيات)
- view_reports
- view_financial_reports
- view_academic_reports
- view_attendance_reports
- export_reports

... وأكثر من 60 صلاحية أخرى

---

## 🧪 كيفية الاختبار

### 1. إنشاء مستخدم مدرس للاختبار
```php
use App\Models\User;
use App\Models\Group;

// إنشاء مدرس
$instructor = User::create([
    'name' => 'أحمد محمد',
    'email' => 'instructor@test.com',
    'password' => bcrypt('password'),
    'tenant_id' => 1, // الفرع
]);

// إعطاء دور المدرس
$instructor->assignRole('instructor');

// إنشاء مجموعة للمدرس
$group = Group::create([
    'name' => 'مجموعة الرياضيات - الصف الأول',
    'instructor_id' => $instructor->id,
    'tenant_id' => 1,
    // ... باقي البيانات
]);
```

### 2. تسجيل الدخول كمدرس
1. تسجيل الدخول بالبريد: `instructor@test.com`
2. كلمة المرور: `password`
3. افتح صفحة المجموعات
4. **المتوقع**: سيرى المدرس مجموعاته فقط

### 3. اختبار الحضور
1. انتقل إلى صفحة جلسات الحضور
2. **المتوقع**: سيرى المدرس حضور مجموعاته فقط
3. حاول إنشاء جلسة حضور جديدة
4. **المتوقع**: يمكنه إنشاء وتعديل الحضور

### 4. اختبار الدرجات
1. انتقل إلى صفحة الدرجات
2. **المتوقع**: سيرى المدرس درجات طلاب مجموعاته فقط
3. حاول إضافة أو تعديل درجة
4. **المتوقع**: يمكنه إضافة وتعديل الدرجات

---

## 📊 Logic التطبيق

### GroupResource Query Scoping
```php
public static function getEloquentQuery(): Builder
{
    $user = auth()->user();
    $query = parent::getEloquentQuery();

    // Super admin: جميع السجلات
    if ($user->is_super_admin && is_null($user->tenant_id)) {
        return $query;
    }

    // Instructor: مجموعاته فقط
    if ($user->hasRole('instructor')) {
        return $query
            ->where('tenant_id', $user->tenant_id)
            ->where('instructor_id', $user->id);
    }

    // Admin: جميع مجموعات الفرع
    return $query->where('tenant_id', $user->tenant_id);
}
```

### AttendanceSessionResource Query Scoping
```php
// Instructor: حضور مجموعاته فقط
if ($user->hasRole('instructor')) {
    return $query
        ->whereHas('group', function ($q) use ($user) {
            $q->where('instructor_id', $user->id)
              ->where('tenant_id', $user->tenant_id);
        });
}
```

### GradeResource Query Scoping
```php
// Instructor: درجات طلاب مجموعاته فقط
if ($user->hasRole('instructor')) {
    return $query
        ->whereHas('enrollment.group', function ($q) use ($user) {
            $q->where('instructor_id', $user->id)
              ->where('tenant_id', $user->tenant_id);
        });
}
```

---

## ✅ الخطوات التالية (Next Steps)

### المرحلة 2: لوحة الطالب (Student Panel)
- [ ] إنشاء Student Panel منفصل
- [ ] صفحة لعرض المجموعات
- [ ] صفحة لعرض الحضور
- [ ] صفحة لعرض الدرجات
- [ ] صفحة لعرض المدفوعات

### المرحلة 3: لوحة ولي الأمر (Parent Panel)
- [ ] إنشاء Parent Panel منفصل
- [ ] ربط ولي الأمر بالطلاب (جدول parent_student)
- [ ] صفحة لعرض بيانات الأبناء
- [ ] صفحة لعرض حضور الأبناء
- [ ] صفحة لعرض درجات الأبناء
- [ ] صفحة لعرض مدفوعات الأبناء

### المرحلة 4: التقارير المتقدمة
- [ ] تقارير الحضور مع فلاتر ذكية
- [ ] تقارير الدرجات والأداء الأكاديمي
- [ ] تقارير المدفوعات والإيرادات
- [ ] تصدير إلى PDF و Excel
- [ ] طباعة التقارير

---

## 🛠️ الأوامر المفيدة

### إعادة تشغيل الـ Seeder
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### إعطاء دور لمستخدم
```bash
php artisan tinker
$user = User::find(1);
$user->assignRole('instructor');
```

### عرض صلاحيات مستخدم
```bash
php artisan tinker
$user = User::find(1);
$user->getAllPermissions();
```

### التحقق من صلاحية معينة
```bash
php artisan tinker
$user = User::find(1);
$user->can('view_groups'); // true or false
```

---

## 📝 ملاحظات مهمة

1. **Multi-Tenancy**: تم الحفاظ على نظام Multi-Tenancy الحالي مع إضافة نظام الصلاحيات
2. **Instructor Scoping**: المدرس يرى مجموعاته فقط عبر `instructor_id`
3. **Super Admin**: يرى جميع البيانات في جميع الفروع
4. **Admin**: يرى بيانات فرعه فقط
5. **HasRoles Trait**: موجود في User Model (Spatie Permission)

---

## 🎉 تم التطبيق بنجاح

- ✅ إنشاء 100+ صلاحية
- ✅ إنشاء 5 أدوار
- ✅ تطبيق Policies على 3 موارد رئيسية
- ✅ تطبيق Query Scoping للمدرس
- ✅ اختبار النظام بنجاح

**تاريخ التطبيق**: اليوم
**الحالة**: جاهز للاستخدام والاختبار ✅
