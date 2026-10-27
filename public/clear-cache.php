<?php
// Production diagnostics and auto-setup
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
echo "<h2>AI School - Cloud Diagnostics & Migration</h2>";

// 1. Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>✅ OPcache cleared successfully.</p>";
} else {
    echo "<p>ℹ️ OPcache not active.</p>";
}

// 2. Bootstrap Laravel
echo "<h3>1. Bootstrapping Laravel Application...</h3>";
try {
    require __DIR__.'/../vendor/autoload.php';
    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "<p>✅ Laravel application bootstrapped successfully.</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>❌ Failed to bootstrap Laravel: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    exit;
}

// 3. Environment & Configuration Check
echo "<h3>2. Configuration & Secrets Check:</h3>";
echo "<ul>";
echo "<li><strong>APP_ENV:</strong> " . htmlspecialchars(config('app.env') ?? 'null') . "</li>";
echo "<li><strong>APP_KEY:</strong> " . (!empty(config('app.key')) ? "✅ Set (Length: " . strlen(config('app.key')) . ")" : "❌ Empty") . "</li>";
echo "<li><strong>SESSION_DRIVER:</strong> " . htmlspecialchars(config('session.driver') ?? 'null') . "</li>";
echo "<li><strong>DB_CONNECTION:</strong> " . htmlspecialchars(config('database.default') ?? 'null') . "</li>";

$defaultConn = config('database.default');
$connConfig = config("database.connections.{$defaultConn}", []);
echo "<li><strong>DB Host:</strong> " . htmlspecialchars($connConfig['host'] ?? 'null') . "</li>";
echo "<li><strong>DB Port:</strong> " . htmlspecialchars((string)($connConfig['port'] ?? 'null')) . "</li>";
echo "<li><strong>DB Database:</strong> " . htmlspecialchars($connConfig['database'] ?? 'null') . "</li>";
echo "<li><strong>DB Username:</strong> " . htmlspecialchars($connConfig['username'] ?? 'null') . "</li>";
echo "</ul>";

// 4. Test Database Connection
echo "<h3>3. Testing Database Connection...</h3>";
try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "<p style='color:green;'>✅ Database connection successful!</p>";

    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    echo "<p>Found <strong>" . count($tables) . "</strong> tables in database.</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 5. Run Migrations & Seeders
echo "<h3>4. Running Migrations & Seeders...</h3>";
try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "<p><strong>Migrate output:</strong></p>";
    echo "<pre style='background:#f4f4f4;padding:10px;border-radius:5px;max-height:200px;overflow:auto;'>" . htmlspecialchars(\Illuminate\Support\Facades\Artisan::output()) . "</pre>";

    if (\Illuminate\Support\Facades\Schema::hasTable('users') && \App\Models\User::count() === 0) {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TenantSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DemoUserSeeder', '--force' => true]);
        echo "<p><strong>Seed output:</strong></p>";
        echo "<pre style='background:#f4f4f4;padding:10px;border-radius:5px;'>" . htmlspecialchars(\Illuminate\Support\Facades\Artisan::output()) . "</pre>";
    }

    if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
        $count = \App\Models\User::count();
        echo "<p style='color:green;'>✅ Users table has <strong>{$count}</strong> users.</p>";
        $admin = \App\Models\User::where('email', 'admin@admin.com')->first();
        if ($admin) {
            echo "<p style='color:green;'>✅ Super Admin found: <strong>admin@admin.com</strong> (Password: <code>password</code>)</p>";
        }
    }
} catch (\Throwable $e) {
    echo "<p style='color:red;'>❌ Migration failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// 6. Test Admin Login Page Generation
echo "<h3>5. Testing Admin Login Route Execution...</h3>";
try {
    $request = \Illuminate\Http\Request::create('/admin/login', 'GET');
    $response = $app->handle($request);
    echo "<p style='color:green;'>✅ Admin login response status: <strong>" . $response->getStatusCode() . "</strong></p>";
    echo "<p><a href='/admin/login' style='background:#2563eb;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin-top:10px;'>Go to Admin Login 🚀</a></p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>❌ Admin login threw Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre style='background:#fee2e2;color:#991b1b;padding:10px;border-radius:5px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
