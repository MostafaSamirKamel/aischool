# Common Errors & Solutions - دليل حل الأخطاء

## 🔴 خطأ: Type of $navigationIcon must be BackedEnum|string|null

### الرسالة الكاملة:
```
Type of App\Filament\Admin\Pages\YourPage::$navigationIcon must be BackedEnum|string|null (as in class Filament\Pages\Page)
```

### ✅ الحل:
```php
// ❌ قبل
protected static ?string $navigationIcon = 'heroicon-o-document';

// ✅ بعد
protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document';
```

---

## 🔴 خطأ: Type of $navigationGroup must be UnitEnum|string|null

### ✅ الحل:
```php
// ❌ قبل
protected static ?string $navigationGroup = 'المجموعة';

// ✅ بعد
protected static \UnitEnum|string|null $navigationGroup = 'المجموعة';
```

---

## 🔴 خطأ: Column 'tenant_id' in WHERE is ambiguous

### الرسالة الكاملة:
```
SQLSTATE[23000]: Column 'tenant_id' in where clause is ambiguous
```

### السبب:
- استخدام JOIN يدوي مع Global Scope
- عدم تحديد اسم الجدول قبل اسم العمود

### ✅ الحل 1 - استخدم whereHas:
```php
// ❌ قبل
Student::query()
    ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
    ->where('tenant_id', auth()->user()->tenant_id)
    ->get();

// ✅ بعد
Student::whereHas('enrollments', function($q) {
    $q->where('enrollments.status', 'active');
})->select('students.*')->get();
```

### ✅ الحل 2 - حدد اسم الجدول:
```php
->where('students.tenant_id', auth()->user()->tenant_id)
```

---

## 🔴 خطأ: Call to a member function pluck() on array|null

### السبب:
- محاولة استدعاء pluck() على relationship قد يكون null

### ✅ الحل:
```php
// ❌ قبل
$data['groups'] = $this->record->groups->pluck('id')->toArray();

// ✅ بعد - الطريقة 1
$groups = $this->record->groups;
if ($groups && is_object($groups)) {
    $data['groups'] = $groups->pluck('id')->toArray();
} else {
    $data['groups'] = [];
}

// ✅ بعد - الطريقة 2
$data['groups'] = $this->record->groups?->pluck('id')->toArray() ?? [];
```

---

## 🔴 خطأ: Argument '1' passed to number_format() is expected to be of type float

### السبب:
- تمرير decimal|null لـ number_format()

### ✅ الحل:
```php
// ❌ قبل
number_format($payment->total_amount, 2)

// ✅ بعد
number_format((float) $payment->total_amount, 2)
```

---

## 🔴 خطأ: الطالب لا يظهر في المجموعة عند المهندس

### السبب:
- عدم حفظ العلاقة في enrollment_group بعد إنشاء Enrollment

### ✅ الحل:
```php
// في CreateEnrollment.php
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
        $groupsData = [];
        foreach ($this->selectedGroups as $groupId) {
            $groupsData[$groupId] = ['enrolled_at' => now()];
        }
        $this->record->groups()->sync($groupsData);
    }
}
```

---

## 🔴 خطأ: الأقساط المتأخرة لا تظهر للطلاب غير النشطين

### السبب:
- فلترة تلقائية حسب حالة الطالب

### ✅ الحل:
```php
// أضف filter للحالة بدون default
SelectFilter::make('student_status')
    ->label('حالة الطالب')
    ->options([
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ])
    ->query(function (Builder $query, array $data): Builder {
        if (isset($data['value']) && $data['value'] !== '') {
            return $query->whereHas('enrollment.student', function ($q) use ($data) {
                $q->where('status', $data['value']);
            });
        }
        return $query;
    })
    ->default(null) // لا نفلتر افتراضياً
    ->placeholder('الكل');
```

---

## 🔴 خطأ: حساب أجر المهندس غير صحيح

### السبب:
- استخدام SessionSchedule بدلاً من AttendanceSession
- عدم احتساب الحصص الفعلية

### ✅ الحل:
```php
// ❌ خطأ
$sessions = SessionSchedule::where('instructor_id', $instructorId)->count();

// ✅ صحيح
$sessions = AttendanceSession::whereHas('group', function($q) use ($instructorId) {
    $q->where('instructor_id', $instructorId);
})
->whereBetween('attendance_date', [$periodStart, $periodEnd])
->count();

$totalAmount = ($sessions * $instructor->hourly_rate) + $instructor->base_salary;
```

---

## 🔴 خطأ: Cannot redeclare non static $view as static

### الرسالة الكاملة:
```
Cannot redeclare non static Filament\Pages\Page::$view as static App\Filament\Admin\Pages\YourPage::$view
```

### السبب:
- الـ `$view` property في Filament\Pages\Page هو non-static
- لكن تم تعريفه كـ static في الـ child class

### ✅ الحل:
```php
// ❌ قبل
protected static string $view = 'filament.admin.pages.your-page';

// ✅ بعد - إزالة static
protected string $view = 'filament.admin.pages.your-page';
```

**ملاحظة:** في Filament 4، الـ `$view` يجب أن يكون non-static property.

---

## 🔴 خطأ: Property [groups] does not exist on this collection

### السبب:
- محاولة الوصول لـ groups كـ property على Enrollment بدون eager loading

### ✅ الحل:
```php
// ❌ قبل
Installment::with('enrollment.student')->get()

// ✅ بعد
Installment::with(['enrollment.student', 'enrollment.groups.course'])->get()
```

---

## 📋 Quick Checklist عند ظهور أي خطأ

1. ✅ هل استخدمت `\BackedEnum|string|null` للـ `$navigationIcon`?
2. ✅ هل استخدمت `\UnitEnum|string|null` للـ `$navigationGroup`?
3. ✅ هل استخدمت whereHas بدلاً من JOIN؟
4. ✅ هل أضفت `select('table.*')` في whereHas؟
5. ✅ هل عملت sync للعلاقات many-to-many؟
6. ✅ هل أضفت type casting قبل number_format؟
7. ✅ هل تحققت من null قبل استدعاء methods؟
8. ✅ هل أضفت eager loading للعلاقات المستخدمة؟

---

**عند ظهور خطأ غير مذكور هنا:**
1. اقرأ رسالة الخطأ بالكامل
2. ابحث عن اسم الملف ورقم السطر
3. ابحث في `FILAMENT_4_COMPATIBILITY.md`
4. استخدم `CODE_TEMPLATES.md` للمرجع

---

**آخر تحديث:** 10 فبراير 2026
