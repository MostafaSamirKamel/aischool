<?php

namespace App\Filament\Parent\Widgets;

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

    protected static ?int $sort = 2;

    public function getTableHeading(): ?string
    {
        return 'الدرجات الأخيرة';
    }

    public function table(Table $table): Table
    {
        $studentId = \App\Services\Parent\ParentStudentAccessService::getSelectedStudentId();
        
        return $table
            ->query(
                Grade::query()
                    ->when($studentId, fn($query) => $query->where('student_id', $studentId), fn($query) => $query->whereRaw('1 = 0'))
                    ->with(['exam'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('الامتحان')
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
