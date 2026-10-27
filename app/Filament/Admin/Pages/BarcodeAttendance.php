<?php

namespace App\Filament\Admin\Pages;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Enrollment;
use App\Models\SessionSchedule;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class BarcodeAttendance extends Page
{
    protected string $view = 'filament.admin.pages.barcode-attendance';

    protected static ?string $navigationLabel = 'تسجيل حضور بالباركود';

    protected static ?string $title = 'تسجيل حضور الطلاب بالباركود';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-qr-code';

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 51;

    public static function getNavigationGroup(): string
    {
        return __('filament/admin/navigation.students_and_registration');
    }

    public ?string $barcode = null;

    public ?array $lastResult = null;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && ($user->is_super_admin || $user->hasAnyRole(['secretary', 'branch_manager', 'super_admin']));
    }

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasAnyRole(['secretary', 'branch_manager', 'super_admin']), 403);
    }

    public function scanBarcode(): void
    {
        $barcode = trim((string) $this->barcode);
        $this->lastResult = null;

        if ($barcode === '') {
            Notification::make()
                ->warning()
                ->title('أدخل الباركود')
                ->send();
            return;
        }

        $student = Student::query()
            ->where('student_code', $barcode)
            ->with('user')
            ->first();

        if (!$student) {
            Notification::make()
                ->danger()
                ->title('الطالب غير موجود')
                ->body('تأكد من صحة الباركود.')
                ->send();
            return;
        }

        $enrollments = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->with('groups.course')
            ->get();

        if ($enrollments->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('لا يوجد تسجيل نشط')
                ->body('هذا الطالب ليس لديه مجموعة نشطة حالياً.')
                ->send();
            return;
        }

        // Get all groups from all enrollments (many-to-many)
        $groupIds = $enrollments->flatMap(function ($enrollment) {
            return $enrollment->groups->pluck('id');
        })->unique()->values();

        $todaySchedules = SessionSchedule::query()
            ->whereIn('group_id', $groupIds)
            ->whereDate('date', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();

        if ($todaySchedules->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('لا توجد جلسة اليوم')
                ->body('لا توجد جلسة مجدولة لهذا الطالب اليوم.')
                ->send();
            return;
        }

        $schedule = $this->resolveSchedule($todaySchedules);
        if (!$schedule) {
            Notification::make()
                ->warning()
                ->title('تعذر تحديد الجلسة')
                ->body('يرجى التحقق من جدول الجلسات.')
                ->send();
            return;
        }

        $group = $schedule->group;
        $courseId = $schedule->course_id ?? $group?->course_id;

        if (!$group || !$courseId) {
            Notification::make()
                ->danger()
                ->title('بيانات الجلسة غير مكتملة')
                ->send();
            return;
        }

        $session = AttendanceSession::updateOrCreate(
            [
                'group_id' => $group->id,
                'attendance_date' => now()->toDateString(),
            ],
            [
                'tenant_id' => auth()->user()->tenant_id,
                'course_id' => $courseId,
                'department_id' => $group->department_id,
                'classroom' => $group->classroom?->name ?? $group->classroom?->code,
                'recorded_by' => auth()->id(),
            ]
        );

        $existingRecord = AttendanceRecord::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingRecord) {
            Notification::make()
                ->warning()
                ->title('تم تسجيل الحضور مسبقاً')
                ->body('الطالب مسجل بالفعل في جلسة اليوم.')
                ->send();

            $this->lastResult = $this->buildResult($student, $group, $schedule, $session, true);
            $this->barcode = null;
            return;
        }

        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'present',
            'check_in_time' => now()->format('H:i'),
            'notes' => 'تم التسجيل بالباركود',
        ]);

        $completedSessions = AttendanceSession::where('group_id', $group->id)->count();
        $group->update(['completed_sessions' => $completedSessions]);

        $schedule->markAttendanceTaken();

        $this->lastResult = $this->buildResult($student, $group, $schedule, $session, false);

        Notification::make()
            ->success()
            ->title('تم تسجيل الحضور')
            ->body($student->user?->name . ' - ' . $group->name)
            ->send();

        $this->barcode = null;
    }

    private function resolveSchedule(Collection $schedules): ?SessionSchedule
    {
        $now = now();

        foreach ($schedules as $schedule) {
            $start = Carbon::parse($schedule->date . ' ' . $schedule->start_time);
            $end = Carbon::parse($schedule->date . ' ' . $schedule->end_time);
            if ($now->between($start, $end)) {
                return $schedule;
            }
        }

        foreach ($schedules as $schedule) {
            $start = Carbon::parse($schedule->date . ' ' . $schedule->start_time);
            if ($start->greaterThan($now)) {
                return $schedule;
            }
        }

        return $schedules->last();
    }

    private function buildResult(Student $student, $group, SessionSchedule $schedule, AttendanceSession $session, bool $alreadyRecorded): array
    {
        return [
            'student_name' => $student->user?->name ?? 'غير محدد',
            'student_code' => $student->student_code,
            'group_name' => $group->name ?? 'غير محدد',
            'course_name' => $group->course?->name ?? 'غير محدد',
            'session_time' => $schedule->time_range,
            'recorded' => !$alreadyRecorded,
            'session_id' => $session->id,
        ];
    }
}
