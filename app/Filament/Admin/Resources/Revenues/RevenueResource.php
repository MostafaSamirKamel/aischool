<?php

namespace App\Filament\Admin\Resources\Revenues;

use App\Filament\Admin\Resources\Revenues\Pages\CreateRevenue;
use App\Filament\Admin\Resources\Revenues\Pages\EditRevenue;
use App\Filament\Admin\Resources\Revenues\Pages\ListRevenues;
use App\Filament\Admin\Resources\Revenues\Schemas\RevenueForm;
use App\Filament\Admin\Resources\Revenues\Tables\RevenuesTable;
use App\Models\Revenue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RevenueResource extends Resource
{
    protected static ?string $model = Revenue::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = 4;

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
        return 'success';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament/admin/revenue_resource.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/revenue_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/revenue_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/revenue_resource.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return RevenueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RevenuesTable::configure($table);
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
            'index' => ListRevenues::route('/'),
            'create' => CreateRevenue::route('/create'),
            'edit' => EditRevenue::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        // Super admin بدون tenant_id يرى جميع السجلات
        if ($user->is_super_admin && is_null($user->tenant_id)) {
            return $query;
        }

        // جميع المستخدمين الآخرين يرون فقط سجلات tenant الخاص بهم
        return $query->where('tenant_id', $user->tenant_id);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
