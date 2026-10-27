<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = \Spatie\Permission\Models\Role::all();
foreach ($roles as $r) {
    echo "Role: {$r->name} | Guard: {$r->guard_name} | Panel: {$r->panel_type}" . PHP_EOL;
}
