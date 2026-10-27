<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Admin\Resources\Users\UserResource;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('filament/admin/user_resource.image'))
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                ImageColumn::make('avatar')
                    ->label(__('filament/admin/user_resource.avatar'))
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                TextColumn::make('name')
                    ->label(__('filament/admin/user_resource.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label(__('filament/admin/user_resource.username'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label(__('filament/admin/user_resource.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('filament/admin/user_resource.phone'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('filament/admin/user_resource.roles.name'))
                    ->badge()
                    ->separator(',')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('job_title')
                    ->label(__('filament/admin/user_resource.job_title'))
                    ->searchable(),
                TextColumn::make('qualification')
                    ->label(__('filament/admin/user_resource.qualification'))
                    ->searchable(),
                TextColumn::make('date_of_birth')
                    ->label(__('filament/admin/user_resource.date_of_birth'))
                    ->date('d-m-Y'),
                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/user_resource.tenant.name'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('filament/admin/user_resource.no_tenant'))
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
                BadgeColumn::make('is_super_admin')
                    ->label(__('filament/admin/user_resource.user_type'))
                    ->formatStateUsing(fn($state) => $state ? __('filament/admin/user_resource.super_admin') : __('filament/admin/user_resource.normal_user'))
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ]),
                BadgeColumn::make('status')
                    ->label(__('filament/admin/user_resource.status'))
                    ->formatStateUsing(fn($state) => __('filament/admin/user_resource.' . $state))
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
