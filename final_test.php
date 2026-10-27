<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إنشاء كورس ومجموعة للاختبار ===\n\n";

$user = \App\Models\User::where('email', 'hossammagdy@admin.com')->first();
echo "المدرس: {$user->name}\n";
echo "Instructor ID: {$user->instructor->id}\n";
echo "Tenant ID: {$user->tenant_id}\n\n";

// إنشاء كورس
try {
    echo "إنشاء كورس...\n";
    $course = new \App\Models\Course();
    $course->tenant_id = $user->tenant_id;
    $course->track_id = 1; // افتراض وجود track
    $course->name = 'كورس الحضور الاختباري';
    $course->description = 'كورس للاختبار';
    $course->duration_months = 3;
    $course->sessions_count = 30;
    $course->price = 500;
    $course->status = 'active';
    $course->save();
    
    echo "✅ تم إنشاء الكورس! ID: {$course->id}\n\n";
    
    // إنشاء مجموعة
    echo "إنشاء مجموعة...\n";
    $group = new \App\Models\Group();
    $group->tenant_id = $user->tenant_id;
    $group->course_id = $course->id;
    $group->instructor_id = $user->instructor->id;
    $group->name = 'مجموعة حضور تجريبية';
    $group->capacity = 15;
    $group->max_students = 20;
    $group->status = 'active';
    $group->start_date = now();
    $group->save();
    
    echo "✅ تم إنشاء المجموعة! ID: {$group->id}\n\n";
    
    // التحقق من الاستعلام
    echo "=== اختبار الاستعلام ===\n";
    $instructorId = $user->instructor->id;
    $testGroups = \App\Models\Group::where('instructor_id', $instructorId)
        ->where('tenant_id', $user->tenant_id)
        ->where('status', 'active')
        ->get();
    
    echo "عدد المجموعات المعثور عليها: {$testGroups->count()}\n";
    foreach ($testGroups as $testGroup) {
        echo "- {$testGroup->name} (ID: {$testGroup->id})\n";
    }
    
    if ($testGroups->count() > 0) {
        echo "\n✅ المشكلة محلولة! المدرس سيتمكن من رؤية المجموعة في نظام الحضور.\n";
    } else {
        echo "\n❌ لا تزال هناك مشكلة في الاستعلام.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "تفاصيل: " . $e->getTraceAsString() . "\n";
}
