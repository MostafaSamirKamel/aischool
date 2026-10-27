<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$courses = \App\Models\Course::where('tenant_id', 1)->get();
echo 'عدد الكورسات في الفرع 1: ' . $courses->count() . PHP_EOL;
if ($courses->count() > 0) {
    foreach ($courses->take(5) as $course) {
        echo '- ' . $course->name . ' (ID: ' . $course->id . ')' . PHP_EOL;
    }
} else {
    // إنشاء كورس للاختبار
    $course = \App\Models\Course::create([
        'tenant_id' => 1,
        'name' => 'كورس اختبار',
        'code' => 'TEST-001',
        'description' => 'كورس للاختبار',
        'duration_hours' => 30,
        'price' => 500,
        'is_active' => true
    ]);
    
    echo '✅ تم إنشاء كورس اختبار: ' . $course->name . ' (ID: ' . $course->id . ')' . PHP_EOL;
}
