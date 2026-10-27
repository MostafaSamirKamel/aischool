<?php

namespace App\Filament\Admin\Resources\VaultTransfers\Pages;

use App\Filament\Admin\Resources\VaultTransfers\VaultTransferResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateVaultTransfer extends CreateRecord
{
    protected static string $resource = VaultTransferResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // التأكد من أن tenant_id موجود
        $data['tenant_id'] = auth()->user()->tenant_id;
        
        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('تم إنشاء التحويل بنجاح')
            ->body('يمكنك الآن اعتماد التحويل أو تعديله.')
            ->success()
            ->send();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء التحويل';
    }
}
