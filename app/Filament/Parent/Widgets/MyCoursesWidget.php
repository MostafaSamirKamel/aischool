<?php

namespace App\Filament\Parent\Widgets;

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
        $studentId = \App\Services\Parent\ParentStudentAccessService::getSelectedStudentId();

        if (!$studentId) {
            return $table->query(Enrollment::query()->whereRaw('1 = 0'));
        }

        return $table
            ->query(
                Enrollment::query()
                    ->where('student_id', $studentId)
                    ->with(['track', 'groups'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('track.name')
                    ->label('المسار')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('group_display')
                    ->label('المجموعة')
                    ->getStateUsing(function ($record) {
                        return $record->groups
                                ?->pluck('name')
                            ->filter()
                            ->unique()
                            ->join(', ') ?: '-';
                    }),
                    
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
            ]);
    }
}
