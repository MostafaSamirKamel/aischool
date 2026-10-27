<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Course;
use App\Models\Group;

echo "=== فحص الكورسات والمجموعات ===\n\n";

// جلب جميع الكورسات
$courses = Course::withoutGlobalScopes()->get();
echo "عدد الكورسات: " . $courses->count() . "\n\n";

foreach ($courses as $course) {
    echo "الكورس: {$course->name}\n";
    echo "ID: {$course->id}\n";
    echo "السعر: {$course->price}\n";
    echo "الحالة: {$course->status}\n";
    
    // جلب المجموعات المرتبطة بهذا الكورس
    $groups = Group::withoutGlobalScopes()->where('course_id', $course->id)->get();
    echo "عدد المجموعات: " . $groups->count() . "\n";
    
    if ($groups->count() > 0) {
        foreach ($groups as $group) {
            echo "  - {$group->name} (ID: {$group->id}, الحالة: {$group->status})\n";
        }
    }
    
    echo "------------------------\n";
}

echo "\n=== جميع المجموعات ===\n\n";
$allGroups = Group::withoutGlobalScopes()->with('course')->get();
echo "عدد المجموعات الكلي: " . $allGroups->count() . "\n\n";

foreach ($allGroups as $group) {
    echo "المجموعة: {$group->name}\n";
    echo "ID: {$group->id}\n";
    echo "course_id: " . ($group->course_id ?? 'NULL') . "\n";
    echo "الكورس: " . ($group->course?->name ?? 'غير مربوط') . "\n";
    echo "الحالة: {$group->status}\n";
    echo "tenant_id: {$group->tenant_id}\n";
    echo "------------------------\n";
}
