# إصلاح مشاكل السيرفر - دليل كامل

## المشكلة الحالية

السيستم واقف على السيرفر بسبب ملفات Resources خاطئة تم إنشاؤها.

## الحل السريع ⚡

### عبر SSH أو Terminal في cPanel:

```bash
cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg

# 1. حذف الـ Resources المشكلة
rm -rf app/Filament/Student/Resources/MyCourseResource
rm -rf app/Filament/Student/Resources/MyExamResource
rm -rf app/Filament/Student/Resources/MyAssignmentResource
rm -rf resources/views/filament/student/resources/my-*

# 2. مسح الـ Cache
php artisan optimize:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 3. إعادة بناء الـ Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### إذا لم يعمل SSH، استخدم File Manager:

1. افتح File Manager في cPanel
2. انتقل إلى: `public_html/aischool.almobtakeralaraby.com.eg`
3. احذف المجلدات التالية:
   ```
   app/Filament/Student/Resources/MyCourseResource/
   app/Filament/Student/Resources/MyExamResource/
   app/Filament/Student/Resources/MyAssignmentResource/
   ```
4. احذف الملفات:
   ```
   resources/views/filament/student/resources/my-assignment-resource/
   resources/views/filament/student/resources/my-exam-resource/
   ```

5. افتح Terminal في cPanel ونفذ:
```bash
cd public_html/aischool.almobtakeralaraby.com.eg
php artisan optimize:clear
```

---

## مشكلة Cache Path (إذا ظهرت)

```
Please provide a valid cache path.
InvalidArgumentException in Compiler.php (line 75)
```

### الحل:

```bash
cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg

# أنشئ المجلدات المطلوبة
mkdir -p storage/framework/{cache/data,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# ضبط الصلاحيات
chmod -R 775 storage bootstrap/cache

# امسح الـ Cache
php artisan optimize:clear
```

---

## مشكلة الصلاحيات (Permissions)

إذا واجهت مشكلة صلاحيات:

```bash
cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg

# ضبط صلاحيات المجلدات
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

# ضبط صلاحيات الملفات
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

# امسح الـ Cache
php artisan optimize:clear
```

---

## مشكلة Composer Autoload

إذا ظهرت أخطاء "Class not found":

```bash
cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg

# إعادة بناء autoload
composer dump-autoload

# مسح الـ cache
php artisan optimize:clear
```

---

## الـ Script الشامل (نسخ ولصق)

```bash
#!/bin/bash

# انتقل للمشروع
cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg

# حذف الـ Resources المشكلة
echo "🗑️  حذف الـ Resources المشكلة..."
rm -rf app/Filament/Student/Resources/MyCourseResource
rm -rf app/Filament/Student/Resources/MyExamResource  
rm -rf app/Filament/Student/Resources/MyAssignmentResource
rm -rf resources/views/filament/student/resources/my-*

# إنشاء المجلدات المطلوبة
echo "📁 إنشاء المجلدات..."
mkdir -p storage/framework/{cache/data,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# ضبط الصلاحيات
echo "🔒 ضبط الصلاحيات..."
chmod -R 775 storage bootstrap/cache

# مسح الـ Cache
echo "🧹 مسح الـ Cache..."
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# إعادة بناء Autoload
echo "📦 إعادة بناء Autoload..."
composer dump-autoload

# إعادة بناء الـ Cache
echo "⚡ بناء الـ Cache..."
php artisan config:cache
php artisan route:cache

echo "✅ تم الإصلاح بنجاح!"
```

احفظ هذا الـ Script في ملف `fix_server.sh` وشغله:
```bash
bash fix_server.sh
```

---

## التحقق من نجاح الإصلاح

بعد تنفيذ الخطوات:

1. افتح الموقع في المتصفح: `https://aischool.almobtakeralaraby.com.eg`
2. جرب تسجيل الدخول
3. تحقق من الـ panels (admin, student, instructor, parent)

---

## إذا استمرت المشكلة

### 1. تحقق من ملف .env

```bash
cat .env | grep -E "APP_ENV|APP_DEBUG|APP_URL"
```

يجب أن يكون:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://aischool.almobtakeralaraby.com.eg
```

### 2. تحقق من Laravel logs

```bash
tail -50 storage/logs/laravel.log
```

### 3. تحقق من PHP version

```bash
php -v
```

يجب أن يكون PHP 8.2 أو أعلى.

### 4. تحقق من Composer dependencies

```bash
composer install --no-dev --optimize-autoloader
```

---

## ملاحظات مهمة للسيرفر المشترك (Shared Hosting)

1. **الصلاحيات**: استخدم 775 للمجلدات و 664 للملفات
2. **Owner**: تأكد أن المستخدم هو نفس حساب cPanel (almobtaker)
3. **PHP version**: تحقق من PHP Selector في cPanel
4. **Memory limit**: تأكد من أن memory_limit كافي (128MB على الأقل)
5. **max_execution_time**: يفضل 300 ثانية على الأقل

---

## الاتصال بالدعم الفني

إذا لم تنجح جميع الحلول، اتصل بالدعم الفني واطلب:

1. التحقق من صلاحيات storage و bootstrap/cache
2. التحقق من PHP version (يجب 8.2+)
3. التحقق من Composer version
4. التحقق من open_basedir settings
5. مراجعة error_log للسيرفر

---

## الوقاية من المشاكل المستقبلية

### قبل رفع أي تحديث:

```bash
# 1. اختبر محلياً
php artisan optimize:clear
php artisan test

# 2. تأكد من عدم وجود أخطاء
composer validate
php artisan config:check

# 3. ارفع التحديثات
git push

# 4. على السيرفر
php artisan optimize:clear
composer install --no-dev --optimize-autoloader
```

---

**آخر تحديث**: 25 ديسمبر 2025  
**الحالة**: تم الإصلاح ✅
