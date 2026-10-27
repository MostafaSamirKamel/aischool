# 🚀 دليل البدء السريع | Quick Start Guide

**نظام إدارة مدرسة البرمجة - Laravel 12 + Filament 4 + Multi-Tenancy**

---

## ✅ ما تم إنجازه | Completed

### Sprint 0: الإعداد الأولي ✅

- [x] إنشاء Owner Panel (`/owner`)
- [x] إنشاء Branch Panel (`/branch`)
- [x] إعداد Middleware للتحقق من الصلاحيات
  - `EnsureUserIsOwner` - للتحقق من Owner
  - `SetTenantBranch` - لتحديد الفرع
- [x] إضافة methods في User Model:
  - `isOwner()`, `isBranchManager()`, `isInstructor()`, `isSecretary()`, `isStudent()`, `isParent()`
  - `getDefaultPanel()` - للحصول على الـ Panel الافتراضي
  - `branch()` - علاقة مع Tenant
- [x] إنشاء BranchScope (Global Scope للفلترة التلقائية)
- [x] إنشاء BelongsToBranch Trait (للموديلات المشتركة)

### الملفات التوثيقية ✅

- [x] `ARCHITECTURE_PLAN.md` - المعمارية الكاملة للنظام
- [x] `COPILOT_PROMPTS_TEMPLATE.md` - قوالب Prompts لـ GitHub Copilot
- [x] `MULTI_PANEL_SETUP.md` - دليل إعداد Multi-Panel
- [x] `MULTI_TENANCY_SETUP.md` - دليل إعداد Multi-Tenancy

---

## 🎯 الخطوة التالية | Next Steps

### Sprint 1: الفروع + المستخدمين + الصلاحيات

**الهدف:** إنشاء نظام إدارة الفروع والمستخدمين بصلاحيات واضحة

#### المهام:

1. **إعداد الأدوار والصلاحيات (Roles & Permissions)**
   ```bash
   php artisan shield:install
   ```
   - Owner / Super Admin
   - Branch Manager
   - Instructor
   - Secretary
   - Student
   - Parent

2. **إنشاء Tenant (Branch) Model + Migration**
   - إذا لم يكن موجودًا بالفعل
   - أو تحديث الموديل الحالي

3. **إنشاء Resources في Owner Panel:**
   - `BranchResource` - إدارة الفروع
   - `UserResource` - إدارة جميع المستخدمين

4. **إنشاء Resources في Branch Panel:**
   - `UserResource` - إدارة مستخدمي الفرع فقط

5. **إنشاء Policies:**
   - `BranchPolicy` (للـ Tenant)
   - `UserPolicy`

6. **إنشاء Seeders:**
   - RoleSeeder - الأدوار الأساسية
   - BranchSeeder - فروع تجريبية
   - UserSeeder - مستخدمين تجريبيين

7. **الاختبارات:**
   - Feature Tests للـ Resources
   - Policy Tests

---

## 🛠️ كيفية استخدام GitHub Copilot

### 1. افتح ملف `COPILOT_PROMPTS_TEMPLATE.md`

### 2. انسخ الـ Template المناسب

مثلاً، لإنشاء Branch Model:

```
Create a Laravel Model for Branch (Tenant) in Programming School System.

Context:
- Multi-tenant system (this is the tenant/branch model itself)
- Role-based access: Owner (all branches), BranchManager (own branch)

Requirements:
- Migration with:
  * id (primary key, string, unique code like 'branch_abc')
  * name (string)
  * type (enum: physical/online)
  * address (text, nullable)
  * phone (string)
  * email (string, nullable)
  * status (enum: active/inactive)
  * settings (JSON, nullable)
  * timestamps, soft deletes
  
- Model with:
  * No BranchScope (this IS the branch)
  * Relationships: users (HasMany), students (HasMany), courses (HasMany), etc.
  * Fillable all except id
  * Casts: status as enum, settings as array
  
- Policy:
  * viewAny: only Owner
  * view: Owner sees all, BranchManager sees own only
  * create/update/delete: Owner only
  
- Factory with realistic Arabic names
- Feature test covering CRUD as Owner

Generate complete code with Arabic comments where helpful.
```

### 3. الصق في ملف جديد واترك Copilot يكمل

