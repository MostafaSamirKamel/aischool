<?php

namespace App\Filament\Student\Pages;

use Filament\Tables;
use Filament\Pages\Page;
use App\Models\AttendanceRecord;
use App\Models\Group;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Concerns\InteractsWithTable;

class MyAttendance extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.student.pages.my-attendance';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('سجل الحضور');
    }

    public function getTitle(): string
    {
        return __('سجل حضوري');
    }

    public function getHeading(): string
    {
        return __('سجل الحضور والغياب');
    }

    public function table(Table $table): Table
    {
        $student = auth()->user()->student;

        return $table
            ->query(
                AttendanceRecord::query()
                    ->where('student_id', $student?->id)
                    ->with(['session.group.course'])
                    ->orderByDesc('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('session.attendance_date')
                    ->label(__('التاريخ'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('session.group.course.name')
                    ->label(__('الكورس'))
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('session.group.name')
                    ->label(__('المجموعة'))
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('check_in_time')
                    ->label(__('وقت الحضور'))
                    ->dateTime('H:i')
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('الحالة'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'present' => 'حاضر',
                        'absent' => 'غائب',
                        'late' => 'متأخر',
                        'excused' => 'غياب بعذر',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'late' => 'warning',
                        'excused' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn ($state) => match ($state) {
                        'present' => 'heroicon-o-check-circle',
                        'absent' => 'heroicon-o-x-circle',
                        'late' => 'heroicon-o-clock',
                        'excused' => 'heroicon-o-information-circle',
                        default => null,
                    }),

                Tables\Columns\TextColumn::make('check_in_time')
                    ->label(__('وقت الحضور'))
                    ->time('H:i')
                    ->placeholder('-')
                    ->icon('heroicon-o-arrow-right-on-rectangle'),

                Tables\Columns\TextColumn::make('check_out_time')
                    ->label(__('وقت الانصراف'))
                    ->time('H:i')
                    ->placeholder('-')
                    ->icon('heroicon-o-arrow-left-on-rectangle'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group_id')
                    ->label(__('المجموعة'))
                    ->options(
                        Group::whereHas('attendanceSessions.records', function ($q) use ($student) {
                            $q->where('student_id', $student?->id);
                        })->pluck('name', 'id')->toArray()
                    )
                    ->searchable()
                    ->query(function ($query, array $data) {
                        return $query->when($data['value'] ?? null, function ($q, $value) {
                            $q->whereHas('session', fn($s) => $s->where('group_id', $value));
                        });
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('الحالة'))
                    ->options([
                        'present' => 'حاضر',
                        'absent' => 'غائب',
                        'late' => 'متأخر',
                        'excused' => 'غياب بعذر',
                    ]),

                Tables\Filters\Filter::make('date')
                    ->form([
                        DatePicker::make('from')
                            ->label(__('من تاريخ')),
                        DatePicker::make('until')
                            ->label(__('إلى تاريخ')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereHas('session', fn($s) => $s->whereDate('attendance_date', '>=', $data['from'])))
                            ->when($data['until'], fn($q) => $q->whereHas('session', fn($s) => $s->whereDate('attendance_date', '<=', $data['until'])));
                    }),
            ])
            ->actions([
                Action::make('view_details')
                    ->label(__('filament/student/my_attendance.view_details'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn ($record) => __('تفاصيل الحضور'))
                    ->modalContent(fn ($record) => view('filament.student.pages.attendance-details-modal', [
                        'attendance' => $record,
                    ]))
                    ->modalWidth('2xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('إغلاق')),
            ])
            ->emptyStateHeading(__('لا توجد سجلات حضور'))
            ->emptyStateDescription(__('لا توجد سجلات حضور متاحة'))
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
