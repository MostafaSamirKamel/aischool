<?php

namespace App\Filament\Admin\Resources\Grades\Pages;

use App\Filament\Admin\Resources\Grades\GradeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditGrade extends EditRecord
{
    protected static string $resource = GradeResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $user = auth()->user();

        // Instructor: يمكنه تعديل درجات طلاب مجموعاته فقط
        if ($user->hasRole('instructor') && $record->enrollment?->group?->instructor_id !== $user->id) {
            Notification::make()
                ->title(__('filament/admin/edit_grade.غيرمصرح'))
                ->body('يمكنك فقط تعديل درجات طلاب مجموعاتك')
                ->danger()
                ->send();

            $this->redirect(GradeResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        // المستخدم الرئيسي لا يمكنه حذف الدرجات
        if ($user->is_super_admin) {
            return [];
        }

        // Instructor لا يمكنه حذف الدرجات
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
        return __('filament/admin/edit_grade.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_grade.title');
    }

}
