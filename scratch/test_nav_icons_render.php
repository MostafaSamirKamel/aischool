<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (['admin', 'instructor', 'supervisor'] as $panelId) {
    echo "=== PANEL: {$panelId} ===\n";
    $panel = \Filament\Facades\Filament::getPanel($panelId);
    \Filament\Facades\Filament::setCurrentPanel($panel);
    
    // Simulate user login if needed, or get navigation
    $user = \App\Models\User::first();
    auth()->login($user);
    
    $navigation = \Filament\Facades\Filament::getNavigation();
    foreach ($navigation as $group) {
        $groupLabel = $group->getLabel() ?? 'Root';
        foreach ($group->getItems() as $item) {
            $label = $item->getLabel();
            $icon = $item->getIcon();
            $iconType = is_object($icon) ? get_class($icon) : gettype($icon);
            $iconVal = $icon instanceof \BackedEnum ? $icon->value : (is_string($icon) ? $icon : 'none');
            
            // Test if rendering this icon in EmployeeTopNavbar causes error
            $renderSuccess = false;
            $err = '';
            try {
                $html = \Illuminate\Support\Facades\Blade::render('<x-filament::icon :icon="$icon" class="w-4 h-4" />', ['icon' => $icon]);
                $renderSuccess = true;
            } catch (\Throwable $e) {
                $err = $e->getMessage();
            }
            
            if (!$renderSuccess) {
                echo "FAIL: [{$panelId}] {$groupLabel} > {$label} | Icon: {$iconVal} ({$iconType}) | Error: {$err}\n";
            } else {
                echo "OK: [{$panelId}] {$groupLabel} > {$label} | Icon: {$iconVal} ({$iconType})\n";
            }
        }
    }
}
