<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إنشاء مجموعة واختبار النظام ===\n\n";

// جلب المدرس
$user = \App\Models\User::where('email', 'hossammagdy@admin.com')->first();
echo "المدرس: {$user->name} (User ID: {$user->id})\n";
echo "Instructor ID: {$user->instructor->id}\n";
echo "Tenant ID: {$user->tenant_id}\n\n";

// جلب أو إنشاء كورس
$course = \App\Models\Course::firstOrCreate(
    ['tenant_id' => $user->tenant_id, 'name' => 'كورس اختبار الحضور'],
    [
        'code' => 'ATT-TEST-001',
        'description' => 'كورس للاختبار',
        'duration_hours' => 30,
        'price' => 500,
        'is_active' => true
    ]
);

echo "الكورس: {$course->name} (ID: {$course->id})\n\n";

// إنشاء مجموعة
try {
    $group = \App\Models\Group::create([
        'tenant_id' => $user->tenant_id,
        'course_id' => $course->id,
        'instructor_id' => $user->instructor->id,
        'name' => 'مجموعة حضور - ' . date('Y-m-d H:i'),
        'capacity' => 20,
        'max_students' => 25,
        'status' => 'active',
        'start_date' => now()->format('Y-m-d'),
        'notes' => 'مجموعة اختبار للحضور'
    ]);
    
    echo "✅ تم إنشاء المجموعة بنجاح!\n";
    echo "ID: {$group->id}\n";
    echo "الاسم: {$group->name}\n\n";
    
    // إنشاء طلاب
    for ($i = 1; $i <= 3; $i++) {
        $studentUser = \App\Models\User::create([
            'name' => "طالب الحضور {$i} - " . date('H:i:s'),
            'username' => "attendance_student_{$i}_" . time() . rand(100, 999),
            'email' => "attendance_student_{$i}_" . time() . rand(100, 999) . "@test.com",
            'password' => bcrypt('password'),
            'tenant_id' => $user->tenant_id,
            'status' => 'active'
        ]);
        
        $studentUser->assignRole('student');
        
        $student = \App\Models\Student::create([
            'user_id' => $studentUser->id,
            'tenant_id' => $user->tenant_id,
            'student_code' => 'ATT' . str_pad($studentUser->id, 4, '0', STR_PAD_LEFT),
            'date_of_birth' => now()->subYears(18)->format('Y-m-d'),
            'gender' => 'male',
            'status' => 'active'
        ]);
        
        \App\Models\Enrollment::create([
            'tenant_id' => $user->tenant_id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-' . $student->id . '-' . $group->id,
            'enrollment_date' => now()->format('Y-m-d'),
            'status' => 'active',
            'total_amount' => 500.00
        ]);
        
        echo "✅ تم إنشاء الطالب {$i}: {$studentUser->name}\n";
    }
    
    echo "\n=== التحقق من البيانات ===\n";
    
    // التحقق من أن المجموعة تظهر في الاستعلام
    $testGroups = \App\Models\Group::where('instructor_id', $user->instructor->id)
        ->where('tenant_id', $user->tenant_id)
        ->where('status', 'active')
        ->get();
    
    echo "عدد المجموعات التي يمكن للمدرس رؤيتها: {$testGroups->count()}\n";
    foreach ($testGroups as $testGroup) {
        echo "- {$testGroup->name} (ID: {$testGroup->id})\n";
    }
    
    echo "\n✅ النظام جاهز للاختبار!\n";
    echo "يمكن للمدرس الآن تسجيل الدخول ورؤية المجموعة في قسم الحضور.\n";
    
} catch (\Exception $e) {
    echo "❌ خطأ في إنشاء المجموعة: " . $e->getMessage() . "\n";
}
