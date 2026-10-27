# 📋 متطلبات النظام والمواصفات الفنية

## المتطلبات الأساسية

### النظام التشغيلي
- Windows 10/11
- macOS 11+
- Linux (Ubuntu 20.04+)

### البرامج المطلوبة
```
PHP >= 8.3
Node.js >= 18.0
MySQL >= 5.7 أو MariaDB >= 10.3
Composer >= 2.0
Git
```

---

## المكتبات والحزم

### Laravel Ecosystem
```
laravel/framework:     12.40.2
laravel/filament:      4.x
livewire/livewire:     3.x
livewire/tables:       3.x
```

### Spatie Packages
```
spatie/laravel-permission:     6.x
spatie/laravel-activitylog:   4.x
spatie/laravel-backup:        8.x
```

### Filament Plugins
```
bezhansalleh/filament-shield:       3.x
filament/notifications:             2.x
filament/tables:                    3.x
filament/forms:                     3.x
```

### أخرى
```
archtechx/tenancy:      3.x (optional)
maatwebsite/excel:      3.x
calebporzio/watch:      4.x
```

---

## متطلبات الأداء

### CPU
- الحد الأدنى: 2 cores
- الموصى به: 4+ cores

### الذاكرة (RAM)
- الحد الأدنى: 2 GB
- الموصى به: 4+ GB

### مساحة التخزين
- المشروع: 500 MB
- قاعدة البيانات: 1+ GB (حسب الاستخدام)
- السجلات: 100+ MB

---

## متطلبات قاعدة البيانات

### MySQL Configuration
```sql
-- الضبط الموصى به
SET GLOBAL max_connections = 1000;
SET GLOBAL max_allowed_packet = 256M;
SET SESSION sql_mode = '';

-- Charset
DEFAULT CHARSET=utf8mb4
DEFAULT COLLATE=utf8mb4_unicode_ci
```

### الجداول المطلوبة
```
users              -- المستخدمون
tenants            -- المؤسسات
roles              -- الأدوار
permissions        -- الصلاحيات
activity_log       -- سجلات النشاط
settings           -- الإعدادات
sessions           -- الجلسات
cache              -- الـ Cache
jobs               -- الوظائف المخطط لها
```

---

## البنية المعمارية

```
┌─────────────────────────────────────────┐
│         Web Browser (Frontend)          │
└─────────────┬───────────────────────────┘
              │ HTTP/HTTPS
┌─────────────▼───────────────────────────┐
│         Laravel 12 (Backend)            │
├─────────────────────────────────────────┤
│ ┌───────────────────────────────────┐   │
│ │ Filament Admin Panel              │   │
│ │ ├─ Resources                      │   │
│ │ ├─ Pages                          │   │
│ │ ├─ Widgets                        │   │
│ │ └─ Forms/Tables                   │   │
│ └───────────────────────────────────┘   │
├─────────────────────────────────────────┤
│ ┌───────────────────────────────────┐   │
│ │ Application Logic                 │   │
│ │ ├─ Models                         │   │
│ │ ├─ Controllers                    │   │
│ │ ├─ Services                       │   │
│ │ └─ Jobs                           │   │
│ └───────────────────────────────────┘   │
├─────────────────────────────────────────┤
│ ┌───────────────────────────────────┐   │
│ │ Cross-Cutting Concerns            │   │
│ │ ├─ Middleware (SetTenantMiddleware)   │
│ │ ├─ Scopes (TenantScope)           │   │
│ │ ├─ Auth & Permission              │   │
│ │ ├─ Activity Logging               │   │
│ │ └─ Exception Handling             │   │
│ └───────────────────────────────────┘   │
└─────────────┬───────────────────────────┘
              │
    ┌─────────┴─────────┐
    │                   │
┌───▼────────┐   ┌──────▼─────┐
│   MySQL    │   │  File      │
│   Database │   │  Storage   │
└────────────┘   └────────────┘
```

---

## معمارية Multi-Tenancy

