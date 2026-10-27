# Code Templates - نسخ ولصق مباشرة

## 📄 Filament 4 Page Template

```php
<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class YourPageName extends Page
{
    use HasPageShield;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.admin.pages.your-page-name'; // ⚠️ NON-STATIC!

    protected static \UnitEnum|string|null $navigationGroup = 'المجموعة';

    protected static ?string $navigationLabel = 'العنوان';

    protected static ?string $title = 'العنوان';

    protected static ?int $navigationSort = 100;
}
```

---

## 📊 Filament 4 Resource Template

```php
<?php

namespace App\Filament\Admin\Resources;

use App\Models\YourModel;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class YourResource extends Resource
{
    protected static ?string $model = YourModel::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static \UnitEnum|string|null $navigationGroup = 'المجموعة';

    protected static ?string $navigationLabel = 'العنوان';

    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['relation1', 'relation2']);
    }
}
```

---

## 🔗 Eloquent Relationship Query Template

```php
// في Model:
public function activeRelatedItems()
{
    return RelatedModel::where('related_models.status', 'active')
        ->whereHas('pivot', function ($query) {
            $query->where('pivot_table.parent_id', $this->id);
        })
        ->select('related_models.*');
}

// استخدام:
$items = $model->activeRelatedItems()->with('user')->get();
```

---

## 💾 Create/Edit with Sync Template

```php
<?php

namespace App\Filament\Admin\Resources\YourResource\Pages;

use Filament\Resources\Pages\CreateRecord;

class CreateYourRecord extends CreateRecord
{
    protected static string $resource = YourResource::class;

    protected array $selectedItems = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // حفظ البيانات المؤقتة
        $this->selectedItems = $data['items'] ?? [];
        unset($data['items']);

        // إضافة tenant_id
        $data['tenant_id'] = auth()->user()->tenant_id;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Sync many-to-many relationships
        if (!empty($this->selectedItems)) {
            $itemsData = [];
            
            foreach ($this->selectedItems as $itemId) {
                $itemsData[$itemId] = [
                    'created_at' => now(),
                    'extra_field' => 'value'
                ];
            }
            
            $this->record->items()->sync($itemsData);
        }
    }
}
```

---

## 🎯 Form Field with Live Calculation Template

```php
Select::make('instructor_id')
    ->label('المهندس')
    ->options(function () {
        return Instructor::query()
            ->with('user')
            ->where('status', 'active')
            ->get()
            ->mapWithKeys(fn ($instructor) => [
                $instructor->id => $instructor->user?->name ?? 'غير محدد'
            ]);
    })
    ->searchable()
    ->preload()
    ->required()
    ->live()
    ->afterStateUpdated(function ($state, callable $set, callable $get) {
        if (!$state) {
            return;
        }
        
        $instructor = Instructor::find($state);
        $periodStart = $get('period_start');
        $periodEnd = $get('period_end');
        
        // حساب تلقائي
        $sessions = AttendanceSession::whereHas('group', 
            fn($q) => $q->where('instructor_id', $state)
        )
        ->whereBetween('attendance_date', [$periodStart, $periodEnd])
        ->count();
        
        $amount = ($sessions * $instructor->hourly_rate) + $instructor->base_salary;
        
        $set('amount', $amount);
        $set('helper_text', "عدد الحصص: {$sessions} × {$instructor->hourly_rate} ج.م + {$instructor->base_salary} ج.م");
    })
```

---

## 📋 Table with Badge Column Template

```php
BadgeColumn::make('status')
    ->label('الحالة')
    ->colors([
        'success' => 'active',
        'danger' => 'inactive',
        'warning' => 'pending',
        'info' => 'other',
    ])
    ->formatStateUsing(fn (string $state): string => match ($state) {
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'pending' => 'قيد الانتظار',
        default => $state,
    })
```

---

## 🔍 Filter with Query Template

```php
SelectFilter::make('student_status')
    ->label('حالة الطالب')
    ->options([
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'suspended' => 'موقوف',
    ])
    ->query(function (Builder $query, array $data): Builder {
        if (isset($data['value']) && $data['value'] !== '') {
            return $query->whereHas('student', function ($q) use ($data) {
                $q->where('status', $data['value']);
            });
        }
        return $query;
    })
    ->default(null)
    ->placeholder('الكل')
```

---

**نسخ أي template وعدله حسب احتياجك!**
