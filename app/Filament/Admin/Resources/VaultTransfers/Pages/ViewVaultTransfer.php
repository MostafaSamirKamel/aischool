<?php

namespace App\Filament\Admin\Resources\VaultTransfers\Pages;

use App\Filament\Admin\Resources\VaultTransfers\VaultTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVaultTransfer extends ViewRecord
{
    protected static string $resource = VaultTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->canBeEdited()),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->canBeDeleted()),
        ];
    }
}
