<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "عدد المجموعات: " . \App\Models\Group::count() . PHP_EOL;
echo "عدد الكورسات: " . \App\Models\Course::count() . PHP_EOL;

// إنشاء مجموعة بسيطة للاختبار
$user = \App\Models\User::where('email', 'hossammagdy@admin.com')->first();
$course = \App\Models\Course::where('tenant_id', $user->tenant_id)->first();

if ($course) {
    try {
        echo "إنشاء مجموعة اختبار...\n";
        
        $group = new \App\Models\Group();
        $group->tenant_id = $user->tenant_id;
        $group->course_id = $course->id;
        $group->instructor_id = $user->instructor->id;
        $group->name = 'اختبار مباشر - ' . date('H:i:s');
        $group->capacity = 15;
        $group->max_students = 20;
        $group->status = 'active';
        $group->start_date = now();
        $group->save();
        
        echo "✅ تم إنشاء المجموعة بنجاح! ID: {$group->id}\n";
        
        // التحقق فوراً
        $check = \App\Models\Group::find($group->id);
        if ($check) {
            echo "✅ المجموعة موجودة في قاعدة البيانات\n";
        } else {
            echo "❌ المجموعة غير موجودة\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ خطأ: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ لا يوجد كورس\n";
}
