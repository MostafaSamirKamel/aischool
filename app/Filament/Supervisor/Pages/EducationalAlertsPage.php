<?php

namespace App\Filament\Supervisor\Pages;

use App\Services\EducationalSupervisionService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class EducationalAlertsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected string $view = 'filament.supervisor.pages.educational-alerts';

    protected static ?string $slug = 'educational-alerts';

    protected static ?string $navigationLabel = 'التنبيهات التعليمية الذكية';

    protected static ?string $title = 'مركز التنبيهات التعليمية والأكاديمية';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'الإشراف التعليمي';
    }

    public Collection $alerts;

    public function mount(): void
    {
        $this->loadAlerts();
    }

    public function loadAlerts(): void
    {
        $service = app(EducationalSupervisionService::class);
        $this->alerts = $service->getEducationalAlerts(auth()->user());
    }
}
