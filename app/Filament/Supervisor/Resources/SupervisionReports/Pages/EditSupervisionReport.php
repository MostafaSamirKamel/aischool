<?php

namespace App\Filament\Supervisor\Resources\SupervisionReports\Pages;

use App\Filament\Supervisor\Resources\SupervisionReports\SupervisionReportResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSupervisionReport extends EditRecord
{
    protected static string $resource = SupervisionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
