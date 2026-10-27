<?php

namespace App\Filament\Instructor\Resources\AttendanceSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attendance_date')
                    ->label(__('filament/instructor/attendance_session_resource.attendance_date'))
                    ->date()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('group.name')
                    ->label(__('filament/instructor/attendance_session_resource.group.name'))
                    ->sortable(),
                TextColumn::make('course.name')
                    ->label(__('filament/instructor/attendance_session_resource.course.name'))
                    ->sortable(),
                TextColumn::make('classroom')
                    ->label(__('filament/instructor/attendance_session_resource.classroom'))
                    ->default('-'),
                TextColumn::make('records_count')
                    ->label(__('filament/instructor/attendance_session_resource.records_count'))
                    ->counts('records')
                    ->sortable(),
                TextColumn::make('present_count')
                    ->label(__('filament/instructor/attendance_session_resource.present_count'))
                    ->getStateUsing(function ($record) {
                        return $record->records()->where('status', 'present')->count();
                    })
                    ->color('success'),
                TextColumn::make('absent_count')
                    ->label(__('filament/instructor/attendance_session_resource.absent_count'))
                    ->getStateUsing(function ($record) {
                        return $record->records()->where('status', 'absent')->count();
                    })
                    ->color('danger'),
                TextColumn::make('late_count')
                    ->label(__('filament/instructor/attendance_session_resource.late_count'))
                    ->getStateUsing(function ($record) {
                        return $record->records()->where('status', 'late')->count();
                    })
                    ->color('warning'),
                TextColumn::make('created_at')
                    ->label(__('filament/instructor/attendance_session_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('group_id')
                    ->label('المجموعة')
                    ->relationship('group', 'name'),
                Filter::make('attendance_date')
                    ->label('تاريخ الحضور')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('من'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('attendance_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('attendance_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('attendance_date', 'desc');
    }
}
