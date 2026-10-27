# نظام جلب إعدادات الموقع (Settings System)

## نظرة عامة

تم إنشاء نظام كامل لإدارة إعدادات الموقع حسب الـ tenant. النظام يدعم:
- إعدادات خاصة لكل فرع (tenant)
- إعدادات عامة للسوبر أدمن (بدون tenant)
- كاش تلقائي لتحسين الأداء
- واجهة Filament لإدارة الإعدادات

## الميزات

### 1. SettingsHelper Class
Helper class شامل لجلب وإدارة الإعدادات:

```php
use App\Helpers\SettingsHelper;

// جلب إعداد معين
$companyName = SettingsHelper::get('company_name');
$phone = SettingsHelper::get('company_phone', '+20 100 000 0000');

// جلب عنوان الموقع
$title = SettingsHelper::getSiteTitle();

// جلب شعار الموقع
$logo = SettingsHelper::getSiteLogo();

// جلب كل الإعدادات
$allSettings = SettingsHelper::all();

// تحديث إعداد
SettingsHelper::set('company_name', 'اسم جديد');

// مسح الكاش
SettingsHelper::clearCache();
```

### 2. Helper Functions
دوال مساعدة عامة يمكن استخدامها في أي مكان:

```php
// في Blade
{{ site_title() }}
{{ site_logo() }}
{{ site_email() }}
{{ site_phone() }}
{{ site_whatsapp() }}

// أو جلب أي إعداد
{{ setting('company_address') }}
{{ setting('timezone', 'Africa/Cairo') }}
```

### 3. استخدام في PanelProviders
تم دمج النظام مع جميع اللوحات:

```php
->brandName(fn() => SettingsHelper::getSiteTitle())
```

## جدول settings

الجدول يحتوي على:

### معلومات الشركة
- `company_name` - اسم الشركة/المدرسة
- `company_address` - العنوان
- `company_phone` - رقم الهاتف
- `company_whatsapp` - رقم الواتساب
- `company_email` - البريد الإلكتروني
- `company_logo` - شعار الموقع
- `company_website` - رابط الموقع

### التواصل الاجتماعي
- `social_facebook`
- `social_instagram`
- `social_twitter`
- `social_linkedin`
- `social_youtube`
- `social_tiktok`
- `social_telegram`

### SEO
- `seo_title` - عنوان الصفحة
- `seo_description` - وصف الموقع
- `seo_keywords` - الكلمات المفتاحية
- `google_analytics_id`
- `google_search_console`

### إعدادات البريد
- `mail_from_address`
- `mail_from_name`
- `mail_driver`
- `mail_host`
- `mail_port`
- `mail_username`
- `mail_password`
- `mail_encryption`

### إعدادات إضافية
- `about_company` - نبذة عن الشركة
- `currency` - العملة (افتراضي: EGP)
- `timezone` - المنطقة الزمنية
- `maintenance_mode` - وضع الصيانة
- `latitude` - خط العرض
- `longitude` - خط الطول

## آلية العمل

### 1. Multi-tenancy
- كل فرع له إعداداته الخاصة
- السوبر أدمن له إعدادات عامة (tenant_id = null)
- TenantScope تلقائي على جدول settings

### 2. الكاش
- تخزين الإعدادات في الكاش لمدة ساعة
- مسح الكاش تلقائياً عند التحديث
- كل tenant له كاش منفصل

### 3. القيم الافتراضية
تم تعيين قيم افتراضية للإعدادات عبر SettingsSeeder:
- إعدادات عامة للسوبر أدمن
- إعدادات خاصة لكل فرع

## إدارة الإعدادات

### من لوحة Admin
يمكن إدارة الإعدادات من:
```
Admin Panel > Settings
```

### من الكود
```php
// إنشاء إعدادات جديدة
Setting::create([
    'tenant_id' => 1,
    'company_name' => 'فرع جديد',
    // ... باقي الإعدادات
]);

// تحديث
$setting = Setting::first();
$setting->update(['company_name' => 'اسم محدث']);

// جلب حسب tenant (تلقائي مع TenantScope)
$settings = Setting::first();

// جلب بدون scope
$allSettings = Setting::withoutGlobalScopes()->get();
```

## الاستخدام في Views

### Blade
```blade
<h1>{{ site_title() }}</h1>
<img src="{{ site_logo() }}" alt="Logo">
<a href="mailto:{{ site_email() }}">راسلنا</a>
<a href="tel:{{ site_phone() }}">اتصل بنا</a>

<!-- أو -->
<p>{{ setting('about_company') }}</p>
<span>العملة: {{ setting('currency', 'EGP') }}</span>
```

### في Controllers
```php
public function index()
{
    return view('welcome', [
        'siteName' => site_title(),
        'settings' => SettingsHelper::all(),
    ]);
}
```

## Seeding

تم إنشاء SettingsSeeder:
```bash
php artisan db:seed --class=SettingsSeeder
```

سينشئ:
1. إعدادات عامة للسوبر أدمن (tenant_id = null)
2. إعدادات لكل فرع موجود

## ملاحظات مهمة

1. **TenantScope**: جدول settings يستخدم TenantScope تلقائياً
2. **Unique Constraint**: كل tenant له سجل إعدادات واحد فقط
3. **Cache**: الإعدادات مخزنة في الكاش لتحسين الأداء
4. **Super Admin**: المستخدمون بدون tenant_id يحصلون على الإعدادات العامة

## أمثلة الاستخدام

### عرض اسم الموقع في الـ Header
```blade
<header>
    <h1>{{ site_title() }}</h1>
</header>
```

### عرض معلومات الاتصال في الـ Footer
```blade
<footer>
    <p>تواصل معنا:</p>
    <p>📧 {{ site_email() }}</p>
    <p>📱 {{ site_phone() }}</p>
    <p>💬 {{ site_whatsapp() }}</p>
</footer>
```

### تخصيص عنوان الصفحة
```blade
<title>{{ setting('seo_title', config('app.name')) }}</title>
<meta name="description" content="{{ setting('seo_description') }}">
```

## التطوير المستقبلي

يمكن إضافة:
- إعدادات للألوان (theme colors)
- إعدادات للغات
- إعدادات للإشعارات
- إعدادات الدفع (payment gateways)
- إعدادات SMS
