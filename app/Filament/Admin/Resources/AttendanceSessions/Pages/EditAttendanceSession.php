<?php

namespace App\Filament\Admin\Resources\AttendanceSessions\Pages;

use App\Filament\Admin\Resources\AttendanceSessions\AttendanceSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAttendanceSession extends EditRecord
{
    protected static string $resource = AttendanceSessionResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $user = auth()->user();

        // Instructor: يمكنه تعديل حضور مجموعاته فقط
        if ($user->hasRole('instructor') && $record->group?->instructor_id !== $user->id) {
            Notification::make()
                ->title(__('filament/admin/edit_attendance_session.غيرمصرح'))
                ->body('يمكنك فقط تعديل حضور مجموعاتك')
                ->danger()
                ->send();

            $this->redirect(AttendanceSessionResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        // المستخدم الرئيسي لا يمكنه حذف جلسات الحضور
        if ($user->is_super_admin) {
            return [];
        }

        // Instructor لا يمكنه حذف جلسات الحضور
        if ($user->hasRole('instructor')) {
            return [];
        }

        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_attendance_session.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_attendance_session.title');
    }

}
