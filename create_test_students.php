<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إنشاء طلاب للمجموعة ===\n\n";

// جلب المجموعة المُنشأة
$group = \App\Models\Group::find(19);
if (!$group) {
    echo "❌ لا يمكن العثور على المجموعة.\n";
    exit;
}

echo "المجموعة: {$group->name}\n";
echo "الكورس: {$group->course->name}\n\n";

// إنشاء 3 طلاب للاختبار
for ($i = 1; $i <= 3; $i++) {
    // إنشاء مستخدم
    $user = \App\Models\User::create([
        'name' => "طالب اختبار {$i}",
        'username' => "test_student_{$i}",
        'email' => "test_student_{$i}@test.com",
        'password' => bcrypt('password'),
        'tenant_id' => $group->tenant_id,
        'status' => 'active'
    ]);
    
    // إعطاء دور طالب
    $user->assignRole('student');
    
    // إنشاء سجل طالب
    $student = \App\Models\Student::create([
        'user_id' => $user->id,
        'tenant_id' => $group->tenant_id,
        'student_code' => 'STD' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
        'date_of_birth' => now()->subYears(18),
        'gender' => 'male',
        'status' => 'active'
    ]);
    
    // تسجيل الطالب في المجموعة
    \App\Models\Enrollment::create([
        'tenant_id' => $group->tenant_id,
        'student_id' => $student->id,
        'group_id' => $group->id,
        'enrollment_date' => now(),
        'status' => 'active'
    ]);
    
    echo "✅ تم إنشاء الطالب {$i}: {$user->name} (ID: {$student->id})\n";
}

echo "\n✅ تم إنشاء 3 طلاب وتسجيلهم في المجموعة بنجاح!\n";
echo "الآن يمكن للمدرس تسجيل حضور هؤلاء الطلاب.\n";
