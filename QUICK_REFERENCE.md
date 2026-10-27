🔥 QUICK TIPS & TRICKS
=====================

## لإضافة نموذج جديد مع Multi-Tenancy

### 1️⃣ إنشاء النموذج
```bash
php artisan make:model Patient -m
```

### 2️⃣ تحديث الـ Migration
```php
Schema::create('patients', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained();  // ← أضف هذا
    $table->string('name');
    $table->string('email');
    $table->timestamps();
});
```

### 3️⃣ تحديث النموذج
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use App\Models\Scopes\TenantScope;

#[ScopedBy([TenantScope::class])]  // ← أضف هذا
class Patient extends Model
{
    protected $fillable = ['tenant_id', 'name', 'email'];
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

### 4️⃣ تشغيل الـ Migration
```bash
php artisan migrate
```

✅ انتهى! جميع استعلامات Patient ستُفلتر تلقائياً حسب المؤسسة!

---

## لإضافة Filament Resource جديد

### 1️⃣ إنشاء Resource
```bash
php artisan make:filament-resource Patient
```

### 2️⃣ فتح الملف وأضف `tenant_id` في Form و Table

```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Hidden::make('tenant_id')
                ->default(auth()->user()->tenant_id),  // ← أضف هذا
            TextInput::make('name'),
            TextInput::make('email'),
        ]);
}
```

### 3️⃣ في الـ Table
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
            // لا تعرض tenant_id في الجدول
        ]);
}
```

✅ الـ Global Scope يتولى باقي الشغل!

---

## تصحيح الأخطاء الشائعة

### ❌ خطأ: مستخدم يرى بيانات مؤسسات أخرى

**الحل:**
```php
// ✅ تأكد من وجود الـ Attribute على النموذج
#[ScopedBy([TenantScope::class])]
class User extends Authenticatable { }

// ✅ تأكد من تشغيل البيانات الأولية لإضافة tenant_id
php artisan migrate:fresh --seed

// ✅ تأكد من أن المستخدم لديه tenant_id في قاعدة البيانات
User::all()->each(fn($u) => dd($u->tenant_id));
```

---

### ❌ خطأ: Middleware يقول "Method not found"

**الحل:**
```php
// ✅ تأكد من تسجيل الـ Middleware بشكل صحيح
// في bootstrap/app.php:
$middleware->web(
    append: [\App\Http\Middleware\SetTenantMiddleware::class]
)

// ✅ تأكد من أن الملف موجود
// app/Http/Middleware/SetTenantMiddleware.php
```

---

### ❌ خطأ: "Protected static property .. cannot be redeclared"

**الحل:**
```php
// ❌ خطأ
protected static string $view = 'view-name';

// ✅ صحيح (في Filament 4)
protected string $view = 'view-name';
```

---

## الاختبار السريع في Tinker

```bash
php artisan tinker
```

### 1️⃣ عرض جميع المؤسسات
```
>>> Tenant::all()
```

### 2️⃣ عرض جميع المستخدمين
```
>>> User::all()
```

### 3️⃣ اختبار الـ Scope
```
>>> session(['tenant_id' => 1])
>>> User::all()  # سيظهر مستخدمي المؤسسة 1 فقط!
```

### 4️⃣ عرض بيانات مستخدم محدد
```
>>> $user = User::first()
>>> $user->tenant_id
>>> $user->tenant->name
```

### 5️⃣ التحقق من الصلاحيات
```
>>> $user->roles
>>> $user->permissions
```

---

## الأوامر المهمة

```bash
# إعادة تشغيل كاملة
php artisan migrate:fresh --seed

# عرض السجلات
tail -f storage/logs/laravel.log

# تشغيل الخادم
php artisan serve

# بناء الأصول
npm run build

# تشغيل الاختبارات
php artisan test

# إنشاء مستخدم في الـ Tinker
php artisan tinker
>>> $user = User::factory()->create(['tenant_id' => 1])
```

---

## البنية النهائية المتوقعة

```
✅ Global Scope يصفي البيانات تلقائياً
✅ Middleware يعيّن السياق لكل طلب
✅ جميع النماذج لها tenant_id
✅ Filament Resources تدعم Multi-Tenancy
✅ الصلاحيات محكومة بـ Spatie Permission
✅ Activity Log مفعل على كل النماذج
```

---

## الخطة المستقبلية المقترحة

### الأسبوع 1:
- [ ] إضافة Patient model
- [ ] إضافة Appointment model
- [ ] إضافة Department model

### الأسبوع 2:
- [ ] بناء Filament Resources للنماذج الجديدة
- [ ] إضافة الصور والملفات

### الأسبوع 3:
- [ ] إضافة التقارير والإحصائيات
- [ ] بناء Dashboard مخصص

### الأسبوع 4:
- [ ] اختبارات شاملة
- [ ] Deployment إلى الإنتاج

---

**تذكر:** كلما أضفت نموذج جديد، أضف له `#[ScopedBy([TenantScope::class])]` و`tenant_id` في الـ Migration!

هذا هو السر وراء أمان وسهولة Multi-Tenancy الخاص بك! ✨
