<?php

$dir = __DIR__ . '/../app/Filament';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$tables = [];

foreach ($it as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), 'Table.php')) continue;
    $filePath = $file->getRealPath();
    $rel = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', $filePath);
    $content = file_get_contents($filePath);
    
    // Extract actions
    $actions = [];
    if (preg_match('/->actions\s*\(\s*\[(.*?)\]\s*\)/s', $content, $m)) {
        preg_match_all('/([A-Za-z0-9_\\\\]+Action)::make\s*\(\s*[\'"]?([^\'")\s]+)?[\'"]?\s*\)/', $m[1], $actM);
        foreach ($actM[0] as $i => $full) {
            $actions[] = [
                'type' => $actM[1][$i],
                'name' => $actM[2][$i] ?? '',
            ];
        }
    }
    
    // Extract icons used in this table
    $icons = [];
    if (preg_match_all('/->icon\s*\(\s*([^)]+)\s*\)/', $content, $iconM)) {
        $icons = array_map('trim', $iconM[1]);
    }

    $tables[$rel] = [
        'file' => $rel,
        'actions' => $actions,
        'icons' => $icons,
        'has_empty_state_icon' => str_contains($content, 'emptyStateIcon'),
    ];
}

echo "Total Table classes found: " . count($tables) . "\n\n";

foreach ($tables as $t) {
    echo "{$t['file']}\n";
    echo "  Actions: " . count($t['actions']) . " | Explicit icons: " . count($t['icons']) . "\n";
    if (!empty($t['icons'])) {
        echo "  Icons: " . implode(', ', $t['icons']) . "\n";
    }
}

file_put_contents(__DIR__ . '/all_tables_audit.json', json_encode($tables, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
