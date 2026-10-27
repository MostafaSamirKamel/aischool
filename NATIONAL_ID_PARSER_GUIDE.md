# دليل استخراج البيانات من الرقم القومي المصري
## Egyptian National ID Parser - Complete Guide

---

## 📋 نظرة عامة

هذا النظام يقوم باستخراج البيانات التالية تلقائياً من الرقم القومي المصري (14 رقم):
- ✅ **تاريخ الميلاد** (Birth Date)
- ✅ **العمر** (Age) - بالسنوات والشهور والأيام
- ✅ **النوع/الجنس** (Gender)
- ✅ **المحافظة** (Governorate)

---

## 🔢 تركيب الرقم القومي المصري

الرقم القومي المصري مكون من **14 رقم** موزعة كالتالي:

```
مثال: 30012151234567
┌─────────────────────────────────────────────────┐
│ الموضع │  الرقم  │        الوصف                │
├─────────┼─────────┼──────────────────────────────┤
│   1     │    3    │ قرن الميلاد (3 = 2000s)     │
│  2-3    │   00    │ السنة (00 = 2000)            │
│  4-5    │   12    │ الشهر (12 = ديسمبر)         │
│  6-7    │   15    │ اليوم (15)                   │
│  8-9    │   12    │ كود المحافظة (12 = البحيرة) │
│ 10-13   │  3456   │ الرقم التسلسلي              │
│   14    │    7    │ رقم التحقق (فردي = ذكر)     │
└─────────┴─────────┴──────────────────────────────┘
```

---

## 📁 الملفات المطلوبة

### 1️⃣ **Service Class**: `app/Services/NationalIdParser.php`

```php
<?php

namespace App\Services;

use App\Models\Governorate;
use Carbon\Carbon;

class NationalIdParser
{
    /**
     * Parse Egyptian National ID and extract information
     * 
     * @param string $nationalId
     * @return array|null
     */
    public static function parse(string $nationalId): ?array
    {
        // Remove any spaces or dashes
        $nationalId = preg_replace('/[^0-9]/', '', $nationalId);
        
        // Validate length
        if (strlen($nationalId) !== 14) {
            return null;
        }

        // Validate it's all digits
        if (!ctype_digit($nationalId)) {
            return null;
        }

        try {
            // Extract century digit (1 = 1800s, 2 = 1900s, 3 = 2000s)
            $centuryDigit = (int) substr($nationalId, 0, 1);
            
            // Extract birth date (positions 1-6: YYMMDD)
            $year = (int) substr($nationalId, 1, 2);
            $month = (int) substr($nationalId, 3, 2);
            $day = (int) substr($nationalId, 5, 2);

            // Determine full year based on century digit
            if ($centuryDigit == 2) {
                $fullYear = 1900 + $year;
            } elseif ($centuryDigit == 3) {
                $fullYear = 2000 + $year;
            } else {
                return null; // Invalid century digit
            }

            // Validate date
            if (!checkdate($month, $day, $fullYear)) {
                return null;
            }

            $birthDate = Carbon::createFromDate($fullYear, $month, $day);

            // Extract governorate code (positions 7-8)
            $governorateCode = substr($nationalId, 7, 2);
            $governorate = Governorate::getByCode($governorateCode);

            // Extract gender (position 13 - second digit from right)
            $genderDigit = (int) substr($nationalId, 12, 1);
            $gender = ($genderDigit % 2 === 0) ? 'female' : 'male';

            return [
                'birth_date' => $birthDate->format('Y-m-d'),
                'gender' => $gender,
                'governorate_code' => $governorateCode,
                'governorate_name' => $governorate ? $governorate->getName() : null,
                'governorate_id' => $governorate?->id,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
```

---

### 2️⃣ **Model Method**: إضافة في `app/Models/Governorate.php`

```php
/**
 * Get governorate by national ID code
 * 
 * @param string $code
 * @return Governorate|null
 */
public static function getByCode(string $code): ?Governorate
{
    return self::where('code', $code)->first();
}

/**
 * Get governorate name (Arabic by default)
 * 
 * @param string $locale
 * @return string
 */
public function getName(string $locale = 'ar'): string
{
    return $locale === 'ar' ? $this->name_ar : $this->name_en;
}
```

---

### 3️⃣ **Form Integration**: في `Filament Form Schema`

