<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;

echo "=== تحديث حالة المجموعات من pending إلى active ===\n\n";

// جلب جميع المجموعات pending
$groups = Group::withoutGlobalScopes()->where('status', 'pending')->get();

echo "عدد المجموعات قيد الانتظار: " . $groups->count() . "\n\n";

foreach ($groups as $group) {
    echo "تحديث المجموعة: {$group->name}\n";
    echo "  - من: pending\n";
    $group->status = 'active';
    $group->save();
    echo "  - إلى: active ✓\n\n";
}

echo "=== تم تحديث جميع المجموعات بنجاح ===\n";
