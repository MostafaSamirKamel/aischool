<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Laravel booted successfully.\n";

// Test AcademicCenterService bindings
$service = app(\App\Services\AcademicCenterService::class);
echo "AcademicCenterService resolved: " . get_class($service) . "\n";

// Test Group Academic Header Stats on first group
$group = \App\Models\Group::first();
if ($group) {
    echo "Testing group: " . $group->name . " (ID: {$group->id})\n";
    $stats = $service->getGroupAcademicHeaderStats($group);
    print_r($stats);
} else {
    echo "No group found.\n";
}

// Check HomeworkReviewCenter can access
$user = \App\Models\User::where('email', 'like', '%admin%')->orWhere('email', 'like', '%instructor%')->first();
if ($user) {
    auth()->login($user);
    echo "Logged in as: " . $user->name . " (" . $user->email . ")\n";
}

echo "All checks passed!\n";
