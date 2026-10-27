<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Creating Instructor Record for User 2 ===\n\n";

$user = \App\Models\User::find(2);

// Check if instructor already exists
if ($user->instructor) {
    echo "✅ Instructor record already exists (ID: {$user->instructor->id})\n";
    $instructor = $user->instructor;
} else {
    // Create instructor record
    $instructor = \App\Models\Instructor::create([
        'tenant_id' => $user->tenant_id,
        'user_id' => $user->id,
        'employee_code' => 'INST-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
        'phone' => '0123456789',
        'specialization' => 'إدارة',
        'hired_date' => now(),
        'status' => 'active',
        'contract_type' => 'full_time',
        'gender' => 'male',
    ]);
    
    echo "✅ Created new instructor record (ID: {$instructor->id})\n";
}

echo "\n=== Creating Test Group ===\n\n";

// Get a course
$course = \App\Models\Course::where('tenant_id', $user->tenant_id)->first();

if (!$course) {
    echo "❌ No courses found! Creating a test course...\n";
    $course = \App\Models\Course::create([
        'tenant_id' => $user->tenant_id,
        'name' => 'دورة تجريبية',
        'description' => 'دورة للاختبار',
        'duration_hours' => 40,
        'status' => 'active',
    ]);
    echo "✅ Created course: {$course->name}\n";
}

// Create group
$group = \App\Models\Group::create([
    'tenant_id' => $user->tenant_id,
    'course_id' => $course->id,
    'instructor_id' => $instructor->id,
    'name' => 'مجموعة تجريبية 1',
    'description' => 'مجموعة للاختبار',
    'start_date' => now()->addDays(7)->format('Y-m-d'),
    'end_date' => now()->addMonths(2)->format('Y-m-d'),
    'max_students' => 30,
    'status' => 'active',
    'schedule' => [
        [
            'day' => 'saturday',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ],
        [
            'day' => 'monday',
            'start_time' => '14:00',
            'end_time' => '16:00',
        ],
    ],
]);

echo "✅ Created group: {$group->name} (ID: {$group->id})\n";
echo "Start Date: {$group->start_date}\n";
echo "End Date: {$group->end_date}\n";
echo "Schedule: " . json_encode($group->schedule) . "\n";

// Wait a moment for observer to run
sleep(1);

// Check if sessions were created
$sessionCount = \App\Models\SessionSchedule::where('group_id', $group->id)->count();
echo "\n=== SessionSchedules Created ===\n";
echo "Total Sessions: {$sessionCount}\n";

if ($sessionCount > 0) {
    $sessions = \App\Models\SessionSchedule::where('group_id', $group->id)
        ->orderBy('date')
        ->take(5)
        ->get();
    
    echo "\nFirst 5 Sessions:\n";
    foreach ($sessions as $session) {
        echo "  - {$session->date->format('Y-m-d')} ({$session->day_of_week}) {$session->start_time}-{$session->end_time}\n";
    }
} else {
    echo "❌ No sessions were created by observer!\n";
    echo "Let me check the group data...\n";
    
    $checkGroup = \App\Models\Group::find($group->id);
    echo "Group Schedule: " . json_encode($checkGroup->schedule) . "\n";
    echo "Group Start Date: {$checkGroup->start_date}\n";
    echo "Group End Date: {$checkGroup->end_date}\n";
    echo "Group Instructor ID: {$checkGroup->instructor_id}\n";
}

echo "\n✅ Done! Now check the instructor panel.\n";
