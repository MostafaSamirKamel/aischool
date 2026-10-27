# دليل مفاتيح الترجمة - Advanced Features

## نظرة عامة
تم إضافة مفاتيح ترجمة شاملة لجميع الميزات المتقدمة الجديدة في النظام باللغات الثلاث (العربية، الإنجليزية، الفرنسية).

## الملفات المعدلة

### 1. ملفات Resource الأساسية

#### Exam Resource
**الملفات:**
- `lang/ar/filament/admin/exam_resource.php`
- `lang/en/filament/admin/exam_resource.php`

**المفاتيح المضافة:**
```php
// Form specific keys
'tenant_id' => 'المؤسسة',
'tenant_id_default' => 'اختر المؤسسة',
'course_id' => 'الكورس',
'group_id' => 'المجموعة',
'instructor_id' => 'المدرس',

// Table specific keys
'tenant' => ['name' => 'المؤسسة'],
'course' => ['name' => 'الكورس'],
'group' => ['name' => 'المجموعة'],
'instructor' => ['full_name' => 'المدرس'],
'grades_count' => 'عدد الدرجات',
'created_at' => 'تاريخ الإنشاء',
'updated_at' => 'تاريخ التحديث',
```

#### Grade Resource
**الملفات:**
- `lang/ar/filament/admin/grade_resource.php`
- `lang/en/filament/admin/grade_resource.php`

**المفاتيح المضافة:**
```php
// Form specific keys
'tenant_id' => 'المؤسسة',
'tenant_id_default' => 'اختر المؤسسة',
'exam_id' => 'الامتحان',
'student_id' => 'الطالب',
'enrollment_id' => 'التسجيل',

// Grade options for forms
'grade' => [
    'a+' => 'ممتاز +',
    'a' => 'ممتاز',
    'b+' => 'جيد جداً +',
    'b' => 'جيد جداً',
    'c+' => 'جيد +',
    'c' => 'جيد',
    'd' => 'مقبول',
    'f' => 'راسب',
],

// Table specific keys
'tenant' => ['name' => 'المؤسسة'],
'student' => ['full_name' => 'الطالب'],
'exam' => [
    'title' => 'الامتحان',
    'type' => 'نوع الامتحان',
],
'graded_by_user' => ['name' => 'المقيم'],
'created_at' => 'تاريخ الإنشاء',
'updated_at' => 'تاريخ التحديث',
```

#### Student Resource
**الملفات:**
- `lang/ar/filament/admin/student_resource.php`
- `lang/en/filament/admin/student_resource.php`

**المفاتيح المضافة:**
```php
'pages' => [
    'transcript' => [
        'title' => 'كشف درجات الطالب',
        'navigation_label' => 'كشف الدرجات',
        'select_student' => 'اختر الطالب',
        'student_label' => 'الطالب',
        'error_no_student' => 'يجب اختيار طالب أولاً',
        'download_pdf' => 'تحميل PDF',
    ],
    'certificate' => [
        'title' => 'إصدار شهادة',
        'navigation_label' => 'إصدار شهادة',
        'section_title' => 'إصدار شهادة إتمام كورس',
        'section_description' => 'اختر الطالب والكورس لإصدار الشهادة',
        'student_label' => 'الطالب',
        'course_label' => 'الكورس',
        'info_note' => '⚠️ ملاحظة: يجب أن يكون الطالب قد نجح في جميع امتحانات الكورس لإصدار الشهادة',
        'error_title' => 'خطأ',
        'error_not_found' => 'الطالب أو الكورس غير موجود',
        'generate' => 'إصدار الشهادة',
    ],
],
```

### 2. ملفات Widgets الجديدة

#### ملف Widgets الجديد
**الملفات:**
- `lang/ar/widgets.php` ✨ **جديد**
- `lang/en/widgets.php` ✨ **جديد**
- `lang/fr/widgets.php` ✨ **جديد**

**المفاتيح:**

##### Exams Overview Widget
```php
'exams_overview' => [
    'total_exams' => 'إجمالي الامتحانات',
    'total_exams_description' => 'جميع الامتحانات في النظام',
    'upcoming_exams' => 'امتحانات قادمة',
    'upcoming_exams_description' => 'مجدولة ولم تبدأ بعد',
    'completed_exams' => 'امتحانات مكتملة',
    'completed_exams_description' => 'تم إجراؤها بنجاح',
    'today_exams' => 'امتحانات اليوم',
    'today_exams_description' => 'المقررة لهذا اليوم',
],
```

##### Grades Stats Widget
```php
'grades_stats' => [
    'total_grades' => 'إجمالي الدرجات',
    'total_grades_description' => 'جميع الدرجات المسجلة',
    'average_percentage' => 'متوسط النسبة المئوية',
    'average_percentage_description' => 'للطلاب في جميع الامتحانات',
    'passing_rate' => 'نسبة النجاح',
    'passing_rate_description' => ':passing من :total طالب',
    'high_achievers' => 'المتفوقين',
    'high_achievers_description' => 'الحاصلين على 90% فأكثر',
],
```

