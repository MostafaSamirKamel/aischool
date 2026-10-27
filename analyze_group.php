<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== تحليل المجموعة المنشأة ===\n\n";

$group = \App\Models\Group::find(24);
if ($group) {
    echo "المجموعة موجودة:\n";
    echo "- ID: {$group->id}\n";
    echo "- الاسم: {$group->name}\n";
    echo "- Instructor ID: {$group->instructor_id}\n";
    echo "- Tenant ID: {$group->tenant_id}\n";
    echo "- Status: {$group->status}\n\n";
    
    $user = \App\Models\User::where('email', 'hossammagdy@admin.com')->first();
    echo "معلومات المدرس:\n";
    echo "- User ID: {$user->id}\n";
    echo "- Instructor ID: {$user->instructor->id}\n";
    echo "- Tenant ID: {$user->tenant_id}\n\n";
    
    echo "مقارنة:\n";
    echo "- Group instructor_id ({$group->instructor_id}) == User instructor_id ({$user->instructor->id})? " . ($group->instructor_id == $user->instructor->id ? '✅' : '❌') . "\n";
    echo "- Group tenant_id ({$group->tenant_id}) == User tenant_id ({$user->tenant_id})? " . ($group->tenant_id == $user->tenant_id ? '✅' : '❌') . "\n";
    echo "- Group status ({$group->status}) == 'active'? " . ($group->status == 'active' ? '✅' : '❌') . "\n";
    
} else {
    echo "❌ المجموعة غير موجودة!\n";
}
