<?php

namespace App\Filament\Supervisor\Resources\InstructorEvaluations\Pages;

use App\Filament\Supervisor\Resources\InstructorEvaluations\InstructorEvaluationResource;
use App\Models\Instructor;
use App\Models\InstructorEvaluation;
use Filament\Resources\Pages\CreateRecord;

class CreateInstructorEvaluation extends CreateRecord
{
    protected static string $resource = InstructorEvaluationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $data['supervisor_id'] = $user->id;
        
        if (empty($data['tenant_id'])) {
            $instructor = Instructor::find($data['instructor_id'] ?? 0);
            $data['tenant_id'] = $user->tenant_id ?? $instructor?->tenant_id ?? 1;
        }

        $data['status'] = InstructorEvaluation::STATUS_DRAFT;
        $data['version'] = 1;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
