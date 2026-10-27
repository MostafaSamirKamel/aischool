<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;
use App\Models\Tenant;
use App\Helpers\SettingsHelper;

echo "🔧 Testing Settings System\n";
echo str_repeat('═', 50) . "\n\n";

// 1. Test global settings (Super Admin)
echo "1️⃣ Global Settings (Super Admin - no tenant):\n";
$globalSettings = Setting::withoutGlobalScopes()->whereNull('tenant_id')->first();
if ($globalSettings) {
    echo "  ✅ Found: {$globalSettings->company_name}\n";
    echo "  📧 Email: {$globalSettings->company_email}\n";
    echo "  📱 Phone: {$globalSettings->company_phone}\n";
} else {
    echo "  ❌ No global settings found\n";
}
echo "\n";

// 2. Test per-tenant settings
echo "2️⃣ Per-Tenant Settings:\n";
$tenants = Tenant::all();
foreach ($tenants as $tenant) {
    $settings = Setting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
    if ($settings) {
        echo "  ✅ {$tenant->name}: {$settings->company_name}\n";
    } else {
        echo "  ❌ {$tenant->name}: No settings found\n";
    }
}
echo "\n";

// 3. Test SettingsHelper
echo "3️⃣ SettingsHelper Test:\n";
// Simulate super admin (no tenant)
try {
    $title = SettingsHelper::getSiteTitle();
    echo "  ✅ Site Title: {$title}\n";
    
    $email = SettingsHelper::getSiteEmail();
    echo "  ✅ Site Email: {$email}\n";
    
    $phone = SettingsHelper::getSitePhone();
    echo "  ✅ Site Phone: {$phone}\n";
} catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Test helper functions
echo "4️⃣ Helper Functions Test:\n";
try {
    echo "  ✅ site_title(): " . site_title() . "\n";
    echo "  ✅ site_email(): " . site_email() . "\n";
    echo "  ✅ site_phone(): " . site_phone() . "\n";
    echo "  ✅ setting('currency'): " . setting('currency', 'N/A') . "\n";
} catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Test cache
echo "5️⃣ Cache Test:\n";
$start = microtime(true);
SettingsHelper::getSiteTitle();
$firstCall = (microtime(true) - $start) * 1000;

$start = microtime(true);
SettingsHelper::getSiteTitle();
$secondCall = (microtime(true) - $start) * 1000;

echo "  📊 First call: " . number_format($firstCall, 2) . " ms\n";
echo "  📊 Second call (cached): " . number_format($secondCall, 2) . " ms\n";
echo "  ✅ Cache speedup: " . number_format($firstCall / $secondCall, 1) . "x\n";
echo "\n";

// Summary
echo "✅ Settings System Test Complete!\n";
