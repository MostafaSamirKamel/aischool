<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::withoutGlobalScopes()->with(['roles', 'tenant'])->get();

echo "Total Users: " . $users->count() . "\n";

$qaUsers = $users->filter(function($u) {
    return str_contains($u->email, '@aischool.test') || str_contains($u->email, '@admin.com');
});

echo "QA & Seeded Core Users: " . $qaUsers->count() . "\n";
foreach ($qaUsers as $u) {
    $roles = $u->roles->pluck('name')->implode(', ');
    if (empty($roles)) {
        if ($u->is_super_admin) $roles = 'super_admin';
        else $roles = 'no_role';
    }
    $branch = $u->tenant ? $u->tenant->name : 'الكل';
    echo "ID: {$u->id} | {$u->name} | {$u->email} | Branch: {$branch} | Roles: {$roles}\n";
}
