<?php

namespace App\Filament\Admin\Resources\Groups\Pages;

use App\Filament\Admin\Resources\Groups\GroupResource;
use App\Services\GroupScheduleConflictValidator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_group.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(GroupResource::getUrl('index'));
        }
    }
    public function getTitle(): string
    {
        return __('filament/admin/create_group.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_group.title');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            app(GroupScheduleConflictValidator::class)->ensureNoConflicts($data);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->join(' ');

            Notification::make()
                ->title('لا يمكن حفظ المجموعة بسبب تعارض في الجدول')
                ->body($message ?: 'يوجد تعارض في مواعيد القاعة أو المدرس.')
                ->danger()
                ->persistent()
                ->send();

            throw $exception;
        }

        return $data;
    }

}
