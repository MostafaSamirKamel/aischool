<?php

namespace App\Filament\Supervisor\Resources\AttendanceSessions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AttendanceSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attendance_date')
                    ->label(__('filament/supervisor/attendance_session_resource.attendance_date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('group.name')
                    ->label(__('filament/supervisor/attendance_session_resource.group.name'))
                    ->searchable(),

                TextColumn::make('instructor.user.name')
                    ->label(__('filament/supervisor/attendance_session_resource.instructor.user.name'))
                    ->default('—'),

                TextColumn::make('attendances_count')
                    ->label(__('filament/supervisor/attendance_session_resource.attendances_count'))
                    ->counts([
                        'attendances' => fn ($query) => $query->where('status', 'present')
                    ])
                    ->badge()
                    ->color('success'),

                TextColumn::make('absents_count')
                    ->label(__('filament/supervisor/attendance_session_resource.absents_count'))
                    ->counts([
                        'attendances' => fn ($query) => $query->where('status', 'absent')
                    ])
                    ->badge()
                    ->color('danger'),

                TextColumn::make('late_count')
                    ->label(__('filament/supervisor/attendance_session_resource.late_count'))
                    ->counts([
                        'attendances' => fn ($query) => $query->where('status', 'late')
                    ])
                    ->badge()
                    ->color('warning'),

                BadgeColumn::make('status')
                    ->label('حالة الجلسة')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة الجلسة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('attendance_date', 'desc');
    }
}
