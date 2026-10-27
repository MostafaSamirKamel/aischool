<?php

namespace App\Filament\Student\Widgets;

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

    protected static ?int $sort = 4;

    public function getTableHeading(): ?string
    {
        return 'سجل الحضور الأخير';
    }

    public function table(Table $table): Table
    {
        $student = Student::where('user_id', auth()->id())->first();
        
        return $table
            ->query(
                AttendanceRecord::query()
                    ->when($student, fn($query) => $query->where('student_id', $student->id))
                    ->with(['session.group.course'])
                    ->orderByDesc('created_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('session.attendance_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                    
                Tables\Columns\TextColumn::make('session.group.course.name')
                    ->label('الكورس')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('session.group.name')
                    ->label('المجموعة')
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('وقت الحضور')
                    ->time('H:i')
                    ->icon('heroicon-o-clock')
                    ->placeholder('-'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'present' => 'حاضر',
                        'absent' => 'غائب',
                        'late' => 'متأخر',
                        'excused' => 'غياب بعذر',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'late' => 'warning',
                        'excused' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn ($state) => match ($state) {
                        'present' => 'heroicon-o-check-circle',
                        'absent' => 'heroicon-o-x-circle',
                        'late' => 'heroicon-o-clock',
                        'excused' => 'heroicon-o-information-circle',
                        default => null,
                    }),
            ]);
    }
}
