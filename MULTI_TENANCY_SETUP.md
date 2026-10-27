# دليل إعداد نظام Multi-Tenancy
# Multi-Tenancy System Setup Guide

## 🎯 الهدف | Objective

إنشاء نظام multi-tenancy بحيث:
- كل فرع (Branch/Tenant) له بياناته المنفصلة منطقيًا
- Owner يشوف كل الفروع
- مدير الفرع يشوف فرعه فقط
- الموديلات تُفلتر تلقائيًا حسب الفرع

---

## 🏗️ الاستراتيجية المختارة | Strategy

**Single Database + tenant_id (Recommended)**

### المميزات:
- ✅ تقارير موحدة سهلة للـ Owner
- ✅ نسخ احتياطي واحد
- ✅ Migrations أسهل
- ✅ أداء أفضل للـ Joins عبر الفروع

### التحديات:
- ⚠️ يحتاج Global Scopes دقيقة
- ⚠️ Policy testing ضروري جدًا

---

## 📋 الخطوات | Implementation Steps

### Step 1: إنشاء BranchScope (Global Scope)

هذا الـ Scope سيُطبق تلقائيًا على كل الموديلات التي تحتوي على `tenant_id` (أو `branch_id`)

**إنشاء الـ Scope:**

```bash
php artisan make:scope BranchScope
```

**في `app/Models/Scopes/BranchScope.php`:**

```php
<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // إذا المستخدم Owner، لا يُطبق أي فلترة
        if (auth()->check() && auth()->user()->isOwner()) {
            return;
        }

        // إذا المستخدم مسجل دخول ولديه tenant_id
        if (auth()->check() && auth()->user()->tenant_id) {
            $builder->where($model->getTable() . '.tenant_id', auth()->user()->tenant_id);
        } else {
            // إذا لم يكن مسجل دخول أو لا يوجد tenant_id، نُرجع نتيجة فارغة
            $builder->whereRaw('0 = 1');
        }
    }
}
```

---

### Step 2: إنشاء Trait للموديلات المشتركة

**إنشاء Trait:**

```php
<?php

namespace App\Models\Concerns;

use App\Models\Scopes\BranchScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToBranch(): void
    {
        // تطبيق BranchScope تلقائيًا
        static::addGlobalScope(new BranchScope());

        // عند الإنشاء، تعيين tenant_id تلقائيًا
        static::creating(function ($model) {
            if (!$model->tenant_id && auth()->check()) {
                // Owner لا يُعين tenant_id تلقائيًا (يجب تحديده يدويًا)
                if (!auth()->user()->isOwner()) {
                    $model->tenant_id = auth()->user()->tenant_id;
                }
            }
        });
    }

    /**
     * Get the branch (tenant) that owns the model
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Get the tenant (alias for branch)
     */
    public function tenant(): BelongsTo
    {
        return $this->branch();
    }

    /**
     * Scope a query to only include models from a specific branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('tenant_id', $branchId);
    }

    /**
     * Disable branch scope for Owner queries
     */
    public function scopeWithoutBranchScope($query)
    {
        return $query->withoutGlobalScope(BranchScope::class);
    }
}
```

**حفظ الـ Trait في:**
`app/Models/Concerns/BelongsToBranch.php`

---

### Step 3: تطبيق الـ Trait على الموديلات

**مثال: Student Model**

```php
<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'parent_name',
        'parent_phone',
        'parent_email',
        'qr_code',
        'barcode',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // العلاقات
    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
```

---

### Step 4: الموديلات التي تحتاج الـ Trait

✅ **يجب تطبيق `BelongsToBranch` على:**

- Student
- Course
- Track
- Group
- Classroom
- Session
- Attendance
- Payment
- CashboxTransaction
- Expense
- InstructorPayment
- Material
- Submission
- Rating
- أي موديل يحتوي على `tenant_id`

❌ **لا تطبق على:**

- User (لأنه موجود عبر كل الفروع)
- Tenant (الفرع نفسه)
- Permission / Role (Spatie)

---

### Step 5: الـ Policies للتحقق من الصلاحيات

**مثال: StudentPolicy**

```php
<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine if the user can view any students
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_students');
    }

    /**
     * Determine if the user can view the student
     */
    public function view(User $user, Student $student): bool
    {
        // Owner يشوف كل الطلاب
        if ($user->isOwner()) {
            return true;
        }

        // باقي المستخدمين يشوفوا طلاب فرعهم فقط
        return $student->tenant_id === $user->tenant_id;
    }

    /**
     * Determine if the user can create students
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'branch_manager', 'secretary']);
    }

    /**
     * Determine if the user can update the student
     */
    public function update(User $user, Student $student): bool
    {
        // Owner يقدر يعدل أي طالب
        if ($user->isOwner()) {
            return true;
        }

        // Branch Manager & Secretary يقدروا يعدلوا طلاب فرعهم
        if ($user->hasAnyRole(['branch_manager', 'secretary'])) {
            return $student->tenant_id === $user->tenant_id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the student
     */
    public function delete(User $user, Student $student): bool
    {
        // Owner أو Branch Manager فقط
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isBranchManager()) {
            return $student->tenant_id === $user->tenant_id;
        }

        return false;
    }
}
```

