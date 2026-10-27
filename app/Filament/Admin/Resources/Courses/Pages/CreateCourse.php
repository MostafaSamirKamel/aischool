<?php

namespace App\Filament\Admin\Resources\Courses\Pages;

use App\Filament\Admin\Resources\Courses\CourseResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_course.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(CourseResource::getUrl('index'));
        }
    }
    public function getTitle(): string
    {
        return __('filament/admin/create_course.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_course.title');
    }

}