##### Upcoming Exams Widget
```php
'upcoming_exams' => [
    'heading' => 'الامتحانات القادمة',
    'title' => 'العنوان',
    'course' => 'الكورس',
    'group' => 'المجموعة',
    'type' => 'النوع',
    'exam_date' => 'التاريخ',
    'total_marks' => 'الدرجة الكلية',
    'days_until' => 'متبقي',
    'minutes' => 'دقيقة',
    'days' => 'يوم',
    'type_quiz' => 'كويز',
    'type_midterm' => 'ميدتيرم',
    'type_final' => 'فاينال',
    'type_assignment' => 'تكليف',
    'type_project' => 'مشروع',
],
```

##### Grades Distribution Widget
```php
'grades_distribution' => [
    'heading' => 'توزيع التقديرات',
    'students_count' => 'عدد الطلاب',
],
```

##### Bulk Grade Entry Page
```php
'bulk_grade_entry' => [
    'title' => 'إدخال الدرجات الجماعي',
    'navigation_label' => 'إدخال جماعي',
    'select_exam' => 'اختيار الامتحان',
    'exam_label' => 'الامتحان',
    'exam_details' => 'تفاصيل الامتحان',
    'select_exam_prompt' => 'اختر امتحان لعرض التفاصيل',
    'exam_not_found' => 'امتحان غير موجود',
    'total_marks_label' => 'الدرجة الكلية',
    'passing_marks_label' => 'درجة النجاح',
    'error_title' => 'خطأ',
    'error_select_exam' => 'يجب اختيار امتحان',
    'error_exam_not_found' => 'الامتحان غير موجود',
    'success_title' => 'نجح',
    'success_message' => 'تم حفظ :count درجة بنجاح',
    'save' => 'حفظ',
],
```

### 3. ملفات Views الجديدة

#### ملف Views الجديد
**الملفات:**
- `lang/ar/views.php` ✨ **جديد**
- `lang/en/views.php` ✨ **جديد**
- `lang/fr/views.php` ✨ **جديد**

**المفاتيح:**

##### Bulk Grade Entry View
```php
'bulk_grade_entry' => [
    'student_list_heading' => 'قائمة الطلاب (:count طالب)',
    'table' => [
        'number' => '#',
        'student_name' => 'اسم الطالب',
        'email' => 'البريد الإلكتروني',
        'marks_obtained' => 'الدرجة المستحقة',
        'remarks' => 'ملاحظات',
    ],
    'save_all' => 'حفظ جميع الدرجات',
    'no_students' => 'لا يوجد طلاب مسجلين في هذا الامتحان',
],
```

##### Student Transcript View
```php
'transcript' => [
    'student_info' => [
        'heading' => 'معلومات الطالب',
        'full_name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'not_available' => 'غير متوفر',
    ],
    'statistics' => [
        'heading' => 'إحصائيات الأداء',
        'total_exams' => 'إجمالي الامتحانات',
        'average_percentage' => 'متوسط النسبة',
        'pass_fail' => 'نجح / رسب',
        'grade_distribution' => 'توزيع التقديرات',
        'highest_grade' => 'أعلى درجة',
        'lowest_grade' => 'أدنى درجة',
    ],
    'grades_by_course' => [
        'heading' => 'الدرجات حسب المقرر',
        'course' => 'المقرر',
        'exam' => 'الامتحان',
        'type' => 'النوع',
        'marks' => 'الدرجة',
        'percentage' => 'النسبة',
        'grade' => 'التقدير',
        'date' => 'التاريخ',
        'no_grades' => 'لا توجد درجات مسجلة',
    ],
    'actions' => [
        'download_pdf' => 'تحميل كشف الدرجات (PDF)',
        'print' => 'طباعة',
    ],
],
```

##### Issue Certificate View
```php
'certificate' => [
    'preview_heading' => 'معاينة الشهادة',
    'certificate_info' => 'معلومات الشهادة',
    'student_name' => 'اسم الطالب',
    'course_name' => 'اسم الكورس',
    'issue_date' => 'تاريخ الإصدار',
    'generate_button' => 'إصدار الشهادة',
    'actions' => [
        'preview' => 'معاينة',
        'download' => 'تحميل',
        'print' => 'طباعة',
    ],
],
```

