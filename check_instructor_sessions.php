<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check instructor (User ID 2 - Ahmad Ramadan)
$user = \App\Models\User::find(2);

echo "=== Instructor Information ===\n";
echo "User ID: {$user->id}\n";
echo "User Name: {$user->name}\n";
echo "Tenant ID: {$user->tenant_id}\n";

$instructor = $user->instructor;

if (!$instructor) {
    echo "❌ No instructor record found!\n";
    exit;
}

echo "Instructor ID: {$instructor->id}\n\n";

// Check groups
echo "=== Groups Information ===\n";
$groups = \App\Models\Group::where('instructor_id', $instructor->id)
    ->where('tenant_id', $user->tenant_id)
    ->get();

echo "Total Groups: {$groups->count()}\n\n";

foreach ($groups as $group) {
    echo "Group ID: {$group->id}\n";
    echo "Group Name: {$group->name}\n";
    echo "Start Date: {$group->start_date}\n";
    echo "End Date: {$group->end_date}\n";
    echo "Schedule: " . json_encode($group->schedule) . "\n";
    echo "Total Sessions (field): {$group->total_sessions}\n";
    
    // Count actual session schedules
    $sessionCount = \App\Models\SessionSchedule::where('group_id', $group->id)->count();
    echo "Actual SessionSchedules in DB: {$sessionCount}\n";
    echo "---\n\n";
}

// Check total sessions for instructor
echo "=== Total SessionSchedules for Instructor ===\n";
$totalSessions = \App\Models\SessionSchedule::where('instructor_id', $instructor->id)
    ->where('tenant_id', $user->tenant_id)
    ->count();

echo "Total SessionSchedules in DB: {$totalSessions}\n";

// Check if there are any sessions at all
$allSessions = \App\Models\SessionSchedule::where('tenant_id', $user->tenant_id)->count();
echo "Total SessionSchedules in tenant: {$allSessions}\n";