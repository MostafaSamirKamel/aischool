<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;

$credentials = [
    'email' => '01070801308',
    'password' => 'password',
];

if (Auth::guard('web')->attempt($credentials)) {
    $user = Auth::guard('web')->user();
    echo "SUCCESS: Logged in as {$user->name} (ID: {$user->id}), Tenant: {$user->tenant_id}, Roles: " . $user->getRoleNames()->join(', ') . PHP_EOL;
} else {
    echo "FAILED: Could not authenticate with credentials" . PHP_EOL;
}

$instructorCreds = [
    'email' => '01091812925',
    'password' => 'password',
];

if (Auth::guard('web')->attempt($instructorCreds)) {
    $inst = Auth::guard('web')->user();
    echo "SUCCESS: Logged in as {$inst->name} (ID: {$inst->id}), InstructorID: " . ($inst->instructor->id ?? 'None') . PHP_EOL;
} else {
    echo "FAILED: Could not authenticate instructor" . PHP_EOL;
}

$studentCreds = [
    'email' => '01007287273',
    'password' => 'password',
];

if (Auth::guard('web')->attempt($studentCreds)) {
    $std = Auth::guard('web')->user();
    echo "SUCCESS: Logged in as {$std->name} (ID: {$std->id}), StudentID: " . ($std->student->id ?? 'None') . PHP_EOL;
} else {
    echo "FAILED: Could not authenticate student" . PHP_EOL;
}
