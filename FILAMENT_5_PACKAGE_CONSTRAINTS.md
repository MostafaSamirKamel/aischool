# حل مشكلة Package Constraints لـ Filament 5

## 🔴 المشكلة

عند محاولة الترقية لـ Filament 5، واجهنا مشكلة **عدم توفر إصدارات متوافقة** لبعض الباكجات:

```
Problem 1: bezhansalleh/filament-language-switch ^5.0 - NOT FOUND
Problem 2: bezhansalleh/filament-shield ^5.0 - NOT FOUND
Problem 3: eightynine/filament-excel-import ^5.0 - NOT FOUND
Problem 4: malzariey/filament-daterangepicker-filter ^5.0 - NOT FOUND
Problem 5: pxlrbt/filament-excel ^4.0 - NOT FOUND
Problem 6: watheqalshowaiter/filament-sticky-table-header ^2.0 - NOT FOUND
```

## ✅ الحل المطبق

تم تحديث [`composer.json`](composer.json) لاستخدام **الإصدارات المتاحة**:

### قبل التعديل (لم يعمل):
```json
{
    "require": {
        "bezhansalleh/filament-language-switch": "^5.0",
        "bezhansalleh/filament-shield": "^5.0",
        "eightynine/filament-excel-import": "^5.0",
        "malzariey/filament-daterangepicker-filter": "^5.0",
        "pxlrbt/filament-excel": "^4.0",
        "watheqalshowaiter/filament-sticky-table-header": "^2.0"
    },
    "minimum-stability": "stable"
}
```

### بعد التعديل (✅ يعمل):
```json
{
    "require": {
        "bezhansalleh/filament-language-switch": "dev-main",
        "bezhansalleh/filament-shield": "dev-main",
        "eightynine/filament-excel-import": "^4.0",
        "malzariey/filament-daterangepicker-filter": "dev-x4",
        "pxlrbt/filament-excel": "^3.6",
        "watheqalshowaiter/filament-sticky-table-header": "dev-filament-v5"
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

## 📊 تفاصيل كل باكج

### 1. bezhansalleh/filament-language-switch
- **المشكلة:** ليس له إصدار 5.0 بعد
- **الحل:** استخدام `dev-main` (قيد التطوير)
- **أعلى إصدار متاح:** 4.x-dev
- **الحالة:** ⚠️ Dev branch

### 2. bezhansalleh/filament-shield
- **المشكلة:** ليس له إصدار 5.0 بعد
- **الحل:** استخدام `dev-main` (قيد التطوير)
- **أعلى إصدار متاح:** 4.x-dev
- **الحالة:** ⚠️ Dev branch

### 3. eightynine/filament-excel-import
- **المشكلة:** ليس له إصدار 5.0 بعد
- **الحل:** استخدام `^4.0` (آخر إصدار مستقر)
- **أعلى إصدار متاح:** 4.x-dev
- **الحالة:** ✅ Stable 4.0

### 4. malzariey/filament-daterangepicker-filter
- **المشكلة:** ليس له إصدار 5.0 بعد
- **الحل:** استخدام `dev-x4` (فرع تطوير 4.x)
- **أعلى إصدار متاح:** 4.1.beta.2
- **الحالة:** ⚠️ Dev branch

### 5. pxlrbt/filament-excel
- **المشكلة:** ليس له إصدار 4.0 بعد
- **الحل:** استخدام `^3.6` (آخر إصدار مستقر)
- **أعلى إصدار متاح:** v3.6.1
- **الحالة:** ✅ Stable 3.6

### 6. watheqalshowaiter/filament-sticky-table-header
- **المشكلة:** ليس له إصدار 2.0 بعد
- **الحل:** استخدام `dev-filament-v5` (فرع خاص بـ Filament 5)
- **أعلى إصدار متاح:** 1.5.1
- **الحالة:** ⚠️ Dev branch (لديه فرع v5 جاهز!)

## 🚀 الخطوات التالية

### 1. تشغيل composer update
```bash
composer update -W
```

الأمر `-W` يسمح بترقية/تخفيض جميع الباكجات المرتبطة.

### 2. مراقبة Warnings
```bash
# قد تظهر تحذيرات مثل:
# - Using dev-main instead of stable
# - Version conflicts
```

### 3. الاختبار
```bash
php artisan filament:upgrade
php artisan optimize:clear
php artisan config:cache
```

## ⚠️ ملاحظات مهمة

### استخدام Dev Branches
- ✅ **الإيجابيات:**
  - دعم Filament 5 مبكر
  - أحدث الميزات
  - إصلاحات الأخطاء

- ❌ **السلبيات:**
  - غير مستقر 100%
  - قد تتغير APIs
  - لا يُنصح به للـ Production

### الحلول البديلة

#### الخيار 1: الانتظار (موصى به للـ Production)
```bash
# البقاء على Filament 4 حتى تصدر النسخ المستقرة
"filament/filament": "^4.0"
```

#### الخيار 2: استخدام Dev (للتطوير/الاختبار)
```bash
# الحل الحالي - استخدام dev branches
"minimum-stability": "dev",
"prefer-stable": true
```

#### الخيار 3: إزالة الباكجات غير الضرورية
```bash
composer remove bezhansalleh/filament-language-switch
composer remove bezhansalleh/filament-shield
# ثم الترقية لـ Filament 5
```

## 📅 متى ستتوفر الإصدارات المستقرة؟

| Package | الحالة | تاريخ التوفر المتوقع |
|---------|--------|----------------------|
| filament-shield | In development | قريباً (شهر-شهرين) |
| filament-language-switch | In development | قريباً (شهر-شهرين) |
| filament-excel-import | Stable v4 | متاح |
| daterangepicker-filter | Beta | قريباً (أسابيع) |
| filament-excel | Stable v3 | متاح |
| sticky-table-header | Dev v5 branch | قريباً (أسابيع) |

## 🔧 إذا فشل composer update

### الخطوة 1: مسح الـ Cache
```bash
composer clear-cache
```

### الخطوة 2: حذف vendor و composer.lock
```bash
rm -rf vendor
rm composer.lock
composer install
```

### الخطوة 3: استخدام -vvv للتفاصيل
```bash
composer update -W -vvv
```

## 📝 توصيات الاستخدام

### للتطوير (Development):
```bash
✅ استخدم الحل الحالي (dev branches)
✅ اختبر بعناية
✅ راقب التغييرات في الباكجات
```

### للإنتاج (Production):
```bash
⚠️ انتظر الإصدارات المستقرة
⚠️ أو ابقَ على Filament 4
⚠️ أو استخدم Docker بإصدارات ثابتة
```

## 🔗 روابط مفيدة

- [Filament 5 Upgrade Guide](https://filamentphp.com/docs/5.x/upgrade-guide)
- [Filament Shield GitHub](https://github.com/bezhansalleh/filament-shield)
- [Filament Language Switch GitHub](https://github.com/bezhansalleh/filament-language-switch)
- [Packagist.org](https://packagist.org) - للبحث عن آخر الإصدارات

## ✅ الخلاصة

تم تعديل [`composer.json`](composer.json) لاستخدام:
- ✅ Dev branches للباكجات قيد التطوير
- ✅ آخر إصدار مستقر للباكجات المتاحة
- ✅ تغيير `minimum-stability` إلى `dev`
- ✅ إبقاء `prefer-stable: true`

**الآن جرّب:**
```bash
composer update -W
```

**إذا نجح:**
```bash
php artisan filament:upgrade
php artisan optimize:clear
```
