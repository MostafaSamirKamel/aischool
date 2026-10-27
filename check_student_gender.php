<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;

echo "=== فحص حقل النوع (gender) في الطلاب ===\n\n";

// جلب جميع الطلاب
$students = Student::withoutGlobalScopes()->with('user')->get();

echo "عدد الطلاب: " . $students->count() . "\n\n";

$genderStats = [
    'male' => 0,
    'female' => 0,
    'null' => 0,
    'other' => 0
];

foreach ($students as $student) {
    $genderValue = $student->gender;
    
    if ($genderValue === 'male') {
        $genderStats['male']++;
    } elseif ($genderValue === 'female') {
        $genderStats['female']++;
    } elseif (is_null($genderValue)) {
        $genderStats['null']++;
        echo "⚠️ الطالب: " . ($student->user?->name ?? 'بدون اسم') . " (ID: {$student->id}) - النوع: NULL\n";
    } else {
        $genderStats['other']++;
        echo "⚠️ الطالب: " . ($student->user?->name ?? 'بدون اسم') . " (ID: {$student->id}) - النوع: '{$genderValue}'\n";
    }
}

echo "\n=== إحصائيات ===\n";
echo "ذكور (male): {$genderStats['male']}\n";
echo "إناث (female): {$genderStats['female']}\n";
echo "NULL: {$genderStats['null']}\n";
echo "قيم أخرى: {$genderStats['other']}\n";

if ($genderStats['null'] > 0 || $genderStats['other'] > 0) {
    echo "\n⚠️ يوجد طلاب بحقول نوع غير صحيحة\n";
}
