<?php

namespace App\Filament\Instructor\Resources\InstructorExamAttemptResource;

use App\Filament\Instructor\Resources\InstructorExamAttemptResource\Pages\ListInstructorExamAttempts;
use App\Models\ExamAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class InstructorExamAttemptResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationGroup(): string
    {
        return __('filament/instructor/navigation.teaching');
    }

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $instructorId = $user?->instructor?->id;

        if (!$user || !$instructorId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // المدرس يرى فقط نتائج الامتحانات الخاصة به
        return parent::getEloquentQuery()
            ->whereHas('exam', function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor->id)
                      ->where('tenant_id', $user->tenant_id);
            })
            ->whereNotNull('completed_at')
            ->with(['exam', 'student.user']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.user.name')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.student.user.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('exam.title')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.exam.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn($record) => $record->exam->title),

                Tables\Columns\TextColumn::make('exam.course.name')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.exam.course.name'))
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('exam.group.name')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.exam.group.name'))
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('score')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.score'))
                    ->formatStateUsing(fn($record) => $record->score . ' / ' . $record->total_score)
                    ->badge()
                    ->color(fn($record) => $record->passed ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('percentage')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.percentage'))
                    ->formatStateUsing(fn($state) => number_format($state, 1) . '%')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn($state) => match(true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'info',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\IconColumn::make('passed')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.passed'))
                    ->boolean()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('time_taken')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.time_taken'))
                    ->formatStateUsing(fn($state) => floor($state / 60) . __('filament/instructor/instructor_exam_attempt_resource.minute_abbr'))
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.completed_at'))
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('passed')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.passed'))
                    ->placeholder(__('filament/instructor/instructor_exam_attempt_resource.all'))
                    ->trueLabel(__('filament/instructor/instructor_exam_attempt_resource.passed'))
                    ->falseLabel(__('filament/instructor/instructor_exam_attempt_resource.failed')),

                Tables\Filters\SelectFilter::make('exam_id')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.exam_id'))
                    ->relationship('exam', 'title', function ($query) {
                        $instructorId = auth()->user()?->instructor?->id;
                        if (!$instructorId) {
                            return $query->whereRaw('1 = 0');
                        }

                        return $query->where('instructor_id', $instructorId);
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('course')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.course'))
                    ->relationship('exam.course', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('group')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.group'))
                    ->relationship('exam.group', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('high_scores')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.high_scores'))
                    ->query(fn($query) => $query->where('percentage', '>=', 90)),

                Tables\Filters\Filter::make('failed')
                    ->label(__('filament/instructor/instructor_exam_attempt_resource.failed'))
                    ->query(fn($query) => $query->where('passed', false)),
            ])
            ->defaultSort('completed_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstructorExamAttempts::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $instructorId = $user?->instructor?->id;

        if (!$user || !$instructorId) {
            return null;
        }

        $todayCount = ExamAttempt::whereHas('exam', function ($query) {
            $query->where('instructor_id', auth()->user()->instructor->id);
            })
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', today())
            ->count();

        return $todayCount > 0 ? (string) $todayCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('filament/instructor/instructor_exam_attempt_resource.today_results');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/instructor_exam_attempt_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/instructor/instructor_exam_attempt_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/instructor/instructor_exam_attempt_resource.plural_model_label');
    }
}
