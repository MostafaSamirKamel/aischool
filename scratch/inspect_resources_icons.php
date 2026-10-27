<?php

$dir = __DIR__ . '/../app/Filament';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$resources = [];

foreach ($it as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), 'Resource.php')) continue;
    $content = file_get_contents($file->getRealPath());
    $rel = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', $file->getRealPath());
    
    // Extract navigationIcon
    $icon = null;
    if (preg_match('/\$navigationIcon\s*=\s*([^;]+);/', $content, $m)) {
        $icon = trim($m[1]);
    }
    
    // Extract navigationGroup
    $group = null;
    if (preg_match('/\$navigationGroup\s*=\s*([^;]+);/', $content, $m)) {
        $group = trim($m[1]);
    }
    
    // Extract modelLabel / navigationLabel
    $label = null;
    if (preg_match('/\$navigationLabel\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) {
        $label = $m[1];
    } elseif (preg_match('/\$pluralModelLabel\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) {
        $label = $m[1];
    } elseif (preg_match('/\$modelLabel\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) {
        $label = $m[1];
    }
    
    $resources[$rel] = [
        'file' => $rel,
        'label' => $label,
        'group' => $group,
        'icon' => $icon,
        'is_generic_stack' => ($icon === 'Heroicon::OutlinedRectangleStack' || $icon === "'heroicon-o-rectangle-stack'" || $icon === "'o-rectangle-stack'")
    ];
}

echo "Total Resource files found: " . count($resources) . "\n";
echo "Resources with generic rectangle stack: " . count(array_filter($resources, fn($r) => $r['is_generic_stack'])) . "\n\n";

echo "=== RESOURCES WITH GENERIC OR POOR ICONS ===\n";
foreach ($resources as $res) {
    if ($res['is_generic_stack'] || empty($res['icon'])) {
        echo "{$res['file']} | Label: {$res['label']} | Group: {$res['group']} | Icon: {$res['icon']}\n";
    }
}

file_put_contents(__DIR__ . '/resources_icon_audit.json', json_encode($resources, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
