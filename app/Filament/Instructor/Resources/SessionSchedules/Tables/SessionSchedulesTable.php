<?php

namespace App\Filament\Instructor\Resources\SessionSchedules\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class SessionSchedulesTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('time_range')
                    ->label('الوقت')
                    ->getStateUsing(fn($record) => $record->time_range)
                    ->sortable(['start_time']),

                TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->toggleable()
                    ->limit(30),

                TextColumn::make('classroom.name')
                    ->label('القاعة')
                    ->searchable()
                    ->toggleable()
                    ->default('غير محدد'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'regular' => 'عادي',
                        'makeup' => 'تعويضي',
                        'extra' => 'إضافي',
                        'exam' => 'امتحان',
                        default => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'regular' => 'info',
                        'makeup' => 'warning',
                        'extra' => 'success',
                        'exam' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'scheduled' => 'مجدول',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغي',
                        'rescheduled' => 'تم إعادة الجدولة',
                        default => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'scheduled' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'rescheduled' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('has_conflicts')
                    ->label('تضارب')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->toggleable(),

                IconColumn::make('attendance_taken')
                    ->label('الحضور')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),

                TextColumn::make('duration_formatted')
                    ->label('المدة')
                    ->getStateUsing(fn($record) => $record->duration_formatted)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'scheduled' => 'مجدول',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغي',
                        'rescheduled' => 'تم إعادة الجدولة',
                    ]),

                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'regular' => 'عادي',
                        'makeup' => 'تعويضي',
                        'extra' => 'إضافي',
                        'exam' => 'امتحان',
                    ]),

                SelectFilter::make('group_id')
                    ->label('المجموعة')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('classroom_id')
                    ->label('القاعة')
                    ->relationship('classroom', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('upcoming')
                    ->label('القادمة فقط')
                    ->query(fn(Builder $query) => $query->upcoming()),

                Filter::make('today')
                    ->label('اليوم فقط')
                    ->query(fn(Builder $query) => $query->today()),

                Filter::make('has_conflicts')
                    ->label('بها تضاربات')
                    ->query(fn(Builder $query) => $query->where('has_conflicts', true)),
            ])
            ->actions([
                ViewAction::make()
                    ->label('عرض'),

                Action::make('record_session')
                    ->label('تسجيل السيشن')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'scheduled')
                    ->action(function ($record) {
                        \App\Actions\IncrementGroupSessions::execute($record->group);
                        $record->update(['status' => 'completed']);
                    })
                    ->successNotificationTitle('تم تسجيل السيشن بنجاح!')
                    ->successNotificationMessage('تم تحديث جميع الطلاب النشطين في المجموعة'),

                Action::make('take_attendance')
                    ->label('تسجيل الحضور')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->visible(fn($record) => 
                        $record->status === 'scheduled' && 
                        !$record->attendance_taken &&
                        $record->isOngoing()
                    )
                    ->url(fn($record) => route('filament.instructor.pages.take-attendance', [
                        'session' => $record->id,
                        'group_id' => $record->group_id,
                        'date' => $record->date?->format('Y-m-d'),
                    ])),

                EditAction::make()
                    ->label('تعديل')
                    ->visible(fn($record) => $record->status === 'scheduled' && $record->isUpcoming()),

                Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'scheduled')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('cancellation_reason')
                            ->label('سبب الإلغاء')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->cancel(auth()->id(), $data['cancellation_reason']);
                    }),

                DeleteAction::make()
                    ->label('حذف')
                    ->visible(fn($record) => $record->status === 'cancelled'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
