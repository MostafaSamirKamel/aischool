# � AI School Management System

نظام إدارة مدرسة/أكاديمية متعدد المؤسسات - **Multi-Tenant School Management System**

<p align="center">
  <strong>Laravel 12</strong> • <strong>Filament 4</strong> • <strong>MySQL</strong> • <strong>Arabic Support</strong>
</p>

---

## 🚀 التشغيل والإنتاج (Docker & Railway Deployment)

المشروع جاهز للإنتاج والنشر المباشر عبر الحاويات ومنصة **Railway**:
- 📖 **الدليل التشغيلي الكامل للإنتاج وRailway:** [DEPLOYMENT_RAILWAY_DOCKER.md](DEPLOYMENT_RAILWAY_DOCKER.md)
- 🐳 **التشغيل المحلي عبر Docker Compose:** `docker compose up -d --build`
- ☁️ **إعدادات Railway:** ملف جاهز `railway.json` يدعم فحص الصحة عبر `/up` وترقية قاعدة البيانات الآمنة عبر `preDeployCommand`.
- 📱 **بوابة الطالب PWA:** مدمجة وتعمل تلقائياً عبر الرابط `/student-portal/` أو `/portal`.

---

## ⚡ البدء الفوري

### 📊 حالة المشروع: 88% مكتمل ✅

**جديد! تم إضافة نظام إدارة المعلمين:**
- 📄 [INSTRUCTOR_SYSTEM_COMPLETE.md](INSTRUCTOR_SYSTEM_COMPLETE.md) ⚡ **نظام المعلمين مكتمل 100%!**
- 📄 [START_NOW.md](START_NOW.md) ⚡ **ابدأ هنا للبدء الفوري!**
- 📄 [COMPLETION_STATUS.md](COMPLETION_STATUS.md) - ملخص الحالة (3 دقائق)
- 📄 [ANALYSIS_REPORT.md](ANALYSIS_REPORT.md) - تحليل شامل (20 دقيقة)
- 📄 [AI_PROMPTS_GUIDE.md](AI_PROMPTS_GUIDE.md) ⭐ - 9 Prompts جاهزة للتطوير
- 📄 [PROJECT_INDEX.md](PROJECT_INDEX.md) - فهرس 25+ ملف

**ما موجود:** Students, Courses, Enrollments, Payments, Dashboard + 9 Widgets, Reports, **Instructors** ✅  
**ما ناقص:** Attendance, Grading (2-3 أيام للإكمال)

---

## ✨ المميزات الرئيسية

### 🔐 Multi-Tenancy (عزل البيانات)
- ✅ عزل كامل للبيانات بين المؤسسات
- ✅ Global Scope للتصفية التلقائية
- ✅ Middleware لتعيين السياق
- ✅ Security-first design

### 📊 Activity Logging (تسجيل النشاطات)
- ✅ تتبع جميع التغييرات على البيانات
- ✅ عرض من/إلى للقيم المعدلة
- ✅ معلومات المستخدم والتاريخ
- ✅ صفحات تفصيلية للعرض

### 🛡️ Role & Permission System
- ✅ Spatie Permission integration
- ✅ FilamentShield for automation
- ✅ Fine-grained access control
- ✅ Role-based pages

### 🌐 Localization
- ✅ اللغة الافتراضية: العربية
- ✅ دعم اللغة الإنجليزية
- ✅ جميع النصوص مترجمة

### 📱 Beautiful Admin Panel
- ✅ Filament 4 with Schemas API
- ✅ Dark mode support
- ✅ Responsive design
- ✅ Custom widgets

---

## 🚀 البدء السريع

### المتطلبات
```
PHP >= 8.3
Composer
Node.js >= 18
MySQL
```

### التثبيت
```bash
# استنساخ المشروع
git clone <repository-url>
cd Almobtaker Alaraby

# تثبيت المكتبات
composer install
npm install

# تكوين البيئة
cp .env.example .env
php artisan key:generate

# قاعدة البيانات
php artisan migrate:fresh --seed

# بناء الأصول
npm run build
```

### التشغيل
```bash
# في نافذة 1
php artisan serve

# في نافذة 2
npm run dev

# اذهب إلى
http://127.0.0.1:8000/admin
```

---

## 👤 بيانات الدخول التجريبية

بعد `migrate:fresh --seed`:

```
بريد: cairo-clinic@admin.local
      alexandria-clinic@admin.local
      giza-clinic@admin.local

كلمة المرور: password (من UserFactory)
```

