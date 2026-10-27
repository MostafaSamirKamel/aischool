<?php

namespace App\Filament\Admin\Resources\Nationalities;

use App\Filament\Admin\Resources\Nationalities\Pages\CreateNationality;
use App\Filament\Admin\Resources\Nationalities\Pages\EditNationality;
use App\Filament\Admin\Resources\Nationalities\Pages\ListNationalities;
use App\Filament\Admin\Resources\Nationalities\Schemas\NationalityForm;
use App\Filament\Admin\Resources\Nationalities\Tables\NationalitiesTable;
use App\Models\Nationality;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NationalityResource extends Resource
{
    protected static ?string $model = Nationality::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected static ?string $recordTitleAttribute = 'Nationality';

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
        return 'info';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('Create:Nationality');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('Update:Nationality');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('Delete:Nationality');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('Delete:Nationality');
    }

    public static function getEloquentQuery(): Builder
    {
        // الجنسيات عامة - الجميع يراها
        return parent::getEloquentQuery();
    }

    public static function form(Schema $schema): Schema
    {
        return NationalityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NationalitiesTable::configure($table);
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
            'index' => ListNationalities::route('/'),
            'create' => CreateNationality::route('/create'),
            'edit' => EditNationality::route('/{record}/edit'),
        ];
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/admin/nationality_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/admin/nationality_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/nationality_resource.plural_model_label');
    }



}
