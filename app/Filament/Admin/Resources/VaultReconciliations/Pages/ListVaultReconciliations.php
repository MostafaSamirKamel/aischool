<?php

namespace App\Filament\Admin\Resources\VaultReconciliations\Pages;

use App\Filament\Admin\Resources\VaultReconciliations\VaultReconciliationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVaultReconciliations extends ListRecords
{
    protected static string $resource = VaultReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_vault_reconciliations.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_vault_reconciliations.title');
    }

}