---

### Step 6: Filament Resource مع Branch Scoping

**مثال: StudentResource (Branch Panel)**

```php
<?php

namespace App\Filament\Branch\Resources;

use App\Models\Student;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'الطلاب';
    protected static ?string $navigationGroup = 'الطلاب والكورسات';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email(),
                // ... باقي الحقول
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'suspended',
                    ]),
                // ... باقي الأعمدة
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'موقوف',
                    ]),
            ]);
    }

    // لا نحتاج getEloquentQuery() لأن BranchScope يفلتر تلقائيًا!
}
```

**مثال: StudentResource (Owner Panel) - يشوف كل الفروع**

```php
<?php

namespace App\Filament\Owner\Resources;

use App\Models\Student;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'الطلاب';
    protected static ?string $navigationGroup = 'الفروع';

    // Owner يشوف كل الفروع - نعطل الـ Scope
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutBranchScope();
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                // إضافة عمود الفرع
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                // ... باقي الأعمدة
            ])
            ->filters([
                // فلتر الفرع للـ Owner
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name'),
                // ... باقي الفلاتر
            ]);
    }
}
```

---

## 🧪 الاختبار | Testing

### اختبار BranchScope

```php
<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_manager_sees_only_own_branch_students(): void
    {
        $branch1 = Tenant::factory()->create(['id' => 'branch1']);
        $branch2 = Tenant::factory()->create(['id' => 'branch2']);

        $manager1 = User::factory()->create(['tenant_id' => 'branch1']);
        $manager1->assignRole('branch_manager');

        Student::factory()->create(['tenant_id' => 'branch1']);
        Student::factory()->create(['tenant_id' => 'branch1']);
        Student::factory()->create(['tenant_id' => 'branch2']); // لا يجب أن يراه

        $this->actingAs($manager1);

        $students = Student::all();

        $this->assertCount(2, $students);
        $this->assertTrue($students->every(fn($s) => $s->tenant_id === 'branch1'));
    }

    public function test_owner_sees_all_branches_students(): void
    {
        $owner = User::factory()->create(['is_super_admin' => true]);

        Student::factory()->create(['tenant_id' => 'branch1']);
        Student::factory()->create(['tenant_id' => 'branch2']);

        $this->actingAs($owner);

        $students = Student::all();

        $this->assertCount(2, $students);
    }

    public function test_creating_student_auto_assigns_branch(): void
    {
        $manager = User::factory()->create(['tenant_id' => 'branch1']);
        $manager->assignRole('branch_manager');

        $this->actingAs($manager);

        $student = Student::create([
            'name' => 'Test Student',
            'phone' => '0123456789',
            'email' => 'test@example.com',
            'status' => 'active',
            // لاحظ: لم نحدد tenant_id، سيُعين تلقائيًا
        ]);

        $this->assertEquals('branch1', $student->tenant_id);
    }
}
```

---

## ✅ Checklist

- [ ] إنشاء `BranchScope`
- [ ] إنشاء `BelongsToBranch` Trait
- [ ] تطبيق الـ Trait على جميع الموديلات المناسبة
- [ ] إنشاء Policies لكل موديل
- [ ] تحديث Filament Resources (Branch Panel)
- [ ] تحديث Filament Resources (Owner Panel) مع `withoutBranchScope()`
- [ ] كتابة Feature Tests للـ Scoping
- [ ] كتابة Policy Tests
- [ ] اختبار يدوي كـ Owner
- [ ] اختبار يدوي كـ Branch Manager

---

## 📝 ملاحظات مهمة | Important Notes

### 1. متى نستخدم `withoutBranchScope()`؟

فقط في:
- Owner Panel Resources (لرؤية كل الفروع)
- Reports تجميعية للـ Owner
- Admin Commands/Jobs

### 2. الـ Seeding

عند عمل Seeding، تأكد من تحديد `tenant_id` يدويًا:

```php
// ملاحظة: tenant_id يجب أن يكون ID موجود في جدول tenants
Student::factory()->create([
    'tenant_id' => 'branch1', // أو ID فرع موجود
    'name' => 'Test Student',
]);
```

### 3. الـ API (إذا وُجد)

تأكد من تطبيق نفس المنطق في API Controllers:

```php
public function index(Request $request)
{
    // BranchScope سيُطبق تلقائيًا
    return Student::paginate();
}
```

---

**آخر تحديث:** 2025-12-18  
**الحالة:** 🟡 جاهز للتنفيذ
