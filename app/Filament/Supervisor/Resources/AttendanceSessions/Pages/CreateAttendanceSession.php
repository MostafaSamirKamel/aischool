<?php

namespace App\Filament\Supervisor\Resources\AttendanceSessions\Pages;

use App\Filament\Supervisor\Resources\AttendanceSessions\AttendanceSessionResource;
use App\Models\AttendanceSession;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceSession extends CreateRecord
{
    protected static string $resource = AttendanceSessionResource::class;

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
