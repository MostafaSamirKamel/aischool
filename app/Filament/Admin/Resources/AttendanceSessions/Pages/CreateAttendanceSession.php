<?php

namespace App\Filament\Admin\Resources\AttendanceSessions\Pages;

use App\Filament\Admin\Resources\AttendanceSessions\AttendanceSessionResource;
use App\Models\AttendanceSession;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceSession extends CreateRecord
{
    protected static string $resource = AttendanceSessionResource::class;

    public function getTitle(): string
    {
        return __('filament/admin/create_attendance_session.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_attendance_session.title');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $groupId = $data['group_id'] ?? null;
        $date = $data['attendance_date'] ?? null;

        if ($groupId && $date) {
            $exists = AttendanceSession::withoutGlobalScopes()
                ->where('group_id', $groupId)
                ->whereDate('attendance_date', $date)
                ->exists();

            if ($exists) {
                Notification::make()
                    ->title('سيشن مكررة')
                    ->body('يوجد بالفعل سيشن حضور لهذه المجموعة في هذا التاريخ.')
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }
}
