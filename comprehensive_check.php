<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== تحقق شامل من قاعدة البيانات ===\n\n";

$lastGroup = \App\Models\Group::latest()->first();
if ($lastGroup) {
    echo "آخر مجموعة محفوظة:\n";
    echo "- ID: {$lastGroup->id}\n";
    echo "- الاسم: {$lastGroup->name}\n";
    echo "- Instructor ID: {$lastGroup->instructor_id}\n";
    echo "- Tenant ID: {$lastGroup->tenant_id}\n";
    echo "- Status: {$lastGroup->status}\n";
    echo "- تاريخ الإنشاء: {$lastGroup->created_at}\n\n";
} else {
    echo "❌ لا توجد مجموعات في قاعدة البيانات!\n";
}

echo "إجمالي عدد المجموعات: " . \App\Models\Group::count() . "\n";
echo "إجمالي عدد الكورسات: " . \App\Models\Course::count() . "\n";

$lastCourse = \App\Models\Course::latest()->first();
if ($lastCourse) {
    echo "\nآخر كورس محفوظ:\n";
    echo "- ID: {$lastCourse->id}\n";
    echo "- الاسم: {$lastCourse->name}\n";
    echo "- Tenant ID: {$lastCourse->tenant_id}\n";
    echo "- Track ID: {$lastCourse->track_id}\n";
}

// إنشاء مجموعة جديدة مع فحص أكثر تفصيلاً
echo "\n=== محاولة إنشاء مجموعة مع فحص مفصل ===\n";
$user = \App\Models\User::where('email', 'hossammagdy@admin.com')->first();
$course = \App\Models\Course::latest()->first();

if ($course && $user && $user->instructor) {
    try {
        DB::beginTransaction();
        
        echo "إنشاء مجموعة جديدة...\n";
        $group = new \App\Models\Group();
        $group->fill([
            'tenant_id' => $user->tenant_id,
            'course_id' => $course->id,
            'instructor_id' => $user->instructor->id,
            'name' => 'مجموعة تشخيص - ' . now()->format('H:i:s'),
            'capacity' => 15,
            'max_students' => 20,
            'status' => 'active',
            'start_date' => now()
        ]);
        
        echo "حفظ المجموعة...\n";
        $result = $group->save();
        echo "نتيجة الحفظ: " . ($result ? 'نجح' : 'فشل') . "\n";
        
        if ($result) {
            echo "ID المجموعة الجديدة: {$group->id}\n";
            DB::commit();
            
            // التحقق الفوري
            $checkGroup = \App\Models\Group::find($group->id);
            echo "التحقق: " . ($checkGroup ? 'المجموعة موجودة' : 'المجموعة غير موجودة') . "\n";
        } else {
            DB::rollBack();
            echo "❌ فشل حفظ المجموعة\n";
        }
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "❌ خطأ: " . $e->getMessage() . "\n";
        echo "الملف: " . $e->getFile() . " في السطر " . $e->getLine() . "\n";
    }
} else {
    echo "❌ البيانات المطلوبة غير مكتملة\n";
    echo "Course: " . ($course ? '✅' : '❌') . "\n";
    echo "User: " . ($user ? '✅' : '❌') . "\n";
    echo "Instructor: " . ($user && $user->instructor ? '✅' : '❌') . "\n";
}
