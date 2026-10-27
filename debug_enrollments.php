<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$group = App\Models\Group::withoutGlobalScopes()->first();
echo "Group: {$group->name} (ID: {$group->id})\n";

$enrollments = App\Models\Enrollment::withoutGlobalScopes()
    ->where('group_id', $group->id)
    ->with('student')
    ->get();

echo "Enrollments count: " . $enrollments->count() . "\n";

foreach ($enrollments as $enrollment) {
    echo "  - Enrollment ID: {$enrollment->id}, Student ID: {$enrollment->student_id}\n";
    if ($enrollment->student) {
        echo "    Student: {$enrollment->student->full_name}\n";
    } else {
        echo "    Student: NULL\n";
    }
}
