<?php

namespace App\Filament\Instructor\Resources\AssignmentSubmissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssignmentSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assignment.title')
                    ->label(__('filament/instructor/assignment_submission_resource.assignment.title'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.name')
                    ->label(__('filament/instructor/assignment_submission_resource.student.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('submitted_at')
                    ->label(__('filament/instructor/assignment_submission_resource.submitted_at'))
                    ->dateTime()
                    ->sortable()
                    ->default('-'),
                TextColumn::make('score')
                    ->label(__('filament/instructor/assignment_submission_resource.score'))
                    ->numeric()
                    ->sortable()
                    ->default('-')
                    ->formatStateUsing(fn ($record) => $record->score ? $record->score . ' / ' . $record->assignment->max_score : '-'),
                TextColumn::make('status')
                    ->label(__('filament/instructor/assignment_submission_resource.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'submitted' => 'info',
                        'graded' => 'success',
                        'late' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'معلق',
                        'submitted' => 'تم التسليم',
                        'graded' => 'تم التصحيح',
                        'late' => 'متأخر',
                        default => $state,
                    }),
                TextColumn::make('graded_at')
                    ->label(__('filament/instructor/assignment_submission_resource.graded_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'submitted' => 'تم التسليم',
                        'graded' => 'تم التصحيح',
                        'late' => 'متأخر',
                    ]),
                SelectFilter::make('assignment_id')
                    ->label('الواجب')
                    ->relationship('assignment', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('مراجعة وتصحيح الصور')
                    ->icon('heroicon-o-photo')
                    ->color('success')
                    ->url(fn ($record): string => \App\Filament\Instructor\Resources\Assignments\AssignmentResource::getUrl('submissions', ['record' => $record->assignment_id]) . '?submission=' . $record->id),
                EditAction::make()
                    ->label('تعديل'),
            ])
            ->toolbarActions([
                //
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
