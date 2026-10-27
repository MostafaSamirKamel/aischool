<?php

/**
 * Debug Tenant ID Issue
 * 
 * هذا الملف لتشخيص مشكلة tenant_id بالتفصيل
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║         تشخيص مشكلة tenant_id - فحص شامل                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 1. فحص المستخدمين في قاعدة البيانات
echo "📌 فحص المستخدمين في قاعدة البيانات...\n\n";

try {
    $totalUsers = User::count();
    $usersWithTenant = User::whereNotNull('tenant_id')->count();
    $usersWithoutTenant = User::whereNull('tenant_id')->count();
    
    echo "✅ إجمالي المستخدمين: {$totalUsers}\n";
    echo "   - لديهم tenant_id: {$usersWithTenant}\n";
    echo "   - ليس لديهم tenant_id (NULL): {$usersWithoutTenant}\n\n";
    
    if ($usersWithoutTenant > 0) {
        echo "⚠️  تحذير: يوجد {$usersWithoutTenant} مستخدم بدون tenant_id!\n";
        echo "   هؤلاء المستخدمين لن يستطيعوا إنشاء سجلات جديدة.\n\n";
        
        $usersWithoutTenantList = User::whereNull('tenant_id')->get(['id', 'name', 'email']);
        foreach ($usersWithoutTenantList as $user) {
            echo "   - [{$user->id}] {$user->name} ({$user->email})\n";
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ في قاعدة البيانات: {$e->getMessage()}\n\n";
    exit(1);
}

// 2. فحص بنية جدول expense_categories
echo "📌 فحص بنية جدول expense_categories...\n\n";

try {
    $columns = DB::select("DESCRIBE expense_categories");
    $tenantColumn = null;
    
    foreach ($columns as $column) {
        if ($column->Field === 'tenant_id') {
            $tenantColumn = $column;
            break;
        }
    }
    
    if ($tenantColumn) {
        echo "✅ عمود tenant_id موجود:\n";
        echo "   - Type: {$tenantColumn->Type}\n";
        echo "   - Null: {$tenantColumn->Null}\n";
        echo "   - Key: {$tenantColumn->Key}\n";
        echo "   - Default: " . ($tenantColumn->Default ?? 'NULL') . "\n\n";
        
        if ($tenantColumn->Null === 'NO') {
            echo "⚠️  tenant_id لا يقبل NULL - يجب تعيين قيمة دائماً!\n\n";
        }
    } else {
        echo "❌ عمود tenant_id غير موجود في الجدول!\n\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ: {$e->getMessage()}\n\n";
}

// 3. فحص السجلات الموجودة
echo "📌 فحص السجلات الموجودة في expense_categories...\n\n";

try {
    $totalCategories = ExpenseCategory::withoutGlobalScopes()->count();
    $categoriesWithTenant = ExpenseCategory::withoutGlobalScopes()->whereNotNull('tenant_id')->count();
    $categoriesWithoutTenant = ExpenseCategory::withoutGlobalScopes()->whereNull('tenant_id')->count();
    
    echo "✅ إجمالي الفئات: {$totalCategories}\n";
    echo "   - لديها tenant_id: {$categoriesWithTenant}\n";
    echo "   - بدون tenant_id: {$categoriesWithoutTenant}\n\n";
    
} catch (\Exception $e) {
    echo "⚠️  لا يمكن قراءة السجلات: {$e->getMessage()}\n\n";
}

// 4. محاكاة إنشاء سجل جديد
echo "📌 محاكاة إنشاء فئة مصروفات جديدة...\n\n";

// البحث عن مستخدم لديه tenant_id
$testUser = User::whereNotNull('tenant_id')->first();

if (!$testUser) {
    echo "❌ لا يوجد مستخدم لديه tenant_id للاختبار!\n";
    echo "   الحل: قم بتعيين tenant_id لأحد المستخدمين:\n";
    echo "   UPDATE users SET tenant_id = 1 WHERE id = 1;\n\n";
    exit(1);
}

echo "✅ مستخدم الاختبار:\n";
echo "   - الاسم: {$testUser->name}\n";
echo "   - البريد: {$testUser->email}\n";
echo "   - tenant_id: {$testUser->tenant_id}\n\n";

// تسجيل دخول المستخدم
Auth::login($testUser);

echo "✅ تم تسجيل دخول المستخدم\n\n";

// محاولة إنشاء فئة جديدة
echo "📌 محاولة إنشاء فئة مصروفات...\n\n";

try {
    $testCode = 'TEST-' . time();
    
    $category = new ExpenseCategory();
    $category->name = 'فئة اختبار';
    $category->name_en = 'Test Category';
    $category->code = $testCode;
    $category->is_active = true;
    
    echo "📝 البيانات قبل الحفظ:\n";
    echo "   - name: {$category->name}\n";
    echo "   - code: {$category->code}\n";
    echo "   - tenant_id: " . ($category->tenant_id ?? 'NULL') . "\n\n";
    
    // محاكاة creating event
    $category->save();
    
    echo "✅ نجح الحفظ!\n";
    echo "   - ID: {$category->id}\n";
    echo "   - tenant_id: {$category->tenant_id}\n\n";
    
    // حذف السجل التجريبي
    $category->delete();
    echo "✅ تم حذف السجل التجريبي\n\n";
    
} catch (\Exception $e) {
    echo "❌ فشل الحفظ!\n";
    echo "   الخطأ: {$e->getMessage()}\n\n";
    
    // تحليل الخطأ
    if (strpos($e->getMessage(), 'tenant_id') !== false) {
        echo "🔍 التحليل:\n";
        echo "   المشكلة في tenant_id:\n";
        echo "   1. المستخدم الحالي tenant_id = " . ($testUser->tenant_id ?? 'NULL') . "\n";
        echo "   2. BelongsToBranch trait قد لا يعمل في سياق هذا الاختبار\n";
        echo "   3. تحقق من أن الـ trait مُفعّل في Model\n\n";
    }
}

// 5. فحص BelongsToBranch trait
echo "📌 فحص BelongsToBranch trait...\n\n";

$traits = class_uses_recursive(ExpenseCategory::class);
if (in_array('App\\Models\\Concerns\\BelongsToBranch', $traits)) {
    echo "✅ ExpenseCategory يستخدم BelongsToBranch trait\n\n";
} else {
    echo "❌ ExpenseCategory لا يستخدم BelongsToBranch trait!\n";
    echo "   الحل: أضف use BelongsToBranch; في Model\n\n";
}

// 6. التوصيات
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                      التوصيات                              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

if ($usersWithoutTenant > 0) {
    echo "⚠️  قم بتعيين tenant_id للمستخدمين:\n";
    echo "   UPDATE users SET tenant_id = 1 WHERE tenant_id IS NULL;\n\n";
}

echo "✅ تأكد من تسجيل الدخول بمستخدم لديه tenant_id\n";
echo "✅ استخدم متصفح حقيقي للاختبار (Filament يعمل عبر المتصفح)\n";
echo "✅ راجع logs: storage/logs/laravel.log\n\n";

echo "═══════════════════════════════════════════════════════════\n\n";
