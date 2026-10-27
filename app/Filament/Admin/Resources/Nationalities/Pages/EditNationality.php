<?php

namespace App\Filament\Admin\Resources\Nationalities\Pages;

use App\Filament\Admin\Resources\Nationalities\NationalityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditNationality extends EditRecord
{
    protected static string $resource = NationalityResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/edit_nationality.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(NationalityResource::getUrl('index'));
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_nationality.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_nationality.title');
    }

}
