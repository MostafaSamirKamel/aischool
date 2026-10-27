# Filament 4 - قواعد التوافق الإلزامية

## ⚠️ مشاكل شائعة يجب تجنبها

### 1. Type Declarations في Pages و Resources

**❌ خطأ شائع:**
```php
protected static ?string $navigationIcon = 'heroicon-o-document';
protected static ?string $navigationGroup = 'المجموعة';
protected static string $view = 'filament.pages.your-page'; // ❌ خطأ!
```

**✅ الطريقة الصحيحة (Filament 4):**
```php
protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document';
protected static \UnitEnum|string|null $navigationGroup = 'المجموعة';
protected string $view = 'filament.pages.your-page'; // ✅ non-static!
```

### 2. قاعدة إلزامية

**في أي Page أو Resource جديد، استخدم دائماً:**

```php
use BackedEnum;

class YourPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-...';
    protected static \UnitEnum|string|null $navigationGroup = 'اسم المجموعة';
    protected string $view = 'filament.pages.your-page'; // ⚠️ non-static!
}
```

### 3. ملفات تم إصلاحها (كمرجع)

- ✅ `app/Filament/Admin/Pages/Reports/InstallmentsReport.php`
- ✅ `app/Filament/Admin/Pages/GenerateInstructorPayments.php`
- ✅ `app/Filament/Admin/Resources/OverdueInstallmentResource.php`

### 4. Eloquent vs Raw SQL

**❌ تجنب:**
```php
Student::query()
    ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
    ->where('tenant_id', $id); // ⚠️ Ambiguous column
```

**✅ استخدم Eloquent relationships:**
```php
Student::whereHas('enrollments', function($q) {
    $q->where('enrollments.status', 'active');
})->select('students.*');
```

### 5. Model Relationships

**في Group model - استخدم:**
```php
public function activeStudents()
{
    return Student::where('students.status', 'active')
        ->whereHas('enrollments.groups', fn($q) => $q->where('groups.id', $this->id))
        ->select('students.*');
}
```

### 6. Sync العلاقات Many-to-Many

**عند حفظ enrollment مع groups:**

```php
// CreateEnrollment & EditEnrollment
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
        $groupsWithTimestamp = [];
        foreach ($this->selectedGroups as $groupId) {
            $groupsWithTimestamp[$groupId] = ['enrolled_at' => now()];
        }
        $this->record->groups()->sync($groupsWithTimestamp);
    }
}
```

### 7. Type Casting في number_format

**❌ خطأ:**
```php
number_format($payment->total_amount, 2) // قد يكون decimal|null
```

**✅ صحيح:**
```php
number_format((float) $payment->total_amount, 2)
```

---

## 📋 Checklist قبل إنشاء Page/Resource جديد

- [ ] استخدمت `\BackedEnum|string|null` للـ `$navigationIcon`
- [ ] استخدمت `\UnitEnum|string|null` للـ `$navigationGroup`
- [ ] تأكدت أن `$view` هو **non-static** (ليس static)
- [ ] استخدمت Eloquent relationships بدلاً من JOIN
- [ ] أضفت `select('table.*')` لتجنب ambiguous columns
- [ ] استخدمت `(float)` cast قبل `number_format()`
- [ ] عملت sync للعلاقات many-to-many في `afterCreate()`/`afterSave()`

---

## 🔍 عند ظهور خطأ Type Declaration

**ابحث في الخطأ عن:**
- `must be BackedEnum|string|null` → غير `$navigationIcon`
- `must be UnitEnum|string|null` → غير `$navigationGroup`
- `Cannot redeclare non static $view as static` → احذف `static` من `$view`
- `Column 'tenant_id' is ambiguous` → استخدم Eloquent whereHas
- `array|null given` → أضف type casting أو null check

---

**آخر تحديث:** 10 فبراير 2026