```
┌─────────────────────────────────────┐
│  User Login                         │
└────────────────┬────────────────────┘
                 │
         ┌───────▼────────┐
         │ Authentication │
         │ (Laravel Auth) │
         └───────┬────────┘
                 │
         ┌───────▼──────────────────┐
         │ SetTenantMiddleware      │
         │ - Read tenant_id from DB │
         │ - Set session['tenant_id']
         └───────┬──────────────────┘
                 │
         ┌───────▼────────────────────┐
         │ Global Scope (TenantScope) │
         │ - Filter by tenant_id     │
         │ - Apply to all queries    │
         └───────┬────────────────────┘
                 │
    ┌────────────▼────────────┐
    │ Database Query          │
    │ (auto-filtered)         │
    └────────────┬────────────┘
                 │
         ┌───────▼────────┐
         │ Filtered Data  │
         │ (Safe!)        │
         └────────────────┘
```

---

## نقاط الضعف المعروفة والحلول

### 1. Performance at Scale
**المشكلة:** قد تكون الاستعلامات بطيئة مع الملايين من الصفوف

**الحل:**
```php
// إضافة indexing
Schema::create('users', function (Blueprint $table) {
    $table->index(['tenant_id', 'email']);
});

// استخدام eager loading
User::with('tenant')->paginate();
```

---

### 2. Tenant Switching
**المشكلة:** يمكن للمستخدم تجاوز الـ Scope

**الحل:**
```php
// Spatie Permission يتحقق من الملكية
User::where('tenant_id', '!=', auth()->user()->tenant_id)
    ->get()
// النتيجة: فارغة (الـ Scope يمنع)
```

---

### 3. Sensitive Data
**المشكلة:** بيانات حساسة قد تُعرض خطأً

**الحل:**
```php
// استخدم mutators
protected $hidden = ['password'];

// أو use Casts
protected $casts = [
    'sensitive_field' => 'encrypted',
];
```

---

## التوصيات الأمنية

### 1. HTTPS
```
الاستخدام الإنتاجي يتطلب SSL/TLS
```

### 2. Environment Variables
```
لا تخزن البيانات الحساسة في الكود
استخدم .env والـ config
```

### 3. Authentication
```
فعّل 2FA عندما يكون ممكناً
استخدم strong passwords
قم بتدوير المفاتيح بانتظام
```

### 4. Database
```
نسخ احتياطية منتظمة
تشفير الحقول الحساسة
الفهرسة الصحيحة
```

### 5. Logging
```
سجّل جميع التغييرات
مراقبة الأخطاء بنشاط
تنظيف السجلات القديمة
```

---

## متطلبات النشر (Deployment)

### الإنتاج
```bash
# البيئة
APP_ENV=production
APP_DEBUG=false
APP_URL=https://Almobtaker Alaraby.example.com

# السجلات
LOG_CHANNEL=stack
LOG_LEVEL=warning

# الكاش
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_DRIVER=redis
```

### Services
```
Web Server:  Nginx/Apache
PHP-FPM:     9.1
Redis:       6.0+
MySQL:       5.7+
```

### Hosting
```
المساحة:     1+ GB SSD
الذاكرة:    2+ GB RAM
النطاق:     Unlimited
SSL:        Required
Backups:    Daily
```

---

## قائمة التحقق قبل الإنتاج

- [ ] جميع الاختبارات تنجح
- [ ] لا توجد أخطاء في logs
- [ ] HTTPS مفعل
- [ ] قاعدة البيانات مأمونة
- [ ] النسخ الاحتياطية مفعلة
- [ ] المراقبة مفعلة
- [ ] CDN مكون (إن أمكن)
- [ ] Backup واستعادة مختبر
- [ ] Performance مختبر
- [ ] Security audit مكتمل

---

## الأمن والامتثال

### GDPR
```
- [ ] سياسة الخصوصية
- [ ] موافقة المستخدم
- [ ] حق الوصول
- [ ] حق الحذف
```

### Security Standards
```
- [ ] OWASP Top 10
- [ ] SQL Injection Prevention
- [ ] XSS Prevention
- [ ] CSRF Protection
- [ ] Authentication Security
```

### Data Protection
```
- [ ] Encryption at rest
- [ ] Encryption in transit
- [ ] Access logs
- [ ] Audit trails
```

---

## الدعم والصيانة

### النسخ الاحتياطية
```bash
# يومياً
php artisan backup:run

# أسبوعياً
php artisan backup:clean
```

### التحديثات
```bash
# مراقبة
composer update --dry-run

# التحديث
composer update
php artisan migrate
npm run build
```

### المراقبة
```
- Uptime monitoring
- Error tracking
- Performance metrics
- User activity logs
```

---

**الإصدار:** 1.0.0  
**آخر تحديث:** ديسمبر 2025  
**الحالة:** Production Ready ✅
