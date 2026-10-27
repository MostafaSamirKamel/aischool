# 🏥 Almobtaker Alaraby Management System

نظام إدارة مستشفى متعدد المؤسسات باستخدام Laravel 12 و Filament 4

## 🚀 البدء السريع

### 1️⃣ التثبيت
```bash
# استنساخ المشروع
git clone <project-url>

# تثبيت المكتبات
composer install
npm install

# نسخ ملف البيئة
cp .env.example .env

# توليد المفتاح
php artisan key:generate

# تشغيل الترحيلات والبيانات الأولية
php artisan migrate:fresh --seed

# بناء الأصول
npm run build
```

### 2️⃣ تشغيل الخادم
```bash
php artisan serve

# في نافذة أخرى
npm run dev
```

اذهب إلى: `http://127.0.0.1:8000/admin`

---

## 👤 بيانات الدخول التجريبية

بعد تشغيل `migrate:fresh --seed`، ستجد المستخدمين التالين:

| البريد الإلكتروني | الدور | المؤسسة |
|------------------|------|--------|
| cairo-clinic@admin.local | Admin | عيادة القاهرة |
| alexandria-clinic@admin.local | Admin | عيادة الإسكندرية |
| giza-clinic@admin.local | Admin | عيادة الجيزة |

**ملاحظة:** كل مستخدم سيرى بيانات مؤسسته فقط!

---

## 📁 هيكل المشروع

```
app/
├── Models/
│   ├── User.php              (مع Global Scope)
│   ├── Tenant.php            (المؤسسة/العيادة)
│   └── Scopes/
│       └── TenantScope.php   (عزل البيانات)
├── Http/
│   └── Middleware/
│       └── SetTenantMiddleware.php  (تعيين السياق)
└── Filament/
    └── Admin/
        ├── Resources/
        │   ├── Users/
        │   └── Tenants/
        └── Widgets/
            └── TenantSwitcher.php

database/
├── migrations/
└── seeders/
    ├── RoleSeeder.php
    ├── TenantSeeder.php
    └── DatabaseSeeder.php
```

---

## ✨ الميزات الرئيسية

### 🔐 Multi-Tenancy
- ✅ عزل تام للبيانات بين المؤسسات
- ✅ Global Scope يفلتر تلقائياً
- ✅ Middleware يعيّن السياق

### 📊 Activity Logging
- ✅ تسجيل جميع التغييرات
- ✅ عرض من/إلى للبيانات
- ✅ معلومات المستخدم والتاريخ

### 🛡️ Security
- ✅ Spatie Permission للصلاحيات
- ✅ FilamentShield للدعم التلقائي
- ✅ Session-based tenant isolation

### 🌐 Localization
- ✅ اللغة الافتراضية: العربية
- ✅ دعم الإنجليزية
- ✅ نصوص مترجمة بالكامل

---

## 🔧 الأوامر المهمة

```bash
# إنشاء مستخدم جديد
php artisan tinker
>>> $user = User::factory()->create(['tenant_id' => 1])
>>> $user->assignRole('user')

# مسح وإعادة تعيين البيانات
php artisan migrate:fresh --seed

# عرض السجلات
tail -f storage/logs/laravel.log

# تشغيل الاختبارات
php artisan test
```

---

## 📚 المزيد من المعلومات

انظر `MULTI_TENANCY_GUIDE.md` للتفاصيل الكاملة عن:
- كيفية عمل عزل البيانات
- إضافة نماذج جديدة مع الـ Scope
- الصلاحيات والأدوار المتقدمة
- التوسع والإضافات المستقبلية

---

## 📝 الترخيص

MIT License

---

**آخر تحديث:** ديسمبر 2025  
**الإصدار:** 1.0.0
