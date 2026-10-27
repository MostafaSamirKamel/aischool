# 📊 بديل Filament Excel Import - الدليل الشامل

**المشكلة:** `eightynine/filament-excel-import` لا يدعم Filament 5  
**الحل:** استخدام `pxlrbt/filament-excel` + `maatwebsite/excel`

---

## ✅ البديل المستخدم

### الباكجات المطلوبة

```bash
# موجود بالفعل في المشروع
"pxlrbt/filament-excel": "^3.6"

# قد تحتاج تثبيته
composer require maatwebsite/excel
```

---

## 📖 مثال كامل: استيراد الطلاب

### 1️⃣ إنشاء Import Class

```bash
php artisan make:import StudentsImport --model=Student
```

**ملف:** `app/Imports/StudentsImport.php`

```php
<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;

class StudentsImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation,
    SkipsOnError
{
    use SkipsErrors;
    
    private $successCount = 0;
    private $errorCount = 0;

    public function model(array $row)
    {
        DB::beginTransaction();
        
        try {
            // إنشاء User أو البحث عنه
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make($row['password'] ?? 'password'),
                    'email_verified_at' => now(),
                ]
            );

            // إنشاء Student
            $student = Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $row['phone'],
                    'gender' => $row['gender'],
                    'date_of_birth' => $row['date_of_birth'] ?? null,
                    'address' => $row['address'] ?? null,
                    'tenant_id' => auth()->user()->tenant_id,
                ]
            );

            $this->successCount++;
            
            DB::commit();
            
            return $student;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorCount++;
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني موجود بالفعل',
            'phone.required' => 'رقم الهاتف مطلوب',
            'gender.required' => 'الجنس مطلوب',
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى',
        ];
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }
}
```

---

### 2️⃣ إضافة Import Action في Resource

**ملف:** `app/Filament/Admin/Resources/StudentResource.php`

```php
<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StudentResource\Pages;
use App\Models\Student;
use App\Imports\StudentsImport;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static \UnitEnum|string|null $navigationGroup = 'إدارة الطلاب';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الاسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف'),
                Tables\Columns\TextColumn::make('gender')
                    ->label('الجنس')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    }),
            ])
            ->headerActions([
                // زر الاستيراد من Excel
                Tables\Actions\Action::make('import')
                    ->label('استيراد من Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        FileUpload::make('file')
                            ->label('ملف Excel')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                            ])
                            ->required()
                            ->helperText('تنسيق الملف: name, email, phone, gender, date_of_birth, address'),
                    ])
                    ->action(function (array $data) {
                        try {
                            $import = new StudentsImport();
                            
                            Excel::import($import, $data['file']);
                            
                            // حذف الملف المؤقت
                            if (Storage::exists($data['file'])) {
                                Storage::delete($data['file']);
                            }
                            
                            $successCount = $import->getSuccessCount();
                            $errorCount = $import->getErrorCount();
                            
                            Notification::make()
                                ->title('تم الاستيراد بنجاح')
                                ->body("تم استيراد {$successCount} طالب بنجاح" . 
                                      ($errorCount > 0 ? " - فشل: {$errorCount}" : ""))
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('خطأ في الاستيراد')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                // زر التصدير إلى Excel
                ExportAction::make()
                    ->label('تصدير إلى Excel')
                    ->color('primary'),
                    
                // زر تحميل نموذج Excel
                Tables\Actions\Action::make('downloadTemplate')
                    ->label('تحميل نموذج')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        return response()->download(public_path('templates/students_template.xlsx'));
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('تصدير المحدد'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    // ... بقية الكود
}
```

---

### 3️⃣ إنشاء نموذج Excel Template

**ملف:** `public/templates/students_template.xlsx`

| name | email | phone | gender | date_of_birth | address | password |
|------|-------|-------|--------|---------------|---------|----------|
| أحمد محمد | ahmed@example.com | 0512345678 | male | 2000-01-01 | الرياض | password |
| فاطمة علي | fatima@example.com | 0523456789 | female | 2001-02-15 | جدة | password |

