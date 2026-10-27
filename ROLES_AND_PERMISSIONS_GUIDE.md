# نظام الأدوار والصلاحيات (Role & Permission System)

## نظرة عامة
تم تطبيق نظام صلاحيات متقدم بثلاث مستويات تدرجية باستخدام مكتبة Spatie Laravel Permission.

## المستويات الثلاث للأدوار

### 1. Super Admin (مدير النظام الكامل)
- **الدور**: `super_admin`
- **الوصول**: جميع الموارد والعمليات
- **الصلاحيات**: 63 صلاحية (9 موارد × 7 عمليات)
- **الملاحظات**: 
  - لا يمكنهم إنشاء أو تعديل أو حذف بيانات المرضى والحجوزات
  - يرون جميع المستخدمين والمؤسسات

### 2. Admin (مالك المؤسسة / مسؤول المؤسسة)
- **الدور**: `admin`
- **التعيين**: تُعيّن تلقائياً لكل مستخدم جديد عند التسجيل
- **الوصول**: جميع الموارد ما عدا:
  - ❌ Tenant (المؤسسات)
  - ❌ Role (الأدوار والصلاحيات)
- **الصلاحيات**: 49 صلاحية (7 موارد × 7 عمليات)
  - Patient, Reservation, Doctor, Ward, Nationality, Setting, User

### 3. User (مستخدم عادي)
- **الدور**: `user`
- **الوصول**: الموارد الأساسية فقط
- **الموارد المتاحة**:
  - ✅ Patient (المرضى)
  - ✅ Reservation (الحجوزات)
  - ✅ Doctor (الأطباء)
  - ✅ Ward (الأقسام)
  - ✅ Nationality (الجنسيات)
  - ✅ Setting (الإعدادات)
- **الموارد غير المتاحة**:
  - ❌ Tenant (المؤسسات)
  - ❌ Role (الأدوار)
  - ❌ User (المستخدمين)
- **الصلاحيات**: 42 صلاحية (6 موارد × 7 عمليات)

## الموارد (Resources)

9 موارد رئيسية في النظام:

1. **Patient** - المرضى
2. **Reservation** - الحجوزات
3. **Doctor** - الأطباء
4. **Ward** - الأقسام
5. **Nationality** - الجنسيات
6. **Setting** - الإعدادات
7. **Tenant** - المؤسسات (للمسؤول فقط)
8. **User** - المستخدمين (للمسؤول فقط)
9. **Role** - الأدوار والصلاحيات (للمسؤول فقط)

## العمليات (Actions)

كل مورد يحتوي على 7 عمليات أساسية:

| الكود | الاسم | الوصف |
|------|-------|-------|
| ViewAny | عرض القائمة | عرض قائمة الموارد |
| View | عرض تفاصيل | عرض تفاصيل مورد واحد |
| Create | إنشاء | إنشاء مورد جديد |
| Update | تعديل | تعديل مورد موجود |
| Delete | حذف | حذف مورد |
| Restore | استرجاع | استرجاع مورد محذوف |
| ForceDelete | حذف نهائي | حذف نهائي من قاعدة البيانات |

## جدول الصلاحيات

### Super Admin
✅ جميع الصلاحيات (63/63)

### Admin
✅ جميع الصلاحيات ما عدا:
- ❌ Tenant (كل العمليات)
- ❌ Role (كل العمليات)
- ✅ 49 صلاحية نشطة من 63

### User
✅ صلاحيات محدودة:
```
Patient:        ViewAny, View, Create, Update, Delete, Restore, ForceDelete
Reservation:    ViewAny, View, Create, Update, Delete, Restore, ForceDelete
Doctor:         ViewAny, View, (Read-Only بشكل فعلي)
Ward:           ViewAny, View, (Read-Only بشكل فعلي)
Nationality:    ViewAny, View, (Read-Only بشكل فعلي)
Setting:        ViewAny, View, (Read-Only بشكل فعلي)
❌ Tenant:      (بدون أي صلاحية)
❌ Role:        (بدون أي صلاحية)
❌ User:        (بدون أي صلاحية)
```

