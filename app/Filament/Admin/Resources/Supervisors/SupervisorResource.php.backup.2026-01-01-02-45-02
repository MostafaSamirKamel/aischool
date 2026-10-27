<?php

namespace App\Filament\Admin\Resources\Supervisors;

use BackedEnum;
use App\Models\Supervisor;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\Supervisors\Pages\EditSupervisor;
use App\Filament\Admin\Resources\Supervisors\Pages\ListSupervisors;
use App\Filament\Admin\Resources\Supervisors\Pages\CreateSupervisor;
use App\Filament\Admin\Resources\Supervisors\Schemas\SupervisorForm;
use App\Filament\Admin\Resources\Supervisors\Tables\SupervisorsTable;

class SupervisorResource extends Resource
{
    protected static ?string $model = Supervisor::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $recordTitleAttribute = 'user.name';

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
        return 'الموارد البشرية';
    }

    public static function form(Schema $schema): Schema
    {
        return SupervisorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupervisorsTable::configure($table);
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
            'index' => ListSupervisors::route('/'),
            'create' => CreateSupervisor::route('/create'),
            'edit' => EditSupervisor::route('/{record}/edit'),
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
        return __('filament/admin/supervisor_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/admin/supervisor_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/supervisor_resource.plural_model_label');
    }
}
