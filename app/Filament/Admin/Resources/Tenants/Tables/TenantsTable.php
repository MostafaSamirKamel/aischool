<?php

namespace App\Filament\Admin\Resources\Tenants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('filament/admin/tenant_resource.image'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament/admin/tenant_resource.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament/admin/tenant_resource.slug'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('domain')
                    ->label(__('filament/admin/tenant_resource.domain'))
                    ->searchable(),
                Tables\Columns\BooleanColumn::make('is_active')
                    ->label(__('filament/admin/tenant_resource.is_active')),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
