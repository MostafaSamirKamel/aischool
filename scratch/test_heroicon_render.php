<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$icon = \Filament\Support\Icons\Heroicon::OutlinedEnvelope;
echo "Heroicon::OutlinedEnvelope value: " . $icon->value . "\n";
echo "Heroicon::OutlinedEnvelope class: " . get_class($icon) . "\n";

// Let's test Blade render of Filament Icon component
try {
    $rendered = \Illuminate\Support\Facades\Blade::render('<x-filament::icon :icon="$icon" class="w-5 h-5" />', ['icon' => $icon]);
    echo "Rendered successfully: " . substr(strip_tags($rendered), 0, 50) . "... (length: " . strlen($rendered) . ")\n";
    echo "SVG output sample: " . substr($rendered, 0, 100) . "\n";
} catch (\Throwable $e) {
    echo "Render FAILED: " . $e->getMessage() . "\n";
}

// Now let's test string 'o-envelope' vs 'heroicon-o-envelope'
try {
    $renderedStr = \Illuminate\Support\Facades\Blade::render('<x-filament::icon icon="o-envelope" class="w-5 h-5" />');
    echo "Rendered 'o-envelope': length " . strlen($renderedStr) . "\n";
} catch (\Throwable $e) {
    echo "Render 'o-envelope' FAILED: " . $e->getMessage() . "\n";
}

try {
    $renderedHero = \Illuminate\Support\Facades\Blade::render('<x-filament::icon icon="heroicon-o-envelope" class="w-5 h-5" />');
    echo "Rendered 'heroicon-o-envelope': length " . strlen($renderedHero) . "\n";
} catch (\Throwable $e) {
    echo "Render 'heroicon-o-envelope' FAILED: " . $e->getMessage() . "\n";
}
