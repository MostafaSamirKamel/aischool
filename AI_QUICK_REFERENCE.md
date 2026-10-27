# AI Assistant - Quick Reference Guide

## 📌 في كل مرة تبدأ العمل، اقرأ هذا الملف أولاً

### 1. Filament 4 Type Requirements

```php
// في أي Page أو Resource:
protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-...';
protected static \UnitEnum|string|null $navigationGroup = 'اسم المجموعة';
protected string $view = 'filament.pages.your-page'; // ⚠️ NON-STATIC!
```

**⚠️ CRITICAL: $view must be non-static in Filament 4!**

### 2. Eloquent Best Practices

```php
// ✅ صحيح - استخدم whereHas
Student::whereHas('enrollments', function($q) {
    $q->where('enrollments.status', 'active');
})->select('students.*');

// ❌ خطأ - تجنب JOIN اليدوي
Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
```

### 3. Many-to-Many Sync Pattern

```php
// في CreateRecord/EditRecord:
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
        foreach ($this->selectedGroups as $id) {
            $groupsData[$id] = ['enrolled_at' => now()];
        }
        $this->record->groups()->sync($groupsData);
    }
}
```

### 4. Type Safety

```php
// ✅ استخدم type casting
number_format((float) $amount, 2)

// ✅ تحقق من القيم
$data['groups'] = $this->record->groups?->pluck('id')->toArray() ?? [];
```

### 5. Files Already Fixed (Don't break them!)

- `app/Filament/Admin/Pages/GenerateInstructorPayments.php` ✅
- `app/Filament/Admin/Pages/Reports/InstallmentsReport.php` ✅
- `app/Filament/Admin/Resources/OverdueInstallmentResource.php` ✅
- `app/Models/Group.php` → `activeStudents()` method ✅

### 6. Common Database Structure

```
students
├── enrollments (hasMany)
    ├── groups (belongsToMany via enrollment_group)
    ├── courses (belongsToMany via enrollment_course)
    └── installments (hasMany)

groups
├── enrollments (belongsToMany via enrollment_group)
├── course (belongsTo)
├── instructor (belongsTo)
└── attendance_sessions (hasMany)
    └── attendance_records (hasMany)
```

### 7. Payment Calculation Logic

```php
// InstructorPayment
$totalAmount = (completedSessions × hourly_rate) + base_salary

// Based on AttendanceSession count, NOT SessionSchedule
```

---

## 🚨 قبل أي تعديل

1. اقرأ `FILAMENT_4_COMPATIBILITY.md`
2. اقرأ الملفات الموجودة في المشروع قبل إنشاء ملفات جديدة
3. استخدم `grep_search` للبحث عن patterns مشابهة
4. تأكد من type declarations في Page/Resource classes

---

**للمستخدم:** ضع رابط هذا الملف في أول رسالة لأي محادثة جديدة مع AI
