<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ALL USERS ===\n";
$users = \App\Models\User::withoutGlobalScopes()->get();
foreach ($users as $u) {
    echo "ID: {$u->id} | Email: {$u->email} | Tenant ID: " . ($u->tenant_id ?? 'NULL') . " | Super Admin: " . ($u->is_super_admin ? 'YES' : 'NO') . "\n";
}

echo "\n=== ALL TENANTS ===\n";
$tenants = \App\Models\Tenant::all();
foreach ($tenants as $t) {
    echo "ID: {$t->id} | Name: {$t->name}\n";
}
