<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vault;
use App\Models\Tenant;

class VaultSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $created = 0;
        $skipped = 0;

        foreach ($tenants as $tenant) {
            // التحقق من عدم وجود خزائن بالفعل لهذا الفرع
            $existingVaultsCount = Vault::where('tenant_id', $tenant->id)->count();
            
            if ($existingVaultsCount > 0) {
                echo "⚠️ الفرع '{$tenant->name}' يحتوي على {$existingVaultsCount} خزائن بالفعل - تم التجاوز\n";
                $skipped++;
                continue;
            }

            // خزينة نقدية
            Vault::create([
                'tenant_id' => $tenant->id,
                'name' => 'الخزينة النقدية - ' . $tenant->name,
                'type' => 'cash',
                'opening_balance' => 0,
                'description' => 'خزينة النقدية الرئيسية',
                'is_active' => true,
            ]);

            // خزينة فودافون كاش
            Vault::create([
                'tenant_id' => $tenant->id,
                'name' => 'فودافون كاش - ' . $tenant->name,
                'type' => 'vodafone',
                'opening_balance' => 0,
                'description' => 'محفظة فودافون كاش الإلكترونية',
                'is_active' => true,
            ]);

            // خزينة إنستاباي
            Vault::create([
                'tenant_id' => $tenant->id,
                'name' => 'إنستاباي - ' . $tenant->name,
                'type' => 'instapay',
                'opening_balance' => 0,
                'description' => 'الحساب البنكي - InstaPay',
                'is_active' => true,
            ]);

            echo "✅ تم إنشاء 3 خزائن للفرع: {$tenant->name}\n";
            $created++;
        }

        echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "✅ تم إنشاء خزائن لـ {$created} فرع\n";
        if ($skipped > 0) {
            echo "⚠️ تم تجاوز {$skipped} فرع (لديهم خزائن بالفعل)\n";
        }
        echo "📊 إجمالي الخزائن في النظام: " . Vault::count() . "\n";
    }
}
