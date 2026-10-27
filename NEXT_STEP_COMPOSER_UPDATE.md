# 🎯 الخطوة التالية: تشغيل composer update

## ✅ ما تم إنجازه

1. ✅ تحديث [`composer.json`](composer.json) للإصدارات المتوافقة
2. ✅ حل مشاكل Package Constraints
3. ✅ تغيير `minimum-stability` إلى `dev` مع `prefer-stable: true`
4. ✅ إنشاء دليل شامل: [`FILAMENT_5_PACKAGE_CONSTRAINTS.md`](FILAMENT_5_PACKAGE_CONSTRAINTS.md)

## 🚀 الآن: قم بتشغيل هذا الأمر

```powershell
cd "d:\My Work\Laravel\Ai School Ga"
composer update -W
```

### ماذا يفعل الأمر؟
- `-W` أو `--with-all-dependencies`: يسمح بترقية/تخفيض جميع الباكجات المرتبطة

### النتائج المتوقعة:

#### ✅ النجاح:
```
Loading composer repositories with package information
Updating dependencies
Lock file operations: X installs, Y updates, Z removals
Writing lock file
Installing dependencies from lock file
...
Generating optimized autoload files
> @php artisan filament:upgrade
```

#### ⚠️ Warnings المتوقعة (طبيعية):
```
  - Using dev-main for filament-shield (May be unstable)
  - Using dev-main for filament-language-switch (May be unstable)
```

هذه Warnings **طبيعية** لأننا نستخدم dev branches.

#### ❌ إذا ظهرت أخطاء:

**خطأ 1: PHP Version**
```
Your requirements could not be resolved to an installable set of packages.
Problem: php ^8.3 required
```
**الحل:** راجع [`PHP_8.3_UPGRADE_GUIDE.md`](PHP_8.3_UPGRADE_GUIDE.md)

**خطأ 2: Memory Limit**
```
PHP Fatal error: Allowed memory size
```
**الحل:**
```powershell
php -d memory_limit=-1 "C:\ProgramData\ComposerSetup\bin\composer.phar" update -W
```

**خطأ 3: Package Not Found**
```
Could not find package X
```
**الحل:**
```powershell
composer clear-cache
composer update -W
```

## 📊 بعد نجاح composer update

### 1. تحقق من النتائج
```powershell
# تحقق من الباكجات المثبتة
composer show | findstr filament

# يجب أن ترى:
# filament/filament                     v5.x.x
# bezhansalleh/filament-shield          dev-main
# bezhansalleh/filament-language-switch dev-main
```

### 2. تشغيل Filament Upgrade
```powershell
php artisan filament:upgrade
```

### 3. تنظيف Cache
```powershell
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. اختبار Dashboard
```powershell
php artisan serve
```
ثم افتح: `http://localhost:8000/admin`

## 📝 الملاحظات

### Dev Branches المستخدمة:
| Package | Version | السبب |
|---------|---------|-------|
| filament-shield | dev-main | لا يوجد v5 stable |
| filament-language-switch | dev-main | لا يوجد v5 stable |
| daterangepicker-filter | dev-x4 | لا يوجد v5 stable |
| sticky-table-header | dev-filament-v5 | يوجد فرع v5 جاهز! |
| excel-import | ^4.0 | آخر stable متوافق |
| filament-excel | ^3.6 | آخر stable متوافق |

### المخاطر:
- ⚠️ Dev branches قد تتغير
- ⚠️ قد تحدث breaking changes
- ⚠️ غير مناسب للـ Production حالياً

### التوصيات:
- ✅ للتطوير: استمر بالحل الحالي
- ✅ للإنتاج: انتظر stable releases
- ✅ راقب updates: `composer outdated`

## 🔄 إذا أردت العودة لـ Filament 4

احتفظ بنسخة من [`composer.json`](composer.json) الحالية:
```powershell
cp composer.json composer.json.filament5.backup
```

ثم عُد للإصدار القديم:
```powershell
git checkout composer.json
composer update -W
```

## 📞 الدعم

إذا واجهت مشاكل:
1. اقرأ [`FILAMENT_5_PACKAGE_CONSTRAINTS.md`](FILAMENT_5_PACKAGE_CONSTRAINTS.md)
2. راجع [`PHP_8.3_UPGRADE_GUIDE.md`](PHP_8.3_UPGRADE_GUIDE.md)
3. افحص الأخطاء بـ: `composer update -W -vvv`
4. اطرح سؤالك مع تفاصيل الخطأ

---

## ✅ جاهز؟ شغّل الأمر:

```powershell
composer update -W
```

**وانتظر النتائج...** ⏳
