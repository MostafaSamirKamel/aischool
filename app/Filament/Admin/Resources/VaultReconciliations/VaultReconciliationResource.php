<?php

namespace App\Filament\Admin\Resources\VaultReconciliations;

use App\Filament\Admin\Resources\VaultReconciliations\Pages\CreateVaultReconciliation;
use App\Filament\Admin\Resources\VaultReconciliations\Pages\EditVaultReconciliation;
use App\Filament\Admin\Resources\VaultReconciliations\Pages\ListVaultReconciliations;
use App\Filament\Admin\Resources\VaultReconciliations\Schemas\VaultReconciliationForm;
use App\Filament\Admin\Resources\VaultReconciliations\Tables\VaultReconciliationsTable;
use App\Models\VaultReconciliation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VaultReconciliationResource extends Resource
{
    protected static ?string $model = VaultReconciliation::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'reconciliation_date';

    public static function getNavigationGroup(): string
    {
        return 'الإدارة المالية';
    }

    protected static ?int $navigationSort = 21;

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
        return 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return VaultReconciliationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VaultReconciliationsTable::configure($table);
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
            'index' => ListVaultReconciliations::route('/'),
            'create' => CreateVaultReconciliation::route('/create'),
            'edit' => EditVaultReconciliation::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/admin/vault_reconciliation_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/admin/vault_reconciliation_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/vault_reconciliation_resource.plural_model_label');
    }



}
