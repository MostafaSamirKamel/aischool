<?php

namespace App\Services\Enrollment;

use App\Models\Enrollment;
use App\Models\Installment;
use Carbon\Carbon;

/**
 * خدمة حساب الأقساط والمدفوعات
 * 
 * التسلسل الصحيح لحساب الأقساط:
 * 1. حساب إجمالي الكورسات (مجموع أسعار الكورسات المختارة)
 * 2. تطبيق الخصم: صافي = إجمالي - الخصم
 * 3. خصم المقدم: المتبقي للأقساط = صافي - المقدم
 * 4. حساب الأقساط: قيمة القسط = المتبقي ÷ عدد الأقساط
 * 
 * قواعد مهمة:
 * ✓ المقدم يُعامل كخصم إضافي - يُخصم من حساب الأقساط
 * ✓ الأقساط تُحسب على: إجمالي - خصم - مقدم
 * ✓ جميع الأقساط تُنشأ بحالة pending (معلق)
 * ✓ المقدم لا يُطبق على الأقساط - يبقى منفصل
 * ✓ فقط المدفوعات من شاشة Payments تغير حالة الأقساط
 * ✓ في حالة وجود كسور يتم تعديل آخر قسط
 */
class InstallmentCalculationService
{
    /**
     * حساب قيمة الأقساط من الإنرولمنت
     * 
     * ⚠️ المعادلة الصحيحة:
     * 1. إجمالي الكورسات
     * 2. ناقص الخصم
     * 3. ناقص المقدم (المدفوع عند التسجيل)
     * 4. الناتج يُقسم على عدد الأقساط
     * 
     * ⚠️ ملاحظات مهمة:
     * - المقدم يُعامل كخصم إضافي
     * - جميع الأقساط تُنشأ بحالة pending
     * - المقدم لا يُطبق على الأقساط (يبقى منفصل)
     * - فقط المدفوعات من شاشة Payments تغير حالة الأقساط
     * 
     * @param float $totalAmount إجمالي الكورسات
     * @param float $discount الخصم
     * @param float $advancePayment المقدم (المدفوع عند التسجيل)
     * @param int $installmentCount عدد الأقساط
     * @param Carbon $firstDueDate تاريخ استحقاق القسط الأول
     * 
     * @return array مصفوفة بيانات الأقساط
     */
    public function calculateInstallments(
        float $totalAmount,
        float $discount = 0,
        float $advancePayment = 0,
        int $installmentCount = 2,
        ?Carbon $firstDueDate = null
    ): array
    {
        // Step 1: حساب إجمالي الكورسات
        $totalCourses = $totalAmount;
        
        // Step 2: تطبيق الخصم
        $netAmount = $totalCourses - $discount;

        // Step 3: خصم المقدم (المقدم = خصم إضافي)
        $remainingForInstallments = $netAmount - $advancePayment;

        // Validate
        if ($remainingForInstallments < 0) {
            throw new \InvalidArgumentException(
                'المقدم لا يمكن أن يكون أكبر من المبلغ الصافي بعد الخصم'
            );
        }
        
        if ($installmentCount < 2) {
            throw new \InvalidArgumentException('عدد الأقساط يجب أن يكون 2 على الأقل');
        }
        
        // Set first due date to next month if not provided
        if (!$firstDueDate) {
            $firstDueDate = now()->addMonth();
        }

        // Step 4: توزيع الأقساط على المتبقي (بعد خصم المقدم)
        // قيمة الأقساط = (إجمالي - خصم - مقدم) ÷ عدد الأقساط
        return $this->distributeInstallments(
            $remainingForInstallments,  // ← المتبقي بعد الخصم والمقدم
            $installmentCount,
            $firstDueDate
        );
    }
    
    /**
     * توزيع المبلغ على الأقساط
     * 
     * @param float $amount المبلغ المراد توزيعه
     * @param int $count عدد الأقساط
     * @param Carbon $firstDueDate تاريخ الاستحقاق الأول
     * 
     * @return array مصفوفة بيانات الأقساط
     */
    private function distributeInstallments(
        float $amount,
        int $count,
        Carbon $firstDueDate
    ): array
    {
        // حساب قيمة القسط الواحد
        $installmentAmount = round($amount / $count, 2);
        $installments = [];
        
        for ($i = 1; $i <= $count; $i++) {
            // آخر قسط يتم تعديله للتعامل مع الكسور العشرية
            if ($i == $count) {
                $finalAmount = round($amount - ($installmentAmount * ($count - 1)), 2);
            } else {
                $finalAmount = $installmentAmount;
            }
            
            // حساب تاريخ الاستحقاق
            $dueDate = $firstDueDate->copy()->addMonths($i - 1);
            
            $installments[] = [
                'installment_number' => $i,
                'amount' => $finalAmount,
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => 'pending',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => ''
            ];
        }
        
        return $installments;
    }
    
