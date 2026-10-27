# Laravel + Filament 4 Best Practices Guide
## 🎯 Universal Rules for Any Project

> **Purpose:** Copy this file to any Laravel + Filament project to ensure code quality and avoid common errors.
> 
> **Last Updated:** February 2026
> 
> **Compatibility:** Laravel 11+, Filament 4.x

---

## 🔴 Critical Rules - ALWAYS Follow

### 1. Filament 4 Type Declarations (MANDATORY)

```php
// ✅ CORRECT - Always use these types in Filament 4
protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document';
protected static \UnitEnum|string|null $navigationGroup = 'Group Name';
protected static \UnitEnum|string|null $navigationLabel = 'Label';
protected string $view = 'filament.pages.your-page'; // ⚠️ NON-STATIC!

// ❌ WRONG - Never use these (will cause type errors)
protected static ?string $navigationIcon = '...';      // ❌ Wrong type
protected static ?string $navigationGroup = '...';     // ❌ Wrong type
protected static string $view = '...';                 // ❌ NEVER static!
```

**Error Messages to Watch For:**
- `Cannot assign ?string to property $navigationIcon of type BackedEnum|string|null`
- `Cannot assign ?string to property $navigationGroup of type UnitEnum|string|null`
- `Cannot redeclare non static $view as static`

---

### 2. Eloquent Over Raw SQL (CRITICAL)

```php
// ✅ CORRECT - Use whereHas with relationships
Model::whereHas('relation', function($q) {
    $q->where('relation_table.column', 'value');
})->select('models.*')  // ⚠️ Always select specific table
  ->get();

// ❌ WRONG - Avoid manual JOINs (causes ambiguous column errors)
Model::join('related_table', 'models.id', '=', 'related_table.model_id')
     ->where('status', 'active')  // ❌ Ambiguous! Which table's status?
     ->get();
```

**Why?**
- `whereHas` respects Global Scopes (like tenant_id filtering)
- Manual JOINs bypass Global Scopes
- JOIN queries cause "Ambiguous column 'id'" errors

---

### 3. Type Safety (MANDATORY)

```php
// ✅ CORRECT - Always cast before number_format
number_format((float) $amount, 2)
number_format((float) $record->total, 2)

// ✅ CORRECT - Always null check with nullsafe operator
$data['items'] = $record->items?->pluck('id')->toArray() ?? [];
$name = $user->profile?->full_name ?? 'N/A';

// ✅ CORRECT - Use Carbon::parse for date operations
$date = \Carbon\Carbon::parse($record->created_at);
$startOfMonth = $date->copy()->startOfMonth();

// ❌ WRONG - Missing cast/null checks
number_format($amount, 2)              // ❌ May receive null
$record->items->pluck('id')            // ❌ Crashes if items is null
$record->date->copy()                  // ❌ Not a Carbon instance
```

---

### 4. Many-to-Many Sync Pattern

When working with pivot tables in Filament Create/Edit pages:

```php
// In your CreatePage or EditPage class

protected array $selectedRelations = [];

protected function mutateFormDataBeforeCreate(array $data): array
{
    // Store many-to-many data before create
    $this->selectedRelations = $data['relations'] ?? [];
    unset($data['relations']);  // Remove from model data
    return $data;
}

protected function afterCreate(): void
{
    // Sync after model is created
    if (!empty($this->selectedRelations)) {
        $pivotData = [];
        foreach ($this->selectedRelations as $id) {
            $pivotData[$id] = [
                'created_at' => now(),
                'extra_field' => 'value'
            ];
        }
        $this->record->relations()->sync($pivotData);
    }
}

protected function mutateFormDataBeforeFill(array $data): array
{
    // Load existing relations for edit
    $data['relations'] = $this->record->relations?->pluck('id')->toArray() ?? [];
    return $data;
}

protected function afterSave(): void
{
    // Update relations after save (for EditPage)
    if (isset($this->data['relations'])) {
        $this->record->relations()->sync($this->data['relations']);
    }
}
```

---

## 🏗️ Project Structure Best Practices

