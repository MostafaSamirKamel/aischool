<?php

namespace App\Filament\Admin\Resources\Groups\Tables;

use App\Filament\Exports\GroupExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.name')
                    ->label(__('filament/admin/group_resource.course.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/group_resource.tenant.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
                    
                TextColumn::make('name')
                     ->label(__('filament/admin/group_resource.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('instructor.user.name')
                    ->label(__('filament/admin/group_resource.instructor.name'))
                    ->sortable()
                    ->searchable()
                    ->default('غير محدد'),

                TextColumn::make('classroom.name')
                    ->label(__('filament/admin/group_resource.classroom.name'))
                    ->sortable()
                    ->default('غير محددة')
                    ->toggleable(),

                TextColumn::make('capacity')
                    ->label(__('filament/admin/group_resource.capacity'))
                    ->formatStateUsing(fn($record) => "{$record->capacity} / {$record->max_students}")
                    ->badge()
                    ->color(fn($record) => $record->isFull() ? 'danger' : 'success'),

                TextColumn::make('enrollmentCourses_count')
                    ->label('عدد الطلبة')
                    ->counts('enrollmentCourses')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        // Count unique students through enrollmentCourses
                        return $record->enrollmentCourses()
                            ->distinct('enrollment_id')
                            ->count('enrollment_id');
                    }),

                TextColumn::make('start_date')
                    ->label(__('filament/admin/group_resource.start_date'))
                    ->date('Y-m-d')
                    ->sortable(),
                    
                TextColumn::make('end_date')
                    ->label(__('filament/admin/group_resource.end_date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('filament/admin/group_resource.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'active' => 'نشطة',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/group_resource.created_at'))
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
                SelectFilter::make('course_id')
                    ->label('الكورس')
                    ->relationship('course', 'name', fn($query) => $query->where('status', 'active'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('instructor_id')
                    ->label('المدرس')
                    ->options(function () {
                        return \App\Models\Instructor::query()
                            ->where('status', 'active')
                            ->with('user')
                            ->get()
                            ->pluck('user.name', 'id');
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'active' => 'نشطة',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                    ])
                    ->native(false),

                TrashedFilter::make()
                    ->label('المحذوفة'),
            ])
            ->recordActions([
                Action::make('viewStudents')
                    ->label('عرض الطلبة')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->modalHeading(fn($record) => 'طلبة مجموعة: ' . $record->name)
                    ->modalContent(function($record) {
                        // Get students through enrollmentCourses
                        $enrollmentCourses = $record->enrollmentCourses()
                            ->with(['enrollment.student.user'])
                            ->get();
                        
                        return view('filament.admin.modals.group-students', [
                            'group' => $record,
                            'enrollmentCourses' => $enrollmentCourses
                        ]);
                    })
                    ->modalWidth('5xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

                EditAction::make()
                    ->label('تعديل')
                    ->visible(fn() => !auth()->user()->is_super_admin),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(GroupExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي'),
                    RestoreBulkAction::make()
                        ->label('استعادة'),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }
}
