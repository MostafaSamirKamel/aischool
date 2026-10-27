<?php

namespace App\Filament\Admin\Resources\Payments\Pages;

use App\Filament\Admin\Resources\Payments\PaymentResource;
use App\Models\Enrollment;
use App\Models\Vault;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_payment.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(PaymentResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = auth()->user()->tenant_id;
        $data['tenant_id'] = $tenantId;

        // 1. Verify Enrollment existence and tenant isolation
        if (empty($data['enrollment_id'])) {
            throw ValidationException::withMessages([
                'student_id' => 'يرجى اختيار الطالب وتحديد التسجيل الدراسي المرتبط بالدفعة.',
            ]);
        }

        $enrollmentQuery = Enrollment::query();
        if (!empty($tenantId)) {
            $enrollmentQuery->where('tenant_id', $tenantId);
        }
        $enrollment = $enrollmentQuery->find($data['enrollment_id']);

        if (!$enrollment) {
            throw ValidationException::withMessages([
                'enrollment_id' => 'التسجيل المحدد غير موجود أو لا ينتمي لنفس الفرع.',
            ]);
        }

        // 2. Validate Payment Amount
        $amount = round((float) ($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'مبلغ الدفع يجب أن يكون أكبر من الصفر.',
            ]);
        }

        // 3. Prevent Overpayment (Revalidate total remaining debt on database)
        $studentId = $enrollment->student_id;
        $studentEnrollments = Enrollment::where('student_id', $studentId)
            ->where('status', 'active')
            ->get();

        $totalRemainingDebt = 0.0;
        foreach ($studentEnrollments as $enr) {
            $totalRemainingDebt += $enr->getRemainingBalance();
        }

        if ($amount > round($totalRemainingDebt + 0.001, 2)) {
            throw ValidationException::withMessages([
                'amount' => 'المبلغ المدفوع (' . number_format($amount, 2) . ' ج.م) أكبر من إجمالي المتبقي على الطالب (' . number_format($totalRemainingDebt, 2) . ' ج.م). يمنع النظام التحصيل الزائد لضمان النزاهة المالية.',
            ]);
        }

        // 4. Validate Active Vault
        $vaultQuery = Vault::where('is_active', true);
        if (!empty($tenantId)) {
            $vaultQuery->where('tenant_id', $tenantId);
        }
        $vault = $vaultQuery->find($data['vault_id']);

        if (!$vault) {
            throw ValidationException::withMessages([
                'vault_id' => 'الخزنة المحددة غير صالحة أو غير نشطة أو لا تنتمي لنفس الفرع.',
            ]);
        }

        // 5. Automatically derive payment_method from vault type for audit/DB consistency
        $data['payment_method'] = match ($vault->type) {
            'cash' => 'cash',
            'vodafone', 'instapay', 'mobile_wallet', 'wallet' => 'mobile_wallet',
            'bank' => 'bank_transfer',
            default => 'cash',
        };

        // 6. Clean pseudo form fields
        unset($data['student_id']);
        unset($data['barcode']);
        unset($data['track_name']);
        unset($data['courses_total']);
        unset($data['enrollment_code']);
        unset($data['paid_upfront']);
        unset($data['total_discount']);
        unset($data['other_payments']);
        unset($data['total_paid']);
        unset($data['remaining_balance']);
        unset($data['payment_plan']);
        unset($data['payment_preview_info']);
        unset($data['installments_info']);
        unset($data['payment_history_info']);
        unset($data['courses_info']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // PaymentObserver handles synchronous installment allocation, vault posting, and debt recalculation.
        Notification::make()
            ->title('تم تسجيل الدفعة بنجاح')
            ->body('تم توزيع المبلغ تلقائياً على الأقساط بالأقدمية وتحديث حسابات الطالب والخزينة.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'إضافة دفعة طالب';
    }

    public static function getNavigationLabel(): string
    {
        return 'إضافة دفعة طالب';
    }
}
