<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = \App\Models\Tenant::all();

foreach ($tenants as $tenant) {
    $setting = \App\Models\Setting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
    
    if (!$setting) {
        echo "Creating settings for tenant: {$tenant->name} (ID: {$tenant->id})\n";
        \App\Models\Setting::create([
            'tenant_id' => $tenant->id,
            'company_name' => $tenant->name,
            'company_email' => 'info@' . str_replace(' ', '-', strtolower($tenant->name)) . '.local',
            'company_phone' => '+20100000000',
            'company_website' => 'https://' . str_replace(' ', '-', strtolower($tenant->name)) . '.local',
            'company_address' => $tenant->description ?? 'Main Branch',
        ]);
    } else {
        echo "Settings already exist for tenant: {$tenant->name} (ID: {$tenant->id})\n";
    }
}

echo "\n=== FINAL CHECK ===\n";
$finalSettings = \App\Models\Setting::withoutGlobalScopes()->get();
foreach ($finalSettings as $s) {
    echo "Tenant ID: {$s->tenant_id} | Company: {$s->company_name}\n";
}
