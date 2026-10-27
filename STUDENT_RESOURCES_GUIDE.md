# 📚 شاشات منصة الطالب - دليل التطوير

## الحالة الحالية

تم إنشاء 3 Resources للطالب ولكن تم حذفهم مؤقتاً بسبب مشاكل في التوافق مع Filament 4.

## الـ Resources المطلوبة

### 1. MyCourseResource 📖
عرض الكورسات التي اشترك فيها الطالب.

**الحقول المطلوبة:**
- اسم الكورس
- صورة الكورس  
- المجموعة
- المحاضر
- تاريخ التسجيل
- الحالة (نشط/مكتمل/متوقف)
- نسبة التقدم
- المبلغ الإجمالي والمدفوع

**الصفحات:**
- ListMyCourses: قائمة الكورسات
- ViewMyCourse: تفاصيل الكورس

**Query:**
```php
$student = Student::where('user_id', auth()->id())->first();
Enrollment::where('student_id', $student->id)
    ->with(['group.course', 'group.instructor']);
```

---

### 2. MyExamResource 📝
عرض الامتحانات المتاحة للطالب.

**الحقول المطلوبة:**
- عنوان الامتحان
- الكورس
- تاريخ ووقت الامتحان
- المدة
- الدرجة الكاملة
- درجة الطالب
- الحالة

**الصفحات:**
- ListMyExams: قائمة الامتحانات (تبويبات: الكل/القادمة/السابقة)
- ViewMyExam: تفاصيل الامتحان
- TakeExam: صفحة حل الامتحان

**Query:**
```php
$student = Student::where('user_id', auth()->id())->first();
$groupIds = $student->enrollments()->pluck('group_id');
Exam::whereIn('group_id', $groupIds);
```

**صفحة TakeExam:**
- Timer عد تنازلي
- عرض الأسئلة
- رفع ملف الإجابات
- تسليم تلقائي عند انتهاء الوقت

---

### 3. MyAssignmentResource 📄
عرض الواجبات المطلوبة من الطالب.

**الحقول المطلوبة:**
- عنوان الواجب
- المجموعة
- تاريخ التسليم
- الدرجة الكاملة
- حالة التسليم
- درجة الطالب

**الصفحات:**
- ListMyAssignments: قائمة الواجبات (تبويبات: الكل/قيد الانتظار/تم التسليم/متأخرة)
- ViewMyAssignment: تفاصيل الواجب
- SubmitAssignment: صفحة تسليم الواجب

**Query:**
```php
$student = Student::where('user_id', auth()->id())->first();
$groupIds = $student->enrollments()->pluck('group_id');
Assignment::whereIn('group_id', $groupIds);
```

**صفحة SubmitAssignment:**
- رفع ملف (PDF/Word/صور حتى 10MB)
- إضافة ملاحظات
- تحميل ملف الواجب من المحاضر

---

## كيفية إنشاء الـ Resources

### الخطوة 1: إنشاء Resource بالـ Artisan

```bash
php artisan make:filament-resource MyCourse --panel=student --view
```

### الخطوة 2: تعديل الـ Resource

في `MyCourseResource.php`:

```php
protected static ?string $model = Enrollment::class;
protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
protected static ?string $navigationLabel = 'كورساتي';
protected static ?int $navigationSort = 1;

public static function getEloquentQuery(): Builder
{
    $user = auth()->user();
    $student = Student::where('user_id', $user->id)->first();
    
    return parent::getEloquentQuery()
        ->where('student_id', $student?->id)
        ->with(['group.course', 'group.instructor']);
}

public static function canCreate(): bool { return false; }
public static function canEdit($record): bool { return false; }
public static function canDelete($record): bool { return false; }
```

### الخطوة 3: تعديل Table

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\ImageColumn::make('group.course.image')
                ->label('الصورة')
                ->circular(),
            
            Tables\Columns\TextColumn::make('group.course.name')
                ->label('الكورس')
                ->searchable()
                ->weight('bold'),
            
            Tables\Columns\TextColumn::make('status')
                ->label('الحالة')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'completed' => 'info',
                    'suspended' => 'danger',
                    default => 'gray',
                }),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
        ]);
}
```

---

## ملاحظات مهمة

### 1. Filament 4 Changes
- **لا يوجد Infolists** - استخدم Form أو عرض بيانات مباشر
- **Schema بدلاً من Infolist** في ViewRecord pages
- استخدم `protected static string $view` لعرض Blade مخصص

### 2. Custom Pages
لإنشاء صفحة مخصصة (مثل TakeExam):

```php
class TakeExam extends Page
{
    protected static string $resource = MyExamResource::class;
    protected static string $view = 'filament.student.pages.take-exam';
    
    public $exam;
    
    public function mount($record): void
    {
        $this->exam = $record;
    }
}
```

في routes:
```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListMyExams::route('/'),
        'view' => Pages\ViewMyExam::route('/{record}'),
        'take' => Pages\TakeExam::route('/{record}/take'),
    ];
}
```

### 3. File Upload
استخدم FileUpload component في Form:

```php
Forms\Components\FileUpload::make('file')
    ->label('ملف الإجابات')
    ->required()
    ->acceptedFileTypes(['application/pdf', 'image/*'])
    ->maxSize(10240) // 10 MB
    ->directory('assignments/submissions')
```

### 4. Timer في صفحة الامتحان
استخدم Alpine.js:

```blade
<div x-data="timer({{ $duration }})" x-init="start()">
    <span x-text="display"></span>
</div>

<script>
function timer(duration) {
    return {
        seconds: duration,
        display: '',
        start() {
            this.interval = setInterval(() => {
                this.seconds--;
                this.updateDisplay();
                if (this.seconds <= 0) {
                    clearInterval(this.interval);
                    @this.call('submitExam');
                }
            }, 1000);
        },
        updateDisplay() {
            const minutes = Math.floor(this.seconds / 60);
            const secs = this.seconds % 60;
            this.display = `${minutes}:${secs.toString().padStart(2, '0')}`;
        }
    }
}
</script>
```

---

## الأخطاء الشائعة وحلولها

### 1. Infolist class not found
❌ الخطأ:
```php
use Filament\Infolists\Infolist;
public function infolist(Infolist $infolist): Infolist
```

✅ الحل:
استخدم Form بدلاً من Infolist أو View مخصص:
```php
protected static string $view = 'filament.student.pages.view-course';
```

### 2. Method authenticateUsing does not exist
❌ لا يوجد authenticateUsing في Filament 4

✅ الحل: استخدم CustomUserProvider في AppServiceProvider

### 3. Type mismatch في Properties
❌ الخطأ:
```php
protected static string $navigationIcon = 'heroicon-o-academic-cap';
```

✅ الحل: تأكد من أن Property Type صحيح:
```php
protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
```

---

## الخطوات التالية

1. ✅ تم إنشاء التصميم الأساسي للمنصة
2. ✅ تم إضافة الألوان والـ gradient
3. ⏳ إنشاء الـ Resources (MyCourse, MyExam, MyAssignment)
4. ⏳ إضافة صفحات مخصصة (TakeExam, SubmitAssignment)
5. ⏳ إضافة Widgets للإحصائيات
6. ⏳ تفعيل Notifications
7. ⏳ إضافة نظام الأسئلة للامتحانات

---

## Resources مفيدة

- [Filament Documentation](https://filamentphp.com/docs)
- [Filament Table Builder](https://filamentphp.com/docs/tables)
- [Filament Forms](https://filamentphp.com/docs/forms)
- [Alpine.js](https://alpinejs.dev/)

---

**Created**: December 25, 2025  
**Status**: In Progress 🚧
