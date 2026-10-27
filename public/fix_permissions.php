<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * Fix Laravel Permissions - PHP Version
 * Upload this file to your server root and run it from browser:
 * http://yourdomain.com/fix_permissions.php
 * 
 * IMPORTANT: Delete this file after use for security!
 */

echo "<h1>Fixing Laravel Permissions</h1>";
echo "<pre>";

$basePath = __DIR__;
echo "Base Path: $basePath\n\n";

// Directories to create
$directories = [
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/testing',
    'storage/logs',
    'storage/app/public',
    'bootstrap/cache',
];

echo "=== Creating Directories ===\n";
foreach ($directories as $dir) {
    $fullPath = $basePath . '/' . $dir;
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0775, true)) {
            echo "✓ Created: $dir\n";
        } else {
            echo "✗ Failed to create: $dir\n";
        }
    } else {
        echo "✓ Already exists: $dir\n";
    }
}

echo "\n=== Setting Permissions ===\n";

// Set permissions for storage
if (chmod($basePath . '/storage', 0775)) {
    echo "✓ Set permissions for storage\n";
} else {
    echo "✗ Failed to set permissions for storage\n";
}

// Set permissions for bootstrap/cache
if (chmod($basePath . '/bootstrap/cache', 0775)) {
    echo "✓ Set permissions for bootstrap/cache\n";
} else {
    echo "✗ Failed to set permissions for bootstrap/cache\n";
}

echo "\n=== Clearing Caches ===\n";

// Clear caches using exec (if allowed)
$commands = [
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan view:clear',
    'php artisan route:clear',
];

foreach ($commands as $command) {
    $output = [];
    $return = 0;
    @exec("cd $basePath && $command 2>&1", $output, $return);
    if ($return === 0) {
        echo "✓ Executed: $command\n";
    } else {
        echo "✗ Failed: $command\n";
    }
}

echo "\n=== Checking Permissions ===\n";

$checkDirs = ['storage', 'bootstrap/cache'];
foreach ($checkDirs as $dir) {
    $fullPath = $basePath . '/' . $dir;
    if (is_writable($fullPath)) {
        echo "✓ $dir is writable\n";
    } else {
        echo "✗ $dir is NOT writable - Please check manually\n";
    }
}

echo "\n=== Done! ===\n";
echo "\n<strong>IMPORTANT: Delete this file (fix_permissions.php) now for security!</strong>\n";
echo "\n</pre>";

echo "<hr>";
echo "<h2>Manual Commands (if needed)</h2>";
echo "<p>If the automatic fix didn't work, SSH into your server and run:</p>";
echo "<pre>";
echo "cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg\n";
echo "mkdir -p bootstrap/cache\n";
echo "chmod -R 775 storage bootstrap/cache\n";
echo "chown -R almobtaker:almobtaker storage bootstrap/cache\n";
echo "php artisan optimize:clear\n";
echo "</pre>";
?>
