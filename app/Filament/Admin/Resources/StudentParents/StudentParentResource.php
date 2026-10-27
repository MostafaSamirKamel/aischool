<?php

namespace App\Filament\Admin\Resources\StudentParents;

use App\Filament\Admin\Resources\StudentParents\Pages\CreateStudentParent;
use App\Filament\Admin\Resources\StudentParents\Pages\EditStudentParent;
use App\Filament\Admin\Resources\StudentParents\Pages\ListStudentParents;
use App\Filament\Admin\Resources\StudentParents\Schemas\StudentParentForm;
use App\Filament\Admin\Resources\StudentParents\Tables\StudentParentsTable;
use App\Models\StudentParent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentParentResource extends Resource
{
    protected static ?string $model = StudentParent::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): string
    {
        return 'الموارد البشرية';
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $query = static::getModel()::query();

        if (!($user->is_super_admin && is_null($user->tenant_id))) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            });
        }

        return (string) $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        // Super admin بدون tenant_id يرى جميع السجلات
        if ($user->is_super_admin && is_null($user->tenant_id)) {
            return parent::getEloquentQuery();
        }

        // فلترة بناءً على tenant_id للطلاب المرتبطين
        return parent::getEloquentQuery()
            ->whereHas('student', function ($query) use ($user) {
                $query->where('tenant_id', $user->tenant_id);
            });
    }

    public static function form(Schema $schema): Schema
    {
        return StudentParentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentParentsTable::configure($table);
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
            'index' => ListStudentParents::route('/'),
            'create' => CreateStudentParent::route('/create'),
            'edit' => EditStudentParent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/student_parent_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/admin/student_parent_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/student_parent_resource.plural_model_label');
    }
}
