<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentUser = App\Models\User::firstOrCreate(
    ['email' => 'student@aischool.test'],
    [
        'name' => 'طالب برمجيات تجريبي موحد',
        'password' => Illuminate\Support\Facades\Hash::make('password'),
        'tenant_id' => 1,
        'status' => 'active',
    ]
);
if (!$studentUser->hasRole('student')) {
    $studentUser->assignRole('student');
}

$firstStudent = App\Models\Student::first();
if ($firstStudent) {
    $firstStudent->update(['user_id' => $studentUser->id]);
}

$parentUser = App\Models\User::firstOrCreate(
    ['email' => 'parent@aischool.test'],
    [
        'name' => 'ولي أمر تجريبي موحد',
        'password' => Illuminate\Support\Facades\Hash::make('password'),
        'tenant_id' => 1,
        'status' => 'active',
    ]
);
if (!$parentUser->hasRole('parent')) {
    $parentUser->assignRole('parent');
}

$firstParent = App\Models\StudentParent::first();
if ($firstParent) {
    $firstParent->update(['user_id' => $parentUser->id]);
}

echo "SUCCESS: Friendly demo accounts configured!" . PHP_EOL;
echo "Student: student@aischool.test (password: password)" . PHP_EOL;
echo "Parent: parent@aischool.test (password: password)" . PHP_EOL;
