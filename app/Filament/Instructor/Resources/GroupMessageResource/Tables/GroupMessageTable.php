<?php

namespace App\Filament\Instructor\Resources\GroupMessageResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class GroupMessageTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group.name')
                    ->label(__('filament/instructor/group_message_resource.group.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject')
                    ->label(__('filament/instructor/group_message_resource.subject'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('type')
                    ->label(__('filament/instructor/group_message_resource.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'homework' => 'success',
                        'reminder' => 'warning',
                        'announcement' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'announcement' => 'إعلان',
                        'homework' => 'واجب',
                        'reminder' => 'تذكير',
                        'general' => 'عام',
                        default => $state,
                    }),

                TextColumn::make('priority')
                    ->label(__('filament/instructor/group_message_resource.priority'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'normal' => 'primary',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'منخفضة',
                        'normal' => 'عادية',
                        'high' => 'عالية',
                        'urgent' => 'عاجلة',
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label(__('filament/instructor/group_message_resource.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'مسودة',
                        'scheduled' => 'مجدولة',
                        'sent' => 'مرسلة',
                        'failed' => 'فشلت',
                        default => $state,
                    }),

                TextColumn::make('recipients_count')
                    ->label(__('filament/instructor/group_message_resource.recipients_count'))
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('read_count')
                    ->label(__('filament/instructor/group_message_resource.read_count'))
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                TextColumn::make('sent_at')
                    ->label(__('filament/instructor/group_message_resource.sent_at'))
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('filament/instructor/group_message_resource.created_at'))
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('group_id')
                    ->label('المجموعة')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'announcement' => 'إعلان',
                        'homework' => 'واجب',
                        'reminder' => 'تذكير',
                        'general' => 'عام',
                    ]),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'scheduled' => 'مجدولة',
                        'sent' => 'مرسلة',
                        'failed' => 'فشلت',
                    ]),

                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
