# 🎓 نظام إدارة مدرسة البرمجة
# Programming School Management System

<div dir="rtl">

نظام شامل لإدارة مدرسة برمجة متعددة الفروع باستخدام **Laravel 12** و **Filament 4** مع بنية **Multi-Tenancy**.

</div>

---

## 🌟 المميزات | Features

### 🏢 إدارة متعددة الفروع (Multi-Branch Management)
- فروع فيزيائية وأونلاين
- بيانات منفصلة لكل فرع
- تقارير موحدة للمدير العام

### 👥 إدارة الأدوار والصلاحيات
- **Owner/Super Admin** - الوصول الكامل
- **Branch Manager** - إدارة فرع واحد
- **Instructor** - إدارة المجموعات والطلاب
- **Secretary** - التسجيل والمدفوعات
- **Student** - بوابة الطالب
- **Parent** - متابعة تقدم الطالب

### 📚 إدارة التعليم
- مسارات تعليمية (Tracks) ودورات (Courses)
- مجموعات (Groups) بسعات محددة
- جدولة ذكية لمنع التعارضات
- إدارة القاعات والموارد

### 👨‍🎓 إدارة الطلاب
- تسجيل الطلاب مع QR/Barcode
- ملفات شخصية شاملة
- تتبع التقدم والتقييمات
- نظام الاشتراكات المرن

### ⏰ نظام الحضور
- تسجيل الحضور بـ QR/Barcode
- تقارير الحضور والغياب
- تتبع تلقائي للمتأخرين

### 💰 الإدارة المالية
- إدارة المدفوعات (كامل/شهري/حسب المستوى)
- نظام الخزنة والمصروفات
- حسابات المدربين والرواتب
- تقارير مالية شاملة

### 📊 التقارير والتحليلات
- تقارير الأداء
- التقارير المالية
- نسب الحضور والإتمام
- تحليلات مخصصة

### 📱 بوابة الطالب/ولي الأمر
- متابعة التقدم
- رفع التسليمات
- التقييمات
- عرض المدفوعات

---

## 🏗️ المعمارية | Architecture

### Multi-Panel System

```
┌─────────────────────────────────────────────┐
│           Owner Panel (/owner)              │
│  - إدارة جميع الفروع                       │
│  - التقارير الموحدة                        │
│  - التحويلات المالية                       │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│          Branch Panel (/branch)             │
│  - إدارة فرع واحد                          │
│  - الطلاب والكورسات                        │
│  - الحضور والمدفوعات                       │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│      Student Portal (قريبًا)                │
│  - تسجيل الدخول                            │
│  - متابعة التقدم                            │
│  - التسليمات والتقييمات                    │
└─────────────────────────────────────────────┘
```

### Multi-Tenancy Strategy

**Single Database + tenant_id** (مُختار)
- فصل منطقي للبيانات
- تقارير موحدة سهلة
- أداء ممتاز

---

## 🚀 التثبيت | Installation

### المتطلبات | Requirements

- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & NPM

### الخطوات | Steps

```bash
# 1. Clone المشروع
git clone <repository-url>
cd ai-school

# 2. تثبيت Dependencies
composer install
npm install

# 3. إعداد البيئة
cp .env.example .env
php artisan key:generate

# 4. إعداد قاعدة البيانات
# عدّل .env بمعلومات قاعدة البيانات
php artisan migrate --seed

# 5. إنشاء Storage Link
php artisan storage:link

# 6. Build Assets
npm run build

# 7. إنشاء Owner User
php artisan make:filament-user
# Email: admin@example.com
# Password: password
# is_super_admin: true
```

---

## 📖 التوثيق | Documentation

### 🎯 ابدأ من هنا
- **[📖 دليل البدء السريع](QUICK_START_IMPLEMENTATION.md)** - الأهم!
- **[📝 قوالب GitHub Copilot](COPILOT_PROMPTS_TEMPLATE.md)** - للتطوير السريع
- **[🤖 دليل Vibe Coding](VIBE_CODING_GUIDE.md)** - التطوير بالذكاء الاصطناعي

### 📚 التوثيق الكامل
- [🏛️ المعمارية الكاملة](ARCHITECTURE_PLAN.md)
- [🎛️ إعداد Multi-Panel](MULTI_PANEL_SETUP.md)
- [🏢 إعداد Multi-Tenancy](MULTI_TENANCY_SETUP.md)
- [📚 فهرس التوثيق الكامل](DOCUMENTATION_INDEX_COMPLETE.md)

