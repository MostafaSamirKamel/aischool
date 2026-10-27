<?php

namespace App\Filament\Admin\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label(__('filament/admin/setting_resource.company_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('company_address')
                    ->label(__('filament/admin/setting_resource.company_address'))
                    ->maxLength(255),
                TextInput::make('company_phone')
                    ->label(__('filament/admin/setting_resource.company_phone'))
                    ->tel()
                    ->maxLength(255),
                TextInput::make('company_whatsapp')
                    ->label(__('filament/admin/setting_resource.company_whatsapp'))
                    ->maxLength(255),
                TextInput::make('company_email')
                    ->label(__('filament/admin/setting_resource.company_email'))
                    ->email()
                    ->maxLength(255),
                TextInput::make('company_logo')
                    ->label(__('filament/admin/setting_resource.company_logo'))
                    ->maxLength(255),
                TextInput::make('company_website')
                    ->label(__('filament/admin/setting_resource.company_website'))
                    ->url()
                    ->maxLength(255),
                TextInput::make('currency')
                    ->label(__('filament/admin/setting_resource.currency'))
                    ->default('EGP')
                    ->maxLength(3),
                TextInput::make('timezone')
                    ->label(__('filament/admin/setting_resource.timezone'))
                    ->default('Africa/Cairo')
                    ->maxLength(50),
                Toggle::make('maintenance_mode')
                    ->label(__('filament/admin/setting_resource.maintenance_mode'))
                    ->default(false),
            ]);
    }
}
