<?php

namespace App\Filament\Parent\Pages;

use BackedEnum;
use Filament\Tables;
use Filament\Pages\Page;
use App\Models\AttendanceRecord;
use App\Models\EnrollmentCourse;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class MyCourses extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.parent.pages.my-courses';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('كورسات الطالب');
    }

    public function getTitle(): string
    {
        return __('كورسات الطالب');
    }

    public function getHeading(): string
    {
        return __('كورسات الطالب');
    }

    protected function getSelectedStudentId(): ?int
    {
        return \App\Services\Parent\ParentStudentAccessService::getSelectedStudentId();
    }

    public function table(Table $table): Table
    {
        $selectedStudentId = $this->getSelectedStudentId();

        return $table
            ->query(
                EnrollmentCourse::query()
                    ->when(
                        $selectedStudentId,
                        fn($q) => $q->whereHas('enrollment', fn($eq) => $eq->where('student_id', $selectedStudentId)->where('status', 'active')),
                        fn($q) => $q->whereRaw('1 = 0')
                    )
                    ->with([
                        'group.course',
                        'group.instructor.user',
                        'group.attendanceSessions',
                        'enrollment',
                        'enrollment.groups.instructor.user',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label(__('الكورس'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-book-open')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('group_display')
                    ->label(__('المجموعة'))
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        if ($record->group?->name) {
                            return $record->group->name;
                        }

                        $groups = $record->enrollment?->groups;
                        if ($groups && $groups->count() > 0) {
                            $matched = $groups->where('course_id', $record->course_id);
                            if ($matched->count() > 0) {
                                return $matched->pluck('name')->filter()->unique()->join(', ');
                            }
                        }

                        return '-';
                    }),

                Tables\Columns\TextColumn::make('instructor_display')
                    ->label(__('المدرس'))
                    ->icon('heroicon-o-user')
                    ->getStateUsing(function ($record) {
                        if ($record->group?->instructor?->user?->name) {
                            return $record->group->instructor->user->name;
                        }

                        $groups = $record->enrollment?->groups;
                        $matched = $groups ? $groups->where('course_id', $record->course_id) : null;
                        $names = $matched
                                ?->map(fn($group) => $group->instructor?->user?->name)
                            ->filter()
                            ->unique()
                            ->join(', ');

                        return $names ?: '-';
                    }),

                Tables\Columns\TextColumn::make('group.schedule')
                    ->label(__('المواعيد'))
                    ->wrap()
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        $schedule = $record->group?->schedule;
                        if (!$schedule) {
                            $groups = $record->enrollment?->groups;
                            $matched = $groups ? $groups->where('course_id', $record->course_id) : null;
                            $schedule = $matched?->first()?->schedule;
                        }
                        if (!$schedule)
                            return '-';
                        $schedules = is_string($schedule) ? json_decode($schedule, true) : $schedule;
                        if (!is_array($schedules)) return '-';
                        
                        $days = [
                            'saturday' => 'السبت',
                            'sunday' => 'الأحد',
                            'monday' => 'الاثنين',
                            'tuesday' => 'الثلاثاء',
                            'wednesday' => 'الأربعاء',
                            'thursday' => 'الخميس',
                            'friday' => 'الجمعة',
                        ];
                        
                        $formatted = [];
                        foreach ($schedules as $schedule) {
                            $day = $days[$schedule['day']] ?? $schedule['day'];
                            $formatted[] = "<strong>{$day}</strong>: {$schedule['start_time']} - {$schedule['end_time']}";
                        }
                        return implode('<br>', $formatted);
                    }),

                Tables\Columns\TextColumn::make('attendance_rate')
                    ->label(__('نسبة الحضور'))
                    ->formatStateUsing(function ($record) use ($selectedStudentId) {
                        $group = $record->group;
                        if (!$group) {
                            $groups = $record->enrollment?->groups;
                            $matched = $groups ? $groups->where('course_id', $record->course_id) : null;
                            $group = $matched?->first();
                        }

                        if (!$group) {
                            return '-';
                        }

                        $totalSessions = $group->attendanceSessions()->count();
                        if ($totalSessions == 0) return '0%';

                        $presentCount = AttendanceRecord::where('student_id', $selectedStudentId)
                            ->whereHas('session', fn($q) => $q->where('group_id', $group->id))
                            ->where('status', 'present')
                            ->count();
                        
                        $rate = round(($presentCount / $totalSessions) * 100);
                        return "{$rate}%";
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        intval($state) >= 90 => 'success',
                        intval($state) >= 75 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('enrollment.enrollment_date')
                    ->label(__('تاريخ الالتحاق'))
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('الحالة'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'completed' => 'مكتمل',
                        'dropped' => 'منسحب',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'completed' => 'info',
                        'dropped' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')
                    ->label(__('الكورس'))
                    ->relationship('course', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('view_details')
                    ->label(__('عرض التفاصيل'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn($record) => __('تفاصيل') . ' ' . ($record->course?->name ?? '-'))
                    ->modalContent(fn ($record) => view('filament.parent.pages.course-details-modal', [
                        'enrollmentCourse' => $record,
                        'group' => $record->group,
                        'studentId' => $selectedStudentId,
                    ]))
                    ->modalWidth('5xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('إغلاق')),
            ])
            ->emptyStateHeading(__('لا توجد كورسات'))
            ->emptyStateDescription(__('لم يلتحق الطالب بأي كورسات بعد'))
            ->emptyStateIcon('heroicon-o-academic-cap');
    }
}
