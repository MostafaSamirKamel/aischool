<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== تشخيص مشكلة المجموعات ===\n\n";

$user = \App\Models\User::where('email', 'hossammagdy@admin.com')->first();
echo "المدرس: {$user->name}\n";
echo "User ID: {$user->id}\n";
echo "Instructor ID: {$user->instructor->id}\n";
echo "Tenant ID: {$user->tenant_id}\n\n";

// فحص المجموعة الأخيرة
$latestGroup = \App\Models\Group::latest()->first();
if ($latestGroup) {
    echo "آخر مجموعة:\n";
    echo "- ID: {$latestGroup->id}\n";
    echo "- الاسم: {$latestGroup->name}\n";
    echo "- Instructor ID: {$latestGroup->instructor_id}\n";
    echo "- Tenant ID: {$latestGroup->tenant_id}\n";
    echo "- Status: {$latestGroup->status}\n\n";
}

// البحث بنفس منطق الكود المصحح
$instructorId = $user->instructor?->id;
echo "البحث بـ instructor_id = {$instructorId}\n\n";

$testQuery = \App\Models\Group::where('instructor_id', $instructorId)
    ->where('tenant_id', $user->tenant_id)
    ->where('status', 'active')
    ->get();

echo "النتائج: {$testQuery->count()} مجموعة\n";
foreach ($testQuery as $group) {
    echo "- {$group->name} (ID: {$group->id})\n";
}

// فحص شامل
echo "\nفحص شامل لجميع المجموعات:\n";
$allGroups = \App\Models\Group::where('tenant_id', $user->tenant_id)->get();
echo "إجمالي المجموعات في الفرع: {$allGroups->count()}\n";
foreach ($allGroups as $group) {
    echo "- {$group->name} (ID: {$group->id}, Instructor: {$group->instructor_id}, Status: {$group->status})\n";
}
