<?php

namespace App\Filament\Admin\Pages\Concerns;

use App\Models\Enrollment;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Vault;
use App\Services\InstallmentDueReportAccess;
use App\Services\InstallmentDueReportService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HandlesInstallmentDuePayments
{
    public bool $showPaymentModal = false;
    public ?Installment $selectedInstallment = null;
    public ?string $paymentAmount = null;
    public ?string $paymentDate = null;
    public ?int $vaultId = null;
    public ?string $receiptNumber = null;
    public ?string $paymentNotes = null;
    public bool $isProcessingPayment = false;

    /**
     * Check if current user has permission to collect installment payments.
     */
    public function canCollectPayment(): bool
    {
        return InstallmentDueReportAccess::canCollectPayment(auth()->user());
    }

    /**
     * Open payment modal for an installment.
     */
    public function openPaymentModal(int|string $installmentId): void
    {
        if (!InstallmentDueReportAccess::canCollectPayment(auth()->user())) {
            $this->showPaymentModal = false;
            return;
        }

        $userTenantId = auth()->user()?->tenant_id;
        $installment = Installment::with(['enrollment.student.user'])->find($installmentId);

        if (!$installment) {
            $this->showPaymentModal = false;
            return;
        }

        // Cross-tenant protection
        if ($userTenantId && (int) $installment->tenant_id !== (int) $userTenantId) {
            $this->showPaymentModal = false;
            return;
        }

        // Exclude fully paid, cancelled, or waived installments
        if ($installment->isFullyPaid() || in_array($installment->status, ['cancelled', 'waived'])) {
            $this->showPaymentModal = false;
            return;
        }

        $this->selectedInstallment = $installment;
        $this->paymentAmount = (string) $installment->getRemainingAmount();
        $this->paymentDate = now()->format('Y-m-d');
        $this->showPaymentModal = true;
    }

    /**
     * Close payment modal and reset properties.
     */
    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->selectedInstallment = null;
        $this->paymentAmount = null;
        $this->vaultId = null;
        $this->paymentNotes = null;
    }

    /**
     * Execute payment collection via the authoritative Payment observer pipeline.
     */
    public function savePayment(): void
    {
        if (!InstallmentDueReportAccess::canCollectPayment(auth()->user())) {
            abort(403, 'غير مصرح لك بتحصيل الأقساط.');
        }

        if (!$this->selectedInstallment) {
            return;
        }

        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'min:0.01'],
            'vaultId' => ['required', 'integer', 'exists:vaults,id'],
        ]);

        DB::transaction(function () {
            /** @var Installment $installment */
            $installment = Installment::lockForUpdate()->findOrFail($this->selectedInstallment->id);

            // Double submission idempotency guard
            if ($installment->isFullyPaid() || in_array($installment->status, ['cancelled', 'waived'])) {
                $this->showPaymentModal = false;
                $this->selectedInstallment = null;
                return;
            }

            $amount = round((float) $this->paymentAmount, 2);

            Payment::create([
                'tenant_id' => $installment->tenant_id,
                'enrollment_id' => $installment->enrollment_id,
                'vault_id' => $this->vaultId,
                'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                'amount' => $amount,
                'payment_method' => 'cash',
                'payment_date' => $this->paymentDate ?: now()->toDateString(),
                'status' => 'completed',
                'received_by' => auth()->id(),
                'notes' => $this->paymentNotes,
            ]);

            $this->showPaymentModal = false;
            $this->selectedInstallment = null;
            $this->paymentAmount = null;
            $this->vaultId = null;
            $this->paymentNotes = null;

            $this->refreshReportData();

            Notification::make()
                ->title('تم تسجيل الدفعة بنجاح')
                ->success()
                ->send();
        });
    }

    /**
     * Get active vaults for dropdown.
     */
    public function getVaultsProperty(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        if (!$tenantId) {
            return [];
        }

        return app(InstallmentDueReportService::class)->getActiveVaults((int) $tenantId);
    }

    /**
     * Refresh report data on the active page after payment or filter changes.
     */
    abstract public function refreshReportData(): void;
}
