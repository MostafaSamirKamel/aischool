<?php

namespace App\Filament\Student\Widgets;

use App\Models\Grade;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentGradesWidget extends BaseWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function getTableHeading(): ?string
    {
        return 'الدرجات الأخيرة';
    }

    public function table(Table $table): Table
    {
        $student = Student::where('user_id', auth()->id())->first();
        
        return $table
            ->query(
                Grade::query()
                    ->when($student, fn($query) => $query->where('student_id', $student->id))
                    ->with(['exam.course'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('الامتحان')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('exam.course.name')
                    ->label('الدورة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('marks_obtained')
                    ->label('الدرجة')
                    ->formatStateUsing(fn($record) => $record->marks_obtained . ' / ' . $record->exam->total_marks),
                    
                Tables\Columns\TextColumn::make('percentage')
                    ->label('النسبة المئوية')
                    ->formatStateUsing(fn($record) => number_format($record->percentage, 1) . '%')
                    ->color(fn ($record) =>
                        $record->percentage >= 60 ? 'success' : 'danger'
                    ),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
            ]);
    }
}
