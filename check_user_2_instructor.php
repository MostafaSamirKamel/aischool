<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check User ID 2
$user = \App\Models\User::find(2);

echo "=== User ID 2 Information ===\n";
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Tenant ID: {$user->tenant_id}\n";
echo "Roles: " . $user->roles->pluck('name')->join(', ') . "\n";

echo "\n=== Checking Instructor Record ===\n";
$instructor = $user->instructor;

if (!$instructor) {
    echo "❌ This user does NOT have an instructor record\n";
    echo "\n💡 To fix this, we need to create an instructor record for this user\n";
    echo "Would you like me to create one? (This will allow session schedules to work)\n";
} else {
    echo "✅ Instructor ID: {$instructor->id}\n";
}

echo "\n=== Checking Groups in System ===\n";
$allGroups = \App\Models\Group::where('tenant_id', $user->tenant_id)->get();

echo "Total Groups in tenant: {$allGroups->count()}\n\n";

foreach ($allGroups as $group) {
    echo "Group ID: {$group->id}\n";
    echo "Group Name: {$group->name}\n";
    echo "Course: {$group->course->name}\n";
    echo "Instructor ID: {$group->instructor_id}\n";
    
    $instructorUser = \App\Models\Instructor::find($group->instructor_id)?->user;
    echo "Instructor Name: " . ($instructorUser ? $instructorUser->name : 'N/A') . "\n";
    
    $sessionCount = \App\Models\SessionSchedule::where('group_id', $group->id)->count();
    echo "SessionSchedules: {$sessionCount}\n";
    echo "Schedule Data: " . json_encode($group->schedule) . "\n";
    echo "---\n\n";
}
