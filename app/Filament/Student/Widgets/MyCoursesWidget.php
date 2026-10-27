<?php

namespace App\Filament\Student\Widgets;

use App\Models\Enrollment;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyCoursesWidget extends BaseWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getTableHeading(): ?string
    {
        return 'دوراتي';
    }

    public function table(Table $table): Table
    {
        $student = Student::where('user_id', auth()->id())->first();
        
        return $table
            ->query(
                Enrollment::query()
                    ->when($student, fn($query) => $query->where('student_id', $student->id))
                    ->with(['enrollmentCourses.course', 'enrollmentCourses.group'])
                    ->where('status', 'active')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('enrollmentCourses.course.name')
                    ->label('الدورة')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->enrollmentCourses->pluck('course.name')->join(', ') ?: '-'
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollmentCourses.group.name')
                    ->label('المجموعات')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->enrollmentCourses
                            ->pluck('group.name')
                            ->filter()
                            ->join(', ') ?: 'غير محدد'
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_session')
                    ->label('السيشن الحالي')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->enrollmentCourses->map(fn($ec) => "{$ec->current_session}/12")->join(', ') ?: '-'
                    ),

                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('تاريخ التسجيل')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'active' => 'نشط',
                        'pending' => 'معلق',
                        'cancelled' => 'ملغي',
                        default => $state,
                    }),
            ])
            ->paginated(false);
    }
}
