<?php

// Script to rebuild autoload files
echo "Rebuilding autoload files...\n";

// Remove old autoload files
$autoloadFiles = [
    __DIR__ . '/vendor/composer/autoload_classmap.php',
    __DIR__ . '/vendor/composer/autoload_static.php',
    __DIR__ . '/vendor/composer/autoload_namespaces.php',
    __DIR__ . '/vendor/composer/autoload_psr4.php',
];

foreach ($autoloadFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "Deleted: $file\n";
    }
}

echo "\nNow run: composer dump-autoload\n";
