<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::withoutGlobalScopes()->with(['roles', 'tenant'])->get();
$byRole = [];

foreach ($users as $u) {
    $roleNames = $u->roles->pluck('name')->toArray();
    $roleKey = !empty($roleNames) ? implode(', ', $roleNames) : ($u->is_super_admin ? 'super_admin' : 'no_role');
    $branch = $u->tenant ? $u->tenant->name : 'الكل (Super Admin)';
    
    $byRole[$roleKey][] = [
        'id' => $u->id,
        'name' => $u->name,
        'email' => $u->email,
        'phone' => $u->phone ?? '—',
        'branch' => $branch,
        'super_admin' => $u->is_super_admin ? 'نعم' : 'لا'
    ];
}

echo "=== ملخص المستخدمين المسجلين في قاعدة البيانات ===\n";
echo "إجمالي المستخدمين: " . $users->count() . "\n\n";

foreach ($byRole as $role => $list) {
    echo "## الدور: " . strtoupper($role) . " (العدد: " . count($list) . ")\n";
    // Show top 5 or all if small
    $slice = count($list) > 10 ? array_slice($list, 0, 8) : $list;
    foreach ($slice as $item) {
        echo " - [{$item['name']}] | الإيميل: {$item['email']} | الفرع: {$item['branch']} | الهاتف: {$item['phone']}\n";
    }
    if (count($list) > 10) {
        echo "   ... و " . (count($list) - 8) . " مستخدمين إضافيين بنفس الدور ونمط الإيميلات.\n";
    }
    echo "\n";
}
