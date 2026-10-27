<?php

namespace App\Filament\Admin\Resources\VaultTransfers\Pages;

use App\Filament\Admin\Resources\VaultTransfers\VaultTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditVaultTransfer extends EditRecord
{
    protected static string $resource = VaultTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->canBeDeleted()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        if (!$this->record->canBeEdited()) {
            Notification::make()
                ->title('خطأ')
                ->body('لا يمكن تعديل هذا التحويل')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث التحويل';
    }
}