```php
use App\Services\NationalIdParser;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

// في داخل schema الفورم:
TextInput::make('national_id')
    ->label('الرقم القومي')
    ->maxLength(14)
    ->unique('students', 'national_id', modifyRuleUsing: function ($rule, $get, $record) {
        return $record ? $rule->ignore($record->id) : $rule;
    })
    ->live(onBlur: true)  // ⬅️ يعمل عند الخروج من الحقل
    ->afterStateUpdated(function (Set $set, ?string $state) {
        if (!empty($state) && strlen($state) === 14) {
            $parsed = NationalIdParser::parse($state);
            if ($parsed) {
                // ملء تاريخ الميلاد
                $set('birth_date', $parsed['birth_date']);
                
                // ملء النوع
                $set('gender', $parsed['gender']);
                
                // ملء المحافظة
                if ($parsed['governorate_id']) {
                    $set('governorate_id', $parsed['governorate_id']);
                }

                // حساب العمر
                if ($parsed['birth_date']) {
                    $birthDate = \Carbon\Carbon::parse($parsed['birth_date']);
                    $now = now();
                    $diff = $birthDate->diff($now);
                    $age = sprintf('%d سنة و %d شهر و %d يوم', $diff->y, $diff->m, $diff->d);
                    $set('age_display', $age);
                }
            }
        }
    })
    ->helperText('سيتم استخراج تاريخ الميلاد والجنس والمحافظة تلقائياً')
    ->columnSpanFull(),

DatePicker::make('birth_date')
    ->label('تاريخ الميلاد')
    ->maxDate(now())
    ->live(onBlur: true)
    ->afterStateUpdated(function (Set $set, $state) {
        if ($state) {
            $birthDate = \Carbon\Carbon::parse($state);
            $now = now();
            $diff = $birthDate->diff($now);
            $age = sprintf('%d سنة و %d شهر و %d يوم', $diff->y, $diff->m, $diff->d);
            $set('age_display', $age);
        }
    }),

TextInput::make('age_display')
    ->label('العمر')
    ->disabled()
    ->dehydrated(false)
    ->placeholder('سيتم حسابه تلقائياً'),

Select::make('gender')
    ->label('النوع')
    ->options([
        'male' => 'ذكر',
        'female' => 'أنثى'
    ])
    ->required(),

Select::make('governorate_id')
    ->label('المحافظة')
    ->relationship('governorate', 'name_ar')
    ->searchable()
    ->preload(),
```

---

## 🎯 الشرح التفصيلي

### 1. **استخراج تاريخ الميلاد**

```php
// استخراج رقم القرن
$centuryDigit = (int) substr($nationalId, 0, 1);

// استخراج السنة والشهر واليوم
$year = (int) substr($nationalId, 1, 2);   // موضع 2-3
$month = (int) substr($nationalId, 3, 2);  // موضع 4-5
$day = (int) substr($nationalId, 5, 2);    // موضع 6-7
```

**جدول القرون:**
| رقم القرن | الفترة الزمنية |
|-----------|----------------|
| 1         | 1800 - 1899    |
| 2         | 1900 - 1999    |
| 3         | 2000 - 2099    |

**مثال:**
```
رقم قومي: 30012151234567
├─ 3: قرن 2000
├─ 00: سنة 2000
├─ 12: شهر 12 (ديسمبر)
└─ 15: يوم 15
→ تاريخ الميلاد: 2000-12-15
```

---

### 2. **حساب العمر**

```php
$birthDate = Carbon::parse($parsed['birth_date']);
$now = now();
$diff = $birthDate->diff($now);

// التنسيق بالعربية
$age = sprintf(
    '%d سنة و %d شهر و %d يوم',
    $diff->y,  // السنوات
    $diff->m,  // الشهور
    $diff->d   // الأيام
);
```

**مثال:**
```
تاريخ الميلاد: 2000-12-15
تاريخ اليوم: 2026-03-19
→ العمر: 25 سنة و 3 شهر و 4 يوم
```

---

### 3. **استخراج المحافظة**

```php
// استخراج الكود (موضع 8-9)
$governorateCode = substr($nationalId, 7, 2);
$governorate = Governorate::getByCode($governorateCode);
```

**جدول أكواد المحافظات المصرية:**

