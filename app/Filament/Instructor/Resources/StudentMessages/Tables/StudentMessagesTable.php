<?php

namespace App\Filament\Instructor\Resources\StudentMessages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;

class StudentMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable()
                    ->limit(50),
                
                BadgeColumn::make('type')
                    ->label('النوع')
                    ->colors([
                        'secondary' => 'info',
                        'warning' => 'warning',
                        'success' => 'success',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'info' => 'معلومة',
                        'warning' => 'تحذير',
                        'success' => 'نجاح',
                        'urgent' => 'عاجل',
                        default => $state,
                    }),
                
                BadgeColumn::make('priority')
                    ->label('الأولوية')
                    ->colors([
                        'secondary' => 'low',
                        'primary' => 'normal',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'منخفضة',
                        'normal' => 'عادية',
                        'high' => 'عالية',
                        'urgent' => 'عاجلة',
                        default => $state,
                    }),
                
                IconColumn::make('is_read')
                    ->label('مقروءة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'sent',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'مسودة',
                        'sent' => 'مرسلة',
                        default => $state,
                    }),
                
                TextColumn::make('sent_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                
                TextColumn::make('read_at')
                    ->label('تاريخ القراءة')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->placeholder('لم تُقرأ بعد'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'info' => 'معلومة',
                        'warning' => 'تحذير',
                        'success' => 'نجاح',
                        'urgent' => 'عاجل',
                    ]),
                
                SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'low' => 'منخفضة',
                        'normal' => 'عادية',
                        'high' => 'عالية',
                        'urgent' => 'عاجلة',
                    ]),
                
                SelectFilter::make('is_read')
                    ->label('حالة القراءة')
                    ->options([
                        '1' => 'مقروءة',
                        '0' => 'غير مقروءة',
                    ]),
                
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
