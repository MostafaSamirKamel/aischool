<?php

namespace App\Filament\Admin\Resources\SmsLogResource\Schemas;

use message;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;

class SmsLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('phone')
                            ->label(__('filament/admin/sms_log_resource.phone'))
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Textarea::make('message')
                            ->label(__('filament/admin/sms_log_resource.message'))
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Select::make('driver')
                            ->label(__('filament/admin/sms_log_resource.driver'))
                            ->options([
                                'twilio' => __('filament/admin/sms_log_resource.driver.twilio'),
                                'vonage' => __('filament/admin/sms_log_resource.driver.vonage'),
                                'vodafone' => __('filament/admin/sms_log_resource.driver.vodafone'),
                                'log' => __('filament/admin/sms_log_resource.driver.log')
                            ])
                            ->default('log')
                            ->required(),
                        Select::make('status')
                            ->label(__('filament/admin/sms_log_resource.status'))
                            ->options([
                                'sent' => __('filament/admin/sms_log_resource.status.sent'),
                                'failed' => __('filament/admin/sms_log_resource.status.failed'),
                                'pending' => __('filament/admin/sms_log_resource.status.pending')
                            ])
                            ->default('pending')
                            ->required(),
                        DateTimePicker::make('sent_at')
                            ->label(__('filament/admin/sms_log_resource.sent_at')),
                    ])
                    ->columns(2),
            ]);
    }
}
