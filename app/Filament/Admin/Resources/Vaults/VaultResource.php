<?php

namespace App\Filament\Admin\Resources\Vaults;

use App\Filament\Admin\Resources\Vaults\Pages\CreateVault;
use App\Filament\Admin\Resources\Vaults\Pages\EditVault;
use App\Filament\Admin\Resources\Vaults\Pages\ListVaults;
use App\Filament\Admin\Resources\Vaults\Pages\VaultTransactions;
use App\Filament\Admin\Resources\Vaults\Schemas\VaultForm;
use App\Filament\Admin\Resources\Vaults\Tables\VaultsTable;
use App\Models\Vault;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VaultResource extends Resource
{
    protected static ?string $model = Vault::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return 'الإدارة المالية';
    }

    protected static ?int $navigationSort = 20;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $query = static::getModel()::query();

        if (!($user->is_super_admin && is_null($user->tenant_id))) {
            $query->where('tenant_id', $user->tenant_id);
        }

        return (string) $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return VaultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VaultsTable::configure($table);
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
            'index' => ListVaults::route('/'),
            'create' => CreateVault::route('/create'),
            'edit' => EditVault::route('/{record}/edit'),
            'transactions' => VaultTransactions::route('/transactions'),
        ];
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/admin/vault_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/admin/vault_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/vault_resource.plural_model_label');
    }



}
