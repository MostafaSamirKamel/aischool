<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vault;
use Illuminate\Support\Facades\DB;

echo "🔍 فحص الخزائن المكررة...\n\n";

// جلب جميع الخزائن مع عدد استخداماتها
$vaults = Vault::withCount([
    'enrollments',
    'payments',
    'expenses',
    'revenues',
    'studentPayments',
    'transfersFrom',
    'transfersTo'
])->orderBy('name')->orderBy('id')->get();

// تجميع الخزائن حسب الاسم
$groupedByName = $vaults->groupBy('name');

echo "📊 إحصائيات الخزائن:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$duplicates = [];
$toDelete = [];

foreach ($groupedByName as $name => $vaultGroup) {
    if ($vaultGroup->count() > 1) {
        echo "❌ اسم مكرر: {$name} (موجود {$vaultGroup->count()} مرات)\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        $duplicates[$name] = [];
        
        foreach ($vaultGroup as $vault) {
            $totalUsage = $vault->enrollments_count + 
                         $vault->payments_count + 
                         $vault->expenses_count + 
                         $vault->revenues_count + 
                         $vault->student_payments_count +
                         $vault->transfers_from_count +
                         $vault->transfers_to_count;
            
            $usageDetails = [];
            if ($vault->enrollments_count > 0) $usageDetails[] = "تسجيلات: {$vault->enrollments_count}";
            if ($vault->payments_count > 0) $usageDetails[] = "دفعات: {$vault->payments_count}";
            if ($vault->expenses_count > 0) $usageDetails[] = "مصروفات: {$vault->expenses_count}";
            if ($vault->revenues_count > 0) $usageDetails[] = "إيرادات: {$vault->revenues_count}";
            if ($vault->student_payments_count > 0) $usageDetails[] = "دفعات طلاب: {$vault->student_payments_count}";
            if ($vault->transfers_from_count > 0) $usageDetails[] = "تحويلات صادرة: {$vault->transfers_from_count}";
            if ($vault->transfers_to_count > 0) $usageDetails[] = "تحويلات واردة: {$vault->transfers_to_count}";
            
            $usageText = $totalUsage > 0 ? implode(', ', $usageDetails) : '❌ غير مستخدمة';
            
            echo "  ID: {$vault->id}\n";
            echo "  النوع: {$vault->type}\n";
            echo "  الفرع: " . ($vault->tenant ? $vault->tenant->name : 'غير محدد') . "\n";
            echo "  الرصيد الافتتاحي: {$vault->opening_balance} ج.م\n";
            echo "  الحالة: " . ($vault->is_active ? '✅ نشط' : '⚠️ غير نشط') . "\n";
            echo "  الاستخدام: {$usageText}\n";
            echo "  تاريخ الإنشاء: {$vault->created_at->format('Y-m-d H:i:s')}\n";
            
            $duplicates[$name][] = [
                'vault' => $vault,
                'total_usage' => $totalUsage,
                'usage_details' => $usageText
            ];
            
            if ($totalUsage == 0) {
                $toDelete[] = $vault;
                echo "  ⚠️ هذه الخزينة غير مستخدمة ويمكن حذفها\n";
            } else {
                echo "  ✅ هذه الخزينة مستخدمة ولا يجب حذفها\n";
            }
            
            echo "\n";
        }
        
        echo "\n";
    }
}

if (empty($duplicates)) {
    echo "✅ لا توجد خزائن مكررة!\n";
    exit;
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📋 ملخص:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "عدد الأسماء المكررة: " . count($duplicates) . "\n";
echo "عدد الخزائن التي يمكن حذفها (غير مستخدمة): " . count($toDelete) . "\n\n";

if (empty($toDelete)) {
    echo "✅ جميع الخزائن المكررة مستخدمة، لا يمكن حذف أي منها تلقائياً.\n";
    echo "⚠️ يجب المراجعة اليدوية والقرار يدوياً أي واحدة تحتفظ بها.\n";
    exit;
}

echo "الخزائن التي سيتم حذفها:\n";
foreach ($toDelete as $vault) {
    echo "  - ID: {$vault->id} | الاسم: {$vault->name} | النوع: {$vault->type}\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "⚠️ هل تريد حذف الخزائن غير المستخدمة؟ (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if (strtolower($line) !== 'yes') {
    echo "\n❌ تم إلغاء العملية.\n";
    exit;
}

echo "\n🗑️ جاري حذف الخزائن غير المستخدمة...\n\n";

$deletedCount = 0;
$errors = [];

foreach ($toDelete as $vault) {
    try {
        DB::beginTransaction();
        
        echo "حذف: ID {$vault->id} - {$vault->name} ({$vault->type})...";
        $vault->delete();
        
        DB::commit();
        $deletedCount++;
        echo " ✅ تم\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo " ❌ فشل\n";
        $errors[] = "ID {$vault->id}: " . $e->getMessage();
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ تم حذف {$deletedCount} خزينة بنجاح!\n";

if (!empty($errors)) {
    echo "\n⚠️ حدثت أخطاء أثناء الحذف:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

echo "\n📊 فحص نهائي للخزائن المتبقية:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$remainingVaults = Vault::orderBy('name')->get();
$remainingGrouped = $remainingVaults->groupBy('name');

foreach ($remainingGrouped as $name => $vaultGroup) {
    if ($vaultGroup->count() > 1) {
        echo "⚠️ لا يزال هناك تكرار: {$name} ({$vaultGroup->count()} مرات)\n";
        foreach ($vaultGroup as $v) {
            echo "   - ID: {$v->id} | النوع: {$v->type} | الفرع: " . ($v->tenant ? $v->tenant->name : 'غير محدد') . "\n";
        }
    } else {
        echo "✅ {$name}: واحدة فقط (ID: {$vaultGroup->first()->id})\n";
    }
}

echo "\n✅ انتهت العملية!\n";
