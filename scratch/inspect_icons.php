<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== BLADE ICONS FACTORY ===\n";
if (app()->bound(\BladeUI\Icons\Factory::class)) {
    $factory = app(\BladeUI\Icons\Factory::class);
    foreach ($factory->all() as $name => $set) {
        $paths = is_array($set['paths'] ?? null) ? implode(', ', $set['paths']) : ($set['paths'] ?? 'none');
        echo "Set: {$name} | Prefix: " . ($set['prefix'] ?? 'none') . " | Paths: {$paths}\n";
    }
} else {
    echo "BladeUI\\Icons\\Factory not bound\n";
}

echo "\n=== COMPOSER PACKAGES (FILAMENT & ICONS) ===\n";
$lock = json_decode(file_get_contents(__DIR__ . '/../composer.lock'), true);
foreach ($lock['packages'] as $p) {
    if (str_contains($p['name'], 'icon') || str_contains($p['name'], 'filament')) {
        echo $p['name'] . ' => ' . $p['version'] . "\n";
    }
}

