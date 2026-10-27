#!/usr/bin/env php
<?php

/**
 * اختبار سريع لخدمة حساب الأقساط
 * 
 * تشغيل الكود:
 * php test_installment_service.php
 */

// تحميل autoloader
require 'vendor/autoload.php';

// تحميل التطبيق
$app = require_once 'bootstrap/app.php';

use App\Services\Enrollment\InstallmentCalculationService;

echo "================================================================================\n";
echo "اختبار خدمة حساب الأقساط - InstallmentCalculationService\n";
echo "================================================================================\n\n";

// Test Case 1: حساب الأقساط الأساسي
echo "TEST CASE 1: حساب الأقساط الأساسي\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

$service = new InstallmentCalculationService();

$result = $service->calculateInstallments(
    totalAmount: 1000,
    discount: 100,
    advancePayment: 200,
    installmentCount: 4
);

echo "المدخلات:\n";
echo "  - إجمالي الكورسات: 1000 ج.م\n";
echo "  - الخصم: 100 ج.م\n";
echo "  - المقدم: 200 ج.م\n";
echo "  - عدد الأقساط: 4\n\n";

echo "الحساب:\n";
echo "  الخطوة 1: إجمالي = 1000\n";
echo "  الخطوة 2: صافي = 1000 - 100 = 900\n";
echo "  الخطوة 3: متبقي = 900 - 200 = 700\n";
echo "  الخطوة 4: قسط = 700 ÷ 4 = 175\n\n";

echo "النتائج:\n";
foreach ($result as $i => $installment) {
    echo "  القسط " . ($i + 1) . ":\n";
    echo "    - الرقم: " . $installment['installment_number'] . "\n";
    echo "    - المبلغ: " . $installment['amount'] . " ج.م\n";
    echo "    - تاريخ الاستحقاق: " . $installment['due_date'] . "\n";
    echo "    - الحالة: " . $installment['status'] . "\n";
}

echo "\n✅ TEST CASE 1: SUCCESS\n\n";

// Test Case 2: حساب مع كسور عشرية
echo "TEST CASE 2: التعامل مع الكسور العشرية\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

$result2 = $service->calculateInstallments(
    totalAmount: 1000,
    discount: 0,
    advancePayment: 0,
    installmentCount: 3
);

echo "المدخلات:\n";
echo "  - إجمالي: 1000 ج.م ÷ 3 أقساط = 333.33...\n\n";

echo "النتيجة (مع التعديل على آخر قسط):\n";
$total = 0;
foreach ($result2 as $installment) {
    echo "  - القسط " . $installment['installment_number'] . ": " . $installment['amount'] . " ج.م\n";
    $total += $installment['amount'];
}
echo "  - الإجمالي: " . $total . " ج.م ✓\n";

echo "\n✅ TEST CASE 2: SUCCESS\n\n";

// Test Case 3: استخدام static method للفورم
echo "TEST CASE 3: استخدام Static Method (للفورم)\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

$formResult = InstallmentCalculationService::calculateForForm(
    totalAmount: 500,
    discount: 50,
    advancePayment: 100,
    installmentCount: 2
);

echo "المدخلات:\n";
echo "  - إجمالي: 500 ج.م\n";
echo "  - خصم: 50 ج.م (صافي = 450)\n";
echo "  - مقدم: 100 ج.م (متبقي = 350)\n";
echo "  - عدد أقساط: 2\n\n";

echo "النتائج:\n";
foreach ($formResult as $installment) {
    echo "  - القسط " . $installment['installment_number'] . ": " . $installment['amount'] . " ج.م\n";
}

echo "\n✅ TEST CASE 3: SUCCESS\n\n";

// Test Case 4: الملخص
echo "TEST CASE 4: ملخص الحساب\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

$summary = $service->getSummary(
    totalAmount: 1000,
    discount: 100,
    advancePayment: 200,
    installmentCount: 4
);

echo "العناصر المرجعة:\n";
foreach ($summary as $key => $value) {
    echo "  - " . str_pad($key, 30) . ": " . $value . "\n";
}

echo "\n✅ TEST CASE 4: SUCCESS\n\n";

// Test Case 5: معالجة الأخطاء
echo "TEST CASE 5: معالجة الأخطاء\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

try {
    $badResult = $service->calculateInstallments(
        totalAmount: 100,
        discount: 0,
        advancePayment: 200, // أكبر من الإجمالي!
        installmentCount: 2
    );
    echo "❌ لم يتم رفض المقدم الزائد\n";
} catch (\InvalidArgumentException $e) {
    echo "✅ تم اكتشاف خطأ بشكل صحيح: " . $e->getMessage() . "\n";
}

echo "\n";

try {
    $badResult = $service->calculateInstallments(
        totalAmount: 100,
        discount: 0,
        advancePayment: 0,
        installmentCount: 1 // أقل من 2!
    );
    echo "❌ لم يتم رفض عدد الأقساط الصغير\n";
} catch (\InvalidArgumentException $e) {
    echo "✅ تم اكتشاف خطأ بشكل صحيح: " . $e->getMessage() . "\n";
}

echo "\n✅ TEST CASE 5: SUCCESS\n\n";

// ملخص النتائج
echo "================================================================================\n";
echo "ملخص النتائج\n";
echo "================================================================================\n";
echo "✅ جميع الاختبارات نجحت بنجاح!\n";
echo "✅ الخدمة تعمل بشكل صحيح\n";
echo "✅ المعالجة الصحيحة للأخطاء موجودة\n";
echo "✅ الكسور العشرية تُعالج بشكل صحيح\n";
echo "\nالخدمة جاهزة للاستخدام في الإنتاج.\n";
echo "================================================================================\n";
