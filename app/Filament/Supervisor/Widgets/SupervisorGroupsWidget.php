<?php

namespace App\Filament\Supervisor\Widgets;

use App\Models\Group;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class SupervisorGroupsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $supervisor = $user?->supervisor;
        $supervisorIds = array_filter([$user?->id, $supervisor?->id]);

        return $table
            ->heading('المجموعات المُشرف عليها')
            ->query(
                Group::query()
                    ->where(function ($q) use ($supervisorIds, $user) {
                        $q->whereIn('supervisor_id', $supervisorIds)
                            ->orWhereHas('assignedSupervisors', fn ($sq) => $sq->where('users.id', $user?->id));
                    })
                    ->with(['course', 'instructor.user'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المجموعة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('الدورة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('instructor.user.name')
                    ->label('المدرس')
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->counts('students')
                    ->badge()
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'cancelled',
                        'info' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'active' => 'نشطة',
                        'cancelled' => 'ملغاة',
                        'completed' => 'مكتملة',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاريخ البداية')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
