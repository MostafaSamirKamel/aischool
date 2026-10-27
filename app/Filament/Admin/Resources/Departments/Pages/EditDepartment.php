<?php

namespace App\Filament\Admin\Resources\Departments\Pages;

use App\Filament\Admin\Resources\Departments\DepartmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/edit_department.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(DepartmentResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->is_super_admin) {
            return [];
        }

        return [
            DeleteAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_department.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_department.title');
    }

}
