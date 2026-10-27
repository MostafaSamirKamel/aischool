<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Enrollments: " . App\Models\Enrollment::withoutGlobalScopes()->count() . PHP_EOL;
echo "Students: " . App\Models\Student::withoutGlobalScopes()->count() . PHP_EOL;
echo "Groups: " . App\Models\Group::withoutGlobalScopes()->count() . PHP_EOL;

$group = App\Models\Group::withoutGlobalScopes()->first();
if ($group) {
    echo "\nFirst Group: " . $group->name . " (ID: {$group->id})" . PHP_EOL;
    $enrollments = App\Models\Enrollment::withoutGlobalScopes()
        ->where('group_id', $group->id)
        ->count();
    echo "Enrollments in this group: {$enrollments}" . PHP_EOL;
}
