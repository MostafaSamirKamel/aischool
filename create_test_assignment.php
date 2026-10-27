<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get instructor, course, and group
$instructor = \App\Models\User::role('instructor')->first();
$course = \App\Models\Course::first();
$group = \App\Models\Group::first();

if (!$instructor || !$course || !$group) {
    echo "Error: Missing required data\n";
    echo "Instructor: " . ($instructor ? "Found" : "Not found") . "\n";
    echo "Course: " . ($course ? "Found" : "Not found") . "\n";
    echo "Group: " . ($group ? "Found" : "Not found") . "\n";
    exit(1);
}

// Create test assignment
$assignment = \App\Models\Assignment::create([
    'title' => 'واجب تجريبي للاختبار',
    'description' => 'هذا واجب تجريبي - يمكنك رفع الملف من هنا',
    'course_id' => $course->id,
    'group_id' => $group->id,
    'instructor_id' => $instructor->id,
    'tenant_id' => $course->tenant_id,
    'due_date' => now()->addDays(7),
    'total_marks' => 100,
]);

echo "✓ Assignment created successfully!\n";
echo "ID: {$assignment->id}\n";
echo "Title: {$assignment->title}\n";
echo "Due Date: {$assignment->due_date->format('Y-m-d')}\n";
echo "\nالآن يمكن للطالب رؤية زر 'رفع التسليم' في صفحة الواجبات\n";
