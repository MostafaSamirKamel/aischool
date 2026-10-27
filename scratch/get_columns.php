<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = Illuminate\Support\Facades\Schema::getColumnListing('vaults');
echo "vaults: " . implode(', ', $cols) . PHP_EOL;

$all = App\Models\Vault::all();
echo "Total Vaults: " . $all->count() . PHP_EOL;
foreach ($all as $v) {
    echo "ID: {$v->id} | Name: {$v->name} | Balance: {$v->balance} | IsActive: " . ($v->is_active ?? 'null') . PHP_EOL;
}
