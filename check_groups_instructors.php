<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;
use App\Models\Instructor;

echo "=== فحص المجموعات والمدرسين ===\n\n";

// جلب جميع المجموعات (بدون tenant scope)
$groups = Group::withoutGlobalScopes()->with(['instructor.user', 'course'])->get();

echo "عدد المجموعات: " . $groups->count() . "\n\n";

foreach ($groups as $group) {
    echo "المجموعة: {$group->name}\n";
    echo "الكورس: " . ($group->course?->name ?? 'غير محدد') . "\n";
    echo "instructor_id: " . ($group->instructor_id ?? 'NULL') . "\n";
    
    if ($group->instructor_id) {
        echo "المدرس (instructor): " . ($group->instructor?->user?->name ?? 'غير موجود') . "\n";
        echo "المدرس (full_name): " . ($group->instructor?->full_name ?? 'غير موجود') . "\n";
    } else {
        echo "المدرس: لا يوجد مدرس مربوط\n";
    }
    
    echo "------------------------\n";
}

echo "\n=== جميع المدرسين المتاحين ===\n\n";
$instructors = Instructor::withoutGlobalScopes()->with('user')->get();
echo "عدد المدرسين: " . $instructors->count() . "\n\n";

foreach ($instructors as $instructor) {
    echo "ID: {$instructor->id}\n";
    echo "الاسم: " . ($instructor->user?->name ?? 'بدون اسم') . "\n";
    echo "الكود: {$instructor->employee_code}\n";
    echo "الحالة: {$instructor->status}\n";
    echo "------------------------\n";
}