### Multi-Tenancy Pattern

```php
// ✅ Always add tenant_id in creating events
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($model) {
        if (empty($model->tenant_id)) {
            $model->tenant_id = auth()->user()?->tenant_id;
        }
    });
}

// ✅ Use Global Scope for automatic filtering
protected static function booted()
{
    static::addGlobalScope('tenant', function ($query) {
        if (auth()->check() && !auth()->user()->is_super_admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
    });
}
```

---

### Event Listeners for Auto-Updates

```php
// Example: Auto-update related records when main record changes

protected static function boot()
{
    parent::boot();
    
    // After create
    static::created(function ($model) {
        \App\Models\RelatedModel::updateFromSource($model);
    });
    
    // After delete
    static::deleted(function ($model) {
        \App\Models\RelatedModel::recalculateAfterDeletion($model);
    });
    
    // Prevent deletion if has dependencies
    static::deleting(function ($model) {
        if ($model->dependencies()->exists()) {
            throw new \Exception(__('Cannot delete. Has :count dependencies.', [
                'count' => $model->dependencies()->count()
            ]));
        }
    });
}
```

---

## � Multi-Tenancy System (Detailed Guide)

### Overview

Multi-tenancy allows multiple organizations (tenants) to use the same application while keeping their data isolated. Each tenant has their own data space, but shares the same application code and database.

---

### 1. Database Schema Setup

#### Tenants Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('database')->nullable();
            $table->text('settings')->nullable();
            $table->string('logo')->nullable();
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
```

#### Add tenant_id to Users

```php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('tenant_id')
          ->nullable()
          ->constrained('tenants')
          ->cascadeOnUpdate()
          ->nullOnDelete();
          
    $table->boolean('is_super_admin')->default(false);
    
    $table->index(['tenant_id', 'email']);
});
```

#### Add tenant_id to All Resource Tables

```php
// Example for any resource table (students, courses, etc.)
Schema::create('resource_table', function (Blueprint $table) {
    $table->id();
    
    // ✅ Always add tenant_id early in structure
    $table->foreignId('tenant_id')
          ->constrained('tenants')
          ->cascadeOnUpdate()
          ->restrictOnDelete(); // Prevent accidental deletion
    
    // ... other columns
    
    $table->timestamps();
    
    // ✅ Add composite indexes for performance
    $table->index(['tenant_id', 'created_at']);
    $table->index(['tenant_id', 'status']); // if has status
});
```

---

### 2. Tenant Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database',
        'settings',
        'logo',
        'status',
        'trial_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function owner(): HasOne
    {
        return $this->hasOne(User::class)->where('is_tenant_owner', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
                     ->where('trial_ends_at', '>', now());
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
}
```

---

### 3. Base Model with Tenant Scope

Create a base model that all tenant-specific models extend:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class TenantModel extends Model
{
    /**
     * Auto-fill tenant_id on create
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set tenant_id
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = auth()->user()?->tenant_id 
                    ?? session('tenant_id') 
                    ?? request()->user()?->tenant_id;
            }
            
            // Validate tenant_id is set
            if (empty($model->tenant_id)) {
                throw new \Exception('Tenant ID is required but was not set.');
            }
        });
    }

    /**
     * Global scope to filter by tenant
     */
    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            $user = auth()->user();
            
            // Super admin sees everything
            if ($user?->is_super_admin) {
                return;
            }
            
            // Regular users see only their tenant data
            if ($user?->tenant_id) {
                $query->where(
                    $query->getModel()->getTable() . '.tenant_id',
                    $user->tenant_id
                );
            }
        });
    }

    /**
     * Relationship to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to bypass tenant filtering (use carefully!)
     */
    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope for specific tenant (useful for super admin)
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->withoutGlobalScope('tenant')
                     ->where('tenant_id', $tenantId);
    }
}
```

**Usage:**

```php
<?php

namespace App\Models;

class Student extends TenantModel
{
    protected $fillable = [
        'name',
        'email',
        // tenant_id is auto-filled, don't include in fillable
    ];

