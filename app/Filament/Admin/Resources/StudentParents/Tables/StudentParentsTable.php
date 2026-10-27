<?php

namespace App\Filament\Admin\Resources\StudentParents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentParentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.name')
                    ->label(__('filament/admin/student_parent_resource.student.user.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.student_code')
                    ->label(__('filament/admin/student_parent_resource.student.student_code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('filament/admin/student_parent_resource.user.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.phone')
                    ->label(__('filament/admin/student_parent_resource.user.phone'))
                    ->searchable(),

                TextColumn::make('relation')
                    ->label(__('filament/admin/student_parent_resource.relation'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'father' => 'أب',
                        'mother' => 'أم',
                        'guardian' => 'ولي أمر',
                        'other' => 'أخرى',
                        default => $state,
                    }),

                IconColumn::make('is_primary')
                    ->label(__('filament/admin/student_parent_resource.is_primary'))
                    ->boolean(),

                IconColumn::make('can_pickup')
                    ->label(__('filament/admin/student_parent_resource.can_pickup'))
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('receive_notifications')
                    ->label(__('filament/admin/student_parent_resource.receive_notifications'))
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/student_parent_resource.created_at'))
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('relation')
                    ->label('صلة القرابة')
                    ->options([
                        'father' => 'أب',
                        'mother' => 'أم',
                        'guardian' => 'ولي أمر',
                        'other' => 'أخرى',
                    ]),

                SelectFilter::make('is_primary')
                    ->label('ولي أمر رئيسي')
                    ->options([
                        true => 'نعم',
                        false => 'لا',
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn () => !auth()->user()->is_super_admin),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => !auth()->user()->is_super_admin),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
