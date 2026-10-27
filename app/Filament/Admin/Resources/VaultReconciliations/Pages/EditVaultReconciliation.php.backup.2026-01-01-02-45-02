<?php

namespace App\Filament\Admin\Resources\VaultReconciliations\Pages;

use App\Filament\Admin\Resources\VaultReconciliations\VaultReconciliationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditVaultReconciliation extends EditRecord
{
    protected static string $resource = VaultReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_vault_reconciliation.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_vault_reconciliation.title');
    }

}
