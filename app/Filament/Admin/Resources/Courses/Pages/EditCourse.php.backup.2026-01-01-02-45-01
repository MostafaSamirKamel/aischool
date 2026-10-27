<?php

namespace App\Filament\Admin\Resources\Courses\Pages;

use App\Filament\Admin\Resources\Courses\CourseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/edit_course.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(CourseResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->is_super_admin) {
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
        return __('filament/admin/edit_course.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_course.title');
    }

}
