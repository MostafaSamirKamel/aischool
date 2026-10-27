<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إنشاء طلاب للمجموعة ===\n\n";

$groupId = 100;
$tenantId = 1;

// إنشاء 3 طلاب
for ($i = 1; $i <= 3; $i++) {
    $userId = 2000 + $i;
    
    // إنشاء مستخدم
    DB::statement("
        INSERT INTO users (id, name, username, email, password, tenant_id, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ", [$userId, "طالب حضور مباشر {$i}", "att_direct_{$i}_" . time(), "att_direct_{$i}_" . time() . "@test.com", bcrypt('password'), $tenantId, 'active']);
    
    // إنشاء سجل طالب
    $studentId = 2000 + $i;
    DB::statement("
        INSERT INTO students (id, user_id, tenant_id, student_code, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ", [$studentId, $userId, $tenantId, "STU{$studentId}", 'active']);
    
    // تسجيل في المجموعة
    $enrollmentId = 2000 + $i;
    DB::statement("
        INSERT INTO enrollments (id, tenant_id, student_id, group_id, enrollment_code, enrollment_date, status, total_amount, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ", [$enrollmentId, $tenantId, $studentId, $groupId, "ENR-{$studentId}-{$groupId}", now()->format('Y-m-d'), 'active', 500.00]);
    
    // إعطاء دور طالب
    DB::statement("
        INSERT INTO model_has_roles (role_id, model_type, model_id) 
        VALUES (?, ?, ?)
    ", [3, 'App\\Models\\User', $userId]); // افتراض أن role_id للطلاب هو 3
    
    echo "✅ تم إنشاء الطالب {$i}: طالب حضور {$i}\n";
}

echo "\n✅ تم إنشاء 3 طلاب وتسجيلهم في المجموعة بنجاح!\n";

// التحقق
$students = DB::table('enrollments')
    ->join('students', 'enrollments.student_id', '=', 'students.id')
    ->join('users', 'students.user_id', '=', 'users.id')
    ->where('enrollments.group_id', $groupId)
    ->select('users.name', 'students.student_code')
    ->get();

echo "\nالطلاب المسجلين في المجموعة:\n";
foreach ($students as $student) {
    echo "- {$student->name} ({$student->student_code})\n";
}

echo "\n🎉 النظام جاهز تماماً للاختبار!\n";
echo "يمكن للمدرس الآن:\n";
echo "1. تسجيل الدخول بالإيميل: hossammagdy@admin.com\n";
echo "2. الذهاب لقسم الحضور\n";
echo "3. إنشاء جلسة حضور جديدة\n";
echo "4. اختيار 'مجموعة حضور مباشرة' من القائمة\n";
echo "5. تسجيل حضور الطلاب الثلاثة\n";