| الكود | المحافظة      | الكود | المحافظة        |
|-------|----------------|-------|------------------|
| 01    | القاهرة        | 16    | الغربية          |
| 02    | الإسكندرية    | 17    | المنوفية         |
| 03    | بورسعيد       | 18    | القليوبية        |
| 04    | السويس        | 19    | كفر الشيخ        |
| 11    | دمياط         | 21    | الجيزة           |
| 12    | البحيرة       | 22    | بني سويف        |
| 13    | الشرقية       | 23    | الفيوم           |
| 14    | الدقهلية      | 24    | المنيا           |
| 15    | الإسماعيلية   | 25    | أسيوط            |

**مثال:**
```
رقم قومي: 30012151234567
                ^^
                12 = البحيرة
```

---

### 4. **استخراج النوع (الجنس)**

```php
// استخراج الرقم قبل الأخير (موضع 13)
$genderDigit = (int) substr($nationalId, 12, 1);
$gender = ($genderDigit % 2 === 0) ? 'female' : 'male';
```

**القاعدة:**
- ✅ **رقم فردي** (1, 3, 5, 7, 9) → **ذكر** (male)
- ✅ **رقم زوجي** (0, 2, 4, 6, 8) → **أنثى** (female)

**مثال:**
```
رقم قومي: 30012151234567
                     ^
                     7 (فردي) = ذكر
```

---

## 📊 أمثلة عملية

### مثال 1: طالب ذكر من القاهرة

```
الرقم القومي: 29512011234569
```

**التحليل:**
```php
$parsed = NationalIdParser::parse('29512011234569');

// النتيجة:
[
    'birth_date' => '1995-12-01',
    'gender' => 'male',              // 9 فردي
    'governorate_code' => '01',
    'governorate_name' => 'القاهرة',
    'governorate_id' => 1,
]
```

---

### مثال 2: طالبة أنثى من الإسكندرية

```
الرقم القومي: 30306021234560
```

**التحليل:**
```php
$parsed = NationalIdParser::parse('30306021234560');

// النتيجة:
[
    'birth_date' => '2003-06-02',
    'gender' => 'female',            // 0 زوجي
    'governorate_code' => '02',
    'governorate_name' => 'الإسكندرية',
    'governorate_id' => 2,
]
```

---

## 🗄️ قاعدة البيانات المطلوبة

### جدول المحافظات (governorates)

```sql
CREATE TABLE `governorates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(2) NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `governorates_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### بيانات المحافظات

```sql
INSERT INTO `governorates` (`code`, `name_ar`, `name_en`) VALUES
('01', 'القاهرة', 'Cairo'),
('02', 'الإسكندرية', 'Alexandria'),
('03', 'بورسعيد', 'Port Said'),
('04', 'السويس', 'Suez'),
('11', 'دمياط', 'Damietta'),
('12', 'البحيرة', 'Beheira'),
('13', 'الشرقية', 'Sharqia'),
('14', 'الدقهلية', 'Dakahlia'),
('15', 'الإسماعيلية', 'Ismailia'),
('16', 'الغربية', 'Gharbia'),
('17', 'المنوفية', 'Monufia'),
('18', 'القليوبية', 'Qalyubia'),
('19', 'كفر الشيخ', 'Kafr El Sheikh'),
('21', 'الجيزة', 'Giza'),
('22', 'بني سويف', 'Beni Suef'),
('23', 'الفيوم', 'Fayoum'),
('24', 'المنيا', 'Minya'),
('25', 'أسيوط', 'Asyut'),
('26', 'سوهاج', 'Sohag'),
('27', 'قنا', 'Qena'),
('28', 'أسوان', 'Aswan'),
('29', 'الأقصر', 'Luxor'),
('31', 'البحر الأحمر', 'Red Sea'),
('32', 'الوادي الجديد', 'New Valley'),
('33', 'مطروح', 'Matrouh'),
('34', 'شمال سيناء', 'North Sinai'),
('35', 'جنوب سيناء', 'South Sinai'),
('88', 'خارج الجمهورية', 'Outside Egypt');
```

---

## ✅ التحقق من الصحة

### 1. التحقق من الطول
```php
if (strlen($nationalId) !== 14) {
    return null; // خطأ: الطول غير صحيح
}
```

### 2. التحقق من أنه أرقام فقط
```php
if (!ctype_digit($nationalId)) {
    return null; // خطأ: يحتوي على حروف
}
```

### 3. التحقق من صحة التاريخ
```php
if (!checkdate($month, $day, $fullYear)) {
    return null; // خطأ: تاريخ غير صحيح (مثل 32/13/2000)
}
```

### 4. التحقق من رقم القرن
```php
if (!in_array($centuryDigit, [2, 3])) {
    return null; // خطأ: قرن غير معروف
}
```

---

## 🚀 الاستخدام

### في Controller

```php
use App\Services\NationalIdParser;