    // No need to add boot() or Global Scope - inherited from TenantModel
}
```

---

### 4. Filament Configuration for Multi-Tenancy

#### Configure Filament Panel

```php
<?php

// config/filament.php or app/Providers/Filament/AdminPanelProvider.php

use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        // Tenant-specific middleware
        ->middleware([
            'auth',
            'tenant.check', // Custom middleware to verify tenant
        ])
        // Authorize access based on tenant
        ->authGuard('web')
        // Navigation setup
        ->navigation(function () {
            // You can customize navigation per tenant
            return [
                // ...
            ];
        });
}
```

#### Middleware for Tenant Checking

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTenantAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Super admin can access everything
        if ($user?->is_super_admin) {
            return $next($request);
        }

        // Regular user must have tenant
        if (!$user || !$user->tenant_id) {
            abort(403, 'No tenant assigned to your account.');
        }

        // Check tenant is active
        $tenant = $user->tenant;
        if (!$tenant || !$tenant->isActive()) {
            abort(403, 'Your organization account is not active.');
        }

        // Store tenant in session for easy access
        session(['tenant_id' => $user->tenant_id]);

        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ...
    'tenant.check' => \App\Http\Middleware\CheckTenantAccess::class,
];
```

---

### 5. Filament Resources with Multi-Tenancy

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    // ✅ The model's Global Scope handles filtering automatically
    // No need to manually add where('tenant_id', ...) in queries

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                
                // ❌ DON'T add tenant_id field in form
                // It's auto-filled by the model
                
                // If you need to show tenant (for super admin)
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->visible(fn() => auth()->user()->is_super_admin)
                    ->disabled(!auth()->user()->is_super_admin),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Organization')
                    ->visible(fn() => auth()->user()->is_super_admin),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
            ])
            // ✅ Filtering by tenant happens automatically via Global Scope
            ->defaultSort('created_at', 'desc');
    }

    // ✅ Override query if super admin needs to see all
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Super admin panel: show tenant selector
        if (auth()->user()?->is_super_admin) {
            // Can add tenant filter in table filters
            return $query->withoutGlobalScope('tenant');
        }

        // Regular users: automatic filtering by Global Scope
        return $query;
    }
}
```

---

### 6. Seeding Multi-Tenant Data

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Student;

class MultiTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create tenants
        $tenant1 = Tenant::create([
            'name' => 'School A',
            'slug' => 'school-a',
            'status' => 'active',
        ]);

        $tenant2 = Tenant::create([
            'name' => 'School B',
            'slug' => 'school-b',
            'status' => 'active',
        ]);

        // Create users for each tenant
        $user1 = User::create([
            'name' => 'Admin A',
            'email' => 'admin@school-a.com',
            'tenant_id' => $tenant1->id,
            'is_tenant_owner' => true,
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'Admin B',
            'email' => 'admin@school-b.com',
            'tenant_id' => $tenant2->id,
            'is_tenant_owner' => true,
            'password' => bcrypt('password'),
        ]);

        // Create students for each tenant
        // ⚠️ Important: Act as tenant user when creating
        auth()->login($user1);
        Student::create(['name' => 'Student from School A']);
        auth()->logout();

        auth()->login($user2);
        Student::create(['name' => 'Student from School B']);
        auth()->logout();

        // Or manually set tenant_id
        Student::create([
            'name' => 'Another Student A',
            'tenant_id' => $tenant1->id,
        ]);
    }
}
```

---

### 7. Testing Multi-Tenancy

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Student;

class MultiTenancyTest extends TestCase
{
    public function test_users_only_see_their_tenant_data()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        $student1 = Student::factory()->create(['tenant_id' => $tenant1->id]);
        $student2 = Student::factory()->create(['tenant_id' => $tenant2->id]);

        // User 1 should only see their tenant's students
        $this->actingAs($user1);
        $this->assertEquals(1, Student::count());
        $this->assertTrue(Student::first()->is($student1));