**أنشئ هذا الملف في Excel وضعه في:**
```
public/templates/students_template.xlsx
```

---

## 🎯 مقارنة مع eightynine/filament-excel-import

| الميزة | eightynine | pxlrbt + maatwebsite |
|--------|-----------|----------------------|
| ✅ متوافق Filament 5 | ❌ | ✅ |
| ✅ Import | ✅ | ✅ |
| ✅ Export | ✅ | ✅ |
| ✅ Validation | محدود | ✅ كامل |
| ✅ Error Handling | محدود | ✅ متقدم |
| ✅ التخصيص | محدود | ✅ كامل |
| ✅ الأداء | جيد | ✅ ممتاز |
| ✅ الصيانة | متوقفة | ✅ نشطة |

---

## 📊 ميزات إضافية

### 1. استيراد مع Progress Bar

```php
use Maatwebsite\Excel\Concerns\WithProgressBar;

class StudentsImport implements 
    ToModel, 
    WithHeadingRow,
    WithProgressBar
{
    // ... الكود
}
```

### 2. استيراد على دفعات (Chunking)

```php
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StudentsImport implements 
    ToModel, 
    WithHeadingRow,
    WithChunkReading
{
    public function chunkSize(): int
    {
        return 1000; // استيراد 1000 صف في المرة
    }
}
```

### 3. استيراد في الخلفية (Queue)

```php
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class StudentsImport implements 
    ToModel, 
    WithHeadingRow,
    ShouldQueue // ← يجعله يعمل في الخلفية
{
    // ... الكود
}
```

**في الـ Action:**

```php
->action(function (array $data) {
    Excel::queueImport(new StudentsImport(), $data['file']);
    
    Notification::make()
        ->title('تم بدء الاستيراد')
        ->body('سيتم إعلامك عند الانتهاء')
        ->success()
        ->send();
})
```

---

## 🔍 اختبار البديل

### 1. إنشاء ملف Excel تجريبي

```bash
php artisan make:command GenerateStudentsTemplate
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateStudentsTemplate extends Command
{
    protected $signature = 'students:template';
    protected $description = 'Generate students Excel template';

    public function handle()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // العناوين
        $sheet->setCellValue('A1', 'name');
        $sheet->setCellValue('B1', 'email');
        $sheet->setCellValue('C1', 'phone');
        $sheet->setCellValue('D1', 'gender');
        $sheet->setCellValue('E1', 'date_of_birth');
        $sheet->setCellValue('F1', 'address');
        $sheet->setCellValue('G1', 'password');
        
        // مثال
        $sheet->setCellValue('A2', 'أحمد محمد');
        $sheet->setCellValue('B2', 'ahmed@example.com');
        $sheet->setCellValue('C2', '0512345678');
        $sheet->setCellValue('D2', 'male');
        $sheet->setCellValue('E2', '2000-01-01');
        $sheet->setCellValue('F2', 'الرياض');
        $sheet->setCellValue('G2', 'password');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path('templates/students_template.xlsx'));
        
        $this->info('Template created at: public/templates/students_template.xlsx');
    }
}
```

### 2. اختبر الاستيراد

```bash
# ولّد النموذج
php artisan students:template

# افتح Dashboard
php artisan serve

# استورد الملف من واجهة Filament
```

---

## 📚 المصادر

- [pxlrbt/filament-excel Docs](https://github.com/pxlrbt/filament-excel)
- [maatwebsite/excel Docs](https://docs.laravel-excel.com/)
- [Filament Actions Docs](https://filamentphp.com/docs/5.x/actions/overview)

---

## 🎉 الخلاصة

✅ **pxlrbt/filament-excel** أفضل بكثير من eightynine  
✅ يدعم Filament 5 بالكامل  
✅ أكثر مرونة وتخصيص  
✅ أداء أفضل للملفات الكبيرة  
✅ صيانة نشطة ومستمرة  

**كل شيء جاهز للاستخدام! 🚀**
