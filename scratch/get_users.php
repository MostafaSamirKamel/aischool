<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$instructorUser = \App\Models\User::whereHas('instructor')->first();
$studentUser = \App\Models\User::whereHas('student')->first();
$parentUser = \App\Models\User::whereHas('students')->first();
$adminUser = \App\Models\User::where('email', 'admin@admin.com')->first();

echo "INSTRUCTOR: " . ($instructorUser ? $instructorUser->email : 'NONE') . "\n";
echo "STUDENT: " . ($studentUser ? $studentUser->email : 'NONE') . "\n";
echo "PARENT: " . ($parentUser ? $parentUser->email : 'NONE') . "\n";
echo "ADMIN: " . ($adminUser ? $adminUser->email : 'NONE') . "\n";
