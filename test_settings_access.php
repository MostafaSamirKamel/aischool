<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test 1: Super admin sees all settings
echo "=== Test 1: Super Admin Access ===\n";
$superAdmin = \App\Models\User::where('is_super_admin', 1)->first();
auth()->login($superAdmin);

echo "Logged in as: {$superAdmin->email}\n";
echo "Tenant ID: " . ($superAdmin->tenant_id ?? 'NULL') . "\n";
echo "Is Super Admin: " . ($superAdmin->is_super_admin ? 'YES' : 'NO') . "\n\n";

$settings = \App\Models\Setting::get();
echo "Settings visible to super admin:\n";
foreach ($settings as $s) {
    echo "- {$s->company_name} (Tenant ID: {$s->tenant_id})\n";
}

// Test 2: Regular user sees only their tenant's settings
echo "\n=== Test 2: Regular User Access ===\n";
$regularUser = \App\Models\User::where('is_super_admin', 0)->where('tenant_id', 2)->first();
auth()->login($regularUser);

echo "Logged in as: {$regularUser->email}\n";
echo "Tenant ID: {$regularUser->tenant_id}\n";
echo "Is Super Admin: NO\n\n";

$userSettings = \App\Models\Setting::get();
echo "Settings visible to user (should only see tenant 2):\n";
foreach ($userSettings as $s) {
    echo "- {$s->company_name} (Tenant ID: {$s->tenant_id})\n";
}
