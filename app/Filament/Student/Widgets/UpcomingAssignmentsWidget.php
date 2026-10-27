<?php

namespace App\Filament\Student\Widgets;

use App\Models\Assignment;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingAssignmentsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getTableHeading(): ?string
    {
        return 'الواجبات القادمة';
    }

    public function table(Table $table): Table
    {
        $student = Student::where('user_id', auth()->id())->first();
        
        return $table
            ->query(
                Assignment::query()
                    ->when($student, function($query) use ($student) {
                        $query->whereHas('group.enrollments', function($q) use ($student) {
                            $q->where('student_id', $student->id);
                        });
                    })
                    ->where('due_date', '>=', now())
                    ->with(['group.course'])
                    ->orderBy('due_date')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('group.course.name')
                    ->label('الدورة')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ التسليم')
                    ->dateTime()
                    ->sortable()
                    ->color(fn($record) => $record->due_date->isPast() ? 'danger' : ($record->due_date->diffInDays(now()) <= 3 ? 'warning' : 'success')),
                    
                Tables\Columns\TextColumn::make('max_score')
                    ->label('الدرجة الكلية')
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->getStateUsing(function($record) use ($student) {
                        $submission = $record->submissions()->where('student_id', $student?->id)->first();
                        if (!$submission) return 'pending';
                        return $submission->submitted_at ? 'submitted' : 'draft';
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'submitted',
                        'info' => 'draft',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'معلق',
                        'submitted' => 'تم التسليم',
                        'draft' => 'مسودة',
                        default => $state,
                    }),
            ]);
    }
}
