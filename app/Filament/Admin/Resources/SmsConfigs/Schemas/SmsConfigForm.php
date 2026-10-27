<?php

namespace App\Filament\Admin\Resources\SmsConfigs\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class SmsConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament/admin/sms_settings.general_settings'))
                    ->schema([
                        Select::make('default_driver')
                            ->label(__('filament/admin/sms_config_resource.default_driver'))
                            ->options(['twilio' => __('filament/admin/sms_config_resource.default_driver.twilio'), 'vonage' => __('filament/admin/sms_config_resource.default_driver.vonage'), 'vodafone' => __('filament/admin/sms_config_resource.default_driver.vodafone'), 'log' => __('filament/admin/sms_config_resource.default_driver.log')])
                            ->default('log')
                            ->required(),
                        Toggle::make('queue')
                            ->label(__('filament/admin/sms_config_resource.queue'))
                            ->default(true)
                            ->helperText('يُنصح بتفعيله لتحسين الأداء'),
                        Toggle::make('log_messages')
                            ->label(__('filament/admin/sms_config_resource.log_messages'))
                            ->default(true)
                            ->helperText('حفظ سجل كامل لجميع الرسائل المرسلة'),
                    ])
                    ->columns(3),

                Section::make(__('filament/admin/sms_settings.rate_limiting'))
                    ->description(__('filament/admin/sms_settings.rate_limiting_description'))
                    ->schema([
                        TextInput::make('rate_limit_per_minute')
                            ->label(__('filament/admin/sms_config_resource.rate_limit_per_minute'))
                            ->numeric()
                            ->default(10)
                            ->required()
                            ->minValue(1)
                            ->maxValue(100),
                        TextInput::make('rate_limit_per_hour')
                            ->label(__('filament/admin/sms_config_resource.rate_limit_per_hour'))
                            ->numeric()
                            ->default(100)
                            ->required()
                            ->minValue(10)
                            ->maxValue(1000),
                    ])
                    ->columns(2),

                Tabs::make(__('filament/admin/sms_settings.provider_settings'))
                    ->tabs([
                        Tab::make('Twilio')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                TextInput::make('twilio_sid')
                                    ->label(__('filament/admin/sms_config_resource.twilio_sid'))
                                    ->password()
                                    ->revealable()
                                    ->placeholder('ACxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                                TextInput::make('twilio_token')
                                    ->label(__('filament/admin/sms_config_resource.twilio_token'))
                                    ->password()
                                    ->revealable()
                                    ->placeholder('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                                TextInput::make('twilio_from')
                                    ->label(__('filament/admin/sms_config_resource.twilio_from'))
                                    ->tel()
                                    ->placeholder('+1234567890')
                                    ->helperText('رقم Twilio المسجل'),
                            ]),

                        Tab::make('Vonage / Nexmo')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                TextInput::make('vonage_key')
                                    ->label(__('filament/admin/sms_config_resource.vonage_key'))
                                    ->password()
                                    ->revealable()
                                    ->placeholder('xxxxxxxxxxxxxxxx'),
                                TextInput::make('vonage_secret')
                                    ->label(__('filament/admin/sms_config_resource.vonage_secret'))
                                    ->password()
                                    ->revealable()
                                    ->placeholder('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                                TextInput::make('vonage_from')
                                    ->label(__('filament/admin/sms_config_resource.vonage_from'))
                                    ->placeholder('MySchool')
                                    ->helperText('الاسم الذي يظهر للمستقبل (11 حرف كحد أقصى)'),
                            ]),

                        Tab::make('Vodafone Egypt')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                TextInput::make('vodafone_username')
                                    ->label(__('filament/admin/sms_config_resource.vodafone_username'))
                                    ->placeholder('username'),
                                TextInput::make('vodafone_password')
                                    ->label(__('filament/admin/sms_config_resource.vodafone_password'))
                                    ->password()
                                    ->revealable()
                                    ->placeholder('********'),
                                TextInput::make('vodafone_sender')
                                    ->label(__('filament/admin/sms_config_resource.vodafone_sender'))
                                    ->placeholder('MySchool')
                                    ->helperText('الاسم الذي يظهر للمستقبل'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
