<?php

namespace App\Filament\Admin\Resources\Classrooms\Pages;

use App\Filament\Admin\Resources\Classrooms\ClassroomResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateClassroom extends CreateRecord
{
    protected static string $resource = ClassroomResource::class;

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_classroom.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(ClassroomResource::getUrl('index'));
        }
    }
    public function getTitle(): string
    {
        return __('filament/admin/create_classroom.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_classroom.title');
    }

}
