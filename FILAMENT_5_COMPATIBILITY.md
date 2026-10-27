# Filament 5 - قواعد التوافق

## ✨ الجديد في Filament 5

### 1. PHP 8.3+ مطلوب
- استخدام PHP 8.3 features
- Typed class constants
- Override attribute
- Better performance

### 2. Type Declarations - نفس Filament 4

**✅ الطريقة الصحيحة (Filament 4 & 5):**
```php
use BackedEnum;
use UnitEnum;

class YourPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document';
    protected static \UnitEnum|string|null $navigationGroup = 'المجموعة';
    protected string $view = 'filament.pages.your-page'; // ⚠️ non-static!
}
```

**❌ خطأ شائع:**
```php
protected static ?string $navigationIcon = 'heroicon-o-document';
protected static ?string $navigationGroup = 'المجموعة';
protected static string $view = 'filament.pages.your-page'; // ❌ never static!
```

### 3. Panel Configuration

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->login()
        ->registration()
        ->profile()
        ->topNavigation() // محسّن في v5
        ->sidebarCollapsibleOnDesktop()
        ->colors([
            'primary' => Color::Blue,
        ])
        ->plugins([
            FilamentShieldPlugin::make()
                ->scopeToTenant(true),
        ]);
}
```

## 📦 Plugins المتوافقة مع Filament 5

### Core Plugins
```php
// Shield Plugin (Permissions)
FilamentShieldPlugin::make()
    ->scopeToTenant(true)
    ->localizePermissionLabels()

// Language Switch
// Sticky Table Header
// Excel Import/Export
```

## 🎨 Heroicons المحدثة

بعض الأيقونات تغيرت في Filament 5:

```php
// ✅ تحقق من الأيقونات على: https://heroicons.com/

// أمثلة شائعة:
'heroicon-o-user-group'
'heroicon-o-academic-cap'
'heroicon-o-document-text'
'heroicon-o-chart-bar'
'heroicon-o-cog-6-tooth' // كانت cog في v4
```

## 🔄 Form Components

### Select Component
```php
Select::make('status')
    ->options([
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ])
    ->native(false) // محسّن في v5
    ->searchable()
    ->preload()
```

### DatePicker
```php
DatePicker::make('date')
    ->native(false) // محسّن في v5
    ->displayFormat('d/m/Y')
    ->format('Y-m-d')
```

## 📊 Table Columns

### TextColumn
```php
TextColumn::make('name')
    ->searchable()
    ->sortable()
    ->copyable() // محسّن في v5
    ->tooltip('نسخ')
```

### Money Column
```php
TextColumn::make('amount')
    ->money('SAR')
    ->sortable()
```

## ⚡ Performance Improvements

### Lazy Loading
```php
// في الـ RelationManager
protected function getTableQuery(): Builder
{
    return parent::getTableQuery()
        ->with(['relation1', 'relation2']); // Eager load
}
```

### Caching
```php
// استخدم Cache للبيانات الثابتة
Select::make('course_id')
    ->options(fn() => Cache::remember('courses', 3600, fn() =>
        Course::pluck('name', 'id')
    ))
```

## 🛡️ Multi-Tenancy

### Panel Configuration
```php
$panel
    ->tenant(Organization::class)
    ->tenantProfile(EditOrganizationProfile::class)
```

### Resource Configuration
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->whereBelongsTo(Filament::getTenant());
}
```

## 🔍 Eloquent Best Practices

### ✅ استخدم whereHas مع العلاقات
```php
Student::whereHas('enrollments', function($q) {
    $q->where('enrollments.status', 'active');
})->select('students.*');
```

### ❌ تجنب manual JOINs
```php
// ❌ تجنب هذا
Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
    ->where('status', 'active'); // Ambiguous column error
```

## 🎯 Many-to-Many Relations

### في Create/Edit Pages
```php
protected array $selectedGroups = [];

protected function mutateFormDataBeforeCreate(array $data): array
{
    $this->selectedGroups = $data['groups'] ?? [];
    unset($data['groups']);
    return $data;
}

protected function afterCreate(): void
{
    if (!empty($this->selectedGroups)) {
        $syncData = [];
        foreach ($this->selectedGroups as $id) {
            $syncData[$id] = ['enrolled_at' => now()];
        }
        $this->record->groups()->sync($syncData);
    }
}
```

## 🧪 Testing في Filament 5

```php
it('can render page', function () {
    $this->get(YourResource::getUrl('index'))
        ->assertSuccessful();
});

it('can create record', function () {
    $newData = ['name' => 'Test'];
    
    livewire(YourResource\Pages\CreateYourRecord::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();
});
```

## 📝 Validation Rules

```php
Forms\Components\TextInput::make('name')
    ->required()
    ->maxLength(255)
    ->rule('string')
    ->validationAttribute('الاسم')
```

## 🌐 Localization

```php
// في config/app.php
'locale' => 'ar',
'fallback_locale' => 'en',

// في الـ forms
->label(__('filament.fields.name'))
->helperText(__('filament.helpers.student_name'))
```

## ⚠️ Common Errors & Solutions

### 1. Type Error في Navigation
```php
// ❌ Wrong
protected static ?string $navigationIcon = '...';

// ✅ Correct
protected static \BackedEnum|string|null $navigationIcon = '...';
```

### 2. Ambiguous Column
```php
// ❌ Wrong
->join('table', ...)
->where('status', 'active'); // أي جدول؟

// ✅ Correct
->where('table.status', 'active')
// أو
->whereHas('relation', fn($q) => $q->where('status', 'active'))
```

### 3. Number Format Error
```php
// ❌ Wrong
number_format($value, 2); // $value قد يكون null

// ✅ Correct
number_format((float) ($value ?? 0), 2)
```

### 4. Null Safety
```php
// ❌ Wrong
$data['groups'] = $record->groups->pluck('id')->toArray();

// ✅ Correct
$data['groups'] = $record->groups?->pluck('id')->toArray() ?? [];
```

## 🎓 Best Practices

1. **استخدم Eloquent ORM دائماً**
2. **Type declarations صحيحة**
3. **Null safety checks**
4. **Eager loading للعلاقات**
5. **Cache للبيانات الثابتة**
6. **Validation rules واضحة**
7. **Tests شاملة**

## 📚 Resources

- [Filament 5 Docs](https://filamentphp.com/docs/5.x)
- [Upgrade Guide](https://filamentphp.com/docs/5.x/upgrade-guide)
- [Community Plugins](https://filamentphp.com/plugins)

---

**تاريخ التحديث:** 2026-03-31  
**النسخة:** Filament 5.x  
**PHP:** 8.3+
