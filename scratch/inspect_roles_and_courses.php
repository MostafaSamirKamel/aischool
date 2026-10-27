<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ROLES IN DATABASE ===" . PHP_EOL;
$roles = \Spatie\Permission\Models\Role::all();
foreach ($roles as $r) {
    echo "ID: {$r->id} | Name: {$r->name} | Guard: {$r->guard_name} | Panel: " . ($r->panel_type ?? 'N/A') . PHP_EOL;
}

echo PHP_EOL . "=== EXISTING COURSES ===" . PHP_EOL;
$courses = \App\Models\Course::all();
foreach ($courses as $c) {
    echo "ID: {$c->id} | Name: {$c->name} | Price: {$c->price} | Sessions: {$c->total_sessions} | Tenant: {$c->tenant_id}" . PHP_EOL;
}

echo PHP_EOL . "=== EXISTING VAULTS ===" . PHP_EOL;
$vaults = \App\Models\Vault::all();
foreach ($vaults as $v) {
    echo "ID: {$v->id} | Name: {$v->name} | Balance: {$v->balance} | Type: {$v->type} | Tenant: {$v->tenant_id}" . PHP_EOL;
}

echo PHP_EOL . "=== INSTRUCTOR SALARY RULES / FIELDS ===" . PHP_EOL;
$instructorTableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('instructors');
echo "Instructor columns: " . implode(', ', $instructorTableColumns) . PHP_EOL;

if (\App\Models\Instructor::count() > 0) {
    $firstInst = \App\Models\Instructor::first();
    echo "Sample instructor: " . json_encode($firstInst->toArray(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
