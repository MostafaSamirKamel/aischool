<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\Assignment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAssignmentsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('الواجبات الأخيرة')
            ->query(
                Assignment::query()
                    ->where('instructor_id', auth()->user()->instructor?->id)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->weight('bold')
                    ->limit(50),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('المجموعة'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('موعد التسليم')
                    ->dateTime('d/m/Y H:i')
                    ->color(fn($record) => $record->due_date < now() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('submissions_count')
                    ->label('التسليمات')
                    ->counts('submissions')
                    ->badge()
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => match($state) {
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'closed' => 'مغلق',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'published',
                        'danger' => 'closed',
                    ]),
            ])
            ->actions([
                Action::make('view')
                    ->label('عرض')
                    ->url(fn($record) => route('filament.instructor.resources.assignments.edit', $record))
                    ->icon('heroicon-o-eye'),
            ])
            ->paginated(false);
    }
}
