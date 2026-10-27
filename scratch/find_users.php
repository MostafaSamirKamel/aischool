<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

foreach (User::with('roles')->get() as $u) {
    $roles = $u->roles->pluck('name')->join(', ');
    if ($roles) {
        echo "{$u->id} | {$u->email} | is_super_admin: {$u->is_super_admin} | Roles: {$roles}\n";
    }
}
