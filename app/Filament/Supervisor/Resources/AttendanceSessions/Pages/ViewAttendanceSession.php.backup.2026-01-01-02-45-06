<?php

namespace App\Filament\Supervisor\Resources\AttendanceSessions\Pages;

use App\Filament\Supervisor\Resources\AttendanceSessions\AttendanceSessionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAttendanceSession extends ViewRecord
{
    protected static string $resource = AttendanceSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/supervisor/view_attendance_session.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/supervisor/view_attendance_session.title');
    }

}