**ملاحظة:** كل مستخدم يرى فقط بيانات مؤسسته!

---

## 📁 هيكل المشروع

```
app/
├── Models/
│   ├── User.php              # مع Global Scope
│   ├── Tenant.php            # المؤسسة/العيادة
│   └── Scopes/
│       └── TenantScope.php   # عزل البيانات
├── Http/
│   └── Middleware/
│       └── SetTenantMiddleware.php
└── Filament/
    └── Admin/
        ├── Resources/
        │   ├── Users/
        │   └── Tenants/
        └── Widgets/

database/
├── migrations/               # جميع الـ migrations
└── seeders/
    ├── RoleSeeder.php       # الأدوار والصلاحيات
    ├── TenantSeeder.php     # المؤسسات والمستخدمين
    └── DatabaseSeeder.php
```

---

## 🔄 كيفية عمل Multi-Tenancy

### الخطوة 1: المستخدم يسجل الدخول
```
User logs in → Laravel authenticates
```

### الخطوة 2: Middleware يعيّن السياق
```
SetTenantMiddleware executes
→ session(['tenant_id' => user.tenant_id])
```

### الخطوة 3: Global Scope يفلتر البيانات
```
User::all()
→ Automatically becomes:
   SELECT * FROM users WHERE tenant_id = 1
```

### النتيجة: ✅ بيانات آمنة ومعزولة

---

## 📊 البيانات المضمنة

### 3 مؤسسات:
- عيادة القاهرة (cairo.Almobtaker Alaraby.local)
- عيادة الإسكندرية (alexandria.Almobtaker Alaraby.local)
- عيادة الجيزة (giza.Almobtaker Alaraby.local)

### لكل مؤسسة:
- 1 مسؤول (Admin)
- 3 موظفين (Users)

### الأدوار:
- `super_admin` - جميع الصلاحيات
- `user` - صلاحيات محدودة

---

## 🛠️ الأوامر المهمة

```bash
# إعادة تشغيل كاملة
php artisan migrate:fresh --seed

# فتح Tinker للاختبار
php artisan tinker

# عرض السجلات
tail -f storage/logs/laravel.log

# تشغيل الاختبارات
php artisan test

# إنشاء مستخدم جديد
php artisan make:filament-user
```

---

## 📚 التوثيق

```
📄 PROJECT_SUMMARY.md        - ملخص شامل للمشروع
📄 MULTI_TENANCY_GUIDE.md    - شرح تفصيلي لـ Multi-Tenancy
📄 SETUP_QUICK_START.md      - خطوات التثبيت السريع
📄 IMPLEMENTATION_SUMMARY.md - ملخص التنفيذ التقني
📄 QUICK_REFERENCE.md        - نصائح سريعة وحلول
📄 TESTING_GUIDE.md          - دليل الاختبار الشامل
```

---

## 🎯 الميزات المرتقبة

- [ ] Patient Management
- [ ] Appointment Scheduling
- [ ] Medical Records
- [ ] Billing System
- [ ] Reports & Analytics
- [ ] SMS Notifications
- [ ] Email Integration
- [ ] API Documentation

---

## 🤝 المساهمة

تفضل بالمساهمة! يرجى:

1. Fork المشروع
2. عمل branch جديد (`git checkout -b feature/amazing-feature`)
3. Commit التغييرات (`git commit -m 'Add amazing feature'`)
4. Push إلى الـ branch (`git push origin feature/amazing-feature`)
5. فتح Pull Request

---

## 📝 الترخيص

MIT License - انظر ملف `LICENSE` للتفاصيل

---

## 👨‍💻 الدعم

للأسئلة والمشاكل:
1. اقرأ الملفات التوثيقية
2. استخدم Tinker للاختبار
3. تحقق من السجلات
4. أعد تشغيل البيانات

---

## 📈 إحصائيات المشروع

| المقياس | القيمة |
|--------|--------|
| Laravel Version | 12.40.2 |
| Filament Version | 4.x |
| PHP Version | 8.3+ |
| Database | MySQL |
| Default Language | Arabic (ar) |
| Test Users | 12 |
| Test Tenants | 3 |
| Roles | 2 |
| Permissions | 10+ |

---

## 🎉 الحالة الحالية

✅ **Production Ready**

جميع الميزات الأساسية مكتملة وجاهزة للاستخدام!

---

**آخر تحديث:** ديسمبر 2025  
**الإصدار:** 1.0.0  
**الحالة:** ✅ مكتمل وجاهز للإنتاج

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# aischool
