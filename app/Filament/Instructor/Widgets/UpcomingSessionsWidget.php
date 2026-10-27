<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\SessionSchedule;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingSessionsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        if (!$user) {
            return $table
                ->query(SessionSchedule::query()->whereRaw('1 = 0'))
                ->emptyStateHeading('لا يوجد مستخدم مسجل')
                ->emptyStateDescription('يجب تسجيل الدخول أولاً');
        }

        $instructor = $user->instructor;

        // إذا لم يكن للمستخدم instructor relationship، استخدم query فارغ
        if (!$instructor) {
            return $table
                ->query(SessionSchedule::query()->whereRaw('1 = 0'))
                ->emptyStateHeading('لا يوجد حساب مدرس مرتبط')
                ->emptyStateDescription('يجب ربط حساب مدرس بهذا المستخدم');
        }

        return $table
            ->query(
                SessionSchedule::query()
                    ->where('tenant_id', $user->tenant_id)
                    ->whereHas('instructor', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->orderBy('date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('time_range')
                    ->label('الوقت')
                    ->getStateUsing(fn($record) => $record->time_range),

                TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->default('سيشن عادي')
                    ->limit(30),

                TextColumn::make('classroom.name')
                    ->label('القاعة')
                    ->default('غير محدد'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'regular' => 'عادي',
                        'makeup' => 'تعويضي',
                        'extra' => 'إضافي',
                        'exam' => 'امتحان',
                        default => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'regular' => 'info',
                        'makeup' => 'warning',
                        'extra' => 'success',
                        'exam' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.instructor.resources.session-schedules.session-schedules.view', ['record' => $record->id])),
            ])
            ->heading('السيشنات')
            ->description('جدول السيشنات المجدولة');
    }
}
