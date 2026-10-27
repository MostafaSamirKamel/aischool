<?php

namespace App\Filament\Admin\Resources\Exams\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;

class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    protected static ?string $title = 'نتائج الطلاب';

    protected static ?string $modelLabel = 'محاولة';

    protected static ?string $pluralModelLabel = 'المحاولات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('score')
                    ->label('الدرجة')
                    ->formatStateUsing(fn($record) => $record->score . ' / ' . $record->total_score)
                    ->badge()
                    ->color(fn($record) => $record->passed ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('النسبة المئوية')
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
                    ->label('النجاح')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('time_taken')
                    ->label('الوقت المستغرق')
                    ->formatStateUsing(fn($state) => floor($state / 60) . ' دقيقة')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('وقت البدء')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('وقت الإكمال')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('passed')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('ناجح')
                    ->falseLabel('راسب'),

                Tables\Filters\Filter::make('completed')
                    ->label('مكتمل فقط')
                    ->query(fn($query) => $query->whereNotNull('completed_at')),
            ])
            ->actions([
                ViewAction::make()
                    ->label('عرض التفاصيل')
                    ->modalHeading(fn($record) => 'تفاصيل محاولة: ' . $record->student->user->name)
                    ->modalWidth(\Filament\Support\Enums\Width::FiveExtraLarge)
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalContent(fn($record) => view('filament.admin.resources.exams.relation-managers.attempt-details', [
                        'attempt' => $record->load('answers.question.options', 'answers.selectedOption')
                    ])),
            ])
            ->defaultSort('completed_at', 'desc')
            ->emptyStateHeading('لا توجد محاولات')
            ->emptyStateDescription('لم يقم أي طالب بحل هذا الامتحان بعد')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}
