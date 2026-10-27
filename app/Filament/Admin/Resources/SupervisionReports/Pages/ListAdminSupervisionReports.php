<?php

namespace App\Filament\Admin\Resources\SupervisionReports\Pages;

use App\Filament\Admin\Resources\SupervisionReports\AdminSupervisionReportResource;
use Filament\Resources\Pages\ListRecords;

class ListAdminSupervisionReports extends ListRecords
{
    protected static string $resource = AdminSupervisionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('supervision_reports_center')
                ->label('مركز تقارير الإشراف')
                ->icon('heroicon-o-presentation-chart-line')
                ->color('gray')
                ->url('/admin/reporting-center-page?section=supervision'),
        ];
    }
}
