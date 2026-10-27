<?php

namespace App\Filament\Admin\Resources\Vaults\Pages;

use App\Filament\Admin\Resources\Vaults\VaultResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateVault extends CreateRecord
{
    protected static string $resource = VaultResource::class;

    public function mount(): void
    {
        parent::mount();

        // منع الأدمن الرئيسي من الإنشاء
        if (auth()->user()->is_super_admin && is_null(auth()->user()->tenant_id)) {
            Notification::make()
                ->title(__('filament/admin/create_vault.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء خزائن')
                ->danger()
                ->send();

            $this->redirect(VaultResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // إضافة tenant_id تلقائياً
        $data['tenant_id'] = auth()->user()->tenant_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function getTitle(): string
    {
        return __('filament/admin/create_vault.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_vault.title');
    }

}
