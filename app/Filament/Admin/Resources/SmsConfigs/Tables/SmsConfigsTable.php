<?php

namespace App\Filament\Admin\Resources\SmsConfigs\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class SmsConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('default_driver')
                    ->label(__('filament/admin/sms_config_resource.default_driver'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'twilio' => 'Twilio',
                        'vonage' => 'Vonage',
                        'vodafone' => 'Vodafone',
                        'log' => 'Log',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'twilio' => 'info',
                        'vonage' => 'warning',
                        'vodafone' => 'danger',
                        'log' => 'gray',
                        default => 'gray',
                    }),
                IconColumn::make('queue')
                    ->label(__('filament/admin/sms_config_resource.queue'))
                    ->boolean(),
                IconColumn::make('log_messages')
                    ->label(__('filament/admin/sms_config_resource.log_messages'))
                    ->boolean(),
                TextColumn::make('rate_limit_per_minute')
                    ->label(__('filament/admin/sms_config_resource.rate_limit_per_minute'))
                    ->numeric(),
                TextColumn::make('rate_limit_per_hour')
                    ->label(__('filament/admin/sms_config_resource.rate_limit_per_hour'))
                    ->numeric(),
                TextColumn::make('updated_at')
                    ->label(__('filament/admin/sms_config_resource.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn() => !auth()->user()->is_super_admin),
            ]);
    }
}
