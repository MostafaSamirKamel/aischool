<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Tenant;

echo "=== DISCOVERING ALL ROUTES & PANELS ===\n";

$allRoutes = Route::getRoutes();
$routesList = [];
$panelRoutes = [
    'admin' => [],
    'instructor' => [],
    'supervisor' => [],
    'student' => [],
    'parent' => [],
    'other' => []
];

foreach ($allRoutes as $route) {
    $uri = $route->uri();
    $methods = implode('|', $route->methods());
    $action = $route->getActionName();
    $name = $route->getName() ?? 'unnamed';

    if (str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum') || str_starts_with($uri, 'api')) {
        continue;
    }

    $entry = [
        'uri' => $uri,
        'methods' => $methods,
        'name' => $name,
        'action' => $action
    ];

    if (str_starts_with($uri, 'admin')) {
        $panelRoutes['admin'][] = $entry;
    } elseif (str_starts_with($uri, 'instructor')) {
        $panelRoutes['instructor'][] = $entry;
    } elseif (str_starts_with($uri, 'supervisor')) {
        $panelRoutes['supervisor'][] = $entry;
    } elseif (str_starts_with($uri, 'student')) {
        $panelRoutes['student'][] = $entry;
    } elseif (str_starts_with($uri, 'parent')) {
        $panelRoutes['parent'][] = $entry;
    } else {
        $panelRoutes['other'][] = $entry;
    }
}

echo "Discovered Routes Breakdown:\n";
foreach ($panelRoutes as $p => $r) {
    echo "  - $p: " . count($r) . " routes\n";
}

// Check Panels and Resources
$panels = ['admin', 'instructor', 'supervisor', 'student', 'parent'];
$panelDetails = [];

foreach ($panels as $pId) {
    $p = Filament::getPanel($pId);
    if ($p) {
        $resources = $p->getResources();
        $pages = $p->getPages();
        $widgets = $p->getWidgets();
        $panelDetails[$pId] = [
            'id' => $pId,
            'path' => $p->getPath(),
            'resources_count' => count($resources),
            'resources' => array_keys($resources),
            'pages_count' => count($pages),
            'pages' => array_keys($pages),
            'widgets_count' => count($widgets),
            'widgets' => array_keys($widgets)
        ];
        echo "Panel: $pId | Resources: " . count($resources) . " | Pages: " . count($pages) . " | Widgets: " . count($widgets) . "\n";
    }
}

// Save discovery manifest
$out = [
    'timestamp' => date('c'),
    'summary' => [
        'total_admin_routes' => count($panelRoutes['admin']),
        'total_instructor_routes' => count($panelRoutes['instructor']),
        'total_supervisor_routes' => count($panelRoutes['supervisor']),
        'total_student_routes' => count($panelRoutes['student']),
        'total_parent_routes' => count($panelRoutes['parent']),
        'total_other_routes' => count($panelRoutes['other']),
        'total_routes' => count($panelRoutes['admin']) + count($panelRoutes['instructor']) + count($panelRoutes['supervisor']) + count($panelRoutes['student']) + count($panelRoutes['parent']) + count($panelRoutes['other'])
    ],
    'panels' => $panelDetails,
    'routes' => $panelRoutes
];

file_put_contents(__DIR__ . '/ui_discovery_manifest.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "Saved UI discovery manifest to scratch/ui_discovery_manifest.json\n";
