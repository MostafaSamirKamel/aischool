# دليل الترقية إلى Filament 5

## 📋 نظرة عامة

هذا الدليل يوضح خطوات ترقية النظام من **Filament 4** إلى **Filament 5**.

## ✅ المتطلبات الأساسية

### قبل الترقية:
- PHP 8.2
- Laravel 12.x
- Filament 4.x

### بعد الترقية:
- **PHP 8.3+** (إلزامي)
- Laravel 12.x (متوافق)
- Filament 5.x

## 🚀 خطوات الترقية

### 1. ترقية PHP إلى 8.3+

```bash
# تحديث PHP على السيرفر أولاً
# ثم تحديث composer.json
```

### 2. تحديث Packages في composer.json

```json
{
    "require": {
        "php": "^8.3",
        "filament/filament": "^5.0",
        "bezhansalleh/filament-language-switch": "^5.0",
        "bezhansalleh/filament-shield": "^5.0",
        "eightynine/filament-excel-import": "^5.0",
        "malzariey/filament-daterangepicker-filter": "^5.0"
    }
}
```

### 3. تشغيل التحديث

```bash
composer update filament/filament --with-all-dependencies
```

## 📦 Packages المطلوب تحديثها

| Package | النسخة الحالية | النسخة المستهدفة | الحالة |
|---------|---------------|------------------|--------|
| filament/filament | ^4.0 | ^5.0 | ✅ متاح |
| bezhansalleh/filament-shield | ^4.0 | ^5.0 | ✅ متاح |
| bezhansalleh/filament-language-switch | ^4.0 | ^5.0 | ✅ متاح |
| eightynine/filament-excel-import | ^4.0 | ^5.0 | ⚠️ تحقق |
| malzariey/filament-daterangepicker-filter | ^4.0 | ^5.0 | ⚠️ تحقق |
| watheqalshowaiter/filament-sticky-table-header | ^1.2 | ⚠️ تحقق | ⚠️ تحقق |
| pxlrbt/filament-excel | ^3.2 | ^4.0 | ⚠️ تحقق |

## 🔄 التغييرات المطلوبة في الكود

### 1. Type Declarations - لا تغيير!

```php
// ✅ نفس الطريقة في Filament 4 و 5
protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document';
protected static \UnitEnum|string|null $navigationGroup = 'المجموعة';
protected string $view = 'filament.pages.your-page';
```

### 2. Panel Configuration

```php
// تحقق من أن جميع الإعدادات متوافقة
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->login()
        ->registration()
        ->profile()
        // ... باقي الإعدادات
        ->plugins([
            FilamentShieldPlugin::make(),
            // ...
        ]);
}
```

### 3. Navigation Groups

```php
// تحديث Navigation Groups إذا لزم الأمر
protected static \UnitEnum|string|null $navigationGroup = 'الإدارة';
```

## ⚠️ نقاط مهمة

### 1. Backup أولاً
```bash
# نسخة احتياطية من قاعدة البيانات
php artisan backup:run

# نسخة احتياطية من ملفات المشروع
```

### 2. تحديث Cache
```bash
php artisan optimize:clear
php artisan filament:upgrade
php artisan filament:cache-components
```

### 3. اختبار شامل
- [ ] Login/Logout
- [ ] Navigation
- [ ] Forms (Create/Edit)
- [ ] Tables (List/Filter/Search)
- [ ] Widgets
- [ ] Permissions (Shield)
- [ ] Multi-tenancy

## 📝 Breaking Changes المحتملة

### Filament 5 Breaking Changes:

1. **PHP 8.3 Required**
   - استخدام features جديدة من PHP 8.3
   - Typed class constants
   - Override attribute

2. **Heroicons Update**
   - بعض الأيقونات تغيرت أسماءها
   - تحقق من جميع الأيقونات المستخدمة

3. **Form Components**
   - بعض الـ methods تغيرت
   - تحقق من الـ custom components

4. **Table Columns**
   - تحسينات في الـ column types
   - تحقق من الـ custom columns

## 🔍 خطوات الاختبار بعد الترقية

### 1. Resources
```bash
# اختبر جميع الـ Resources
- StudentResource
- EnrollmentResource
- GroupResource
- CourseResource
- InstructorResource
- PaymentResource
```

### 2. Pages
```bash
# اختبر جميع الـ Custom Pages
- Dashboard
- Reports
- GenerateInstructorPayments
- AttendanceSessions
```

### 3. Widgets
```bash
# اختبر جميع الـ Widgets
- StatsOverview
- Charts
- TenantSwitcher
```

## 🐛 مشاكل شائعة وحلولها

### 1. Plugin Compatibility Error
```bash
# إذا واجهت مشكلة توافق plugin
composer show [package-name] --all
# ابحث عن النسخة المتوافقة مع Filament 5
```

### 2. Cache Issues
```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
composer dump-autoload
```

### 3. Icon Not Found
```php
// تحقق من الأيقونات الجديدة في Heroicons
// https://heroicons.com/
protected static string $navigationIcon = 'heroicon-o-user-group';
```

## 📚 موارد إضافية

- [Filament 5 Documentation](https://filamentphp.com/docs/5.x)
- [Upgrade Guide](https://filamentphp.com/docs/5.x/upgrade-guide)
- [Breaking Changes](https://filamentphp.com/docs/5.x/breaking-changes)

## ✨ ميزات Filament 5 الجديدة

1. **أداء محسّن**
   - Lazy loading محسّن
   - Query optimization
   - Caching improvements

2. **UI/UX محسّن**
   - تصميم أفضل
   - Responsive improvements
   - Dark mode enhancements

3. **Developer Experience**
   - Better error messages
   - Improved debugging tools
   - Better documentation

## 🎯 الخطة النهائية

1. ✅ تحديث PHP إلى 8.3
2. ✅ تحديث composer.json
3. ⏳ تشغيل composer update
4. ⏳ تحديث config files
5. ⏳ تشغيل upgrade commands
6. ⏳ اختبار شامل
7. ⏳ Deploy to production

---

**تاريخ الإنشاء:** 2026-03-31  
**النسخة:** 1.0  
**الحالة:** جاهز للتنفيذ
