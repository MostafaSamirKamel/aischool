<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$perms = \Spatie\Permission\Models\Permission::pluck('name')->toArray();
foreach ($perms as $p) {
    if (str_contains($p, 'assignment') || str_contains($p, 'exam')) {
        echo " - " . $p . "\n";
    }
}
