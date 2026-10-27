<?php

namespace App\Filament\Instructor\Resources\InstructorEvaluations;

use App\Filament\Instructor\Resources\InstructorEvaluations\Pages\ListInstructorMyEvaluations;
use App\Filament\Instructor\Resources\InstructorEvaluations\Pages\ViewInstructorMyEvaluation;
use App\Models\Instructor;
use App\Models\InstructorEvaluation;
use Filament\Resources\Resource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstructorMyEvaluationResource extends Resource
{
    protected static ?string $model = InstructorEvaluation::class;

    protected static ?string $slug = 'my-evaluations';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'تقييماتي التعليمية الرسمية';

    protected static ?string $modelLabel = 'تقييم تعليمي';

    protected static ?string $pluralModelLabel = 'تقييماتي التعليمية الرسمية';

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return 'المتابعة الإشرافية';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $instructor = Instructor::where('user_id', $user->id)->first();
        $instructorId = $instructor?->id ?? 0;

        // CRITICAL RULE: Draft evaluations are STRICTLY invisible to instructors!
        return parent::getEloquentQuery()
            ->where('instructor_id', $instructorId)
            ->where('status', InstructorEvaluation::STATUS_PUBLISHED)
            ->with(['supervisor', 'publisher', 'appeals']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('evaluation_code')
                    ->label('كود التقييم')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('evaluation_period')
                    ->label('الفترة / الشهر')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('evaluation_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        InstructorEvaluation::TYPE_SESSION => 'تقييم سيشن',
                        default => 'تقييم شهري رسمي',
                    })
                    ->color('info'),

                TextColumn::make('calculated_average')
                    ->label('المتوسط العام')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? "⭐ {$state} / 5.00" : 'غير محسوب')
                    ->color('success'),

                TextColumn::make('supervisor.name')
                    ->label('المشرف التعليمي المقيم'),

                TextColumn::make('published_at')
                    ->label('تاريخ النشر الرسمي')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make()->label('معاينة التقييم والاعتراضات'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstructorMyEvaluations::route('/'),
            'view' => ViewInstructorMyEvaluation::route('/{record}'),
        ];
    }
}
