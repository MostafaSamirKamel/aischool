<?php

namespace App\Filament\Supervisor\Widgets;

use App\Models\AttendanceSession;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentAttendanceWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $supervisor = $user?->supervisor;
        $supervisorIds = array_filter([$user?->id, $supervisor?->id]);

        return $table
            ->heading('جلسات الحضور الأخيرة')
            ->query(
                AttendanceSession::query()
                    ->whereHas('group', function ($query) use ($supervisorIds, $user) {
                        $query->whereIn('supervisor_id', $supervisorIds)
                            ->orWhereHas('assignedSupervisors', fn ($sq) => $sq->where('users.id', $user?->id));
                    })
                    ->with(['group', 'instructor.user'])
                    ->latest('attendance_date')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('تاريخ الجلسة')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('instructor.user.name')
                    ->label('المدرس')
                    ->default('—'),

                Tables\Columns\TextColumn::make('attendances_count')
                    ->label('الحاضرون')
                    ->counts([
                        'attendances' => fn ($query) => $query->where('status', 'present')
                    ])
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('absents_count')
                    ->label('الغائبون')
                    ->counts([
                        'attendances' => fn ($query) => $query->where('status', 'absent')
                    ])
                    ->badge()
                    ->color('danger'),

                Tables\Columns\BadgeColumn::make('status')
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
            ->defaultSort('attendance_date', 'desc');
    }
}
