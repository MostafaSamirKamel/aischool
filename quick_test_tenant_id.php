<?php

/**
 * Quick Test: Verify Tenant ID Auto-Assignment
 * 
 * يختبر هذا الملف أن tenant_id يتم إضافته تلقائياً
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  اختبار سريع: التحقق من إضافة tenant_id تلقائياً        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 1. البحث عن مستخدم لديه tenant_id
echo "📌 البحث عن مستخدم للاختبار...\n";
$user = User::whereNotNull('tenant_id')->first();

if (!$user) {
    echo "❌ لم يتم العثور على مستخدم لديه tenant_id\n";
    echo "   يرجى التأكد من وجود مستخدمين في النظام\n\n";
    exit(1);
}

echo "✅ تم العثور على المستخدم: {$user->name}\n";
echo "   - tenant_id: {$user->tenant_id}\n";
echo "   - email: {$user->email}\n\n";

// 2. تسجيل دخول المستخدم
Auth::login($user);
echo "✅ تم تسجيل دخول المستخدم\n\n";

// 3. اختبار إنشاء سجل جديد
echo "📌 اختبار إنشاء فئة مصروفات جديدة...\n";

try {
    $category = new \App\Models\ExpenseCategory();
    $category->name = 'Test Category - ' . date('Y-m-d H:i:s');
    $category->name_en = 'Test Category - ' . date('Y-m-d H:i:s');
    $category->code = 'TEST-' . time();
    $category->is_active = true;
    // ملاحظة: لم نقم بتعيين tenant_id يدوياً
    
    // محاكاة الـ creating event
    $category->fireModelEvent('creating');
    
    // التحقق من tenant_id
    if ($category->tenant_id === $user->tenant_id) {
        echo "✅ نجح! تم تعيين tenant_id تلقائياً\n";
        echo "   - tenant_id المتوقع: {$user->tenant_id}\n";
        echo "   - tenant_id الفعلي: {$category->tenant_id}\n\n";
    } else {
        echo "❌ فشل! tenant_id غير صحيح\n";
        echo "   - tenant_id المتوقع: {$user->tenant_id}\n";
        echo "   - tenant_id الفعلي: " . ($category->tenant_id ?? 'NULL') . "\n\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ حدث خطأ: {$e->getMessage()}\n\n";
    exit(1);
}

// 4. اختبار إنشاء سجلات أخرى
$modelsToTest = [
    'Department' => ['name' => 'Test Dept', 'is_active' => true],
    'RevenueCategory' => ['name' => 'Test Revenue', 'name_en' => 'Test', 'code' => 'TR-'.time(), 'is_active' => true],
    'Nationality' => ['name' => 'Test Nationality', 'code' => 'TN-'.time()],
];

echo "📌 اختبار نماذج إضافية...\n\n";

$allPassed = true;
foreach ($modelsToTest as $modelName => $attributes) {
    $className = "App\\Models\\{$modelName}";
    
    try {
        $model = new $className();
        foreach ($attributes as $key => $value) {
            $model->$key = $value;
        }
        
        // محاكاة الـ creating event
        $model->fireModelEvent('creating');
        
        if ($model->tenant_id === $user->tenant_id) {
            echo "  ✅ {$modelName}: tenant_id صحيح\n";
        } else {
            echo "  ❌ {$modelName}: tenant_id غير صحيح (متوقع: {$user->tenant_id}, فعلي: " . ($model->tenant_id ?? 'NULL') . ")\n";
            $allPassed = false;
        }
        
    } catch (\Exception $e) {
        echo "  ❌ {$modelName}: خطأ - {$e->getMessage()}\n";
        $allPassed = false;
    }
}

echo "\n";

// النتيجة النهائية
if ($allPassed) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║                   ✅ الاختبار نجح بالكامل                 ║\n";
    echo "║     جميع النماذج تضيف tenant_id تلقائياً بنجاح!         ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    exit(0);
} else {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║                   ⚠️  بعض الاختبارات فشلت                ║\n";
    echo "║          يرجى مراجعة الأخطاء أعلاه                       ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    exit(1);
}
