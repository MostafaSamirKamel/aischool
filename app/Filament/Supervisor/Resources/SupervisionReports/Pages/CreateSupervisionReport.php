<?php

namespace App\Filament\Supervisor\Resources\SupervisionReports\Pages;

use App\Filament\Supervisor\Resources\SupervisionReports\SupervisionReportResource;
use App\Models\SupervisionReport;
use Filament\Resources\Pages\CreateRecord;

class CreateSupervisionReport extends CreateRecord
{
    protected static string $resource = SupervisionReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $data['prepared_by'] = $user->id;
        $data['tenant_id'] = $user->tenant_id ?? 1;
        $data['status'] = SupervisionReport::STATUS_DRAFT;
        $data['version'] = 1;
        $data['metrics_snapshot'] = $data['metrics_snapshot'] ?? [
            'scheduled_sessions' => 0,
            'completed_sessions' => 0,
            'missing_attendance' => 0,
            'confirmed_absences' => 0,
            'homework_awaiting_grading' => 0,
            'new_observations_count' => 0,
            'completed_observations_count' => 0,
        ];

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
