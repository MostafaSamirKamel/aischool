<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Pages\Concerns\HandlesInstallmentDuePayments;
use App\Services\InstallmentDueReportAccess;
use App\Services\InstallmentDueReportService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class InstallmentsDueMonth extends Page
{
    use HasPageShield, HandlesInstallmentDuePayments;

    public function getTitle(): string
    {
        return 'أقساط الشهر';
    }

    public static function getNavigationLabel(): string
    {
        return 'أقساط الشهر';
    }

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected static \UnitEnum|string|null $navigationGroup = 'تقارير الأقساط';

    protected string $view = 'filament.admin.pages.installments-due-month';

    public static function canAccess(): bool
    {
        return InstallmentDueReportAccess::canViewReport(auth()->user());
    }

    public $installments;
    public string $installmentFilter = 'all'; // all, overdue, today, upcoming

    public function mount(): void
    {
        $this->paymentDate = now()->format('Y-m-d');
        $this->filterInstallments();
    }

    public function updated($property): void
    {
        if ($property === 'installmentFilter') {
            $this->filterInstallments();
        }
    }

    public function updatedInstallmentFilter(): void
    {
        $this->filterInstallments();
    }

    public function filterInstallments(): void
    {
        $tenantId = auth()->user()?->tenant_id;
        $this->installments = app(InstallmentDueReportService::class)
            ->getMonthInstallments($tenantId, $this->installmentFilter);
    }

    public function getInstallmentStats(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        return app(InstallmentDueReportService::class)->getMonthStats($tenantId);
    }

    public function refreshReportData(): void
    {
        $this->filterInstallments();
    }
}
