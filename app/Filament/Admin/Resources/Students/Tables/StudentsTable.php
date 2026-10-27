<?php

namespace App\Filament\Admin\Resources\Students\Tables;

use App\Filament\Admin\Pages\BarcodePrint;
use App\Filament\Exports\StudentExporter;
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

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_code')
                    ->label(__('filament/admin/student_resource.student_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('tenant.name')
                    ->label('الفرع')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('tenant', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
                    
                TextColumn::make('user.name')
                     ->label(__('filament/admin/student_resource.user.name'))
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                    
                TextColumn::make('gender')
                    ->label(__('filament/admin/student_resource.gender'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'pink',
                        default => 'gray',
                    }),
                    
                TextColumn::make('phone')
                    ->label(__('filament/admin/student_resource.phone'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('governorate.name_ar')
                    ->label(__('filament/admin/student_resource.governorate_id'))
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('governorate', function ($q) use ($search) {
                            $q->where('name_ar', 'like', "%{$search}%")
                                ->orWhere('name_en', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->toggleable()
                    ->default('—'),

                TextColumn::make('district.name_ar')
                    ->label(__('filament/admin/student_resource.district_id'))
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('district', function ($q) use ($search) {
                            $q->where('name_ar', 'like', "%{$search}%")
                                ->orWhere('name_en', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->toggleable()
                    ->default('—'),
                    
                TextColumn::make('birth_date')
                    ->label(__('filament/admin/student_resource.birth_date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('age')
                    ->label('العمر')
                    ->getStateUsing(fn($record) => $record->age)
                    ->badge()
                    ->color('success')
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('birth_date', $direction === 'asc' ? 'desc' : 'asc');
                    })
                    ->toggleable(),

                TextColumn::make('education_level')
                    ->label(__('filament/admin/student_resource.education_level'))
                    ->searchable()
                    ->toggleable(),
                    
                TextColumn::make('enrollments_count')
                    ->label(__('filament/admin/student_resource.enrollments_count'))
                    ->counts('enrollments')
                    ->badge()
                    ->sortable(),
                    
                TextColumn::make('status')
                    ->label(__('filament/admin/student_resource.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'موقوف',
                        'graduated' => 'متخرج',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        'graduated' => 'info',
                        default => 'gray',
                    }),
                    
                TextColumn::make('created_at')
                    ->label(__('filament/admin/student_resource.created_at'))
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

                SelectFilter::make('gender')
                    ->label('النوع')
                    ->options([
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    ])
                    ->native(false),
                    
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'موقوف',
                        'graduated' => 'متخرج',
                    ])
                    ->native(false),
                    
                TrashedFilter::make()
                    ->label('المحذوفة'),
            ])
            ->actions([
                Action::make('viewCourses')
                    ->label('عرض الكورسات')
                    ->icon('heroicon-o-academic-cap')
                    ->color('info')
                    ->modalHeading(fn($record) => 'كورسات الطالب: ' . $record->user->name)
                    ->modalContent(function ($record) {
                        $enrollment = $record->enrollments()->latest()->first();
                        
                        if (!$enrollment) {
                            return view('filament.admin.components.no-enrollment');
                        }

                        return view('filament.admin.components.student-courses-wrapper', [
                            'enrollmentId' => $enrollment->id
                        ]);
                    })
                    ->modalWidth('7xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

                Action::make('printBarcode')
                    ->label('طباعة باركود')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn($record) => BarcodePrint::getUrl([
                        'type' => 'students',
                        'student_scope' => 'single',
                        'student_id' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
                    
                EditAction::make()
                    ->label('تعديل'),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(StudentExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي'),
                    RestoreBulkAction::make()
                        ->label('استعادة'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
