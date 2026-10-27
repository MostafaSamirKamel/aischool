<?php

namespace App\Filament\Instructor\Resources\Assignments;

use App\Filament\Instructor\Resources\Assignments\Pages\CreateAssignment;
use App\Filament\Instructor\Resources\Assignments\Pages\EditAssignment;
use App\Filament\Instructor\Resources\Assignments\Pages\ListAssignments;
use App\Filament\Instructor\Resources\Assignments\Pages\ManageAssignmentSubmissions;
use App\Filament\Instructor\Resources\Assignments\Schemas\AssignmentForm;
use App\Filament\Instructor\Resources\Assignments\Tables\AssignmentsTable;
use App\Models\Assignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $recordTitleAttribute = 'title';
    
    public static function getNavigationGroup(): string
    {
        return __('filament/instructor/navigation.content_management');
    }
    
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $instructorId = $user?->instructor?->id;

        if (!$instructorId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('instructor_id', $instructorId)
            ->where('tenant_id', $user->tenant_id);
    }

    public static function form(Schema $schema): Schema
    {
        return AssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssignmentsTable::configure($table);
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
            'index' => ListAssignments::route('/'),
            'create' => CreateAssignment::route('/create'),
            'edit' => EditAssignment::route('/{record}/edit'),
            'submissions' => ManageAssignmentSubmissions::route('/{record}/submissions'),
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
        return __('filament/instructor/assignment_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/instructor/assignment_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/instructor/assignment_resource.plural_model_label');
    }



}
