# Filament 5 Upgrade & Student API - TODO Checklist

## ✅ تم إنجازه

### 1. Filament 5 Upgrade Preparation
- [x] تحديث composer.json إلى Filament 5
- [x] رفع PHP requirement إلى 8.3
- [x] تحديث جميع Filament plugins إلى v5
- [x] إنشاء دليل ترقية شامل ([FILAMENT_5_UPGRADE_GUIDE.md](FILAMENT_5_UPGRADE_GUIDE.md))
- [x] إنشاء دليل ترقية PHP ([PHP_8.3_UPGRADE_GUIDE.md](PHP_8.3_UPGRADE_GUIDE.md))
- [x] إنشاء ملف compatibility ([FILAMENT_5_COMPATIBILITY.md](FILAMENT_5_COMPATIBILITY.md))

### 2. Student API V1
- [x] StudentController مع Swagger annotations
- [x] AuthController مع Swagger annotations
- [x] StudentResource (JSON transformation)
- [x] EnrollmentResource
- [x] StoreStudentRequest (validation)
- [x] UpdateStudentRequest (validation)
- [x] API Routes (v1 prefix)
- [x] L5-Swagger configuration
- [x] API Documentation ([API_V1_STUDENT_GUIDE.md](API_V1_STUDENT_GUIDE.md))

## ⏳ الخطوات التالية (مطلوبة من المستخدم)

### الخطوة 1: ترقية PHP
```powershell
# تحقق من النسخة الحالية
php -v  # حالياً: 8.2.12

# اتبع دليل الترقية في:
# PHP_8.3_UPGRADE_GUIDE.md
```

### الخطوة 2: تحديث Dependencies
```powershell
cd "d:\My Work\Laravel\Ai School Ga"

# تحديث Composer dependencies
composer update

# أو تحديث Filament فقط
composer update filament/filament --with-all-dependencies
```

### الخطوة 3: تثبيت L5-Swagger
```powershell
# تثبيت الباكج
composer require "darkaonline/l5-swagger"

# نشر Config (اختياري - متوفر بالفعل)
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

# توليد Documentation
php artisan l5-swagger:generate
```

### الخطوة 4: تنظيف Cache
```powershell
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Filament specific
php artisan filament:upgrade
php artisan filament:cache-components
```

### الخطوة 5: اختبار API
```powershell
# تشغيل السيرفر
php artisan serve

# في نافذة جديدة - اختبار الـ API
curl -X POST http://localhost:8000/api/v1/auth/login `
  -H "Content-Type: application/json" `
  -d '{\"email\":\"admin@example.com\",\"password\":\"password\"}'
```

### الخطوة 6: عرض Swagger Documentation
```
http://localhost:8000/api/documentation
```

## 📋 Checklist تفصيلي

### Filament 5 Upgrade

- [ ] **PHP 8.3 installed**
  - [ ] تحميل PHP 8.3
  - [ ] تحديث PATH
  - [ ] تحقق من Extensions
  - [ ] اختبار: `php -v`

- [ ] **Composer Update**
  - [ ] `composer update`
  - [ ] حل أي تعارضات
  - [ ] تحقق من الباكجات المثبتة

- [ ] **Configuration Update**
  - [ ] تحديث config files
  - [ ] تنظيف cache
  - [ ] `php artisan filament:upgrade`

- [ ] **Testing**
  - [ ] Login/Logout
  - [ ] Navigation
  - [ ] Forms (Create/Edit)
  - [ ] Tables (List/Filter)
  - [ ] Widgets
  - [ ] Permissions (Shield)
  - [ ] Multi-tenancy

### Student API

- [ ] **Authentication**
  - [ ] تثبيت Sanctum
  - [ ] نشر migrations
  - [ ] `php artisan migrate`
  - [ ] اختبار Login endpoint

- [ ] **Swagger Setup**
  - [ ] تثبيت L5-Swagger
  - [ ] توليد documentation
  - [ ] اختبار: `http://localhost:8000/api/documentation`

