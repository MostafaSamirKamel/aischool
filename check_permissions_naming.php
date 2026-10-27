<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== صلاحيات VaultTransfer ===\n";
$vaultPerms = DB::table('permissions')
    ->where('name', 'like', '%vault%')
    ->orWhere('name', 'like', '%transfer%')
    ->get(['name']);
foreach ($vaultPerms as $perm) {
    echo $perm->name . "\n";
}

echo "\n=== صلاحيات Students ===\n";
$studentPerms = DB::table('permissions')
    ->where('name', 'like', '%student%')
    ->take(15)
    ->get(['name']);
foreach ($studentPerms as $perm) {
    echo $perm->name . "\n";
}

echo "\n=== صلاحيات Courses ===\n";
$coursePerms = DB::table('permissions')
    ->where('name', 'like', '%course%')
    ->take(15)
    ->get(['name']);
foreach ($coursePerms as $perm) {
    echo $perm->name . "\n";
}

echo "\n=== أمثلة من صلاحيات أخرى (للمقارنة) ===\n";
$otherPerms = DB::table('permissions')
    ->where('name', 'like', '%enrollment%')
    ->take(10)
    ->get(['name']);
foreach ($otherPerms as $perm) {
    echo $perm->name . "\n";
}