        // User 2 should only see their tenant's students
        $this->actingAs($user2);
        $this->assertEquals(1, Student::count());
        $this->assertTrue(Student::first()->is($student2));
    }

    public function test_super_admin_sees_all_data()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $superAdmin = User::factory()->create(['is_super_admin' => true]);

        Student::factory()->create(['tenant_id' => $tenant1->id]);
        Student::factory()->create(['tenant_id' => $tenant2->id]);

        $this->actingAs($superAdmin);
        $this->assertEquals(2, Student::count());
    }

    public function test_cannot_create_record_without_tenant()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tenant ID is required');

        // No authenticated user = no tenant_id
        Student::create(['name' => 'Test']);
    }
}
```

---

### 8. Common Multi-Tenancy Pitfalls

#### ❌ Problem: Forgetting tenant_id in Relationships

```php
// ❌ WRONG - May return records from other tenants
$group = Group::find($id);
$students = $group->students; // No tenant filter in relationship!

// ✅ CORRECT - Add where clause in relationship definition
public function students(): BelongsToMany
{
    return $this->belongsToMany(Student::class)
                ->where('students.tenant_id', auth()->user()->tenant_id);
}

// ✅ OR use Global Scope (automatic if Student extends TenantModel)
public function students(): BelongsToMany
{
    return $this->belongsToMany(Student::class);
    // Global Scope on Student model will filter automatically
}
```

#### ❌ Problem: Manual JOINs Bypass Global Scopes

```php
// ❌ WRONG - Global Scope doesn't apply to JOINs
Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
       ->get(); // Returns ALL tenants' data!

// ✅ CORRECT - Use whereHas
Student::whereHas('enrollments', function($q) {
    $q->where('enrollments.status', 'active');
})->get(); // Global Scope still applies
```

#### ❌ Problem: Forgetting to Check Tenant in APIs

```php
// ❌ WRONG - API endpoint without tenant check
Route::get('/api/students/{id}', function($id) {
    return Student::findOrFail($id); // May return other tenant's student!
});

// ✅ CORRECT - Verify belongs to authenticated user's tenant
Route::get('/api/students/{id}', function($id) {
    $student = Student::findOrFail($id);
    
    if ($student->tenant_id !== auth()->user()->tenant_id) {
        abort(403, 'Unauthorized access');
    }
    
    return $student;
});

// ✅ BETTER - Use route model binding with Global Scope
Route::get('/api/students/{student}', function(Student $student) {
    // Global Scope ensures $student belongs to current tenant
    return $student;
});
```

#### ❌ Problem: Seeding Without Tenant Context

```php
// ❌ WRONG
Student::factory()->count(50)->create(); // tenant_id will be null or error

// ✅ CORRECT
$tenant = Tenant::first();
Student::factory()->count(50)->create(['tenant_id' => $tenant->id]);

// ✅ OR set auth context
auth()->login($tenant->owner);
Student::factory()->count(50)->create(); // tenant_id auto-filled
auth()->logout();
```

---

### 9. Performance Optimization

#### Add Database Indexes

```php
// In migrations, add composite indexes
$table->index(['tenant_id', 'created_at']);
$table->index(['tenant_id', 'status']);
$table->index(['tenant_id', 'email']);

// This makes queries like this very fast:
Student::where('tenant_id', $tenantId)
       ->where('status', 'active')
       ->latest()
       ->get();
```

#### Eager Load Tenant Relationship

```php
// ✅ When showing tenant name frequently
$students = Student::with('tenant')->get();

foreach ($students as $student) {
    echo $student->tenant->name; // No N+1 query problem
}
```

---

### 10. Multi-Tenancy Checklist

Before deploying multi-tenant application:

- [ ] All resource tables have `tenant_id` column with foreign key
- [ ] All resource tables have composite indexes with `tenant_id`
- [ ] All models extend `TenantModel` or have Global Scope
- [ ] `tenant_id` is NOT in `$fillable` array (auto-filled in boot)
- [ ] All relationships respect tenant boundaries
- [ ] No manual JOINs used (use `whereHas` instead)
- [ ] Middleware checks tenant status before allowing access
- [ ] Super admin can bypass tenant filtering when needed
- [ ] All API endpoints verify tenant ownership
- [ ] All seeders provide `tenant_id` or authenticate
- [ ] All tests verify tenant data isolation
- [ ] Performance indexes added for common queries

---

## �🎨 Code Style Rules

### 1. UI Labels

```php
// Use your project's language consistently
// Example for Arabic:
->label('اسم المستخدم')
->placeholder('أدخل الاسم')
->helperText('الحقل مطلوب')

