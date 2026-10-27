<?php

/**
 * اختبار حماية الحذف من القيود الأجنبية
 * Foreign Key Constraint Protection Test
 * 
 * هذا السكريبت يختبر أن جميع النماذج محمية بشكل صحيح من الحذف
 * 
 * الاستخدام:
 * php test_foreign_key_protection.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 اختبار حماية الحذف من القيود الأجنبية\n";
echo "==========================================\n\n";

$tests = [
    [
        'model' => 'App\Models\Student',
        'relations' => ['enrollments', 'grades', 'attendanceRecords', 'payments'],
        'name' => 'Student (الطالب)',
    ],
    [
        'model' => 'App\Models\Course',
        'relations' => ['groups', 'sessionSchedules'],
        'name' => 'Course (الدورة)',
    ],
    [
        'model' => 'App\Models\Group',
        'relations' => ['enrollments', 'sessionSchedules'],
        'name' => 'Group (المجموعة)',
    ],
    [
        'model' => 'App\Models\Instructor',
        'relations' => ['groups'],
        'name' => 'Instructor (المدرس)',
    ],
    [
        'model' => 'App\Models\Exam',
        'relations' => ['grades', 'questions', 'attempts'],
        'name' => 'Exam (الامتحان)',
    ],
    [
        'model' => 'App\Models\Track',
        'relations' => ['courses'],
        'name' => 'Track (المسار)',
    ],
    [
        'model' => 'App\Models\Classroom',
        'relations' => ['groups'],
        'name' => 'Classroom (الفصل)',
    ],
    [
        'model' => 'App\Models\Department',
        'relations' => ['reservations'],
        'name' => 'Department (القسم)',
    ],
    [
        'model' => 'App\Models\Enrollment',
        'relations' => ['payments', 'installments'],
        'name' => 'Enrollment (التسجيل)',
    ],
    [
        'model' => 'App\Models\Vault',
        'relations' => ['payments', 'enrollments', 'revenues', 'expenses'],
        'name' => 'Vault (الخزنة)',
    ],
    [
        'model' => 'App\Models\Governorate',
        'relations' => ['districts', 'students'],
        'name' => 'Governorate (المحافظة)',
    ],
    [
        'model' => 'App\Models\District',
        'relations' => ['students'],
        'name' => 'District (المنطقة)',
    ],
    [
        'model' => 'App\Models\SessionSchedule',
        'relations' => ['attendanceRecords'],
        'name' => 'SessionSchedule (جدول الحصص)',
    ],
    [
        'model' => 'App\Models\AttendanceSession',
        'relations' => ['records'],
        'name' => 'AttendanceSession (جلسة الحضور)',
    ],
    [
        'model' => 'App\Models\ExamQuestion',
        'relations' => ['answers'],
        'name' => 'ExamQuestion (سؤال الامتحان)',
    ],
    [
        'model' => 'App\Models\ExpenseCategory',
        'relations' => ['expenses'],
        'name' => 'ExpenseCategory (فئة المصروفات)',
    ],
    [
        'model' => 'App\Models\RevenueCategory',
        'relations' => ['revenues'],
        'name' => 'RevenueCategory (فئة الإيرادات)',
    ],
];

$passed = 0;
$failed = 0;
$skipped = 0;

foreach ($tests as $test) {
    echo "📝 اختبار: {$test['name']}\n";
    echo "   النموذج: {$test['model']}\n";
    
    // التحقق من وجود النموذج
    if (!class_exists($test['model'])) {
        echo "   ⚠️  تخطي: الملف غير موجود\n\n";
        $skipped++;
        continue;
    }
    
    // التحقق من وجود دالة booted مع deleting event
    $reflection = new ReflectionClass($test['model']);
    $hasBootedMethod = $reflection->hasMethod('booted');
    
    if (!$hasBootedMethod) {
        echo "   ❌ فشل: لا يوجد دالة booted()\n\n";
        $failed++;
        continue;
    }
    
    // التحقق من أن النموذج لديه العلاقات المطلوبة
    $model = new $test['model'];
    $missingRelations = [];
    
    foreach ($test['relations'] as $relation) {
        if (!method_exists($model, $relation)) {
            $missingRelations[] = $relation;
        }
    }
    
    if (!empty($missingRelations)) {
        echo "   ⚠️  تحذير: علاقات مفقودة: " . implode(', ', $missingRelations) . "\n";
    }
    
    // محاولة قراءة محتوى الملف للتحقق من وجود deleting
    $file = $reflection->getFileName();
    $content = file_get_contents($file);
    
    if (strpos($content, 'static::deleting') !== false) {
        echo "   ✅ نجح: يوجد حماية من الحذف (deleting event)\n";
        $passed++;
    } else {
        echo "   ❌ فشل: لا يوجد حماية من الحذف\n";
        $failed++;
    }
    
    echo "\n";
}

echo "==========================================\n";
echo "📊 ملخص النتائج:\n";
echo "   ✅ نجح: $passed\n";
echo "   ❌ فشل: $failed\n";
echo "   ⚠️  تخطي: $skipped\n";
echo "   📈 إجمالي: " . count($tests) . "\n\n";

if ($failed === 0 && $skipped === 0) {
    echo "🎉 ممتاز! جميع النماذج محمية بشكل صحيح!\n";
} elseif ($failed === 0) {
    echo "✅ جيد! جميع النماذج الموجودة محمية.\n";
} else {
    echo "⚠️  يوجد نماذج تحتاج حماية!\n";
}

echo "\n";
