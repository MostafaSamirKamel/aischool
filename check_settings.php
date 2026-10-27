<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Authenticate as the current user
$user = \App\Models\User::first(); // Get first user for testing

echo "Current User: {$user->email} (Tenant ID: {$user->tenant_id}, is_super_admin: {$user->is_super_admin})\n\n";

echo "=== ALL SETTINGS (Without Filter) ===\n";
$allSettings = \App\Models\Setting::withoutGlobalScopes()->get();
foreach ($allSettings as $s) {
    echo "ID: {$s->id} | Tenant ID: " . ($s->tenant_id ?? 'NULL') . " | Company: {$s->company_name}\n";
}

echo "\n=== SETTINGS WITH TENANTSCOPE ===\n";
$scopedSettings = \App\Models\Setting::get();
foreach ($scopedSettings as $s) {
    echo "ID: {$s->id} | Tenant ID: {$s->tenant_id} | Company: {$s->company_name}\n";
}
