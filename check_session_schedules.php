<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check Groups
$groups = \App\Models\Group::with('instructor.user')->get();
echo "Total Groups: " . $groups->count() . "\n\n";

foreach ($groups as $group) {
    echo "Group ID: {$group->id} - {$group->name}\n";
    echo "Instructor ID: {$group->instructor_id}\n";
    echo "Instructor Name: " . ($group->instructor ? $group->instructor->user->name : 'N/A') . "\n";
    echo "Start Date: {$group->start_date}\n";
    echo "End Date: {$group->end_date}\n";
    echo "---\n";
}

// Check SessionSchedules count
$count = \App\Models\SessionSchedule::count();
echo "\nTotal SessionSchedules: $count\n";
