# دليل إعداد Multi-Panel في Filament 4
# Filament 4 Multi-Panel Setup Guide

## 🎯 الهدف | Objective

إنشاء 3 لوحات منفصلة في النظام:
1. **Owner Panel** - لوحة المدير العام (يشوف كل الفروع)
2. **Branch Panel** - لوحة الفرع (فرع واحد فقط)
3. **Student Portal** - بوابة الطالب (خارج Filament لاحقًا)

---

## 📋 الخطوات | Steps

### Step 1: إنشاء Owner Panel (Admin Panel)

```bash
php artisan make:filament-panel owner
```

**الإعدادات في `app/Providers/Filament/OwnerPanelProvider.php`:**

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OwnerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('owner')
            ->path('owner')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Owner/Resources'), for: 'App\\Filament\\Owner\\Resources')
            ->discoverPages(in: app_path('Filament/Owner/Pages'), for: 'App\\Filament\\Owner\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Owner/Widgets'), for: 'App\\Filament\\Owner\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // Custom middleware لـ Owner فقط
            ->authMiddleware([
                \App\Http\Middleware\EnsureUserIsOwner::class,
            ])
            ->navigationGroups([
                'إدارة النظام',
                'الفروع',
                'التقارير',
                'المالية',
                'الإعدادات',
            ])
            ->brandName('لوحة المدير العام')
            ->brandLogo(asset('images/logo.png'))
            ->favicon(asset('images/favicon.png'))
            ->sidebarCollapsibleOnDesktop()
            ->spa();
    }
}
```

---

### Step 2: إنشاء Branch Panel

```bash
php artisan make:filament-panel branch
```

**الإعدادات في `app/Providers/Filament/BranchPanelProvider.php`:**

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class BranchPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('branch')
            ->path('branch')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Branch/Resources'), for: 'App\\Filament\\Branch\\Resources')
            ->discoverPages(in: app_path('Filament/Branch/Pages'), for: 'App\\Filament\\Branch\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Branch/Widgets'), for: 'App\\Filament\\Branch\\Widgets')
            ->widgets([
                // Widgets\StatsOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // Custom middleware لفلترة الفرع
            ->authMiddleware([
                \App\Http\Middleware\SetTenantBranch::class,
            ])
            ->navigationGroups([
                'الطلاب والكورسات',
                'الحضور والجلسات',
                'المدفوعات',
                'التقارير',
                'الإعدادات',
            ])
            ->brandName('لوحة إدارة الفرع')
            ->brandLogo(fn () => auth()->user()?->branch?->logo_url ?? asset('images/logo.png'))
            ->favicon(asset('images/favicon.png'))
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            // عرض اسم الفرع في الـ Header
            ->userMenuItems([
                'branch' => [
                    'label' => fn () => 'الفرع: ' . auth()->user()?->branch?->name,
                    'icon' => 'heroicon-o-building-office',
                    'url' => '#',
                ],
            ]);
    }
}
```

---

### Step 3: إنشاء Middleware للـ Owner

```bash
php artisan make:middleware EnsureUserIsOwner
```

**في `app/Http/Middleware/EnsureUserIsOwner.php`:**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق من أن المستخدم Owner أو Admin
        if (!auth()->check() || !auth()->user()->isOwner()) {
            abort(403, 'غير مصرح لك بالوصول إلى لوحة المدير العام');
        }

        return $next($request);
    }
}
```

---

### Step 4: إنشاء Middleware لتحديد الفرع

```bash
php artisan make:middleware SetTenantBranch
```

**في `app/Http/Middleware/SetTenantBranch.php`:**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantBranch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Owner يستطيع الوصول (لكن لن يُستخدم هذا الـ panel)
        if ($user?->isOwner()) {
            return $next($request);
        }

        // تحديد الفرع من المستخدم
        if ($user && $user->branch_id) {
            // حفظ branch_id في session أو config للاستخدام في الـ Scopes
            config(['tenant.current_branch_id' => $user->branch_id]);
        } else {
            abort(403, 'لا يوجد فرع مرتبط بحسابك');
        }

        return $next($request);
    }
}
```

---

### Step 5: إضافة Methods في User Model

