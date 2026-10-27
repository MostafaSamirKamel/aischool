<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking Filament Resources..." . PHP_EOL;
echo "=============================" . PHP_EOL;

$panel = Filament\Facades\Filament::getPanel('admin');
$resources = $panel->getResources();

echo "Total Resources Found: " . count($resources) . PHP_EOL . PHP_EOL;

foreach ($resources as $resource) {
    echo "✓ " . class_basename($resource) . PHP_EOL;
    if (method_exists($resource, 'getNavigationGroup')) {
        echo "  Group: " . $resource::getNavigationGroup() . PHP_EOL;
    }
    if (method_exists($resource, 'getNavigationLabel')) {
        echo "  Label: " . $resource::getNavigationLabel() . PHP_EOL;
    }
    echo PHP_EOL;
}
