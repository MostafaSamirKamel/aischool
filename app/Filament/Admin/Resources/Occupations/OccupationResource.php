<?php

namespace App\Filament\Admin\Resources\Occupations;

use App\Filament\Admin\Resources\Occupations\Pages\CreateOccupation;
use App\Filament\Admin\Resources\Occupations\Pages\EditOccupation;
use App\Filament\Admin\Resources\Occupations\Pages\ListOccupations;
use App\Filament\Admin\Resources\Occupations\Schemas\OccupationForm;
use App\Filament\Admin\Resources\Occupations\Tables\OccupationsTable;
use App\Models\Occupation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OccupationResource extends Resource
{
    protected static ?string $model = Occupation::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return __('filament/admin/navigation.basic_data');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/occupation_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/occupation_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/occupation_resource.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Super admin بدون tenant_id يرى جميع السجلات
        if ($user->is_super_admin && is_null($user->tenant_id)) {
            return parent::getEloquentQuery();
        }

        // جميع المستخدمين الآخرين يرون فقط سجلات tenant الخاص بهم
        return parent::getEloquentQuery()
            ->where('tenant_id', $user->tenant_id);
    }

    public static function form(Schema $schema): Schema
    {
        return OccupationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OccupationsTable::configure($table);
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
            'index' => ListOccupations::route('/'),
            'create' => CreateOccupation::route('/create'),
            'edit' => EditOccupation::route('/{record}/edit'),
        ];
    }
}