// Example for English:
->label('User Name')
->placeholder('Enter name')
->helperText('This field is required')
```

### 2. Validation

```php
// ✅ CORRECT - Validate before processing
if ($record && $record->amount) {
    $formatted = number_format((float) $record->amount, 2);
}

// ✅ CORRECT - Use type hints
public function process(int $id, string $name, ?float $amount = null): bool
{
    // ...
}
```

### 3. Query Optimization

```php
// ✅ CORRECT - Always specify table in whereHas
Model::whereHas('relation', function($q) {
    $q->where('relation_table.status', 'active');  // ✅ Table specified
})->select('models.*')  // ✅ Select specific table columns
  ->with('relation')    // ✅ Eager load to avoid N+1
  ->get();

// ✅ CORRECT - Use specific scopes
Model::whereActive()
     ->whereTenant(auth()->user()->tenant_id)
     ->latest()
     ->paginate(20);
```

---

## 🛡️ Panel-Specific Permissions (Role + Shield)

في المشاريع متعددة الـ panels، الأفضل إن كل `Role` يكون مرتبط بـ `panel_type`، وبناءً عليه تظهر فقط صلاحيات:
- `Resources`
- `Pages`
- `Widgets`

الخاصة بنفس البانل، بدل عرض كل صلاحيات النظام.

### الفكرة

- المستخدم يختار `نوع البانل` في نموذج الدور (`admin`, `instructor`, `student`, `parent`, `supervisor`).
- Tabs الصلاحيات في Shield تتفلتر تلقائيًا حسب الـ namespace الخاص بالبانل.
- لو `panel_type` فارغ أو `admin`، يمكن عرض كل الصلاحيات (حسب سياسة المشروع).

### 1) إضافة `panel_type` في نموذج الدور

```php
Select::make('panel_type')
    ->label('نوع البانل')
    ->options([
        'admin' => '🔐 Admin Panel (كل الصلاحيات)',
        'instructor' => '👨‍🏫 Instructor Panel',
        'student' => '🎓 Student Panel',
        'parent' => '👨‍👩‍👧 Parent Panel',
        'supervisor' => '👁️ Supervisor Panel',
    ])
    ->nullable()
    ->live();
```

### 2) استخدام Trait لفلترة صلاحيات Shield حسب البانل

```php
use App\Filament\Traits\HasPanelFilteredPermissions;

class RoleResource extends ShieldRoleResource
{
    use HasPanelFilteredPermissions;
}
```

### 3) منطق الفلترة (Resources / Pages / Widgets)

```php
$panelNamespace = "App\\Filament\\" . ucfirst($panelType) . "\\";

return str_starts_with($entity['resourceFqcn'], $panelNamespace);
return str_starts_with($page['pageFqcn'], $panelNamespace);
return str_starts_with($widget['widgetFqcn'], $panelNamespace);
```

### النتيجة المتوقعة في UI

- عند اختيار `Instructor Panel` يظهر فقط صلاحيات Classes داخل `App\\Filament\\Instructor\\...`.
- عند اختيار `Student Panel` يظهر فقط صلاحيات `App\\Filament\\Student\\...`.
- نفس الفكرة لباقي الـ panels.
- هذا يقلل الأخطاء في منح الصلاحيات ويمنع إعطاء Role صلاحيات Panels أخرى بالخطأ.

### ملاحظات مهمة

- اجعل أسماء الـ classes داخل كل panel namespace واضحة ومنظمة.
- لا تعتمد على SQL يدوي للصلاحيات؛ استخدم Shield + Eloquent.
- في `EditRole` احتفِظ بقيمة `currentPanelType` لضمان استمرار الفلترة أثناء التعديل.
- اختبر إنشاء وتعديل Role لكل panel للتأكد من ظهور الصلاحيات الصحيحة فقط.

---

## 🚀 When Creating New Pages/Resources

### Checklist for New Filament Page

```php
<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class YourPage extends Page
{
    // ✅ 1. Correct type declarations
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document';
    protected static \UnitEnum|string|null $navigationGroup = 'Your Group';
    protected static int|string|null $navigationSort = 10;
    
    // ✅ 2. Non-static view
    protected string $view = 'filament.admin.pages.your-page';
    
    // ✅ 3. Authorization
    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_your_page') ?? false;
    }
    
    // ✅ 4. Multi-tenant support
    protected function getTableQuery()
    {
        return YourModel::query()
            ->where('tenant_id', auth()->user()->tenant_id);
    }
}
```

### Checklist for New Filament Resource

```php
<?php

