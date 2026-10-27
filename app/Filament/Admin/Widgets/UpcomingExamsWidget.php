<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Exam;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingExamsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('الامتحانات القادمة')
            ->query(
                Exam::query()
                    ->where('exam_date', '>', now())
                    ->where('status', 'scheduled')
                    ->orderBy('exam_date', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('الكورس')
                    ->searchable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('المجموعة'),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('النوع')
                    ->colors([
                        'primary' => 'quiz',
                        'warning' => 'midterm',
                        'danger' => 'final',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'quiz' => 'كويز',
                        'midterm' => 'ميدتيرم',
                        'final' => 'فاينال',
                        'assignment' => 'تكليف',
                        'project' => 'مشروع',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('exam_date')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->description(fn($record) => $record->duration . ' دقيقة'),

                Tables\Columns\TextColumn::make('total_marks')
                    ->label('الدرجة الكلية')
                    ->numeric()
                    ->suffix(' درجة'),

                Tables\Columns\TextColumn::make('days_until')
                    ->label('متبقي')
                    ->getStateUsing(fn($record) => now()->diffInDays($record->exam_date))
                    ->suffix(' يوم')
                    ->color(fn($state) => $state <= 3 ? 'danger' : ($state <= 7 ? 'warning' : 'success')),
            ])
            ->defaultPaginationPageOption(5);
    }
}
