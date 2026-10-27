<?php

namespace App\Filament\Admin\Resources\RevenueCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RevenueCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/admin/revenue_category_resource.name'))
                    ->searchable(),
                TextColumn::make('name_en')
                    ->label(__('filament/admin/revenue_category_resource.name_en'))
                    ->searchable(),
                TextColumn::make('code')
                    ->label(__('filament/admin/revenue_category_resource.code'))
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label(__('filament/admin/revenue_category_resource.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('filament/admin/revenue_category_resource.created_at'))
                    ->dateTime()
                    ->sortable(),
                    
                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/revenue_category_resource.tenant.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
                    
                TextColumn::make('updated_at')
                    ->label(__('filament/admin/revenue_category_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('filament/admin/revenue_category_resource.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                
                SelectFilter::make('tenant_id')
                    ->label('الفرع')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
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
            ]);
    }
}
