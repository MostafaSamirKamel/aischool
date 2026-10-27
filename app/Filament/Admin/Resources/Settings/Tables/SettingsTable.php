<?php

namespace App\Filament\Admin\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label(__('filament/admin/setting_resource.company_name'))
                    ->searchable(),
                TextColumn::make('company_address')
                    ->label(__('filament/admin/setting_resource.company_address'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company_phone')
                    ->label(__('filament/admin/setting_resource.company_phone'))
                    ->searchable(),
                TextColumn::make('company_whatsapp')
                    ->label(__('filament/admin/setting_resource.company_whatsapp'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company_email')
                    ->label(__('filament/admin/setting_resource.company_email'))
                    ->searchable(),
                TextColumn::make('company_logo')
                    ->label(__('filament/admin/setting_resource.company_logo'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company_website')
                    ->label(__('filament/admin/setting_resource.company_website'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('currency')
                    ->label(__('filament/admin/setting_resource.currency'))
                    ->searchable(),
                TextColumn::make('timezone')
                    ->label(__('filament/admin/setting_resource.timezone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('maintenance_mode')
                    ->label(__('filament/admin/setting_resource.maintenance_mode'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('filament/admin/setting_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament/admin/setting_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                // Removed delete actions
            ]);
    }
}
