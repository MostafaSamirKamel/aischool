<?php
// Clear all caches on production server
// Access this file once then delete it for security

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared<br>";
} else {
    echo "❌ OPcache not available<br>";
}

// Clear APCu cache if available
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✅ APCu cache cleared<br>";
} else {
    echo "ℹ️ APCu not available<br>";
}

echo "<br><strong>Now run these commands via SSH:</strong><br>";
echo "<code>php artisan config:clear</code><br>";
echo "<code>php artisan cache:clear</code><br>";
echo "<code>php artisan view:clear</code><br>";
echo "<code>php artisan route:clear</code><br>";
echo "<code>php artisan optimize:clear</code><br>";
echo "<br><strong style='color:red;'>⚠️ DELETE THIS FILE AFTER USE!</strong>";
