<?php

namespace App\Filament\Instructor\Resources\InstructorReviews\Tables;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class InstructorReviewsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rating')
                    ->label('التقييم')
                    ->sortable()
                    ->formatStateUsing(fn($state) => str_repeat('⭐', $state))
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    }),
                    
                TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                    
                TextColumn::make('reviewer_type')
                    ->label('نوع المقيّم')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'student' => 'طالب',
                        'parent' => 'ولي أمر',
                        default => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'student' => 'info',
                        'parent' => 'warning',
                        default => 'gray',
                    }),
                    
                TextColumn::make('reviewer_name')
                    ->label('اسم المقيّم')
                    ->getStateUsing(fn($record) => $record->reviewer_name),
                    
                TextColumn::make('course.name')
                    ->label('الكورس')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                IconColumn::make('is_anonymous')
                    ->label('مجهول')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label('تاريخ التقييم')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([
                        5 => '⭐⭐⭐⭐⭐',
                        4 => '⭐⭐⭐⭐',
                        3 => '⭐⭐⭐',
                        2 => '⭐⭐',
                        1 => '⭐',
                    ]),
                    
                SelectFilter::make('reviewer_type')
                    ->label('نوع المقيّم')
                    ->options([
                        'student' => 'طالب',
                        'parent' => 'ولي أمر',
                    ]),
                    
                SelectFilter::make('course_id')
                    ->label('الكورس')
                    ->relationship('course', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('group_id')
                    ->label('المجموعة')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make()
                    ->label('عرض'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
