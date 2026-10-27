<?php

namespace App\Filament\Parent\Resources\Messages\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('type')
                    ->label('النوع')
                    ->colors([
                        'secondary' => 'info',
                        'warning' => 'warning',
                        'success' => 'success',
                        'danger' => 'urgent',
                    ])
                    ->icons([
                        'heroicon-o-information-circle' => 'info',
                        'heroicon-o-exclamation-triangle' => 'warning',
                        'heroicon-o-check-circle' => 'success',
                        'heroicon-o-bell-alert' => 'urgent',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'info' => 'معلومة',
                        'warning' => 'تحذير',
                        'success' => 'نجاح',
                        'urgent' => 'عاجل',
                        default => $state,
                    }),
                
                TextColumn::make('student.user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable()
                    ->limit(50)
                    ->weight('bold')
                    ->description(fn ($record) => $record ? \Str::limit(strip_tags((string) $record->message), 100) : null),
                
                TextColumn::make('sender.name')
                    ->label('المرسل')
                    ->badge()
                    ->color('primary'),
                
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
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn ($record) => $record?->is_read ? 'مقروءة' : 'جديدة'),
                
                TextColumn::make('sent_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->since(),
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
                        '0' => 'غير مقروءة',
                        '1' => 'مقروءة',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),
            ])
            ->defaultSort('sent_at', 'desc')
            ->striped();
    }
}
