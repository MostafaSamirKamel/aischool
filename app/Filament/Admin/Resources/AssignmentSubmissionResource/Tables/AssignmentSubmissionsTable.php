<?php

namespace App\Filament\Admin\Resources\AssignmentSubmissionResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->label(__('filament/admin/assignment_submission_resource.assignment.title'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.user.name')
                    ->label(__('filament/admin/assignment_submission_resource.student.user.name'))
                    ->searchable(),

                TextColumn::make('assignment.group.name')
                    ->label(__('filament/admin/assignment_submission_resource.assignment.group.name'))
                    ->searchable(),

                TextColumn::make('submitted_at')
                    ->label(__('filament/admin/assignment_submission_resource.submitted_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('score')
                    ->label(__('filament/admin/assignment_submission_resource.score'))
                    ->badge()
                    ->color(fn ($state, $record) => 
                        $state >= ($record->assignment->max_score * 0.9) ? 'success' : 
                        ($state >= ($record->assignment->max_score * 0.75) ? 'warning' : 'danger')
                    )
                    ->formatStateUsing(fn ($state, $record) => 
                        $state ? $state . ' / ' . $record->assignment->max_score : '-'
                    ),

                TextColumn::make('status')
                    ->label(__('filament/admin/assignment_submission_resource.status'))
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'قيد الانتظار',
                        'submitted' => 'تم التسليم',
                        'graded' => 'تم التصحيح',
                        'late' => 'متأخر',
                        default => $state
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pending' => 'warning',
                        'submitted' => 'info',
                        'graded' => 'success',
                        'late' => 'danger',
                        default => 'gray'
                    }),
            ])
            ->filters([
                SelectFilter::make('assignment_id')
                    ->label(__('filament/admin/assignment_submission_resource.assignment_id'))
                    ->relationship('assignment', 'title'),

                SelectFilter::make('status')
                    ->label(__('filament/admin/assignment_submission_resource.status'))
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'submitted' => 'تم التسليم',
                        'graded' => 'تم التصحيح',
                        'late' => 'متأخر',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
