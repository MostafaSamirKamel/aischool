<?php

namespace App\Filament\Admin\Resources\VaultTransfers;

use App\Filament\Admin\Resources\VaultTransfers\Pages\CreateVaultTransfer;
use App\Filament\Admin\Resources\VaultTransfers\Pages\EditVaultTransfer;
use App\Filament\Admin\Resources\VaultTransfers\Pages\ListVaultTransfers;
use App\Filament\Admin\Resources\VaultTransfers\Pages\ViewVaultTransfer;
use App\Filament\Admin\Resources\VaultTransfers\Schemas\VaultTransferForm;
use App\Filament\Admin\Resources\VaultTransfers\Tables\VaultTransfersTable;
use App\Models\VaultTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class VaultTransferResource extends Resource
{
    protected static ?string $model = VaultTransfer::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return VaultTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VaultTransfersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVaultTransfers::route('/'),
            'create' => CreateVaultTransfer::route('/create'),
            'view' => ViewVaultTransfer::route('/{record}'),
            'edit' => EditVaultTransfer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/vault_transfer_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/vault_transfer_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/vault_transfer_resource.plural_model_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament/admin/vault_transfer_resource.navigation_group');
    }
}