---

## 🛠️ التطوير | Development

### استخدام GitHub Copilot

نحن نستخدم **Vibe Coding** للتطوير السريع:

```php
// مثال: إنشاء Model جديد
// Create Student model for Programming School System
// Multi-tenant (uses BelongsToBranch trait)
// Fields: tenant_id, name, phone, email, qr_code, status
// Relationships: branch, enrollments, attendances
// Soft deletes

class Student extends Model
{
    // GitHub Copilot سيكمل تلقائيًا!
```

اقرأ [دليل Vibe Coding](VIBE_CODING_GUIDE.md) للمزيد.

### الأوامر المفيدة

```bash
# إنشاء Resource جديد
php artisan make:filament-resource Student --panel=branch

# إنشاء Migration
php artisan make:migration create_students_table

# إنشاء Policy
php artisan make:policy StudentPolicy --model=Student

# تشغيل Tests
php artisan test

# مسح الـ Cache
php artisan optimize:clear
```

---

## 🧪 الاختبار | Testing

```bash
# تشغيل جميع الاختبارات
php artisan test

# اختبارات محددة
php artisan test --filter=StudentTest

# مع Coverage
php artisan test --coverage
```

---

## 📦 التقنيات المستخدمة | Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **Filament 4** - Admin Panel
- **Spatie Laravel Permission** - Roles & Permissions
- **Spatie Activity Log** - Audit Trail

### Frontend
- **Livewire 3** - Dynamic UI
- **Alpine.js** - JavaScript Framework
- **Tailwind CSS** - Styling

### Database
- **MySQL 8** - Primary Database
- **Redis** (Optional) - Caching & Queues

### Tools
- **GitHub Copilot** - AI-Assisted Development
- **PHPUnit** - Testing
- **Laravel Pint** - Code Style

---

## 📊 الحالة الحالية | Current Status

### ✅ Sprint 0: الإعداد (مكتمل)
- [x] Multi-Panel Setup
- [x] Multi-Tenancy System
- [x] Middleware & Authorization
- [x] Complete Documentation

### 🔄 Sprint 1: الفروع + المستخدمين (قيد التنفيذ)
- [ ] Roles & Permissions
- [ ] Branch Management
- [ ] User Management
- [ ] Policies & Tests

### 🔲 قريبًا...
- Sprint 2: الكورسات والتراكات
- Sprint 3: الطلاب + QR
- Sprint 4: الحضور والجلسات
- Sprint 5: المدفوعات والخزنة
- Sprint 6: رواتب المدربين
- Sprint 7: المواد والتقييمات + Portal

---

## 🔐 الأمان | Security

- ✅ Branch-level data isolation
- ✅ Role-based access control (RBAC)
- ✅ Policy-based authorization
- ✅ Activity logging
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ XSS protection

---

## 🌐 URLs

- **Owner Panel:** `http://localhost/owner`
- **Branch Panel:** `http://localhost/branch`
- **Admin Panel:** `http://localhost/admin` (legacy)

---

## 🤝 المساهمة | Contributing

نرحب بالمساهمات! يرجى:

1. Fork المشروع
2. إنشاء Feature Branch
3. Commit التغييرات
4. Push للـ Branch
5. فتح Pull Request

---

## 📝 الترخيص | License

هذا المشروع ملك [اسم المؤسسة]. جميع الحقوق محفوظة.

---

## 👥 الفريق | Team

- **تطوير:** [اسم المطور]
- **تصميم:** [اسم المصمم]
- **إدارة المشروع:** [اسم المدير]

---

## 📞 التواصل | Contact

- **البريد الإلكتروني:** info@example.com
- **الموقع:** https://example.com
- **الدعم الفني:** support@example.com

---

## 🎉 شكر خاص | Special Thanks

- **Laravel Team** - لـ Framework الرائع
- **Filament Team** - لـ Admin Panel المذهل
- **GitHub Copilot** - للمساعدة في التطوير السريع
- **المجتمع العربي** - للدعم المستمر

---

<div align="center">

**صُنع بـ ❤️ في مصر**

**Made with ❤️ in Egypt**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-4-FFAA00?style=for-the-badge)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)](https://php.net)

</div>
