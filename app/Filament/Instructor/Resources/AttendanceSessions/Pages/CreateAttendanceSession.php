<?php

namespace App\Filament\Instructor\Resources\AttendanceSessions\Pages;

use App\Filament\Instructor\Resources\AttendanceSessions\AttendanceSessionResource;
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

        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['recorded_by'] = auth()->id();
        
        // Get course_id and department_id from group
        if (isset($data['group_id'])) {
            $group = \App\Models\Group::find($data['group_id']);
            $data['course_id'] = $group?->course_id;
            $data['department_id'] = $group?->course?->department_id;
        }
        
        return $data;
    }
}
