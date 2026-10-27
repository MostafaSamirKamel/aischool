<?php

namespace App\Filament\Admin\Resources\Departments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->is_super_admin && is_null($user->tenant_id);

        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/department_resource.tenant.name'))
                    ->searchable()
                    ->sortable()
                    ->visible($isSuperAdmin),

                TextColumn::make('name')
                    ->label(__('filament/admin/department_resource.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('filament/admin/department_resource.description'))
                    ->limit(50)
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('filament/admin/department_resource.is_active'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/department_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('filament/admin/department_resource.is_active'))
                    ->boolean()
                    ->trueLabel(__('filament/admin/department_resource.active_only'))
                    ->falseLabel(__('filament/admin/department_resource.inactive_only')),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn() => !auth()->user()->is_super_admin),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn() => !auth()->user()->is_super_admin),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