namespace App\Filament\Admin\Resources;

use Filament\Resources\Resource;

class YourResource extends Resource
{
    protected static ?string $model = YourModel::class;
    
    // ✅ Correct types
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static \UnitEnum|string|null $navigationGroup = 'Your Group';
    
    // ✅ Forms with validation
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            
            // ✅ Relationships
            Select::make('category_id')
                ->relationship('category', 'name')
                ->required(),
        ]);
    }
    
    // ✅ Tables with proper formatting
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 2)),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
```

---

## ❌ NEVER DO (Common Mistakes)

### Type Declarations
```php
// ❌ NEVER use ?string for navigation properties in Filament 4
protected static ?string $navigationIcon = '...';

// ❌ NEVER make $view static
protected static string $view = '...';

// ❌ NEVER forget parent::boot()
protected static function boot()
{
    // Missing parent::boot(); ❌
    static::creating(function($model) { /* ... */ });
}
```

### Queries
```php
// ❌ NEVER use manual JOINs when Global Scopes exist
Model::join('table', ...)

// ❌ NEVER forget to select table in whereHas
Model::whereHas('relation', fn($q) => $q->where('id', 1))  // ❌ Ambiguous

// ❌ NEVER use raw SQL where Eloquent can be used
DB::raw("SELECT * FROM users WHERE tenant_id = {$id}")  // ❌ SQL injection risk
```

### Data Handling
```php
// ❌ NEVER forget type casting
number_format($amount, 2)  // ❌ May crash

// ❌ NEVER skip null checks
$record->items->pluck('id')  // ❌ Crashes if items is null

// ❌ NEVER forget to sync() many-to-many relationships
$model->save();
// Missing: $model->relations()->sync($data); ❌
```

### Multi-Tenancy
```php
// ❌ NEVER add tenant_id to $fillable
protected $fillable = ['name', 'tenant_id'];  // ❌ Let boot() handle it

// ❌ NEVER use manual tenant_id filtering
Model::where('tenant_id', auth()->user()->tenant_id)->get();  // ❌ Use Global Scope

// ❌ NEVER bypass Global Scope without good reason
Model::withoutGlobalScope('tenant')->get();  // ❌ Exposes all tenants' data

// ❌ NEVER use JOINs in multi-tenant apps
Model::join('related', ...)->get();  // ❌ Bypasses Global Scope

// ❌ NEVER forget to verify tenant ownership in APIs
Route::get('/api/record/{id}', fn($id) => Model::find($id));  // ❌ Security risk
```

---

## 📦 Base Classes (Optional)

Create a base page class for consistent type declarations:

```php
<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page as BasePage;

/**
 * Base class for all custom Filament pages
 * Ensures correct Filament 4 type declarations
 */
abstract class BaseFilamentPage extends BasePage
{
    protected static \BackedEnum|string|null $navigationIcon = null;
    protected static \UnitEnum|string|null $navigationGroup = null;
    
    /**
     * ⚠️ IMPORTANT: $view must be non-static in Filament 4
     */
    protected string $view;
    
