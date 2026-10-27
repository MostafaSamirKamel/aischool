<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\RevenueCategory;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        // عند إنشاء دفعة جديدة وتكون مكتملة، نسجلها كإيراد
        if ($payment->status === 'completed') {
            $this->createRevenueFromPayment($payment);
            $this->updateInstallmentPayment($payment);
        }
    }

    public function updated(Payment $payment): void
    {
        if ($payment->status === 'completed') {
            // تحقق إذا تم إنشاء إيراد مسبقاً
            $existingRevenue = Revenue::where('payment_id', $payment->id)->first();
            if (!$existingRevenue) {
                $this->createRevenueFromPayment($payment);
            } else {
                // تحديث بيانات الإيراد لتطابق الدفعة
                $existingRevenue->update([
                    'vault_id' => $payment->vault_id,
                    'amount' => $payment->amount,
                    'revenue_date' => $payment->payment_date,
                    'payment_method' => $this->resolveRevenuePaymentMethod($payment->payment_method),
                    'status' => 'received',
                ]);
            }

            if ($payment->wasChanged(['status', 'amount', 'payment_date', 'enrollment_id'])) {
                $this->updateInstallmentPayment($payment);
            }
        } else {
            // إذا تغيرت حالة الدفعة من مكتمل إلى غير مكتمل، نحذف الإيراد المرتبط
            Revenue::where('payment_id', $payment->id)->delete();

            if ($payment->wasChanged('status') || $payment->wasChanged('enrollment_id')) {
                $this->updateInstallmentPayment($payment);
            }
        }
    }
    
    public function deleted(Payment $payment): void
    {
        // حذف الإيراد المرتبط بالدفعة لتجنب الإيرادات الوهمية (سوفت ديليت لحفظ السجل المالي)
        Revenue::where('payment_id', $payment->id)->delete();
        $this->updateInstallmentPayment($payment, true);

        // تسجيل العملية في سجل التدقيق المالي
        \App\Services\AuditLogService::log(
            'payment_reversed',
            $payment,
            "عكس الدفعة المالية ({$payment->payment_code}) بمبلغ {$payment->amount} واستعادة رصيد الخزنة والأقساط",
            [
                'payment_code' => $payment->payment_code,
                'amount' => (float) $payment->amount,
                'vault_id' => $payment->vault_id,
                'enrollment_id' => $payment->enrollment_id,
                'actor_id' => auth()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        );
    }

    public function restored(Payment $payment): void
    {
        // استعادة الإيراد المرتبط بالدفعة وإعادة تطبيق المبالغ على الأقساط
        Revenue::withTrashed()->where('payment_id', $payment->id)->restore();
        $this->updateInstallmentPayment($payment);

        \App\Services\AuditLogService::log(
            'payment_restored',
            $payment,
            "استعادة الدفعة المالية ({$payment->payment_code}) بمبلغ {$payment->amount} وتطبيقها على الخزنة والأقساط",
            [
                'payment_code' => $payment->payment_code,
                'amount' => (float) $payment->amount,
                'vault_id' => $payment->vault_id,
                'enrollment_id' => $payment->enrollment_id,
                'actor_id' => auth()->id(),
                'timestamp' => now()->toIso8601String(),
            ]
        );
    }

    protected function createRevenueFromPayment(Payment $payment): void
    {
        // البحث عن فئة مدفوعات الطلاب للـ tenant هذا
        $category = RevenueCategory::where('tenant_id', $payment->tenant_id)
            ->where('name', 'مدفوعات الطلاب')
            ->first();

        if (!$category) {
            // إنشاء الفئة مع كود فريد
            $categoryCode = 'STD-PAY-T' . $payment->tenant_id . '-' . time();

            try {
                $category = RevenueCategory::create([
                    'tenant_id' => $payment->tenant_id,
                    'code' => $categoryCode,
                    'name' => 'مدفوعات الطلاب',
                    'name_en' => 'Student Payments',
                    'description' => 'الإيرادات من مدفوعات الطلاب',
                    'is_active' => true,
                ]);
            } catch (\Exception $e) {
                // إذا فشل الإنشاء، نحاول البحث مرة أخرى
                $category = RevenueCategory::where('tenant_id', $payment->tenant_id)
                    ->where('name', 'مدفوعات الطلاب')
                    ->first();
            }
        }

        // التأكد من وجود الفئة
        if (!$category) {
            \Log::error('Failed to create or retrieve RevenueCategory', [
                'tenant_id' => $payment->tenant_id,
                'payment_id' => $payment->id,
            ]);
            return;
        }

        $studentName = $payment->enrollment?->student?->user?->name ?? 'طالب';

        $creatorId = $payment->received_by
            ?? auth()->id()
            ?? $payment->enrollment?->student?->user_id
            ?? \App\Models\User::where('tenant_id', $payment->tenant_id)->value('id')
            ?? \App\Models\User::value('id');

        // إنشاء سجل الإيراد
        Revenue::create([
            'tenant_id' => $payment->tenant_id,
            'vault_id' => $payment->vault_id,
            'revenue_category_id' => $category->id,
            'title' => 'دفعة من الطالب - ' . $studentName,
            'description' => 'دفعة رقم: ' . $payment->payment_code,
            'amount' => $payment->amount,
            'revenue_date' => $payment->payment_date,
            'payment_method' => $this->resolveRevenuePaymentMethod($payment->payment_method),
            'customer_name' => $studentName,
            'payment_id' => $payment->id,
            'status' => 'received',
            'received_by' => $payment->received_by ?? auth()->id() ?? $creatorId,
            'received_at' => now(),
            'created_by' => $creatorId,
        ]);
    }

    /**
     * Revenues enum: cash|bank_transfer|card|online|other.
     * Payments may contain null or mobile_wallet, so normalize here.
     */
    protected function resolveRevenuePaymentMethod(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'cash' => 'cash',
            'bank_transfer' => 'bank_transfer',
            'card' => 'card',
            'online' => 'online',
            'mobile_wallet' => 'online',
            'other' => 'other',
            default => 'cash',
        };
    }

    /**
     * Update installment payment when payment is completed
     * 
     * آلية تطبيق المدفوعات على الأقساط:
     * 1. جلب الأقساط المعلقة (غير المدفوعة بالكامل) حسب تاريخ الاستحقاق
     * 2. توزيع المبلغ المدفوع على الأقساط بالترتيب
     * 3. تحديث paid_amount للإنرولمنت = المقدم + مجموع paid_amount من الأقساط
     * 
     * ⚠️ المعادلة الموحدة:
     * - عند التسجيل: enrollment->paid_amount = المقدم
     * - عند كل دفعة: enrollment->paid_amount = المقدم + sum(installments->paid_amount)
     * 
     * ملاحظة: المقدم لا يُطبق على الأقساط - يبقى منفصل
     * فقط المدفوعات من شاشة Payments تغير حالة الأقساط
     * 
     * @param Payment $payment
     * @return void
     */
    protected function updateInstallmentPayment(Payment $payment, bool $isDelete = false): void
    {
        $studentIds = collect();

        $currentEnrollment = $payment->enrollment;
        if ($currentEnrollment?->student_id) {
            $studentIds->push((int) $currentEnrollment->student_id);
        }

        if ($payment->wasChanged('enrollment_id')) {
            $originalEnrollmentId = $payment->getOriginal('enrollment_id');

            if (!empty($originalEnrollmentId)) {
                $oldEnrollment = Enrollment::find($originalEnrollmentId);
                if ($oldEnrollment?->student_id) {
                    $studentIds->push((int) $oldEnrollment->student_id);
                }
            }
        }

        foreach ($studentIds->unique()->filter() as $studentId) {
            $this->recalculateStudentInstallments((int) $studentId);
        }
    }

    /**
     * Recalculate all installment allocations for a student across all enrollments.
     */
    protected function recalculateStudentInstallments(int $studentId): void
    {
        $studentEnrollments = Enrollment::query()
            ->where('student_id', $studentId)
            ->with('installments')
            ->get();

        if ($studentEnrollments->isEmpty()) {
            return;
        }

        $installmentEnrollments = $studentEnrollments
            ->filter(fn(Enrollment $enrollment): bool => $enrollment->payment_plan === 'installments')
            ->values();

        if ($installmentEnrollments->isEmpty()) {
            return;
        }

        $installments = collect();

        foreach ($installmentEnrollments as $enrollment) {
            foreach ($enrollment->installments as $installment) {
                if ($installment->status === 'cancelled') {
                    continue;
                }
                $installments->push($installment);
            }
        }

        $installments = $installments
            ->sortBy(function (Installment $installment): string {
                $dueDate = $installment->due_date
                    ? \Carbon\Carbon::parse($installment->due_date)->format('Y-m-d')
                    : '9999-12-31';

                return sprintf('%s-%05d-%010d', $dueDate, (int) $installment->installment_number, (int) $installment->id);
            })
            ->values();

        foreach ($installments as $installment) {
            /** @var Installment $installment */
            $installment->forceFill(['paid_amount' => 0.0]);
            $installment->paid_date = null;
            $isOverdue = $installment->due_date
                ? now()->startOfDay()->gt(\Carbon\Carbon::parse($installment->due_date)->startOfDay())
                : false;

            $installment->status = $isOverdue
                ? 'overdue'
                : 'pending';
            $installment->save();
        }

        $completedPayments = Payment::query()
            ->whereIn('enrollment_id', $studentEnrollments->pluck('id')->all())
            ->where('status', 'completed')
            ->orderBy('payment_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($completedPayments as $completedPayment) {
            $remainingAmount = (float) $completedPayment->amount;

            foreach ($installments as $installment) {
                /** @var Installment $installment */
                if ($remainingAmount <= 0) {
                    break;
                }

                $installmentRemaining = (float) $installment->amount - (float) $installment->paid_amount;
                if ($installmentRemaining <= 0) {
                    continue;
                }

                $amountToApply = min($remainingAmount, $installmentRemaining);

                $newPaidAmount = round(((float) $installment->paid_amount + $amountToApply), 2);
                $installment->forceFill(['paid_amount' => $newPaidAmount]);
                if ($installment->paid_amount > 0 && !$installment->paid_date) {
                    $installment->paid_date = $completedPayment->payment_date;
                }

                $isOverdue = $installment->due_date && now()->startOfDay()->gt(\Carbon\Carbon::parse($installment->due_date)->startOfDay());

                if ((float) $installment->paid_amount >= (float) $installment->amount) {
                    $installment->status = 'paid';
                } elseif ($isOverdue) {
                    $installment->status = 'overdue';
                } elseif ((float) $installment->paid_amount > 0) {
                    $installment->status = 'partial';
                } else {
                    $installment->status = 'pending';
                }

                $installment->save();
                $remainingAmount -= $amountToApply;
            }
        }

        foreach ($installmentEnrollments as $enrollment) {
            $enrollment->refresh();

            $upfrontAmount = $enrollment->getCalculatedUpfrontAmount();
            $totalPaidFromInstallments = (float) $enrollment->installments()->sum('paid_amount');
            $effectivePaid = min($enrollment->getNetAmount(), $upfrontAmount + $totalPaidFromInstallments);

            Enrollment::whereKey($enrollment->id)->update([
                'paid_amount' => $effectivePaid,
            ]);
        }

        \Log::info('Student installments recalculated from aggregate payments', [
            'student_id' => $studentId,
            'enrollments_count' => $studentEnrollments->count(),
            'installment_enrollments_count' => $installmentEnrollments->count(),
            'installments_count' => $installments->count(),
            'completed_payments_count' => $completedPayments->count(),
        ]);
    }
}
