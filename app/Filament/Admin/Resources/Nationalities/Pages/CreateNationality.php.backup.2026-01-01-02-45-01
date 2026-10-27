<?php

namespace App\Filament\Admin\Resources\Nationalities\Pages;

use App\Filament\Admin\Resources\Nationalities\NationalityResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateNationality extends CreateRecord
{
    protected static string $resource = NationalityResource::class;

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_nationality.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(NationalityResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }
    public function getTitle(): string
    {
        return __('filament/admin/create_nationality.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_nationality.title');
    }

}
