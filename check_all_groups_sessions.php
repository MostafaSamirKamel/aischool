<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== All Groups in System ===\n\n";

$allGroups = \App\Models\Group::all();

if ($allGroups->isEmpty()) {
    echo "❌ No groups found in the entire system!\n";
} else {
    echo "Total Groups: {$allGroups->count()}\n\n";
    
    foreach ($allGroups as $group) {
        echo "Group ID: {$group->id}\n";
        echo "Group Name: {$group->name}\n";
        echo "Tenant ID: {$group->tenant_id}\n";
        echo "Course ID: {$group->course_id}\n";
        echo "Instructor ID: {$group->instructor_id}\n";
        echo "Start Date: {$group->start_date}\n";
        echo "End Date: {$group->end_date}\n";
        echo "Total Sessions (field): {$group->total_sessions}\n";
        echo "Schedule: " . json_encode($group->schedule) . "\n";
        
        $sessionCount = \App\Models\SessionSchedule::where('group_id', $group->id)->count();
        echo "Actual SessionSchedules: {$sessionCount}\n";
        
        echo "Updated At: {$group->updated_at}\n";
        echo "---\n\n";
    }
}

echo "\n=== All SessionSchedules in System ===\n\n";
$allSessions = \App\Models\SessionSchedule::all();

if ($allSessions->isEmpty()) {
    echo "❌ No session schedules found in the system!\n";
} else {
    echo "Total SessionSchedules: {$allSessions->count()}\n\n";
    
    foreach ($allSessions->take(5) as $session) {
        echo "Session ID: {$session->id}\n";
        echo "Group ID: {$session->group_id}\n";
        echo "Instructor ID: {$session->instructor_id}\n";
        echo "Date: {$session->date}\n";
        echo "Time: {$session->start_time} - {$session->end_time}\n";
        echo "---\n";
    }
}
