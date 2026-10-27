<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== التحقق من مشكلة المعلمين ===\n\n";

// جلب جميع المستخدمين الذين لديهم دور instructor
$usersWithInstructorRole = \App\Models\User::role('instructor')->get();

echo "عدد المستخدمين الذين لديهم دور instructor: " . $usersWithInstructorRole->count() . "\n\n";

foreach ($usersWithInstructorRole as $user) {
    echo "المستخدم: {$user->name} (ID: {$user->id})\n";
    echo "  - البريد: {$user->email}\n";
    echo "  - لديه سجل instructor: " . ($user->instructor ? "نعم (ID: {$user->instructor->id})" : "لا ❌") . "\n";
    
    if (!$user->instructor) {
        echo "  ⚠️ المستخدم لديه دور instructor ولكن بدون سجل في جدول instructors!\n";
        echo "  💡 الحل: إنشاء سجل instructor لهذا المستخدم\n";
    }
    echo "\n";
}

echo "\n=== التحقق من سجلات Instructors بدون مستخدمين ===\n\n";

$instructors = \App\Models\Instructor::with('user')->get();
echo "عدد سجلات Instructors: " . $instructors->count() . "\n\n";

foreach ($instructors as $instructor) {
    if (!$instructor->user) {
        echo "⚠️ Instructor ID {$instructor->id} ليس له user مرتبط!\n";
    }
}

echo "\nانتهى الفحص.\n";
