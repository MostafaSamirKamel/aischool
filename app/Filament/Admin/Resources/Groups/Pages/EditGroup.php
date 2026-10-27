<?php

namespace App\Filament\Admin\Resources\Groups\Pages;

use App\Filament\Admin\Resources\Groups\GroupResource;
use App\Services\GroupScheduleConflictValidator;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $user = auth()->user();

        if ($user->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/edit_group.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(GroupResource::getUrl('index'));
        }

        // Instructor: يمكنه فقط عرض مجموعاته (لا يمكنه التعديل)
        if ($user->hasRole('instructor')) {
            Notification::make()
                ->title(__('filament/admin/edit_group.غيرمصرح'))
                ->body('المدرسين لا يمكنهم تعديل بيانات المجموعات')
                ->danger()
                ->send();

            $this->redirect(GroupResource::getUrl('index'));
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
        return __('filament/admin/edit_group.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_group.title');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            app(GroupScheduleConflictValidator::class)->ensureNoConflicts($data, $this->record);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->join(' ');

            Notification::make()
                ->title('لا يمكن تحديث المجموعة بسبب تعارض في الجدول')
                ->body($message ?: 'يوجد تعارض في مواعيد القاعة أو المدرس.')
                ->danger()
                ->persistent()
                ->send();

            throw $exception;
        }

        return $data;
    }

}