- [ ] **Student Endpoints Testing**
  - [ ] GET /students
  - [ ] POST /students
  - [ ] GET /students/{id}
  - [ ] PUT /students/{id}
  - [ ] DELETE /students/{id}
  - [ ] GET /students/{id}/enrollments
  - [ ] GET /students/{id}/attendance

- [ ] **Flutter Integration**
  - [ ] تجهيز HTTP client
  - [ ] تجهيز Authentication
  - [ ] اختبار GET requests
  - [ ] اختبار POST requests

## 📁 الملفات المنشأة

### Configuration Files
1. `composer.json` - تم تحديثه
2. `config/l5-swagger.php` - جاهز للاستخدام
3. `routes/api.php` - تم تحديثه بـ V1 routes

### Controllers
1. `app/Http/Controllers/Api/V1/Controller.php` - Base controller
2. `app/Http/Controllers/Api/V1/AuthController.php` - Authentication
3. `app/Http/Controllers/Api/V1/StudentController.php` - Student CRUD

### Resources
1. `app/Http/Resources/StudentResource.php` - Student JSON structure
2. `app/Http/Resources/EnrollmentResource.php` - Enrollment JSON structure

### Requests (Validation)
1. `app/Http/Requests/Api/StoreStudentRequest.php` - Create validation
2. `app/Http/Requests/Api/UpdateStudentRequest.php` - Update validation

### Documentation Files
1. `FILAMENT_5_UPGRADE_GUIDE.md` - دليل الترقية الشامل
2. `PHP_8.3_UPGRADE_GUIDE.md` - دليل ترقية PHP
3. `FILAMENT_5_COMPATIBILITY.md` - قواعد التوافق
4. `API_V1_STUDENT_GUIDE.md` - دليل Student API
5. `UPGRADE_SUMMARY.md` - ملخص الترقية

## 🎯 الخطة الزمنية المقترحة

### اليوم 1: ترقية PHP و Composer
- ⏱️ 1-2 ساعة
- ترقية PHP إلى 8.3
- تشغيل `composer update`
- حل أي مشاكل

### اليوم 2: اختبار Filament 5
- ⏱️ 2-3 ساعات
- اختبار شامل لجميع Resources
- اختبار Pages و Widgets
- اختبار Permissions

### اليوم 3: إعداد API
- ⏱️ 1-2 ساعة
- تثبيت L5-Swagger
- اختبار Student endpoints
- توثيق أي مشاكل

### اليوم 4: Flutter Integration
- ⏱️ حسب الحاجة
- إنشاء API service في Flutter
- اختبار التكامل
- UI Implementation

## ⚠️ نقاط مهمة

1. **Backup أولاً**
   ```powershell
   # Database backup
   php artisan backup:run
   
   # Code backup
   git commit -am "Before Filament 5 upgrade"
   ```

2. **Testing على Local أولاً**
   - لا تطبق على Production مباشرة
   - اختبر كل شيء محلياً
   - حل جميع المشاكل

3. **Documentation**
   - جميع الأدلة جاهزة
   - راجعها قبل البدء
   - احفظ ملاحظات عن أي مشاكل

## 🆘 حل المشاكل

### مشكلة: Composer يرفض التحديث
```powershell
# حل 1: تحديث Composer نفسه
composer self-update

# حل 2: Clear cache
composer clear-cache

# حل 3: تحديث بـ compatibility flags
composer update --with-all-dependencies --ignore-platform-reqs
```

### مشكلة: Extensions مفقودة
```ini
; في php.ini
extension=openssl
extension=pdo_mysql
extension=mbstring
extension=fileinfo
extension=gd
extension=curl
extension=zip
```

### مشكلة: Permission denied
```powershell
# على Windows (كـ Admin)
icacls "storage" /grant Users:F /t
icacls "bootstrap/cache" /grant Users:F /t
```

## 📞 الدعم

إذا واجهت أي مشكلة:
1. راجع الدليل المناسب في المجلد
2. تحقق من logs: `storage/logs/laravel.log`
3. ابحث في Filament Docs: https://filamentphp.com/docs/5.x

---

**آخر تحديث:** 2026-03-31  
**الحالة:** جاهز للتنفيذ (بانتظار ترقية PHP)
