# نظام إدارة المستشفى متعدد المؤسسات - ملخص النظام

## 📋 نظرة عامة
تطبيق ويب شامل لإدارة المستشفيات ومراكز الرعاية الصحية مع دعم كامل للعديد من المؤسسات (Multi-Tenant) مع نظام متقدم للأدوار والصلاحيات.

## 🎯 المميزات الرئيسية

### 1️⃣ نظام متعدد المؤسسات (Multi-Tenancy)
- ✅ عزل كامل للبيانات بين المؤسسات
- ✅ كل مؤسسة لها بيانات مستقلة تماماً
- ✅ Global Scope على جميع الموارد الحساسة
- ✅ تسجيل الوصول التلقائي للمؤسسة الحالية

### 2️⃣ إدارة المرضى
- ✅ نموذج شامل لبيانات المريض
- ✅ تاريخ طبي مفصل (RichEditor)
- ✅ معلومات الحساسية والأدوية الحالية
- ✅ ربط مع الجنسيات والأقسام
- ✅ مراقبة الحالة (نشط/غير نشط)

### 3️⃣ إدارة الحجوزات
- ✅ ربط الحجز بـ (مريض + طبيب + قسم)
- ✅ تاريخ ووقت الحجز
- ✅ التشخيص الأولي
- ✅ حالة الحجز والمتابعة

### 4️⃣ إدارة الأطباء
- ✅ بيانات الطبيب الشاملة
- ✅ التخصص الطبي
- ✅ الـ Bio الشخصي
- ✅ ربط مع الأقسام
- ✅ حالة النشاط

### 5️⃣ إدارة الأقسام والجنسيات
- ✅ إدارة أقسام المستشفى
- ✅ إدارة الجنسيات
- ✅ أكواد تعريفية
- ✅ أوصاف تفصيلية

### 6️⃣ نظام الأدوار والصلاحيات
- ✅ **Super Admin**: مدير النظام الكامل
- ✅ **Admin**: مالك المؤسسة (يُعيّن تلقائياً)
- ✅ **User**: مستخدم عادي (وصول محدود)
- ✅ 63 صلاحية موزعة على 9 موارد
- ✅ تعيين تلقائي عند التسجيل الجديد

### 7️⃣ دعم اللغات
- ✅ اللغة العربية (الواجهة الرئيسية)
- ✅ اللغة الإنجليزية
- ✅ دعم التبديل السهل بين اللغات
- ✅ 200+ مفتاح ترجمة

### 8️⃣ واجهة المستخدم
- ✅ تخطيط 2-column للنماذج
- ✅ تنظيم الموارد في مجموعات ملاحة
- ✅ جداول بيانات متقدمة مع الفلترة
- ✅ RichEditor لحقول النصوص الطويلة

## 📁 هيكل المشروع

```
Almobtaker Alaraby/
├── app/
│   ├── Filament/Admin/Resources/       # موارد Filament
│   │   ├── Patients/                   # إدارة المرضى
│   │   ├── Reservations/               # إدارة الحجوزات
│   │   ├── Doctors/                    # إدارة الأطباء
│   │   ├── Wards/                      # إدارة الأقسام
│   │   ├── Nationalities/              # إدارة الجنسيات
│   │   ├── Settings/                   # الإعدادات
│   │   ├── Users/                      # إدارة المستخدمين
│   │   └── Tenants/                    # إدارة المؤسسات
│   ├── Models/                         # نماذج Eloquent
│   ├── Listeners/
│   │   └── AssignAdminRoleToNewUser.php
│   └── Providers/
│       ├── EventServiceProvider.php
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/                     # هجرات الجداول
│   └── seeders/
│       ├── RoleAndPermissionSeeder.php # إنشاء الأدوار والصلاحيات
│       └── DatabaseSeeder.php
├── lang/
│   ├── ar/                             # الترجمات العربية
│   └── en/                             # الترجمات الإنجليزية
├── resources/
│   ├── css/                            # أنماط CSS
│   ├── js/                             # سكريبتات JavaScript
│   └── views/                          # عروض Blade
├── routes/                             # مسارات التطبيق
├── storage/                            # التخزين المؤقت والملفات
├── tests/                              # الاختبارات
└── public/                             # الملفات العامة

```

## 🔐 نظام الأدوار والصلاحيات

### الأدوار الثلاثة:

| الدور | الوصول | الصلاحيات | التعيين |
|------|--------|----------|--------|
| **Super Admin** | النظام كاملاً | 63/63 | يدوي |
| **Admin** | كل شيء ما عدا Tenant/Role | 49/63 | تلقائي |
| **User** | موارد أساسية فقط | 42/63 | يدوي |

### الموارد والعمليات:

