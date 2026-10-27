<?php

namespace App\Filament\Instructor\Resources\LearningResources;

use App\Filament\Instructor\Resources\LearningResources\Pages\CreateLearningResource;
use App\Filament\Instructor\Resources\LearningResources\Pages\EditLearningResource;
use App\Filament\Instructor\Resources\LearningResources\Pages\ListLearningResources;
use App\Filament\Instructor\Resources\LearningResources\Schemas\LearningResourceForm;
use App\Filament\Instructor\Resources\LearningResources\Tables\LearningResourcesTable;
use App\Models\LearningResource;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LearningResourceResource extends Resource
{
    protected static ?string $model = LearningResource::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder-arrow-down';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-folder-arrow-down';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المجموعات';
    }

    protected static ?int $navigationSort = 30;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (!$user?->id || !$user?->tenant_id) {
            return '0';
        }

        $ownerIds = array_values(array_unique(array_filter([
            (int) $user->id,
            (int) ($user->instructor?->id ?? 0),
        ])));
        
        return static::getModel()::whereIn('instructor_id', $ownerIds)
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->count();
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if (!$user?->id || !$user?->tenant_id) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        $ownerIds = array_values(array_unique(array_filter([
            (int) $user->id,
            (int) ($user->instructor?->id ?? 0),
        ])));
        
        return parent::getEloquentQuery()
            ->whereIn('instructor_id', $ownerIds)
            ->where('tenant_id', $user->tenant_id);
    }

    public static function form(Schema $schema): Schema
    {
        return LearningResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LearningResourcesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLearningResources::route('/'),
            'create' => CreateLearningResource::route('/create'),
            'edit' => EditLearningResource::route('/{record}/edit'),
        ];
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/learning_resource_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/instructor/learning_resource_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/instructor/learning_resource_resource.plural_model_label');
    }



}
