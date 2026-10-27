<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$special = User::where('is_super_admin', true)
    ->orWhereHas('roles', function($q) {
        $q->whereIn('name', ['super_admin', 'accountant', 'secretary', 'supervisor', 'admin']);
    })->get();

echo "Special role accounts:\n";
foreach ($special as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Phone: {$u->phone} | Tenant: {$u->tenant_id} | Roles: " . $u->getRoleNames()->implode(', ') . " | Super: " . ($u->is_super_admin ? 'Yes' : 'No') . "\n";
}