    /**
     * Multi-tenant authorization
     */
    public static function canAccess(): bool
    {
        return auth()->check() && static::canViewInPanel();
    }
}
```

---

## 🔧 Quick Fix Reference

| Error Message | Quick Fix |
|---------------|-----------|
| `Cannot assign ?string to property $navigationIcon` | Change to `\BackedEnum\|string\|null` |
| `Cannot assign ?string to property $navigationGroup` | Change to `\UnitEnum\|string\|null` |
| `Cannot redeclare non static $view as static` | Remove `static` from `$view` |
| `Ambiguous column 'id'` | Use `whereHas()` instead of `join()` |
| `Column not found: tenant_id` | Add foreign key in migration or check Global Scope |
| `Tenant ID is required but was not set` | Authenticate user before creating model or pass `tenant_id` |
| `Call to member function on null` | Add nullsafe operator `?->` |
| `Implicit conversion float to decimal` | Cast before: `(float)$value` |
| `Seeing other tenants' data` | Add Global Scope to model or extend `TenantModel` |
| `SQLSTATE[23000]: Integrity constraint violation: tenant_id` | Set `tenant_id` before save or authenticate user |

---

## 📚 Documentation Structure

When setting up a new project, create these files:

1. **`.github/copilot-instructions.md`** - GitHub Copilot rules (project-specific)
2. **`FILAMENT_4_COMPATIBILITY.md`** - Detailed Filament 4 rules
3. **`CODE_TEMPLATES.md`** - Ready-to-use code templates
4. **`COMMON_ERRORS.md`** - Error solutions handbook
5. **`AI_QUICK_REFERENCE.md`** - Quick reference for AI assistants

---

## ✅ Pre-Deployment Checklist

Before pushing to production:

**Filament 4 Compatibility:**
- [ ] All navigation properties use correct types (`BackedEnum|string|null`, `UnitEnum|string|null`)
- [ ] No static `$view` properties
- [ ] All forms have proper validation

**Query & Performance:**
- [ ] All queries use `whereHas()` instead of manual JOINs
- [ ] All `number_format()` calls have type casting
- [ ] All nullable accesses use nullsafe operator `?->`
- [ ] All date operations use `Carbon::parse()`

**Relationships:**
- [ ] All many-to-many relations are synced after create/update
- [ ] All event listeners call `parent::boot()` first

**Multi-Tenancy (if applicable):**
- [ ] All resource models extend `TenantModel` or have Global Scope
- [ ] All tables have `tenant_id` foreign key with index
- [ ] All tables have composite indexes: `['tenant_id', 'common_column']`
- [ ] `tenant_id` is NOT in `$fillable` arrays (auto-filled in boot)
- [ ] Middleware checks tenant status and access
- [ ] Super admin can bypass tenant filtering when needed
- [ ] All tests verify tenant data isolation
- [ ] No manual JOINs bypass Global Scopes
- [ ] All API endpoints verify tenant ownership
- [ ] All seeders provide `tenant_id` or set auth context

---

## 🎯 Summary

**4 Golden Rules:**
1. **Types Matter:** Use correct Filament 4 type declarations
2. **Eloquent First:** Avoid raw SQL and manual JOINs
3. **Safety First:** Always cast, check nulls, validate
4. **Tenant Isolation:** Always use Global Scopes, never manual tenant_id filters

**Remember:**
- `$navigationIcon` → `\BackedEnum|string|null`
- `$navigationGroup` → `\UnitEnum|string|null`
- `$view` → `protected string` (NON-STATIC!)
- All models extend `TenantModel` for automatic tenant isolation

**Multi-Tenancy Quick Tips:**
- ✅ Use Global Scopes on all tenant models
- ✅ Add composite indexes: `['tenant_id', 'column']`
- ✅ Use `whereHas()` not `join()` to preserve scope
- ✅ Test tenant isolation thoroughly
- ❌ Never add `tenant_id` to `$fillable`
- ❌ Never use manual JOINs in multi-tenant apps

---

**Copy this file to your new Laravel + Filament project and follow these rules to avoid 90% of common errors!** 🚀