    /**
     * إنشاء أقساط الإنرولمنت
     * 
     * @param Enrollment $enrollment
     * @param array $installmentsData بيانات الأقساط من calculateInstallments
     * 
     * @return array مصفوفة الأقساط المُنشأة
     */
    public function createInstallmentsForEnrollment(
        Enrollment $enrollment,
        array $installmentsData
    ): array
    {
        $createdInstallments = [];
        
        foreach ($installmentsData as $data) {
            $installment = Installment::create([
                'tenant_id' => $enrollment->tenant_id,
                'enrollment_id' => $enrollment->id,
                'installment_number' => $data['installment_number'],
                'amount' => $data['amount'],
                'due_date' => $data['due_date'],
                'status' => $data['status'],
                'paid_amount' => $data['paid_amount'],
                'paid_date' => $data['paid_date'],
                'notes' => $data['notes'] ?? ''
            ]);
            
            $createdInstallments[] = $installment;
        }
        
        return $createdInstallments;
    }
    
    /**
     * تطبيق المقدم على الأقساط
     * 
     * ⚠️ تحذير: هذا المتد لم يعد مستخدماً في النظام الجديد
     * 
     * السبب: المقدم يُعامل كخصم إضافي ويُخصم من حساب الأقساط مباشرة
     * الأقساط تُنشأ بحالة pending ولا يتم تطبيق المقدم عليها
     * فقط المدفوعات من شاشة Payments تغير حالة الأقساط
     * 
     * @deprecated لا يُستخدم بعد الآن - المقدم يُخصم من حساب الأقساط مباشرة
     * @param Enrollment $enrollment
     * @param float $advancePayment المقدم
     * 
     * @return void
     */
    public function applyAdvancePaymentToInstallments(
        Enrollment $enrollment,
        float $advancePayment = 0
    ): void
    {
        // ⚠️ هذا المتد لم يعد مستخدماً
        // المقدم يُحسب في enrollment->paid_amount فقط
        // ولا يُطبق على الأقساط

        if ($advancePayment <= 0) {
            return;
        }

        // للتوافقية فقط - لو تم استدعاء المتد عن طريق الخطأ
        // نحفظ المقدم في enrollment مباشرة بدون تطبيقه على الأقساط
        $enrollment->paid_amount = $advancePayment;
        $enrollment->save();
    }
    
    /**
     * الحصول على ملخص الحساب
     * 
     * @param float $totalAmount
     * @param float $discount
     * @param float $advancePayment
     * @param int $installmentCount
     * 
     * @return array
     */
    public function getSummary(
        float $totalAmount,
        float $discount = 0,
        float $advancePayment = 0,
        int $installmentCount = 2
    ): array
    {
        $netAmount = $totalAmount - $discount;
        $remainingAmount = $netAmount - $advancePayment;
        $installmentAmount = round($remainingAmount / $installmentCount, 2);
        
        return [
            'total_courses' => $totalAmount,
            'discount' => $discount,
            'net_after_discount' => $netAmount,
            'advance_payment' => $advancePayment,
            'remaining_to_installments' => $remainingAmount,
            'installment_count' => $installmentCount,
            'installment_amount' => $installmentAmount,
            'total_after_advance' => $netAmount // يساوي المقدم + المتبقي
        ];
    }

    /**
     * Static helper للفورم لحساب الأقساط بشكل فوري (live calculation)
     * 
     * استخدم هذا المتد في Filament Form للحسابات الحية
     * 
     * @param float $totalAmount
     * @param float $discount
     * @param float $advancePayment
     * @param int $installmentCount
     * 
     * @return array بيانات الأقساط جاهزة للعرض في الفورم
     */
    public static function calculateForForm(
        float $totalAmount,
        float $discount = 0,
        float $advancePayment = 0,
        int $installmentCount = 2
    ): array
    {
        $service = new self();
        
        try {
            return $service->calculateInstallments(
                $totalAmount,
                $discount,
                $advancePayment,
                $installmentCount,
                now()->addMonth()
            );
        } catch (\InvalidArgumentException $e) {
            return [];
        }
    }
}
