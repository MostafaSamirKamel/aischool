<?php

// حذف المراجع لـ filament-reports من ملفات composer

$files = [
    __DIR__ . '/vendor/composer/installed.json',
    __DIR__ . '/vendor/composer/installed.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // حذف package من installed.json
        if (str_contains($file, '.json')) {
            $data = json_decode($content, true);
            if (isset($data['packages'])) {
                $data['packages'] = array_filter($data['packages'], function($package) {
                    return $package['name'] !== 'eightynine/filament-reports';
                });
                $data['packages'] = array_values($data['packages']);
                file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                echo "Updated: $file\n";
            }
        }
        
        // حذف من installed.php
        if (str_contains($file, '.php')) {
            $content = preg_replace(
                "/\s*'eightynine\/filament-reports'\s*=>\s*array\s*\([^)]+\),?/s",
                '',
                $content
            );
            file_put_contents($file, $content);
            echo "Updated: $file\n";
        }
    }
}

// حذف من autoload files
$autoloadFiles = __DIR__ . '/vendor/composer/autoload_files.php';
if (file_exists($autoloadFiles)) {
    $content = file_get_contents($autoloadFiles);
    $content = preg_replace(
        "/\s*'[^']*'\s*=>\s*\\$vendorDir\s*\.\s*'\/eightynine\/filament-reports\/src\/helpers\.php',?/",
        '',
        $content
    );
    file_put_contents($autoloadFiles, $content);
    echo "Updated autoload_files.php\n";
}

echo "\nDone! Now run: composer dump-autoload\n";