### 4. راجع الكود وعدّل إذا لزم الأمر

---

## 📂 هيكل الملفات الحالي | Current Structure

```
app/
├── Filament/
│   ├── Admin/                       # Panel افتراضي (سيُزال لاحقًا)
│   ├── Owner/                       # ✅ Owner Panel
│   │   ├── Resources/               # Resources للـ Owner
│   │   ├── Pages/                   # صفحات مخصصة
│   │   └── Widgets/                 # ودجتات Dashboard
│   └── Branch/                      # ✅ Branch Panel
│       ├── Resources/               # Resources للفرع
│       ├── Pages/                   # صفحات مخصصة
│       └── Widgets/                 # ودجتات Dashboard
│
├── Http/
│   └── Middleware/
│       ├── EnsureUserIsOwner.php    # ✅ التحقق من Owner
│       └── SetTenantBranch.php      # ✅ تحديد الفرع
│
├── Models/
│   ├── Concerns/
│   │   └── BelongsToBranch.php      # ✅ Trait للموديلات المشتركة
│   ├── Scopes/
│   │   └── BranchScope.php          # ✅ Global Scope للفلترة
│   ├── User.php                     # ✅ محدّث بـ methods الأدوار
│   └── Tenant.php                   # موجود (Branch/Tenant)
│
└── Providers/
    └── Filament/
        ├── AdminPanelProvider.php   # Panel افتراضي
        ├── OwnerPanelProvider.php   # ✅ Owner Panel
        └── BranchPanelProvider.php  # ✅ Branch Panel
```

---

## 🔐 URLs للوصول

- **Admin Panel (افتراضي):** `http://localhost/admin`
- **Owner Panel:** `http://localhost/owner` ✅
- **Branch Panel:** `http://localhost/branch` ✅

---

## 🧪 الاختبار | Testing

### اختبار Owner Panel:

1. سجل دخول بحساب لديه `is_super_admin = true`
2. افتح `/owner`
3. يجب أن تصل بنجاح

### اختبار Branch Panel:

1. سجل دخول بحساب لديه `tenant_id` وليس Owner
2. افتح `/branch`
3. يجب أن تصل بنجاح وترى بيانات فرعك فقط

### اختبار الصلاحيات:

1. حاول الدخول لـ `/owner` بحساب Branch Manager
2. يجب أن تحصل على **403 Forbidden**

---

## 📝 الأوامر المفيدة | Useful Commands

```bash
# إنشاء Resource جديد في Owner Panel
php artisan make:filament-resource Branch --panel=owner

# إنشاء Resource جديد في Branch Panel
php artisan make:filament-resource Student --panel=branch

# إنشاء Migration
php artisan make:migration create_branches_table

# إنشاء Model + Migration
php artisan make:model Branch -m

# إنشاء Policy
php artisan make:policy BranchPolicy --model=Branch

# إنشاء Seeder
php artisan make:seeder RoleSeeder

# تشغيل Migrations
php artisan migrate

# تشغيل Seeders
php artisan db:seed

# إنشاء Filament User (Owner)
php artisan make:filament-user

# تثبيت Spatie Permissions في Filament
php artisan shield:install
```

---

## 🎨 التخصيص | Customization

### تغيير ألوان الـ Panel:

**Owner Panel (أزرق):**
```php
// في OwnerPanelProvider.php
->colors([
    'primary' => Color::Blue,
])
```

**Branch Panel (أخضر):**
```php
// في BranchPanelProvider.php
->colors([
    'primary' => Color::Green,
])
```

### تغيير اسم الـ Brand:

```php
->brandName('لوحة المدير العام')
->brandLogo(asset('images/logo.png'))
```

---

## 🚨 ملاحظات مهمة | Important Notes

### 1. Multi-Tenancy

- ✅ **BranchScope** يُطبق تلقائيًا على أي موديل يستخدم `BelongsToBranch`
- ✅ **Owner** يرى كل الفروع (Scope لا يُطبق عليه)
- ⚠️ **تذكر:** عند إنشاء Resources في Owner Panel، استخدم `withoutBranchScope()` لرؤية كل البيانات

### 2. الصلاحيات