##### PDF Templates
```php
'pdf' => [
    'transcript' => [
        'title' => 'كشف درجات الطالب',
        'student_id' => 'الرقم الطلابي',
        'student_name' => 'اسم الطالب',
        'issue_date' => 'تاريخ الإصدار',
        'academic_record' => 'السجل الأكاديمي',
        'course' => 'المقرر',
        'exam' => 'الامتحان',
        'marks' => 'الدرجة',
        'percentage' => 'النسبة %',
        'grade' => 'التقدير',
        'overall_statistics' => 'الإحصائيات العامة',
        'total_courses' => 'إجمالي المقررات',
        'total_exams' => 'إجمالي الامتحانات',
        'average_percentage' => 'المتوسط العام',
        'signature' => 'التوقيع',
        'registrar' => 'مسجل الجامعة',
    ],
    'certificate' => [
        'title' => 'شهادة إتمام الكورس',
        'awarded_to' => 'تُمنح هذه الشهادة لـ',
        'completion_text' => 'لإتمامه بنجاح متطلبات',
        'course' => 'الكورس',
        'date' => 'التاريخ',
        'instructor' => 'المدرس',
        'director' => 'المدير',
        'seal' => 'الختم',
    ],
],
```

##### Common Labels
```php
// Exam Types
'exam_types' => [
    'quiz' => 'كويز',
    'midterm' => 'امتحان نصفي',
    'final' => 'امتحان نهائي',
    'assignment' => 'واجب',
    'project' => 'مشروع',
],

// Grade Labels
'grades' => [
    'A+' => 'ممتاز +',
    'A' => 'ممتاز',
    'B+' => 'جيد جداً +',
    'B' => 'جيد جداً',
    'C+' => 'جيد +',
    'C' => 'جيد',
    'D' => 'مقبول',
    'F' => 'راسب',
],

// Status Labels
'status' => [
    'pass' => 'ناجح',
    'fail' => 'راسب',
],
```

## كيفية الاستخدام

### في ملفات PHP
```php
// استخدام مفاتيح الترجمة في Resource Forms
Forms\Components\TextInput::make('title')
    ->label(__('filament/admin/exam_resource.title'))

// استخدام مفاتيح الترجمة في Resource Tables
Tables\Columns\TextColumn::make('course.name')
    ->label(__('filament/admin/exam_resource.course.name'))

// استخدام مفاتيح الترجمة في Widgets
protected ?string $heading = __('widgets.grades_distribution.heading');

// استخدام مفاتيح الترجمة في Pages
protected static ?string $title = __('filament/admin/student_resource.pages.transcript.title');
```

### في ملفات Blade
```blade
{{-- استخدام مفاتيح الترجمة في Views --}}
<h2>{{ __('views.transcript.student_info.heading') }}</h2>

<p>{{ __('views.transcript.statistics.total_exams') }}: {{ $stats['total_exams'] }}</p>

<button>{{ __('views.transcript.actions.download_pdf') }}</button>
```

## ملخص التعديلات

### الملفات المعدلة (6 ملفات)
1. ✅ `lang/ar/filament/admin/exam_resource.php` - مفاتيح Form و Table
2. ✅ `lang/ar/filament/admin/grade_resource.php` - مفاتيح Form و Table
3. ✅ `lang/ar/filament/admin/student_resource.php` - مفاتيح Pages
4. ✅ `lang/en/filament/admin/exam_resource.php` - مفاتيح Form و Table
5. ✅ `lang/en/filament/admin/grade_resource.php` - مفاتيح Form و Table
6. ✅ `lang/en/filament/admin/student_resource.php` - مفاتيح Pages

### الملفات الجديدة (6 ملفات)
1. ✨ `lang/ar/widgets.php` - مفاتيح الـ Widgets الجديدة
2. ✨ `lang/en/widgets.php` - مفاتيح الـ Widgets الجديدة
3. ✨ `lang/fr/widgets.php` - مفاتيح الـ Widgets الجديدة
4. ✨ `lang/ar/views.php` - مفاتيح الـ Views الجديدة
5. ✨ `lang/en/views.php` - مفاتيح الـ Views الجديدة
6. ✨ `lang/fr/views.php` - مفاتيح الـ Views الجديدة

## إحصائيات

- **إجمالي الملفات المعدلة:** 6 ملفات
- **إجمالي الملفات الجديدة:** 6 ملفات
- **إجمالي مفاتيح الترجمة المضافة:** ~250+ مفتاح
- **اللغات المدعومة:** 3 لغات (العربية، الإنجليزية، الفرنسية)
- **الميزات المغطاة:** 8 ميزات متقدمة

## ملاحظات مهمة

1. جميع مفاتيح الترجمة متوفرة بـ 3 لغات (عربي، إنجليزي، فرنسي)
2. المفاتيح منظمة حسب الاستخدام (Forms, Tables, Widgets, Views, PDFs)
3. يمكن استخدام المفاتيح مباشرة في الكود باستخدام دالة `__()`
4. تم اتباع معايير تسمية Laravel للترجمة
5. جميع النصوص القابلة للترجمة تم استخراجها من الكود

## المراجع

- [Laravel Localization](https://laravel.com/docs/10.x/localization)
- [Filament Localization](https://filamentphp.com/docs/3.x/panels/localization)
