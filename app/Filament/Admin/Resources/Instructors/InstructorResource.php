<?php

namespace App\Filament\Admin\Resources\Instructors;

use App\Filament\Admin\Resources\Instructors\Pages\CreateInstructor;
use App\Filament\Admin\Resources\Instructors\Pages\EditInstructor;
use App\Filament\Admin\Resources\Instructors\Pages\ListInstructors;
use App\Filament\Admin\Resources\Instructors\Schemas\InstructorForm;
use App\Filament\Admin\Resources\Instructors\Tables\InstructorsTable;
use App\Models\Instructor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class InstructorResource extends Resource
{
    protected static ?string $model = Instructor::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationGroup(): string
    {
        return __('filament/admin/navigation.human_resources');
    }

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'employee_code';

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
        return 'info';
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
        return InstructorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstructorsTable::configure($table);
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
            'index' => ListInstructors::route('/'),
            'create' => CreateInstructor::route('/create'),
            'edit' => EditInstructor::route('/{record}/edit'),
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
        return __('filament/admin/instructor_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/admin/instructor_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/instructor_resource.plural_model_label');
    }



}