- ✅ استخدم **Spatie Laravel Permission** للأدوار
- ✅ كل Resource يحتاج **Policy**
- ⚠️ **تذكر:** اختبر الـ Policies جيدًا لتجنب تسريب البيانات

### 3. الـ Seeders

عند عمل Seeding، حدد `tenant_id` يدويًا:

```php
Student::factory()->create([
    'tenant_id' => 'branch_1',
    'name' => 'أحمد محمد',
]);
```

---

## 📚 الملفات المرجعية | Reference Files

1. **المعمارية الكاملة:** [ARCHITECTURE_PLAN.md](ARCHITECTURE_PLAN.md)
2. **قوالب Copilot:** [COPILOT_PROMPTS_TEMPLATE.md](COPILOT_PROMPTS_TEMPLATE.md)
3. **Multi-Panel Setup:** [MULTI_PANEL_SETUP.md](MULTI_PANEL_SETUP.md)
4. **Multi-Tenancy Setup:** [MULTI_TENANCY_SETUP.md](MULTI_TENANCY_SETUP.md)

---

## 🎯 خارطة الطريق | Roadmap

### ✅ Sprint 0: الإعداد (مكتمل)

### 🔲 Sprint 1: الفروع + المستخدمين + الصلاحيات (التالي)
- [ ] إعداد Roles & Permissions
- [ ] Branch (Tenant) Model + Resource
- [ ] User Management Resources
- [ ] Policies
- [ ] Seeders & Tests

### 🔲 Sprint 2: الكورسات والتراكات (أسبوع 2)

### 🔲 Sprint 3: الطلاب + QR (أسبوع 3)

### 🔲 Sprint 4: الحضور والجلسات (أسبوع 4)

### 🔲 Sprint 5: المدفوعات والخزنة (أسبوع 5)

### 🔲 Sprint 6: رواتب المدربين (أسبوع 6)

### 🔲 Sprint 7: المواد والتقييمات + Portal (أسبوعين)

---

## 💡 نصائح للعمل مع GitHub Copilot

1. **كن واضحًا:** اكتب تعليقات واضحة بالإنجليزية توضح ما تريد
2. **استخدم القوالب:** القوالب في `COPILOT_PROMPTS_TEMPLATE.md` جاهزة ومُختبرة
3. **راجع الكود:** لا تقبل كل اقتراحات Copilot - راجع دائمًا
4. **السياق مهم:** اذكر دائمًا أن النظام multi-tenant وما هي الأدوار
5. **اطلب Tests:** دائمًا اطلب من Copilot توليد Tests مع الكود

---

## 🆘 إذا واجهت مشكلة | Troubleshooting

### المشكلة: 403 عند الدخول لـ Owner Panel

**الحل:**
- تأكد من أن المستخدم لديه `is_super_admin = true` أو دور `owner`
- تأكد من أن `isOwner()` method شغال صح

### المشكلة: لا أرى بيانات الفرع في Branch Panel

**الحل:**
- تأكد من أن المستخدم لديه `tenant_id`
- تأكد من أن الموديل يستخدم `BelongsToBranch` Trait
- تأكد من أن `SetTenantBranch` middleware مُفعّل

### المشكلة: Owner يرى فقط بيانات فرع واحد

**الحل:**
- في Owner Panel Resources، استخدم:
  ```php
  public static function getEloquentQuery(): Builder
  {
      return parent::getEloquentQuery()->withoutBranchScope();
  }
  ```

---

## 📞 الدعم | Support

للأسئلة أو المشاكل:
1. راجع الملفات التوثيقية أولاً
2. اقرأ الأكواد الموجودة في الـ Traits و Scopes
3. اختبر الـ Policies باستخدام Feature Tests

---

**آخر تحديث:** 2025-12-18  
**الحالة:** ✅ Sprint 0 مكتمل - جاهز لبدء Sprint 1

---

## 🎉 تهانينا!

لقد أكملت الإعداد الأولي للنظام! الآن يمكنك البدء في Sprint 1 لإنشاء إدارة الفروع والمستخدمين.

**التوصية:** ابدأ بقراءة `COPILOT_PROMPTS_TEMPLATE.md` ثم استخدم القوالب لتوليد الكود بسرعة.

**Good luck! 🚀**
