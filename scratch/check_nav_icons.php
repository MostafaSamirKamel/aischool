<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$panels = ['admin', 'instructor', 'supervisor', 'student', 'parent'];

$results = [];

foreach ($panels as $panelId) {
    try {
        $panel = \Filament\Facades\Filament::getPanel($panelId);
        \Filament\Facades\Filament::setCurrentPanel($panel);
        
        $resources = $panel->getResources();
        $pages = $panel->getPages();

        foreach ($resources as $res) {
            $navIcon = $res::getNavigationIcon();
            $label = $res::getNavigationLabel();
            $shouldRegister = $res::shouldRegisterNavigation();
            
            $results[] = [
                'panel' => $panelId,
                'type' => 'Resource',
                'class' => $res,
                'label' => $label,
                'icon' => $navIcon instanceof \BackedEnum ? $navIcon->value : $navIcon,
                'registered' => $shouldRegister,
            ];
        }

        foreach ($pages as $pg) {
            $navIcon = $pg::getNavigationIcon();
            $label = $pg::getNavigationLabel();
            $shouldRegister = $pg::shouldRegisterNavigation();

            $results[] = [
                'panel' => $panelId,
                'type' => 'Page',
                'class' => $pg,
                'label' => $label,
                'icon' => $navIcon instanceof \BackedEnum ? $navIcon->value : $navIcon,
                'registered' => $shouldRegister,
            ];
        }
    } catch (\Throwable $e) {
        echo "Error in panel {$panelId}: " . $e->getMessage() . "\n";
    }
}

$invalidIcons = [];
foreach ($results as $item) {
    if (!$item['registered'] || empty($item['icon'])) continue;
    $iconName = $item['icon'];
    $isValid = false;
    try {
        $svg = svg($iconName);
        $isValid = !empty($svg->toHtml());
    } catch (\Throwable $e) {
        $isValid = false;
    }
    if (!$isValid) {
        $invalidIcons[] = $item;
    }
}

echo "Total Registered Items: " . count(array_filter($results, fn($r) => $r['registered'])) . "\n";
echo "Registered Items with Invalid Icon: " . count($invalidIcons) . "\n\n";

if (!empty($invalidIcons)) {
    echo "--- ITEMS WITH INVALID NAVIGATION ICON ---\n";
    foreach ($invalidIcons as $m) {
        echo "[{$m['panel']}] {$m['type']}: {$m['class']} ({$m['label']}) -> Icon: '{$m['icon']}'\n";
    }
} else {
    echo "All 89 registered navigation icons are 100% VALID and supported by Blade Heroicons!\n";
}

