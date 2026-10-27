<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $rendered = \Illuminate\Support\Facades\Blade::render('<x-heroicon-o-user-group class="w-5 h-5" />');
    echo "x-heroicon-o-user-group rendered OK! Length: " . strlen($rendered) . "\n";
} catch (\Throwable $e) {
    echo "x-heroicon-o-user-group FAILED: " . $e->getMessage() . "\n";
}

try {
    $rendered2 = \Illuminate\Support\Facades\Blade::render('<x-filament::icon icon="heroicon-o-user-group" class="w-5 h-5" />');
    echo "x-filament::icon heroicon-o-user-group rendered OK! Length: " . strlen($rendered2) . "\n";
} catch (\Throwable $e) {
    echo "x-filament::icon heroicon-o-user-group FAILED: " . $e->getMessage() . "\n";
}
