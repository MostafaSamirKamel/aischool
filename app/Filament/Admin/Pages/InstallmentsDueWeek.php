<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Pages\Concerns\HandlesInstallmentDuePayments;
use App\Services\InstallmentDueReportAccess;
use App\Services\InstallmentDueReportService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class InstallmentsDueWeek extends Page
{
    use HasPageShield, HandlesInstallmentDuePayments;

    public function getTitle(): string
    {
        return 'أقساط الأسبوع';
    }

    public static function getNavigationLabel(): string
    {
        return 'أقساط الأسبوع';
    }

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected static \UnitEnum|string|null $navigationGroup = 'تقارير الأقساط';

    protected string $view = 'filament.admin.pages.installments-due-week';

    public static function canAccess(): bool
    {
        return InstallmentDueReportAccess::canViewReport(auth()->user());
    }

    public array $installmentsByDay = [];
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
        $this->installmentsByDay = app(InstallmentDueReportService::class)
            ->getWeekInstallmentsByDay($tenantId, $this->installmentFilter);
    }

    public function getInstallmentStats(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        return app(InstallmentDueReportService::class)->getWeekStats($tenantId);
    }

    public function refreshReportData(): void
    {
        $this->filterInstallments();
    }
}
