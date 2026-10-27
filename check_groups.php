<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== المجموعات ومدرسيها ===\n\n";

$groups = \App\Models\Group::with(['instructor.user', 'course'])->get();

foreach ($groups as $group) {
    echo "Group: {$group->name} (ID: {$group->id})\n";
    echo "  Course: {$group->course->name}\n";
    echo "  Instructor ID in groups table: {$group->instructor_id}\n";
    
    if ($group->instructor) {
        echo "  Instructor Name: {$group->instructor->user->name}\n";
        echo "  Instructor User ID: {$group->instructor->user_id}\n";
    } else {
        echo "  No instructor assigned\n";
    }
    echo "\n";
}

// فحص مدرس معين
echo "=== فحص المدرس حسام مجدي ===\n";
$user = \App\Models\User::where('email', 'hossammagdy@admin.com')->first();
if ($user) {
    echo "User ID: {$user->id}\n";
    echo "Has Instructor record: " . ($user->instructor ? "Yes (ID: {$user->instructor->id})" : "No") . "\n";
    
    if ($user->instructor) {
        $instructorGroups = \App\Models\Group::where('instructor_id', $user->instructor->id)->get();
        echo "Groups assigned to instructor: " . $instructorGroups->count() . "\n";
        foreach ($instructorGroups as $group) {
            echo "  - {$group->name}\n";
        }
    }
    
    // فحص المجموعات باستخدام User ID (الطريقة القديمة)
    $oldWayGroups = \App\Models\Group::where('instructor_id', $user->id)->get();
    echo "Groups using old way (user_id): " . $oldWayGroups->count() . "\n";
}
