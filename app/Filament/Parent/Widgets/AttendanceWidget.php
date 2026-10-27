<?php

namespace App\Filament\Parent\Widgets;

use App\Models\AttendanceRecord;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AttendanceWidget extends BaseWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function getTableHeading(): ?string
    {
        return 'سجل الحضور الأخير';
    }

    public function table(Table $table): Table
    {
        $studentId = \App\Services\Parent\ParentStudentAccessService::getSelectedStudentId();

        if (!$studentId) {
            return $table->query(AttendanceRecord::query()->whereRaw('1 = 0'));
        }

        return $table
            ->query(
                AttendanceRecord::query()
                    ->where('student_id', $studentId)
                    ->with(['session.group.course'])
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('session.attendance_date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('attendanceSession.session_number')
                    ->label('رقم الجلسة')
                    ->default('-'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'gray' => 'excused',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'present' => 'حاضر',
                        'absent' => 'غائب',
                        'late' => 'متأخر',
                        'excused' => 'غياب بعذر',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50)
                    ->toggleable(),
            ]);
    }
}
