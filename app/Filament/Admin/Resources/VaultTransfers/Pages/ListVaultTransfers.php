<?php

namespace App\Filament\Admin\Resources\VaultTransfers\Pages;

use App\Filament\Admin\Resources\VaultTransfers\VaultTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVaultTransfers extends ListRecords
{
    protected static string $resource = VaultTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('تحويل جديد'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // يمكن إضافة widgets هنا
        ];
    }
}
