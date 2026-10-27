<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n🔍 Debug Enrollment Creation:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$groups = DB::table('groups')->get();
echo "Total Groups: " . $groups->count() . "\n";
echo "Active Groups: " . DB::table('groups')->where('status', 'active')->count() . "\n\n";

echo "Groups by branch:\n";
foreach (\App\Models\Tenant::all() as $branch) {
    $branchGroups = DB::table('groups')->where('tenant_id', $branch->id)->get();
    $activeGroups = DB::table('groups')->where('tenant_id', $branch->id)->where('status', 'active')->count();
    
    echo "  ├─ {$branch->name}: {$branchGroups->count()} total, {$activeGroups} active\n";
    
    foreach ($branchGroups as $group) {
        echo "  │  ├─ Group ID {$group->id}: {$group->name} - Status: {$group->status}\n";
    }
}

echo "\n";
