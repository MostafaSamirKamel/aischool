<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== All Instructors in System ===\n\n";

$instructors = \App\Models\Instructor::with('user')->get();

if ($instructors->isEmpty()) {
    echo "❌ No instructors found in the system!\n";
    exit;
}

foreach ($instructors as $instructor) {
    echo "Instructor ID: {$instructor->id}\n";
    echo "User ID: {$instructor->user_id}\n";
    echo "User Name: {$instructor->user->name}\n";
    echo "Email: {$instructor->user->email}\n";
    echo "Tenant ID: {$instructor->user->tenant_id}\n";
    
    $groupCount = \App\Models\Group::where('instructor_id', $instructor->id)->count();
    $sessionCount = \App\Models\SessionSchedule::where('instructor_id', $instructor->id)->count();
    
    echo "Groups: {$groupCount}\n";
    echo "Sessions: {$sessionCount}\n";
    echo "---\n\n";
}

// Check which user is currently logged in Filament as instructor
echo "\n=== Users with Instructor Role ===\n\n";

$instructorUsers = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'instructor');
})->get();

foreach ($instructorUsers as $user) {
    $hasInstructorRecord = $user->instructor ? '✅ Has Instructor Record' : '❌ No Instructor Record';
    echo "User ID: {$user->id} - {$user->name} - {$hasInstructorRecord}\n";
}
