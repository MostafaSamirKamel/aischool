<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = [];
foreach (\Illuminate\Support\Facades\Route::getRoutes() as $r) {
    $routes[] = [
        'uri' => $r->uri(),
        'methods' => $r->methods(),
        'name' => $r->getName(),
        'action' => $r->getActionName(),
    ];
}

$panelCounts = [
    'admin' => 0,
    'supervisor' => 0,
    'instructor' => 0,
    'student' => 0,
    'parent' => 0,
    'api_or_other' => 0
];

$screenInventory = [];

foreach ($routes as $r) {
    $uri = $r['uri'];
    if (str_starts_with($uri, 'admin')) {
        $panelCounts['admin']++;
        $screenInventory['admin'][] = $uri;
    } elseif (str_starts_with($uri, 'supervisor')) {
        $panelCounts['supervisor']++;
        $screenInventory['supervisor'][] = $uri;
    } elseif (str_starts_with($uri, 'instructor')) {
        $panelCounts['instructor']++;
        $screenInventory['instructor'][] = $uri;
    } elseif (str_starts_with($uri, 'student')) {
        $panelCounts['student']++;
        $screenInventory['student'][] = $uri;
    } elseif (str_starts_with($uri, 'parent')) {
        $panelCounts['parent']++;
        $screenInventory['parent'][] = $uri;
    } else {
        $panelCounts['api_or_other']++;
        $screenInventory['api_or_other'][] = $uri;
    }
}

echo "Total Routes: " . count($routes) . "\n";
print_r($panelCounts);
file_put_contents(__DIR__ . '/storage/screen_inventory.json', json_encode($screenInventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