```
كل مورد يحتوي على 7 عمليات:
1. ViewAny    - عرض القائمة
2. View       - عرض التفاصيل
3. Create     - إنشاء
4. Update     - تعديل
5. Delete     - حذف
6. Restore    - استرجاع
7. ForceDelete - حذف نهائي
```

## 🚀 التثبيت والتشغيل

### المتطلبات:
- PHP 8.2+
- Laravel 12+
- MySQL 8.0+
- Composer

### خطوات التثبيت:

```bash
# 1. استنساخ المشروع
git clone <repository>
cd Almobtaker Alaraby

# 2. تثبيت الحزم
composer install
npm install

# 3. إنشاء ملف .env
cp .env.example .env
php artisan key:generate

# 4. إعداد قاعدة البيانات
php artisan migrate

# 5. تشغيل Seeders
php artisan db:seed
php artisan db:seed --class=RoleAndPermissionSeeder

# 6. بناء Assets
npm run build

# 7. تشغيل الخادم
php artisan serve
```

الوصول إلى التطبيق: `http://127.0.0.1:8000`

## 📊 جداول قاعدة البيانات

### الجداول الرئيسية:
- `users` - المستخدمين
- `tenants` - المؤسسات
- `patients` - المرضى
- `doctors` - الأطباء
- `wards` - الأقسام
- `nationalities` - الجنسيات
- `reservations` - الحجوزات
- `settings` - الإعدادات
- `authentication_logs` - سجل الدخول

### جداول Spatie Permission:
- `roles` - الأدوار
- `permissions` - الصلاحيات
- `role_has_permissions` - ربط الأدوار بالصلاحيات
- `model_has_roles` - ربط المستخدمين بالأدوار
- `model_has_permissions` - ربط المستخدمين بالصلاحيات

## 🔧 التكوين الأساسي

### المتغيرات البيئية (.env):
```env
APP_NAME="Almobtaker Alaraby Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Almobtaker Alaraby
DB_USERNAME=root
DB_PASSWORD=

FILAMENT_THEME=light
```

### اللغة الافتراضية:
```php
// config/app.php
'locale' => 'ar',
'fallback_locale' => 'en',
```

## 📚 المكتبات الرئيسية

- **Laravel**: إطار عمل PHP
- **Filament**: واجهة إدارة حديثة
- **Spatie Permission**: إدارة الأدوار والصلاحيات
- **Laravel Activity Log**: تسجيل الأنشطة
- **Vite**: بناء Assets

## 🧪 الاختبار

```bash
# تشغيل جميع الاختبارات
php artisan test

# تشغيل اختبار محدد
php artisan test --filter=PatientTest

# مع تغطية الكود
php artisan test --coverage
```

## 📖 الملفات الموثقة

- `ROLES_AND_PERMISSIONS_GUIDE.md` - دليل الأدوار والصلاحيات
- `ROLES_SYSTEM_IMPLEMENTATION.md` - ملخص التنفيذ

## 🐛 حل المشاكل الشائعة

### مشكلة: الصلاحيات لا تعمل
**الحل**: 
```bash
php artisan cache:clear
php artisan config:clear
```

### مشكلة: لم يتم تعيين الدور تلقائياً
**الحل**:
- تأكد من تفعيل EventServiceProvider
- تحقق من AssignAdminRoleToNewUser Listener

### مشكلة: بيانات tenant تظهر بشكل خاطئ
**الحل**:
- تأكد من وجود `tenant_id` على النموذج
- تحقق من Global Scope التطبيق

## 🔒 الأمان

### معايير الأمان المطبقة:
- ✅ عزل البيانات بين المؤسسات
- ✅ التحقق من الصلاحيات على كل عملية
- ✅ تشفير كلمات المرور (bcrypt)
- ✅ CSRF Protection
- ✅ تسجيل جميع الأنشطة (Activity Log)
- ✅ منع Super Admin من تعديل بيانات المرضى

## 📞 الدعم والمساهمة

للإبلاغ عن الأخطاء أو المساهمة:
1. افتح Issue جديدة
2. وصف المشكلة بوضوح
3. قدم خطوات لإعادة الإنتاج

## 📝 الترخيص

هذا المشروع مرخص تحت رخصة MIT.

---

## ✨ التحديثات الأخيرة

- ✅ تطبيق نظام الأدوار والصلاحيات الكامل
- ✅ تعيين تلقائي للأدوار عند التسجيل
- ✅ توثيق شامل لنظام الأدوار
- ✅ تحديث جميع Resources لاستخدام الصلاحيات

---

**آخر تحديث**: 2 ديسمبر 2025
**الإصدار**: 1.0.0
**الحالة**: ✅ جاهز للإنتاج