**في `app/Models/User.php`:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // العلاقة مع الفرع
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // التحقق من دور Owner
    public function isOwner(): bool
    {
        return $this->hasRole('owner') || $this->role === 'owner';
    }

    // التحقق من دور Branch Manager
    public function isBranchManager(): bool
    {
        return $this->hasRole('branch_manager') || $this->role === 'branch_manager';
    }

    // التحقق من دور Instructor
    public function isInstructor(): bool
    {
        return $this->hasRole('instructor') || $this->role === 'instructor';
    }

    // التحقق من دور Secretary
    public function isSecretary(): bool
    {
        return $this->hasRole('secretary') || $this->role === 'secretary';
    }

    // الحصول على الـ Panel الصحيح حسب الدور
    public function getDefaultPanel(): string
    {
        if ($this->isOwner()) {
            return 'owner';
        }

        return 'branch';
    }
}
```

---

### Step 6: تحديث Login لإعادة التوجيه حسب الدور

في Filament، يمكن تخصيص redirect بعد Login في كل Panel:

**في `OwnerPanelProvider` أضف:**

```php
->loginRedirect(fn () => route('filament.owner.pages.dashboard'))
```

**في `BranchPanelProvider` أضف:**

```php
->loginRedirect(fn () => route('filament.branch.pages.dashboard'))
```

---

### Step 7: إنشاء Dashboard Pages

**Owner Dashboard:**
```bash
php artisan make:filament-page Dashboard --type=custom --panel=owner
```

**Branch Dashboard:**
```bash
php artisan make:filament-page Dashboard --type=custom --panel=branch
```

---

## 🗂️ الهيكل النهائي | Final Structure

```
app/
├── Filament/
│   ├── Owner/                    # Owner Panel
│   │   ├── Resources/
│   │   │   ├── BranchResource.php
│   │   │   ├── UserResource.php
│   │   │   └── ...
│   │   ├── Pages/
│   │   │   ├── Dashboard.php
│   │   │   └── ...
│   │   └── Widgets/
│   │       └── ...
│   │
│   └── Branch/                   # Branch Panel
│       ├── Resources/
│       │   ├── StudentResource.php
│       │   ├── CourseResource.php
│       │   ├── GroupResource.php
│       │   ├── PaymentResource.php
│       │   └── ...
│       ├── Pages/
│       │   ├── Dashboard.php
│       │   └── ...
│       └── Widgets/
│           └── ...
│
├── Http/
│   └── Middleware/
│       ├── EnsureUserIsOwner.php
│       └── SetTenantBranch.php
│
├── Models/
│   ├── User.php
│   ├── Branch.php
│   └── ...
│
└── Providers/
    └── Filament/
        ├── OwnerPanelProvider.php
        └── BranchPanelProvider.php
```

---

## 🔗 URLs

- **Owner Panel:** `https://yourapp.test/owner`
- **Branch Panel:** `https://yourapp.test/branch`
- **(لاحقًا) Student Portal:** `https://yourapp.test/student`

---

## ✅ Checklist

- [ ] إنشاء Owner Panel
- [ ] إنشاء Branch Panel
- [ ] إنشاء Middleware: EnsureUserIsOwner
- [ ] إنشاء Middleware: SetTenantBranch
- [ ] تحديث User Model بـ methods الأدوار
- [ ] إنشاء Dashboard لكل Panel
- [ ] تسجيل الـ Panels في `bootstrap/providers.php`
- [ ] اختبار Login في كل Panel
- [ ] التأكد من فصل الصلاحيات

---

## 🧪 الاختبار | Testing

### Test Owner Panel Access:
1. سجل دخول بحساب Owner
2. افتح `/owner`
3. تأكد من رؤية جميع الفروع في الـ Resources

### Test Branch Panel Access:
1. سجل دخول بحساب Branch Manager
2. افتح `/branch`
3. تأكد من رؤية بيانات الفرع فقط

### Test Unauthorized Access:
1. حاول الدخول لـ `/owner` بحساب Branch Manager
2. يجب أن تحصل على 403 Forbidden

---

## 📝 ملاحظات | Notes

### لماذا Multi-Panel وليس Panel واحد؟

✅ **المميزات:**
- فصل واضح بين Owner و Branch
- Resources مختلفة لكل Panel (مثلاً: Owner يحتاج BranchResource، Branch لا يحتاجه)
- Customization أسهل (ألوان، navigation، widgets مختلفة)
- Performance أفضل (كل Panel يُحمّل Resources الخاصة به فقط)

❌ **العيوب:**
- Duplication محتمل (لكن يُحل بـ Traits/Base Classes)
- صيانة أكثر (لكن مع التنظيم الجيد، سهلة)

---

**آخر تحديث:** 2025-12-18  
**الحالة:** 🟡 جاهز للتنفيذ
