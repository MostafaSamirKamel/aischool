<?php

namespace App\Filament\Admin\Resources\Nationalities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NationalitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/admin/nationality_resource.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('filament/admin/nationality_resource.code'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(function () {
                        $user = auth()->user();
                        // Super admin بدون tenant يمكنه التعديل
                        if ($user->is_super_admin && is_null($user->tenant_id)) {
                            return true;
                        }
                        // المستخدمون الآخرون لا يمكنهم التعديل
                        return false;
                    }),
                DeleteAction::make()
                    ->visible(function () {
                        $user = auth()->user();
                        // Super admin بدون tenant يمكنه الحذف
                        if ($user->is_super_admin && is_null($user->tenant_id)) {
                            return true;
                        }
                        return false;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(function () {
                            $user = auth()->user();
                            // Super admin بدون tenant يمكنه الحذف
                            if ($user->is_super_admin && is_null($user->tenant_id)) {
                                return true;
                            }
                            return false;
                        }),
                ]),
            ]);
    }
}
