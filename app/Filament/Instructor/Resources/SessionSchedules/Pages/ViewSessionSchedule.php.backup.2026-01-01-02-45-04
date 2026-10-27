<?php

namespace App\Filament\Instructor\Resources\SessionSchedules\Pages;

use App\Filament\Instructor\Resources\SessionSchedules\SessionScheduleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSessionSchedule extends ViewRecord
{
    protected static string $resource = SessionScheduleResource::class;

    public function getTitle(): string
    {
        return __('filament/instructor/view_session_schedule.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label(__('filament/instructor/view_session_schedule.تعديل'))
                ->visible(fn() => $this->record->status === 'scheduled' && $this->record->isUpcoming()),
        ];
    }

    public function getView(): string
    {
        return 'filament.instructor.pages.view-session-schedule';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/view_session_schedule.title');
    }

}
