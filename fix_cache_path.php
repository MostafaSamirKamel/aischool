<?php

/**
 * Fix Laravel Cache Path Error
 * Upload this file to your server root and run it via browser or PHP CLI
 * Example: php fix_cache_path.php
 */

echo "========================================\n";
echo "Fixing Laravel Cache Path Error\n";
echo "========================================\n\n";

$baseDir = __DIR__;

// Define required directories
$directories = [
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/testing',
    'storage/logs',
    'bootstrap/cache',
];

echo "1. Creating required directories...\n";

foreach ($directories as $dir) {
    $fullPath = $baseDir . '/' . $dir;
    
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0775, true)) {
            echo "   ✓ Created: $dir\n";
        } else {
            echo "   ✗ Failed to create: $dir\n";
        }
    } else {
        echo "   ✓ Exists: $dir\n";
    }
    
    // Try to set permissions
    @chmod($fullPath, 0775);
}

echo "\n2. Creating .gitignore files...\n";

$gitignoreContent = "*\n!.gitignore\n";
$gitignoreDirs = [
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
];

foreach ($gitignoreDirs as $dir) {
    $gitignorePath = $baseDir . '/' . $dir . '/.gitignore';
    if (file_put_contents($gitignorePath, $gitignoreContent)) {
        echo "   ✓ Created: $dir/.gitignore\n";
    }
}

echo "\n3. Setting permissions...\n";

// Set permissions recursively
function setPermissionsRecursive($path) {
    if (is_dir($path)) {
        @chmod($path, 0775);
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                setPermissionsRecursive($path . '/' . $item);
            }
        }
    } else {
        @chmod($path, 0664);
    }
}

setPermissionsRecursive($baseDir . '/storage');
setPermissionsRecursive($baseDir . '/bootstrap/cache');

echo "   ✓ Permissions set for storage and bootstrap/cache\n";

echo "\n4. Clearing Laravel caches...\n";

// Clear caches using artisan
$commands = [
    'config:clear',
    'cache:clear',
    'view:clear',
    'route:clear',
];

foreach ($commands as $command) {
    $output = [];
    $returnVar = 0;
    exec("cd $baseDir && php artisan $command 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "   ✓ Cleared: $command\n";
    } else {
        echo "   ⚠ Warning: $command (might not have cached data)\n";
    }
}

echo "\n5. Verifying directories...\n";

$allGood = true;
foreach ($directories as $dir) {
    $fullPath = $baseDir . '/' . $dir;
    $exists = is_dir($fullPath);
    $writable = is_writable($fullPath);
    
    $status = $exists && $writable ? '✓' : '✗';
    echo "   $status $dir - ";
    echo "Exists: " . ($exists ? 'Yes' : 'No') . ", ";
    echo "Writable: " . ($writable ? 'Yes' : 'No') . "\n";
    
    if (!$exists || !$writable) {
        $allGood = false;
    }
}

echo "\n========================================\n";
if ($allGood) {
    echo "✓ Cache path fix completed successfully!\n";
    echo "========================================\n";
    echo "\nYour Laravel application should now work correctly.\n";
} else {
    echo "⚠ Fix completed with warnings!\n";
    echo "========================================\n";
    echo "\nSome directories are not writable.\n";
    echo "You may need to contact your hosting provider to fix permissions.\n";
}

echo "\nIf you still have issues:\n";
echo "1. Check if .env file exists and is readable\n";
echo "2. Verify PHP has write permissions to storage directories\n";
echo "3. Check server error logs for more details\n";
echo "4. Contact your hosting provider if permission issues persist\n";
echo "\n";
