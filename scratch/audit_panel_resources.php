<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bladeHeroiconsDir = __DIR__ . '/../vendor/blade-ui-kit/blade-heroicons/resources/svg';
$validHeroicons = [];
if (is_dir($bladeHeroiconsDir)) {
    foreach (scandir($bladeHeroiconsDir) as $file) {
        if (str_ends_with($file, '.svg')) {
            $base = basename($file, '.svg');
            $validHeroicons["heroicon-{$base}"] = true;
        }
    }
}

$panels = ['admin', 'instructor', 'parent', 'student', 'supervisor'];
$resourcesByPanel = [];
$pagesByPanel = [];
$widgetsByPanel = [];

foreach ($panels as $panelId) {
    $panel = \Filament\Facades\Filament::getPanel($panelId);
    \Filament\Facades\Filament::setCurrentPanel($panel);
    
    $resources = $panel->getResources();
    $pages = $panel->getPages();
    $widgets = $panel->getWidgets();
    
    $resourcesByPanel[$panelId] = [];
    foreach ($resources as $resClass) {
        $navIcon = null;
        $activeNavIcon = null;
        $navGroup = null;
        $navLabel = null;
        $model = null;
        
        try {
            $navIcon = $resClass::getNavigationIcon();
            $activeNavIcon = $resClass::getActiveNavigationIcon();
            $navGroup = $resClass::getNavigationGroup();
            $navLabel = $resClass::getNavigationLabel();
            $model = $resClass::getModel();
        } catch (\Throwable $e) {}
        
        $navIconStr = $navIcon instanceof \BackedEnum ? $navIcon->value : (is_string($navIcon) ? $navIcon : (is_object($navIcon) && method_exists($navIcon, '__toString') ? (string)$navIcon : ($navIcon ? get_class($navIcon) : null)));
        $activeNavIconStr = $activeNavIcon instanceof \BackedEnum ? $activeNavIcon->value : (is_string($activeNavIcon) ? $activeNavIcon : null);
        
        $resourcesByPanel[$panelId][$resClass] = [
            'class' => $resClass,
            'model' => $model,
            'label' => $navLabel,
            'group' => $navGroup,
            'nav_icon' => $navIconStr,
            'active_nav_icon' => $activeNavIconStr,
            'valid_icon' => $navIconStr ? isset($validHeroicons[$navIconStr]) : null,
        ];
    }
    
    $pagesByPanel[$panelId] = [];
    foreach ($pages as $pageClass) {
        $navIcon = null;
        $navGroup = null;
        $navLabel = null;
        try {
            $navIcon = $pageClass::getNavigationIcon();
            $navGroup = $pageClass::getNavigationGroup();
            $navLabel = $pageClass::getNavigationLabel();
        } catch (\Throwable $e) {}
        
        $pageIconStr = $navIcon instanceof \BackedEnum ? $navIcon->value : (is_string($navIcon) ? $navIcon : (is_object($navIcon) && method_exists($navIcon, '__toString') ? (string)$navIcon : ($navIcon ? get_class($navIcon) : null)));

        $pagesByPanel[$panelId][$pageClass] = [
            'class' => $pageClass,
            'label' => $navLabel,
            'group' => $navGroup,
            'nav_icon' => $pageIconStr,
            'valid_icon' => $pageIconStr ? isset($validHeroicons[$pageIconStr]) : null,
        ];
    }
    
    $widgetsByPanel[$panelId] = array_map(function($w) {
        return is_string($w) ? $w : get_class($w);
    }, $widgets);
}

echo "=== RESOURCES AUDIT BY PANEL ===\n";
foreach ($resourcesByPanel as $panelId => $resList) {
    echo "\n--- PANEL: {$panelId} (Total Resources: " . count($resList) . ") ---\n";
    foreach ($resList as $res) {
        $validStr = $res['nav_icon'] ? ($res['valid_icon'] ? 'VALID' : 'INVALID!') : 'NO ICON';
        echo "{$res['class']}\n  Label: {$res['label']} | Group: {$res['group']}\n  NavIcon: {$res['nav_icon']} [{$validStr}]\n";
    }
}

echo "\n=== PAGES AUDIT BY PANEL ===\n";
foreach ($pagesByPanel as $panelId => $pgList) {
    echo "\n--- PANEL: {$panelId} (Total Pages: " . count($pgList) . ") ---\n";
    foreach ($pgList as $pg) {
        $validStr = $pg['nav_icon'] ? ($pg['valid_icon'] ? 'VALID' : 'INVALID!') : 'NO ICON';
        echo "{$pg['class']}\n  Label: {$pg['label']} | Group: {$pg['group']}\n  NavIcon: {$pg['nav_icon']} [{$validStr}]\n";
    }
}

file_put_contents(__DIR__ . '/panel_nav_audit.json', json_encode([
    'resources' => $resourcesByPanel,
    'pages' => $pagesByPanel,
    'widgets' => $widgetsByPanel,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
