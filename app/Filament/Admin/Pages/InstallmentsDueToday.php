<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Pages\Concerns\HandlesInstallmentDuePayments;
use App\Services\InstallmentDueReportAccess;
use App\Services\InstallmentDueReportService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class InstallmentsDueToday extends Page
{
    use HasPageShield, HandlesInstallmentDuePayments;

    public function getTitle(): string
    {
        return 'أقساط اليوم';
    }

    public static function getNavigationLabel(): string
    {
        return 'أقساط اليوم';
    }

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected static \UnitEnum|string|null $navigationGroup = 'تقارير الأقساط';

    protected string $view = 'filament.admin.pages.installments-due-today';

    public static function canAccess(): bool
    {
        return InstallmentDueReportAccess::canViewReport(auth()->user());
    }

    public $installments;

    public function mount(): void
    {
        $this->loadInstallments();
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function loadInstallments(): void
    {
        $tenantId = auth()->user()?->tenant_id;
        $this->installments = app(InstallmentDueReportService::class)->getTodayInstallments($tenantId);
    }

    public function refreshReportData(): void
    {
        $this->loadInstallments();
    }
}
