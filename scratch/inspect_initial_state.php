<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ENVIRONMENT & DATABASE ===" . PHP_EOL;
echo "APP_ENV: " . config('app.env') . PHP_EOL;
echo "DB_CONNECTION: " . config('database.default') . PHP_EOL;
echo "DB_DATABASE: " . config('database.connections.sqlite.database') . PHP_EOL;
echo "MAIL_MAILER: " . config('mail.default') . PHP_EOL;

echo PHP_EOL . "=== TENANTS / BRANCHES ===" . PHP_EOL;
$tenants = \App\Models\Tenant::all();
foreach ($tenants as $t) {
    echo "ID: {$t->id} | Name: {$t->name} | Code: " . ($t->code ?? 'N/A') . PHP_EOL;
}

echo PHP_EOL . "=== TARGET ACCOUNTS CHECK ===" . PHP_EOL;
$targetPhonesOrUsernames = [
    '01070801308' => 'Abu El Matamir Manager',
    '01501101213' => 'Online Manager',
    '01091812925' => 'Instructor',
    '01007287273' => 'Student',
];

foreach ($targetPhonesOrUsernames as $identifier => $roleTitle) {
    $user = \App\Models\User::where('username', $identifier)
        ->orWhere('phone', $identifier)
        ->orWhere('email', 'like', "%{$identifier}%")
        ->first();

    if ($user) {
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->join(', ') : 'N/A';
        echo "[FOUND] {$roleTitle} ({$identifier}): ID={$user->id}, Name={$user->name}, Email={$user->email}, Username={$user->username}, TenantID={$user->tenant_id}, Roles=[{$roles}]" . PHP_EOL;
    } else {
        echo "[MISSING] {$roleTitle} ({$identifier}) not found!" . PHP_EOL;
    }
}

echo PHP_EOL . "=== CLASSROOMS (HALLS) ===" . PHP_EOL;
if (class_exists(\App\Models\Classroom::class)) {
    $classrooms = \App\Models\Classroom::all();
    echo "Found " . $classrooms->count() . " classrooms:" . PHP_EOL;
    foreach ($classrooms as $c) {
        echo "ID: {$c->id} | Name: {$c->name} | Capacity: {$c->capacity} | Tenant: {$c->tenant_id}" . PHP_EOL;
    }
} elseif (class_exists(\App\Models\Hall::class)) {
    $halls = \App\Models\Hall::all();
    echo "Found " . $halls->count() . " halls:" . PHP_EOL;
    foreach ($halls as $h) {
        echo "ID: {$h->id} | Name: {$h->name} | Capacity: {$h->capacity} | Tenant: {$h->tenant_id}" . PHP_EOL;
    }
} else {
    echo "Checking tables for classroom / hall..." . PHP_EOL;
}
