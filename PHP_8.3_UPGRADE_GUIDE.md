# خطوات ترقية PHP إلى 8.3 على Windows

## 📌 الوضع الحالي

- **PHP الحالية:** 8.2.12
- **PHP المطلوبة:** 8.3+ (لـ Filament 5)

## 🚀 خطوات الترقية

### الطريقة 1: عبر Laragon (موصى بها)

إذا كنت تستخدم Laragon:

1. **تحميل PHP 8.3**
   - افتح Laragon
   - اذهب إلى Menu → PHP → Download → PHP 8.3.x
   - أو حمل يدوياً من: https://windows.php.net/download/

2. **تثبيت PHP 8.3 في Laragon**
   ```
   - حمل PHP 8.3 (Thread Safe x64)
   - فك الضغط في: C:\laragon\bin\php\php-8.3.x
   - في Laragon: Menu → PHP → Version → php-8.3.x
   - Restart Laragon
   ```

3. **التحقق من التثبيت**
   ```powershell
   php -v
   # يجب أن يظهر: PHP 8.3.x
   ```

### الطريقة 2: عبر XAMPP

1. **تحميل XAMPP مع PHP 8.3**
   - حمل من: https://www.apachefriends.org/
   - اختر النسخة التي تحتوي PHP 8.3

2. **تحديث PATH Environment**
   ```
   - Control Panel → System → Advanced → Environment Variables
   - Path → Edit
   - أضف: C:\xampp\php (أو مسار XAMPP الخاص بك)
   - أعد تشغيل الـ Terminal
   ```

### الطريقة 3: تحميل مباشر

1. **تحميل PHP 8.3**
   ```
   https://windows.php.net/download/
   - اختر: PHP 8.3.x Thread Safe (x64)
   ```

2. **التثبيت**
   ```
   - فك الضغط في: C:\php83
   - انسخ php.ini-development إلى php.ini
   - فعّل Extensions المطلوبة في php.ini:
     extension=openssl
     extension=pdo_mysql
     extension=mbstring
     extension=fileinfo
     extension=gd
     extension=curl
     extension=zip
   ```

3. **تحديث System PATH**
   ```
   - System Properties → Environment Variables
   - Path → Add: C:\php83
   - Restart Terminal
   ```

## ⚙️ Extensions المطلوبة

تأكد من تفعيل Extensions التالية في `php.ini`:

```ini
extension=openssl
extension=pdo_mysql
extension=mbstring
extension=fileinfo
extension=gd
extension=curl
extension=zip
extension=intl
extension=exif
```

## ✅ التحقق بعد الترقية

```powershell
# تحقق من النسخة
php -v
# يجب أن يظهر: PHP 8.3.x

# تحقق من Extensions
php -m

# تحقق من Composer
composer --version
```

## 🔄 بعد ترقية PHP

1. **تحديث Composer Dependencies**
   ```powershell
   cd "d:\My Work\Laravel\Ai School Ga"
   composer update
   ```

2. **تنظيف Cache**
   ```powershell
   php artisan optimize:clear
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **تشغيل Filament Upgrade**
   ```powershell
   php artisan filament:upgrade
   ```

## ⚠️ مشاكل محتملة

### 1. Composer يستخدم PHP خاطئة

```powershell
# تحديد PHP path لـ Composer
composer config -g -- php-path "C:/php83/php.exe"
```

### 2. Extensions مفقودة

```powershell
# تحقق من Extensions المثبتة
php -m

# إذا كانت مفقودة:
# - افتح php.ini
# - ابحث عن extension=xxx
# - أزل ; من البداية لتفعيلها
```

### 3. Error في PATH

```powershell
# تأكد من أن PHP في PATH
$env:Path -split ';' | Select-String php

# إذا لم تظهر، أضف يدوياً
```

## 📝 ملاحظات مهمة

1. **Backup أولاً**
   - احتفظ بنسخة من PHP 8.2 القديمة
   - نسخة احتياطية من المشروع

2. **اختبار محلي أولاً**
   - جرب الترقية على بيئة التطوير
   - تأكد من عمل كل شيء
   - ثم انقل للسيرفر

3. **Server Deployment**
   - إذا كان المشروع على server مشترك، تواصل مع الدعم الفني
   - قد تحتاج لترقية الـ hosting plan

## 🎯 الخطوة التالية

بعد ترقية PHP إلى 8.3:

```powershell
# 1. التحقق من PHP
php -v

# 2. تحديث Dependencies
composer update

# 3. اختبار المشروع
php artisan serve
```

---

**هل أنت مستعد للترقية؟**  
بعد ترقية PHP، قم بتشغيل:
```powershell
composer update filament/filament --with-all-dependencies
```
