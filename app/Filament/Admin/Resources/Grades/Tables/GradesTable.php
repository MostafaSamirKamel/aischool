<?php

namespace App\Filament\Admin\Resources\Grades\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.full_name')
                    ->label(__('filament/admin/grade_resource.student.full_name'))
                    ->getStateUsing(fn($record) => $record->student?->user?->name ?? '-')
                    ->description(fn($record) => $record->student?->user?->email)
                    ->searchable()
                    ->sortable(),

                
                TextColumn::make('tenant.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
                    
                TextColumn::make('exam.title')
                     ->label(__('filament/admin/grade_resource.exam.title'))
                    ->sortable()
                    ->searchable()
                    ->description(fn($record) => $record->exam?->course?->name),

                TextColumn::make('exam.type')
                    ->label(__('filament/admin/grade_resource.exam.type'))
                    ->badge()
                    ->colors([
                        'primary' => 'quiz',
                        'warning' => 'midterm',
                        'danger' => 'final',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'quiz' => 'كويز',
                        'midterm' => 'ميدتيرم',
                        'final' => 'فاينال',
                        'assignment' => 'تكليف',
                        'project' => 'مشروع',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('marks_obtained')
                    ->label(__('filament/admin/grade_resource.marks_obtained'))
                    ->numeric()
                    ->sortable()
                    ->description(fn($record) => 'من ' . $record->exam?->total_marks)
                    ->color(fn($record) => $record->marks_obtained >= $record->exam?->passing_marks ? 'success' : 'danger'),

                TextColumn::make('percentage')
                    ->label(__('filament/admin/grade_resource.percentage'))
                    ->numeric()
                    ->sortable()
                    ->suffix('%')
                    ->color(fn($value) => match (true) {
                        $value >= 90 => 'success',
                        $value >= 75 => 'warning',
                        $value >= 60 => 'primary',
                        default => 'danger',
                    }),

                BadgeColumn::make('grade')
                    ->label(__('filament/admin/grade_resource.grade_label'))
                    ->colors([
                        'success' => ['A+', 'A'],
                        'primary' => ['B+', 'B'],
                        'warning' => ['C+', 'C'],
                        'danger' => ['D', 'F'],
                    ])
                    ->sortable(),

                TextColumn::make('graded_by_user.name')
                    ->label(__('filament/admin/grade_resource.graded_by_user.name'))
                    ->getStateUsing(fn($record) => $record->gradedBy ? $record->gradedBy->name : '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('graded_at')
                    ->label(__('filament/admin/grade_resource.graded_at'))
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/grade_resource.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('filament/admin/grade_resource.updated_at'))
                    ->dateTime('d/m/Y')
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
SelectFilter::make('exam_id')
                    ->label('الامتحان')
                    ->relationship('exam', 'title', fn($query) => $query->whereIn('status', ['scheduled', 'in_progress', 'completed']))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('student_id')
                    ->label('الطالب')
                    ->options(function () {
                        return \App\Models\Student::with('user')
                            ->where('status', 'active')
                            ->get()
                            ->mapWithKeys(fn($student) => [
                                $student->id => $student->user->name
                            ]);
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('grade')
                    ->label('التقدير')
                    ->options([
                        'A+' => 'A+ (ممتاز)',
                        'A' => 'A (جيد جداً)',
                        'B+' => 'B+ (جيد جداً)',
                        'B' => 'B (جيد)',
                        'C+' => 'C+ (جيد)',
                        'C' => 'C (مقبول)',
                        'D' => 'D (ضعيف)',
                        'F' => 'F (راسب)',
                    ])
                    ->multiple(),

                Filter::make('passing')
                    ->label('ناجح')
                    ->query(fn(Builder $query): Builder => $query->whereHas('exam', function ($q) {
                        $q->whereRaw('grades.marks_obtained >= exams.passing_marks');
                    }))
                    ->toggle(),

                Filter::make('failing')
                    ->label('راسب')
                    ->query(fn(Builder $query): Builder => $query->whereHas('exam', function ($q) {
                        $q->whereRaw('grades.marks_obtained < exams.passing_marks');
                    }))
                    ->toggle(),

                Filter::make('high_achievers')
                    ->label('متفوقين (90%+)')
                    ->query(fn(Builder $query): Builder => $query->where('percentage', '>=', 90))
                    ->toggle(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('graded_at', 'desc');
    }
}
