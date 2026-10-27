<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::withoutGlobalScopes()->with(['roles', 'tenant'])->get();

$out = [];
$out[] = "# دليل الحسابات والمستخدمين الفعليين للنظام (Actual System Users)";
$out[] = "تاريخ الاستخراج: " . date('Y-m-d H:i:s');
$out[] = "إجمالي الحسابات المسجلة: " . $users->count();
$out[] = "كلمة المرور الافتراضية لجميع الحسابات التجريبية والإدارية هي: `password`";
$out[] = "\n------------------------------------------------------------\n";

$rolesGroup = [];
foreach ($users as $u) {
    $roleNames = $u->roles->pluck('name')->toArray();
    $role = !empty($roleNames) ? implode(' / ', $roleNames) : ($u->is_super_admin ? 'super_admin' : 'no_role');
    $rolesGroup[$role][] = $u;
}

foreach ($rolesGroup as $role => $members) {
    $out[] = "\n## دور: " . strtoupper($role) . " (عدد: " . count($members) . ")";
    $out[] = "| ID | الاسم | البريد الإلكتروني | الهاتف | الفرع / Tenant | Super Admin |";
    $out[] = "|---|---|---|---|---|---|";
    foreach ($members as $m) {
        $branch = $m->tenant ? $m->tenant->name : 'الكل (Super / Global)';
        $phone = $m->phone ?: '—';
        $isSa = $m->is_super_admin ? '✅ نعم' : 'لا';
        $out[] = "| {$m->id} | {$m->name} | `{$m->email}` | {$phone} | {$branch} | {$isSa} |";
    }
}

file_put_contents('users_catalog_report.md', implode("\n", $out));
echo "Successfully generated users_catalog_report.md\n";
