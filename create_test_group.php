<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إنشاء مجموعة اختبار للمدرس حسام مجدي ===\n\n";

// جلب المدرس حسام مجدي
$user = \App\Models\User::where('email', 'hossammagdy@admin.com')->first();
if (!$user || !$user->instructor) {
    echo "❌ لا يمكن العثور على المدرس أو سجل المدرس.\n";
    exit;
}

echo "المدرس: {$user->name}\n";
echo "Instructor ID: {$user->instructor->id}\n";
echo "Tenant ID: {$user->tenant_id}\n\n";

// البحث عن كورس - إنشاء واحد إذا لم يكن موجوداً
$course = \App\Models\Course::where('tenant_id', $user->tenant_id)->first();
if (!$course) {
    $course = \App\Models\Course::create([
        'tenant_id' => $user->tenant_id,
        'name' => 'كورس اختبار للحضور',
        'code' => 'TEST-ATTENDANCE-001',
        'description' => 'كورس للاختبار',
        'duration_hours' => 30,
        'price' => 500,
        'is_active' => true
    ]);
    echo "✅ تم إنشاء كورس جديد: {$course->name}\n";
}

echo "الكورس المستخدم: {$course->name}\n\n";

// إنشاء مجموعة
$group = \App\Models\Group::create([
    'tenant_id' => $user->tenant_id,
    'course_id' => $course->id,
    'instructor_id' => $user->instructor->id, // استخدام instructor ID وليس user ID
    'name' => 'مجموعة اختبار - ' . $user->name,
    'capacity' => 20,
    'max_students' => 25,
    'status' => 'active',
    'start_date' => now(),
    'notes' => 'مجموعة تم إنشاؤها لاختبار النظام'
]);

echo "✅ تم إنشاء مجموعة بنجاح!\n";
echo "ID: {$group->id}\n";
echo "الاسم: {$group->name}\n";
echo "الحالة: {$group->status}\n";
echo "المدرس ID: {$group->instructor_id}\n\n";

echo "الآن يمكن للمدرس رؤية هذه المجموعة في نظام الحضور.\n";
