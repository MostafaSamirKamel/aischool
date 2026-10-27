<?php

namespace App\Filament\Admin\Resources\SmsLogResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;

class SmsLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('filament/admin/sms_log_resource.phone'))
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-phone'),
                Tables\Columns\TextColumn::make('message')
                    ->label(__('filament/admin/sms_log_resource.message'))
                    ->limit(50)
                    ->searchable()
                    ->wrap()
                    ->tooltip(fn ($record) => $record->message),
                Tables\Columns\TextColumn::make('driver')
                    ->label(__('filament/admin/sms_log_resource.driver'))
                    ->badge()
                    ->colors([
                        'primary' => 'twilio',
                        'success' => 'vonage',
                        'warning' => 'vodafone',
                        'gray' => 'log',
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament/admin/sms_log_resource.status'))
                    ->badge()
                    ->colors([
                        'success' => 'sent',
                        'danger' => 'failed',
                        'warning' => 'pending',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sent' => 'مرسل',
                        'failed' => 'فاشل',
                        'pending' => 'قيد الانتظار',
                        default => $state
                    }),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label(__('filament/admin/sms_log_resource.sent_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament/admin/sms_log_resource.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('filament/admin/sms_log_resource.status'))
                    ->options([
                        'sent' => 'مرسل',
                        'failed' => 'فاشل',
                        'pending' => 'قيد الانتظار',
                    ]),
                Tables\Filters\SelectFilter::make('driver')
                    ->label(__('filament/admin/sms_log_resource.driver'))
                    ->options([
                        'twilio' => 'Twilio',
                        'vonage' => 'Vonage',
                        'vodafone' => 'Vodafone',
                        'log' => 'Log',
                    ]),
                Tables\Filters\Filter::make('sent_today')
                    ->label(__('filament/admin/sms_log_resource.sent_today'))
                    ->query(fn ($query) => $query->today()),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        TextInput::make('from')
                            ->type('date')
                            ->label(__('filament/admin/sms_log_resource.date_range')),
                        TextInput::make('to')
                            ->type('date')
                            ->label(__('filament/admin/sms_log_resource.to')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('sent_at', '>=', $data['from']))
                            ->when($data['to'], fn ($q) => $q->whereDate('sent_at', '<=', $data['to']));
                    }),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد رسائل نصية')
            ->emptyStateDescription('سيتم عرض جميع الرسائل النصية المرسلة هنا')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
