<?php

namespace App\Filament\Supervisor\Resources\AttendanceSessions\Pages;

use App\Filament\Supervisor\Resources\AttendanceSessions\AttendanceSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceSession extends EditRecord
{
    protected static string $resource = AttendanceSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/supervisor/edit_attendance_session.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/supervisor/edit_attendance_session.title');
    }

}
