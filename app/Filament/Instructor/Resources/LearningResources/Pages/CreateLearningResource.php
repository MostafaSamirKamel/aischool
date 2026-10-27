<?php

namespace App\Filament\Instructor\Resources\LearningResources\Pages;

use App\Filament\Instructor\Resources\LearningResources\LearningResourceResource;
use App\Models\SessionSchedule;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningResource extends CreateRecord
{
    protected static string $resource = LearningResourceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['tenant_id'] = $user?->tenant_id;
        $data['instructor_id'] = $user?->id;

        if (!empty($data['session_schedule_id']) && !empty($data['group_id'])) {
            $isSessionInGroup = SessionSchedule::query()
                ->where('id', $data['session_schedule_id'])
                ->where('group_id', $data['group_id'])
                ->exists();

            if (!$isSessionInGroup) {
                $data['session_schedule_id'] = null;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
