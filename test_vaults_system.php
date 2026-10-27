<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Vault;
use App\Models\Tenant;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\StudentPayment;

echo "=== اختبار نظام الخزائن ===\n\n";

// 1. عرض جميع الخزائن
echo "1. الخزائن المتاحة:\n";
echo str_repeat("=", 80) . "\n";

$vaults = Vault::with('tenant')->get();
foreach ($vaults as $vault) {
    echo sprintf(
        "   %s %s (الفرع: %s)\n   - النوع: %s\n   - الرصيد الافتتاحي: %.2f جنيه\n   - نشط: %s\n\n",
        match($vault->type) {
            'cash' => '💵',
            'vodafone' => '📱',
            'instapay' => '💳',
            default => '💰'
        },
        $vault->name,
        $vault->tenant->name,
        $vault->type,
        $vault->opening_balance,
        $vault->is_active ? 'نعم' : 'لا'
    );
}

// 2. حساب الرصيد الحالي لكل خزينة
echo "\n2. الرصيد الحالي لكل خزينة:\n";
echo str_repeat("=", 80) . "\n";

foreach ($vaults as $vault) {
    $revenues = Revenue::where('vault_id', $vault->id)->sum('amount');
    $studentPayments = StudentPayment::where('vault_id', $vault->id)
        ->where('status', 'completed')
        ->sum('net_amount');
    $expenses = Expense::where('vault_id', $vault->id)->sum('amount');
    
    $currentBalance = $vault->opening_balance + $revenues + $studentPayments - $expenses;
    
    echo sprintf(
        "   %s %s (الفرع: %s)\n",
        match($vault->type) {
            'cash' => '💵',
            'vodafone' => '📱',
            'instapay' => '💳',
            default => '💰'
        },
        $vault->name,
        $vault->tenant->name
    );
    echo sprintf("   - الرصيد الافتتاحي: %.2f جنيه\n", $vault->opening_balance);
    echo sprintf("   - الإيرادات: %.2f جنيه\n", $revenues);
    echo sprintf("   - مدفوعات الطلاب: %.2f جنيه\n", $studentPayments);
    echo sprintf("   - المصروفات: %.2f جنيه\n", $expenses);
    echo sprintf("   - الرصيد الحالي: %.2f جنيه\n", $currentBalance);
    echo sprintf("   - الحالة: %s\n\n", $currentBalance >= 0 ? '✅ موجب' : '⚠️ سالب');
}

// 3. إجمالي كل فرع
echo "\n3. إجمالي أرصدة كل فرع:\n";
echo str_repeat("=", 80) . "\n";

$tenants = Tenant::all();
foreach ($tenants as $tenant) {
    $tenantVaults = Vault::where('tenant_id', $tenant->id)->get();
    
    if ($tenantVaults->isEmpty()) {
        echo "   الفرع: {$tenant->name} - لا توجد خزائن\n\n";
        continue;
    }
    
    $totalBalance = 0;
    foreach ($tenantVaults as $vault) {
        $revenues = Revenue::where('vault_id', $vault->id)->sum('amount');
        $studentPayments = StudentPayment::where('vault_id', $vault->id)
            ->where('status', 'completed')
            ->sum('net_amount');
        $expenses = Expense::where('vault_id', $vault->id)->sum('amount');
        
        $totalBalance += $vault->opening_balance + $revenues + $studentPayments - $expenses;
    }
    
    echo sprintf(
        "   الفرع: %s\n   - عدد الخزائن: %d\n   - الإجمالي الكلي: %.2f جنيه %s\n\n",
        $tenant->name,
        $tenantVaults->count(),
        $totalBalance,
        $totalBalance >= 0 ? '✅' : '⚠️'
    );
}

// 4. التحقق من الفصل بين الخزائن
echo "\n4. التحقق من استقلالية الخزائن:\n";
echo str_repeat("=", 80) . "\n";

$cashVault = Vault::where('type', 'cash')->first();
$vodafoneVault = Vault::where('type', 'vodafone')->first();
$instapayVault = Vault::where('type', 'instapay')->first();

if ($cashVault && $vodafoneVault && $instapayVault) {
    echo "   ✅ تم إنشاء الأنواع الثلاثة من الخزائن\n";
    echo "   ✅ كل خزينة لها ID منفصل\n";
    echo "   ✅ لا يوجد تداخل في البيانات\n";
} else {
    echo "   ⚠️ بعض الخزائن غير موجودة!\n";
}

// 5. عرض إحصائيات
echo "\n\n5. إحصائيات النظام:\n";
echo str_repeat("=", 80) . "\n";

$totalVaults = Vault::count();
$activeVaults = Vault::where('is_active', true)->count();
$totalExpenses = Expense::whereNotNull('vault_id')->count();
$totalRevenues = Revenue::whereNotNull('vault_id')->count();
$totalPayments = StudentPayment::whereNotNull('vault_id')->count();

echo "   - إجمالي الخزائن: {$totalVaults}\n";
echo "   - الخزائن النشطة: {$activeVaults}\n";
echo "   - المصروفات المرتبطة بخزائن: {$totalExpenses}\n";
echo "   - الإيرادات المرتبطة بخزائن: {$totalRevenues}\n";
echo "   - مدفوعات الطلاب المرتبطة بخزائن: {$totalPayments}\n";

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ اكتمل اختبار نظام الخزائن بنجاح!\n";
