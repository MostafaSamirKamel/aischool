<?php

namespace App\Filament\Instructor\Resources\LearningResources\Pages;

use App\Filament\Instructor\Resources\LearningResources\LearningResourceResource;
use App\Models\SessionSchedule;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningResource extends EditRecord
{
    protected static string $resource = LearningResourceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        $data['tenant_id'] = $user?->tenant_id;

        if (isset($data['instructor_id']) && (int) $data['instructor_id'] !== (int) ($user?->id ?? 0)) {
            $data['instructor_id'] = $user?->id;
        }

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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('filament/instructor/edit_learning_resource.حذف')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function getTitle(): string
    {
        return __('filament/instructor/edit_learning_resource.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/edit_learning_resource.title');
    }

}