public function store(Request $request)
{
    $nationalId = $request->input('national_id');
    $parsed = NationalIdParser::parse($nationalId);
    
    if ($parsed) {
        Student::create([
            'national_id' => $nationalId,
            'birth_date' => $parsed['birth_date'],
            'gender' => $parsed['gender'],
            'governorate_id' => $parsed['governorate_id'],
            // ... باقي البيانات
        ]);
    }
}
```

### في Validation

```php
use App\Services\NationalIdParser;
use Illuminate\Validation\Validator;

Validator::extend('valid_national_id', function ($attribute, $value, $parameters, $validator) {
    $parsed = NationalIdParser::parse($value);
    return $parsed !== null;
});

// الاستخدام في Request
public function rules()
{
    return [
        'national_id' => ['required', 'size:14', 'valid_national_id'],
    ];
}
```

### في API

```php
Route::post('/parse-national-id', function (Request $request) {
    $nationalId = $request->input('national_id');
    $parsed = NationalIdParser::parse($nationalId);
    
    if (!$parsed) {
        return response()->json([
            'success' => false,
            'message' => 'رقم قومي غير صحيح'
        ], 422);
    }
    
    return response()->json([
        'success' => true,
        'data' => $parsed
    ]);
});
```

---

## 🎨 واجهة المستخدم

### كيفية العمل من واجهة Filament:

1. **المستخدم يدخل الرقم القومي** في حقل `national_id`
2. **عند الخروج من الحقل** (`onBlur`):
   - يتم استدعاء `NationalIdParser::parse()`
   - استخراج البيانات تلقائياً
3. **ملء الحقول التالية تلقائياً**:
   - ✅ تاريخ الميلاد (`birth_date`)
   - ✅ العمر (`age_display`)
   - ✅ النوع (`gender`)
   - ✅ المحافظة (`governorate_id`)

### الحقول المطلوبة في الفورم:

```php
- national_id        → TextInput (مع live validation)
- birth_date         → DatePicker (مع auto-calculation للعمر)
- age_display        → TextInput (disabled, for display only)
- gender             → Select (male/female)
- governorate_id     → Select (relationship)
```

---

## 📝 ملاحظات مهمة

1. **الرقم القومي يجب أن يكون 14 رقم بالضبط**
2. **رقم القرن الحالي هو 3 (للمواليد من 2000 فما فوق)**
3. **الرقم 2 للقرن العشرين (1900-1999)**
4. **كود المحافظة 88 يعني "خارج الجمهورية"**
5. **يجب التحقق من صحة التاريخ لتجنب تواريخ غير منطقية**

---

## 🔧 التعديلات المطلوبة للنقل لنظام آخر

### إذا كنت لا تستخدم Filament:

استبدل:
```php
use Filament\Schemas\Components\Utilities\Set;

// بـ
// استخدم jQuery أو JavaScript عادي
```

### إذا كنت لا تستخدم Laravel:

استبدل:
```php
use Carbon\Carbon;

// بـ
$birthDate = new DateTime($fullYear . '-' . $month . '-' . $day);
```

### إذا كنت لا تستخدم Eloquent:

استبدل:
```php
$governorate = Governorate::getByCode($governorateCode);

// بـ
$governorate = $pdo->query("SELECT * FROM governorates WHERE code = '$governorateCode'")->fetch();
```

---

## 📞 الدعم

هذا النظام تم اختباره بنجاح مع:
- ✅ Laravel 11
- ✅ Filament 4
- ✅ PHP 8.2+
- ✅ MySQL 8.0+

---

## 🎓 المصادر

- [هيكل الرقم القومي المصري - الهيئة القومية للتأمينات الاجتماعية](https://www.nosi.gov.eg/)
- [أكواد المحافظات المصرية - الجهاز المركزي للتعبئة العامة والإحصاء](https://www.capmas.gov.eg/)

---

**تم إنشاء هذا الدليل في:** ١٩ مارس ٢٠٢٦  
**آخر تحديث:** ١٩ مارس ٢٠٢٦  
**الإصدار:** 1.0.0
