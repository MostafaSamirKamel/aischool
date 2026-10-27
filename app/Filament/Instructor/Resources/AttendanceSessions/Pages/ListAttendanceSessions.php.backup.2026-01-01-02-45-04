<?php

namespace App\Filament\Instructor\Resources\AttendanceSessions\Pages;

use App\Filament\Instructor\Resources\AttendanceSessions\AttendanceSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceSessions extends ListRecords
{
    protected static string $resource = AttendanceSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/instructor/list_attendance_sessions.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/list_attendance_sessions.title');
    }

}
