<?php

namespace App\Filament\Admin\Resources\Courses\Tables;

use App\Filament\Exports\CourseExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('track.name')
                    ->label(__('filament/admin/course_resource.track.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/course_resource.tenant.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
                    
                TextColumn::make('name')
                    ->label(__('filament/admin/course_resource.name'))
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('duration_months')
                    ->label(__('filament/admin/course_resource.duration_months'))
                    ->numeric()
                    ->sortable()
                    ->suffix(' شهر'),
                    
                TextColumn::make('sessions_count')
                    ->label(__('filament/admin/course_resource.sessions_count'))
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('price')
                    ->label(__('filament/admin/course_resource.price'))
                    ->money('EGP')
                    ->sortable(),
                    
                TextColumn::make('groups_count')
                    ->label(__('filament/admin/course_resource.groups_count'))
                    ->counts('groups')
                    ->sortable()
                    ->badge(),
                    
                TextColumn::make('status')
                    ->label(__('filament/admin/course_resource.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'مسودة',
                        'active' => 'نشط',
                        'archived' => 'مؤرشف',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'archived' => 'warning',
                        default => 'gray',
                    }),
                    
                TextColumn::make('created_at')
                    ->label(__('filament/admin/course_resource.created_at'))
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
                SelectFilter::make('track_id')
                    ->label('المسار التعليمي')
                    ->relationship('track', 'name', fn($query) => $query->where('status', 'active'))
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'active' => 'نشط',
                        'archived' => 'مؤرشف',
                    ])
                    ->native(false),
                    
                TrashedFilter::make()
                    ->label('المحذوفة'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل')
                    ->visible(fn() => !auth()->user()->is_super_admin),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(CourseExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي'),
                    RestoreBulkAction::make()
                        ->label('استعادة'),
                ]),
            ])
            ->defaultSort('track_id', 'asc')
            ->defaultSort('order', 'asc');
    }
}
