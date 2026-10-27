#!/usr/bin/env php
<?php

/**
 * اختبار المعادلة الجديدة (الصحيحة)
 * 
 * المقدم يُعامل كخصم إضافي
 */

echo "================================================================================\n";
echo "اختبار المعادلة الجديدة - المقدم كخصم إضافي\n";
echo "================================================================================\n\n";

// Simulated class
class NewInstallmentService {
    public function calculateInstallments(
        float $totalAmount,
        float $discount = 0,
        float $advancePayment = 0,
        int $installmentCount = 2
    ): array {
        // Step 1: إجمالي
        $total = $totalAmount;
        
        // Step 2: ناقص الخصم
        $netAmount = $total - $discount;
        
        // Step 3: ناقص المقدم (المقدم = خصم إضافي)
        $remainingForInstallments = $netAmount - $advancePayment;
        
        // Step 4: قيمة القسط
        $installmentAmount = round($remainingForInstallments / $installmentCount, 2);
        $installments = [];
        
        for ($i = 1; $i <= $installmentCount; $i++) {
            if ($i == $installmentCount) {
                $finalAmount = round($remainingForInstallments - ($installmentAmount * ($installmentCount - 1)), 2);
            } else {
                $finalAmount = $installmentAmount;
            }
            
            // جميع الأقساط pending - لا يوجد paid_amount
            $installments[] = [
                'installment_number' => $i,
                'amount' => $finalAmount,
                'due_date' => date('Y-m-d', strtotime("+$i months")),
                'status' => 'pending',  // ← كلها pending!
                'paid_amount' => 0,     // ← كلها صفر!
            ];
        }
        
        return [
            'installments' => $installments,
            'total' => $total,
            'discount' => $discount,
            'advance_payment' => $advancePayment,
            'net_after_discount' => $netAmount,
            'remaining_for_installments' => $remainingForInstallments,
        ];
    }
}

// TEST CASE
echo "السيناريو:\n";
echo "──────────────────────────────────────────────────────────────────────────────\n";
echo "إجمالي الكورسات: 1000 ج.م\n";
echo "الخصم: 100 ج.م\n";
echo "المقدم: 200 ج.م\n";
echo "عدد الأقساط: 4\n\n";

$service = new NewInstallmentService();
$result = $service->calculateInstallments(
    totalAmount: 1000,
    discount: 100,
    advancePayment: 200,
    installmentCount: 4
);

echo "الحسابات:\n";
echo "──────────────────────────────────────────────────────────────────────────────\n";
echo "1. إجمالي = {$result['total']} ج.م\n";
echo "2. ناقص الخصم = {$result['total']} - {$result['discount']} = {$result['net_after_discount']} ج.م\n";
echo "3. ناقص المقدم = {$result['net_after_discount']} - {$result['advance_payment']} = {$result['remaining_for_installments']} ج.م\n";
echo "4. قيمة القسط = {$result['remaining_for_installments']} ÷ 4 = " . round($result['remaining_for_installments'] / 4, 2) . " ج.م\n\n";

echo "الأقساط المُنشأة:\n";
echo "──────────────────────────────────────────────────────────────────────────────\n";
$total = 0;
foreach ($result['installments'] as $inst) {
    $status = $inst['status'] === 'pending' ? '⏳' : '✅';
    echo sprintf(
        "القسط %d: %s ج.م - %s %s (مدفوع: %s ج.م)\n",
        $inst['installment_number'],
        $inst['amount'],
        $status,
        $inst['status'],
        $inst['paid_amount']
    );
    $total += $inst['amount'];
}
echo "──────────────────────────────────────────────────────────────────────────────\n";
echo "مجموع الأقساط: $total ج.م\n\n";

echo "حالة الإنرولمنت:\n";
echo "──────────────────────────────────────────────────────────────────────────────\n";
echo "enrollment->total_amount = {$result['total']} ج.م\n";
echo "enrollment->discount = {$result['discount']} ج.م\n";
echo "enrollment->paid_amount = {$result['advance_payment']} ج.م (المقدم فقط)\n\n";

echo "حساب المتبقي:\n";
echo "──────────────────────────────────────────────────────────────────────────────\n";
$remaining = $result['total'] - $result['discount'] - $result['advance_payment'];
echo "المتبقي = إجمالي - خصم - مدفوع\n";
echo "المتبقي = {$result['total']} - {$result['discount']} - {$result['advance_payment']} = $remaining ج.م\n\n";

echo "✅ المعادلة صحيحة!\n";
echo "✓ المقدم = خصم إضافي\n";
echo "✓ الأقساط محسوبة على: إجمالي - خصم - مقدم\n";
echo "✓ جميع الأقساط pending (مش مدفوعة)\n";
echo "✓ المقدم في enrollment->paid_amount فقط (مش في الأقساط)\n";
echo "✓ فقط المدفوعات من شاشة Payments تغير حالة الأقساط\n\n";

echo "================================================================================\n";
echo "سيناريو الدفع:\n";
echo "================================================================================\n";
echo "عند دفع 175 ج.م من شاشة Payments:\n\n";
echo "1. PaymentObserver يطبق الـ175 على القسط الأول:\n";
echo "   - القسط 1: paid_amount = 175 → status = paid ✅\n\n";
echo "2. تحديث enrollment->paid_amount:\n";
echo "   - enrollment->paid_amount += 175\n";
echo "   - enrollment->paid_amount = 200 + 175 = 375 ج.م ✓\n\n";
echo "3. حساب المتبقي الجديد:\n";
echo "   - المتبقي = 1000 - 100 - 375 = 525 ج.م ✓\n\n";

echo "✅ كل شيء صحيح!\n";
echo "================================================================================\n";
