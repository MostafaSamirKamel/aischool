<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== فحص مشكلة foreign key في جدول groups ===\n\n";

// فحص المجموعات التي لها instructor_id غير موجود في جدول instructors
$orphanedGroups = DB::table('groups')
    ->leftJoin('instructors', 'groups.instructor_id', '=', 'instructors.id')
    ->whereNotNull('groups.instructor_id')
    ->whereNull('instructors.id')
    ->select('groups.id', 'groups.name', 'groups.instructor_id')
    ->get();

echo "المجموعات التي تحتوي على instructor_id غير موجود:\n";
echo "عدد المجموعات المتأثرة: " . $orphanedGroups->count() . "\n\n";

foreach ($orphanedGroups as $group) {
    echo "- Group ID: {$group->id}, Name: {$group->name}, Instructor ID: {$group->instructor_id}\n";
}

if ($orphanedGroups->count() > 0) {
    echo "\n=== إصلاح البيانات ===\n";
    
    // تحديد خيار الإصلاح
    echo "الحلول المتاحة:\n";
    echo "1. وضع null للمجموعات بدلاً من instructor_id غير موجود\n";
    echo "2. البحث عن instructors موجودين وربطهم\n";
    
    // فحص المدرسين الموجودين
    $availableInstructors = DB::table('instructors')
        ->join('users', 'instructors.user_id', '=', 'users.id')
        ->select('instructors.id', 'users.name')
        ->get();
    
    echo "\nالمدرسين المتاحين:\n";
    foreach ($availableInstructors as $instructor) {
        echo "- ID: {$instructor->id}, Name: {$instructor->name}\n";
    }
    
    // إصلاح البيانات - وضع null للمجموعات orphaned
    echo "\n=== تطبيق الإصلاح ===\n";
    
    $affected = DB::table('groups')
        ->leftJoin('instructors', 'groups.instructor_id', '=', 'instructors.id')
        ->whereNotNull('groups.instructor_id')
        ->whereNull('instructors.id')
        ->update(['groups.instructor_id' => null]);
    
    echo "تم تحديث {$affected} مجموعة وإزالة instructor_id غير الصحيح\n";
    
    echo "\nيمكنك الآن تشغيل:\n";
    echo "php artisan migrate\n";
    
} else {
    echo "✅ لا توجد مشاكل في البيانات\n";
    echo "يمكنك تشغيل: php artisan migrate\n";
}
