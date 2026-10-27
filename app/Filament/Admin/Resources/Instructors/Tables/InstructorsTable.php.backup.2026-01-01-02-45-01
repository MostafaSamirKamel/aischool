<?php

namespace App\Filament\Admin\Resources\Instructors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InstructorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_code')
                    ->label(__('filament/admin/instructor_resource.employee_code'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/instructor_resource.tenant.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
                    
                TextColumn::make('user.name')
                     ->label(__('filament/admin/instructor_resource.user.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label(__('filament/admin/instructor_resource.department.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('specialization')
                    ->label(__('filament/admin/instructor_resource.specialization'))
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('years_of_experience')
                    ->label(__('filament/admin/instructor_resource.years_of_experience'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                BadgeColumn::make('status')
                    ->label(__('filament/admin/instructor_resource.status'))
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'on_leave',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active' => __('filament/admin/instructor_resource.status_options.active'),
                        'inactive' => __('filament/admin/instructor_resource.status_options.inactive'),
                        'on_leave' => __('filament/admin/instructor_resource.status_options.on_leave'),
                        default => $state,
                    }),
                BadgeColumn::make('contract_type')
                    ->label(__('filament/admin/instructor_resource.contract_type'))
                    ->colors([
                        'primary' => 'full_time',
                        'warning' => 'part_time',
                        'secondary' => 'contract',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'full_time' => __('filament/admin/instructor_resource.contract_type.full_time'),
                        'part_time' => __('filament/admin/instructor_resource.contract_type.part_time'),
                        'contract' => __('filament/admin/instructor_resource.contract_type.contract'),
                        default => $state,
                    })
                    ->toggleable(),
                TextColumn::make('hired_date')
                    ->label(__('filament/admin/instructor_resource.hired_date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('age')
                    ->label(__('filament/admin/instructor_resource.age'))
                    ->getStateUsing(fn($record) => $record->age)
                    ->badge()
                    ->color('success')
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('birth_date', $direction === 'asc' ? 'desc' : 'asc');
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label(__('filament/admin/instructor_resource.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('filament/admin/instructor_resource.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                
                SelectFilter::make('tenant_id')
                    ->label(__('filament/admin/instructor_resource.tenant.name'))
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
SelectFilter::make('department_id')
                    ->label(__('filament/admin/instructor_resource.department.name'))
                    ->relationship('department', 'name', fn($query) => $query->where('is_active', true))
                    ->multiple()
                    ->preload(),
                SelectFilter::make('status')
                    ->label(__('filament/admin/instructor_resource.status'))
                    ->options([
                        'active' => __('filament/admin/instructor_resource.status_options.active'),
                        'inactive' => __('filament/admin/instructor_resource.status_options.inactive'),
                        'on_leave' => __('filament/admin/instructor_resource.status_options.on_leave'),
                    ])
                    ->multiple(),
                SelectFilter::make('contract_type')
                    ->label(__('filament/admin/instructor_resource.contract_type'))
                    ->options([
                        'full_time' => __('filament/admin/instructor_resource.contract_type.full_time'),
                        'part_time' => __('filament/admin/instructor_resource.contract_type.part_time'),
                        'contract' => __('filament/admin/instructor_resource.contract_type.contract'),
                    ])
                    ->multiple(),
                TrashedFilter::make()
                    ->label(__('filament/admin/instructor_resource.filter.trashed')),
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
