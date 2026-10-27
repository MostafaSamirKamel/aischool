<?php

namespace App\Filament\Admin\Resources\Classrooms\Tables;

use App\Filament\Admin\Resources\Classrooms\ClassroomResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClassroomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/admin/classroom_resource.name'))
                    ->searchable()
                    ->sortable(),
                    
                
                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/classroom_resource.tenant.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
                    
                TextColumn::make('capacity')
                     ->label(__('filament/admin/classroom_resource.capacity'))
                    ->numeric()
                    ->sortable()
                    ->suffix(' طالب'),
                    
                TextColumn::make('location')
                    ->label(__('filament/admin/classroom_resource.location'))
                    ->searchable()
                    ->toggleable(),
                    
                TextColumn::make('groups_count')
                    ->label(__('filament/admin/classroom_resource.groups_count'))
                    ->counts('activeGroups')
                    ->sortable()
                    ->badge(),
                    
                TextColumn::make('status')
                    ->label(__('filament/admin/classroom_resource.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشطة',
                        'inactive' => 'غير نشطة',
                        'maintenance' => 'صيانة',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'maintenance' => 'warning',
                        default => 'gray',
                    }),
                    
                TextColumn::make('created_at')
                    ->label(__('filament/admin/classroom_resource.created_at'))
                    ->dateTime('Y-m-d')
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
SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشطة',
                        'inactive' => 'غير نشطة',
                        'maintenance' => 'صيانة',
                    ])
                    ->native(false),
                    
                TrashedFilter::make()
                    ->label('المحذوفة'),
            ])
            ->recordActions([
                Action::make('availability')
                    ->label('جدول الإشغال')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->url(fn($record) => ClassroomResource::getUrl('availability', ['classroom_id' => $record->id])),
                EditAction::make()
                    ->label('تعديل')
                    ->visible(fn() => !auth()->user()->is_super_admin),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي'),
                    RestoreBulkAction::make()
                        ->label('استعادة'),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
