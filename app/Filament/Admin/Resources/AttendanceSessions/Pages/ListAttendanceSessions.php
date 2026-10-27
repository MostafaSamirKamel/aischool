<?php

namespace App\Filament\Admin\Resources\AttendanceSessions\Pages;

use App\Filament\Admin\Resources\AttendanceSessions\AttendanceSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAttendanceSessions extends ListRecords
{
    protected static string $resource = AttendanceSessionResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه إضافة سجلات حضور
        if (!$user->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getTableQuery();

        // Instructor: يرى حضور مجموعاته فقط
        if ($user->hasRole('instructor')) {
            return $query->whereHas('group', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        }

        return $query;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_attendance_sessions.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_attendance_sessions.title');
    }

}