## تنفيذ النظام

### 1. Seeder (RoleAndPermissionSeeder)
يقوم بـ:
- إنشاء جميع الصلاحيات (63 صلاحية)
- إنشاء الأدوار الثلاثة
- ربط الصلاحيات بالأدوار المناسبة

**التشغيل**:
```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

### 2. Event Listener (AssignAdminRoleToNewUser)
يقوم بـ:
- استماع حدث التسجيل الجديد (Registered event)
- تعيين دور `admin` تلقائياً للمستخدمين الجدد
- استثناء المسؤولين الفائقين

**الموقع**:
```
app/Listeners/AssignAdminRoleToNewUser.php
```

**المسجل في**:
```
app/Providers/EventServiceProvider.php
```

### 3. Resource Authorization
تم تحديث جميع Filament Resources لاستخدام صلاحيات Spatie بدلاً من is_super_admin:

```php
public static function canCreate(): bool
{
    return auth()->user()->can('Create:Doctor');
}

public static function canEdit($record): bool
{
    return auth()->user()->can('Update:Doctor');
}

public static function canDelete($record): bool
{
    return auth()->user()->can('Delete:Doctor');
}
```

**الموارد المحدثة**:
- PatientResource
- ReservationResource
- DoctorResource
- WardResource
- NationalityResource
- SettingResource
- UserResource
- TenantResource

## التحكم بالوصول

### على مستوى Resource
كل resource يتحكم بـ:
- ✅ عرض الزر إظهار/إخفاء
- ✅ تفعيل/تعطيل الوصول إلى الصفحات
- ✅ التحكم بالعمليات المسموحة

### على مستوى Database
- جميع الموارد الحساسة لها `tenant_id`
- العوازل التلقائية (Global Scope) تضمن الفصل التام بين المؤسسات

## أمثلة الاستخدام

### التحقق من الصلاحية في الـ Code
```php
// في Blade
@can('Create:Patient')
    <button>إضافة مريض</button>
@endcan

// في الكود
if (auth()->user()->can('Update:Doctor')) {
    // السماح بالتعديل
}

// استخدام Gate
Gate::authorize('Delete:Ward');
```

### تعيين صلاحية لمستخدم
```php
$user->givePermissionTo('Create:Patient');
```

### تعيين دور لمستخدم
```php
$user->assignRole('admin');
```

## الأمان والملاحظات

1. **عزل البيانات**: كل مستخدم يرى بيانات مؤسسته فقط تلقائياً
2. **الصلاحيات الافتراضية**: المستخدمون الجدد يحصلون على دور `admin` تلقائياً
3. **المسؤولون الفائقون**: لا يمكنهم التعديل على بيانات المرضى والحجوزات
4. **إعادة التحميل**: استخدم `php artisan cache:clear` عند تغيير الصلاحيات

## الملفات المتعلقة

```
database/seeders/
├── RoleAndPermissionSeeder.php    # إنشاء الأدوار والصلاحيات
└── DatabaseSeeder.php              # استدعاء RoleAndPermissionSeeder

app/Listeners/
└── AssignAdminRoleToNewUser.php    # تعيين دور admin للمستخدمين الجدد

app/Providers/
└── EventServiceProvider.php         # تسجيل Listener

app/Filament/Admin/Resources/
├── Patients/PatientResource.php
├── Reservations/ReservationResource.php
├── Doctors/DoctorResource.php
├── Wards/WardResource.php
├── Nationalities/NationalityResource.php
├── Settings/SettingResource.php
├── Users/UserResource.php
└── Tenants/TenantResource.php
```

## ملخص التحديثات

✅ تم إنشاء RoleAndPermissionSeeder
✅ تم تعيين الصلاحيات للأدوار الثلاثة
✅ تم إنشاء AssignAdminRoleToNewUser Listener
✅ تم تحديث جميع Resources لاستخدام الصلاحيات
✅ تم تشغيل النظام وتأكيد العمل

النظام جاهز للاستخدام! 🎉
