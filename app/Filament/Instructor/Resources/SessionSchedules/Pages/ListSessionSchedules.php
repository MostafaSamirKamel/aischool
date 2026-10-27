<?php

namespace App\Filament\Instructor\Resources\SessionSchedules\Pages;

use App\Filament\Instructor\Resources\SessionSchedules\SessionScheduleResource;
use Filament\Resources\Pages\ListRecords;

class ListSessionSchedules extends ListRecords
{
    protected static string $resource = SessionScheduleResource::class;

    public function getTitle(): string
    {
        return __('filament/instructor/list_session_schedules.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label(__('filament/instructor/list_session_schedules.create_new')),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/list_session_schedules.title');
    }

}
