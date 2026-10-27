<?php

namespace App\Filament\Admin\Resources\Exams;

use App\Filament\Admin\Resources\Exams\Pages\CreateExam;
use App\Filament\Admin\Resources\Exams\Pages\EditExam;
use App\Filament\Admin\Resources\Exams\Pages\ListExams;
use App\Filament\Admin\Resources\Exams\Schemas\ExamForm;
use App\Filament\Admin\Resources\Exams\Tables\ExamsTable;
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

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string
    {
        return 'الامتحانات والتقييم';
    }

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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExams::route('/'),
            'create' => CreateExam::route('/create'),
            'edit' => EditExam::route('/{record}/edit'),
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
        return __('filament/admin/exam_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/admin/exam_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/exam_resource.plural_model_label');
    }



}
