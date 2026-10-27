<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::all();
echo "Total users: " . count($users) . "\n";
foreach ($users as $u) {
    $roles = $u->getRoleNames()->implode(', ');
    if ($roles || $u->is_super_admin) {
        echo "ID: {$u->id} | Name: {$u->name} | Phone: {$u->phone} | Tenant: {$u->tenant_id} | Roles: {$roles} | Super: " . ($u->is_super_admin ? 'Yes' : 'No') . "\n";
    }
}
