<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\Group;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingGroupsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('المجموعات القادمة')
            ->query(
                Group::query()
                    ->where('instructor_id', auth()->user()->instructor?->id)
                    ->where('status', 'pending')
                    ->where('start_date', '>', now())
                    ->orderBy('start_date')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المجموعة')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('الكورس'),

                Tables\Columns\TextColumn::make('classroom.name')
                    ->label('القاعة')
                    ->default('غير محدد'),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('الطلاب')
                    ->formatStateUsing(fn($record) => "{$record->capacity} / {$record->max_students}"),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
