<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إنشاء بيانات مباشرة عبر SQL ===\n\n";

$user = \App\Models\User::where('email', 'hossammagdy@admin.com')->first();
echo "المدرس: {$user->name}\n";
echo "Instructor ID: {$user->instructor->id}\n";
echo "Tenant ID: {$user->tenant_id}\n\n";

// إنشاء كورس مباشرة
try {
    DB::statement("
        INSERT INTO courses (id, tenant_id, track_id, name, description, duration_months, sessions_count, price, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ", [100, $user->tenant_id, 1, 'كورس حضور مباشر', 'كورس للاختبار', 3, 30, 500, 'active']);
    
    echo "✅ تم إنشاء الكورس بنجاح!\n";
    
    // إنشاء مجموعة مباشرة
    DB::statement("
        INSERT INTO groups (id, tenant_id, course_id, instructor_id, name, capacity, max_students, status, start_date, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ", [100, $user->tenant_id, 100, $user->instructor->id, 'مجموعة حضور مباشرة', 15, 20, 'active', now()->format('Y-m-d')]);
    
    echo "✅ تم إنشاء المجموعة بنجاح!\n\n";
    
    // التحقق
    $course = DB::table('courses')->where('id', 100)->first();
    $group = DB::table('groups')->where('id', 100)->first();
    
    if ($course && $group) {
        echo "=== التحقق من البيانات ===\n";
        echo "الكورس: {$course->name} (ID: {$course->id})\n";
        echo "المجموعة: {$group->name} (ID: {$group->id})\n";
        echo "المدرس في المجموعة: {$group->instructor_id}\n\n";
        
        // اختبار الاستعلام
        echo "=== اختبار الاستعلام ===\n";
        $testGroups = DB::table('groups')
            ->where('instructor_id', $user->instructor->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->get();
        
        echo "عدد المجموعات المعثور عليها: " . $testGroups->count() . "\n";
        foreach ($testGroups as $testGroup) {
            echo "- {$testGroup->name} (ID: {$testGroup->id})\n";
        }
        
        if ($testGroups->count() > 0) {
            echo "\n🎉 المشكلة محلولة! المدرس سيتمكن الآن من رؤية المجموعة في نظام الحضور.\n";
        } else {
            echo "\n❌ لا تزال هناك مشكلة في الاستعلام.\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
