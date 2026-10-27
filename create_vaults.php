<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\Vault;

echo "=== التحقق من وجود الفروع ===\n\n";

$tenants = Tenant::all();

if ($tenants->isEmpty()) {
    echo "⚠️ لا توجد فروع في قاعدة البيانات!\n";
    echo "يجب تشغيل seeders أو إضافة فروع أولاً.\n";
} else {
    echo "✅ عدد الفروع: " . $tenants->count() . "\n\n";
    
    foreach ($tenants as $tenant) {
        echo "   - {$tenant->name} (ID: {$tenant->id})\n";
    }
    
    echo "\n=== إنشاء الخزائن للفروع ===\n\n";
    
    foreach ($tenants as $tenant) {
        // التحقق من عدم وجود خزائن بالفعل
        $existingVaults = Vault::where('tenant_id', $tenant->id)->count();
        
        if ($existingVaults > 0) {
            echo "   الفرع: {$tenant->name} - يوجد بالفعل {$existingVaults} خزائن\n";
            continue;
        }
        
        // إنشاء الخزائن الثلاثة
        $vaults = [
            [
                'name' => 'الخزينة النقدية',
                'type' => 'cash',
                'icon' => '💵'
            ],
            [
                'name' => 'فودافون كاش',
                'type' => 'vodafone',
                'icon' => '📱'
            ],
            [
                'name' => 'إنستاباي',
                'type' => 'instapay',
                'icon' => '💳'
            ],
        ];
        
        foreach ($vaults as $vaultData) {
            Vault::create([
                'tenant_id' => $tenant->id,
                'name' => $vaultData['name'],
                'type' => $vaultData['type'],
                'opening_balance' => 0,
                'is_active' => true,
                'description' => 'خزينة ' . $vaultData['name'] . ' للفرع ' . $tenant->name,
            ]);
            
            echo "   ✅ تم إنشاء {$vaultData['icon']} {$vaultData['name']} للفرع: {$tenant->name}\n";
        }
    }
    
    echo "\n=== النتيجة النهائية ===\n";
    echo "إجمالي الخزائن المنشأة: " . Vault::count() . "\n";
}
