<?php

namespace App\Filament\Instructor\Resources\Exams;

use App\Filament\Instructor\Resources\Exams\Pages\CreateExam;
use App\Filament\Instructor\Resources\Exams\Pages\EditExam;
use App\Filament\Instructor\Resources\Exams\Pages\ListExams;
use App\Filament\Instructor\Resources\Exams\RelationManagers;
use App\Filament\Instructor\Resources\Exams\Schemas\ExamForm;
use App\Filament\Instructor\Resources\Exams\Tables\ExamsTable;
use App\Models\Exam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string
    {
        return __('filament/instructor/navigation.content_management');
    }

    public static function getNavigationBadge(): ?string
    {
        $instructorId = auth()->user()->instructor?->id;
        if (!$instructorId) {
            return '0';
        }

        return (string) static::getModel()::query()
            ->where('instructor_id', $instructorId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getEloquentQuery(): Builder
    {
        // المدرس يرى امتحاناته فقط
        $user = auth()->user();

        // الحصول على instructor_id من علاقة User
        $instructorId = $user->instructor?->id;

        if (!$instructorId) {
            // إذا لم يكن له instructor profile، نرجع query فاضي
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('instructor_id', $instructorId)
            ->where('tenant_id', $user->tenant_id);
    }

    public static function form(Schema $schema): Schema
    {
        return ExamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
            RelationManagers\AttemptsRelationManager::class,
            RelationManagers\GradesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExams::route('/'),
            'create' => CreateExam::route('/create'),
            'edit' => EditExam::route('/{record}/edit'),
            'view' => Pages\ViewExam::route('/{record}'),
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
        return __('filament/instructor/exam_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/instructor/exam_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/instructor/exam_resource.plural_model_label');
    }



}
