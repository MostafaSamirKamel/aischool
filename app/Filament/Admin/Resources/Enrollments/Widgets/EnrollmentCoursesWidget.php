<?php

namespace App\Filament\Admin\Resources\Enrollments\Widgets;

use App\Filament\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class EnrollmentCoursesWidget extends Widget
{
    use HasWidgetShield;
    
    protected string $view = 'filament.admin.resources.enrollments.widgets.enrollment-courses-widget';

    protected int | string | array $columnSpan = 'full';

    public ?Model $record = null;

    protected function getViewData(): array
    {
        $enrollment = $this->record;

        if (!$enrollment) {
            return ['courses' => [], 'availableGroups' => []];
        }

        $enrollment->load([
            'enrollmentCourses' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'enrollmentCourses.course' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'enrollmentCourses.group.sessionSchedules',
            'enrollmentCourses.group.instructor.user',
            'student.attendanceRecords',
        ]);

        $enrollmentNetAmount = max(0, (float) $enrollment->total_amount - (float) $enrollment->discount);
        $enrollmentPaidAmount = $enrollment->getEffectivePaidAmount();
        $paymentRatio = $enrollmentNetAmount > 0
            ? min(1, $enrollmentPaidAmount / $enrollmentNetAmount)
            : 1;

        $courses = [];
        foreach ($enrollment->enrollmentCourses as $enrollmentCourse) {
            $course = $enrollmentCourse->course;

            // تخطي فقط لو الكورس مش موجود
            if (!$course)
                continue;

            $group = $enrollmentCourse->group;
            $courseStatus = $enrollmentCourse->status ?? (!is_null($group) ? 'active' : 'inactive');
            $isActive = $courseStatus === 'active';
            $coursePrice = (float) ($enrollmentCourse->course_price ?? $course->price ?? 0);
            $coursePaidAmount = round($coursePrice * $paymentRatio, 2);
            $courseRemainingAmount = max(0, round($coursePrice - $coursePaidAmount, 2));

            $paymentStatus = $courseRemainingAmount <= 0
                ? 'paid'
                : ($coursePaidAmount > 0 ? 'partial' : 'pending');

            // جلب المجموعات المتاحة للكورس
            $availableGroups = \App\Models\Group::where('course_id', $course->id)
                ->where('status', 'active')
                ->with('instructor.user')
                ->get()
                ->map(function ($g) {
                    return [
                        'id' => $g->id,
                        'name' => $g->name,
                        'instructor_name' => optional(optional($g->instructor)->user)->name,
                    ];
                })
                ->toArray();

            // لو مافيش group، نعرض الكورس بدون تفاصيل الجلسات
            if (!$group) {
                $courses[] = [
                    'enrollment_course_id' => $enrollmentCourse->id,
                    'course_name' => $course->name,
                    'course_code' => $course->code ?? 'N/A',
                    'course_price' => $coursePrice,
                    'course_paid_amount' => $coursePaidAmount,
                    'course_remaining_amount' => $courseRemainingAmount,
                    'payment_status' => $paymentStatus,
                    'is_active' => false,
                    'has_group' => false,
                    'course_status' => $courseStatus,
                    'group_id' => null,
                    'group_name' => null,
                    'instructor_name' => null,
                    'total_sessions' => 0,
                    'completed_sessions' => 0,
                    'remaining_sessions' => 0,
                    'attended_sessions' => 0,
                    'session_progress' => 0,
                    'attendance_progress' => 0,
                    'sessions' => [],
                    'available_groups' => $availableGroups,
                ];
                continue;
            }

            $sessions = $group->sessionSchedules->map(function ($session) {
                $sessionDate = \Carbon\Carbon::parse($session->date);
                $startTime = \Carbon\Carbon::parse($session->start_time);
                $endTime = \Carbon\Carbon::parse($session->end_time);
                $now = now();

                $status = 'مجدولة';
                if ($sessionDate->isPast()) {
                    $status = 'منتهية';
                } elseif ($sessionDate->isToday()) {
                    if ($now->between($startTime, $endTime)) {
                        $status = 'جارية';
                    } elseif ($now->greaterThan($endTime)) {
                        $status = 'منتهية';
                    }
                }

                return [
                    'id' => $session->id,
                    'date' => $sessionDate->format('Y-m-d'),
                    'day' => $sessionDate->locale('ar')->translatedFormat('l'),
                    'start_time' => $startTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                    'status' => $status,
                    'attendance_taken' => $session->attendance_taken,
                ];
            })->toArray();

            // إجمالي الجلسات: الأولوية للكورس، ثم المجموعة، ثم عدد SessionSchedules الموجودة
            $totalSessions = $course->sessions_count ?? $group->total_sessions ?? count($sessions);
            
            // لو sessions_count = 0، استخدم عدد SessionSchedules كـ fallback
            if ($totalSessions == 0 && count($sessions) > 0) {
                $totalSessions = count($sessions);
            }

            // الجلسات المنجزة (تم تسجيل حضور لها)
            $completedSessions = \App\Models\AttendanceSession::where('group_id', $group->id)->count();

            // الجلسات المتبقية
            $remainingSessions = $totalSessions - $completedSessions;

            // الحضور الفعلي للطالب (عدد المرات اللي حضر فيها)
            $attendedSessions = \App\Models\AttendanceRecord::where('student_id', $enrollment->student_id)
                ->whereHas('session', function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                })
                ->where('status', 'present')
                ->count();

            // Progress percentages
            $sessionProgress = $totalSessions > 0 ? ($completedSessions / $totalSessions) * 100 : 0;
            $attendanceProgress = $totalSessions > 0 ? ($attendedSessions / $totalSessions) * 100 : 0;

            // جلب المجموعات المتاحة للكورس
            $availableGroups = \App\Models\Group::where('course_id', $course->id)
                ->where('status', 'active')
                ->with('instructor.user')
                ->get()
                ->map(function ($g) {
                    return [
                        'id' => $g->id,
                        'name' => $g->name,
                        'instructor_name' => optional(optional($g->instructor)->user)->name,
                    ];
                })
                ->toArray();

            $courses[] = [
                'enrollment_course_id' => $enrollmentCourse->id,
                'course_name' => $course->name,
                'course_code' => $course->code ?? 'N/A',
                'course_price' => $coursePrice,
                'course_paid_amount' => $coursePaidAmount,
                'course_remaining_amount' => $courseRemainingAmount,
                'payment_status' => $paymentStatus,
                'is_active' => $isActive,
                'has_group' => true,
                'course_status' => $courseStatus,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'instructor_name' => optional(optional($group->instructor)->user)->name ?? 'غير محدد',
                'total_sessions' => $totalSessions,
                'completed_sessions' => $completedSessions,
                'remaining_sessions' => $remainingSessions,
                'attended_sessions' => $attendedSessions,
                'session_progress' => round($sessionProgress, 1),
                'attendance_progress' => round($attendanceProgress, 1),
                'sessions' => $sessions,
                'available_groups' => $availableGroups,
            ];
        }

        return ['courses' => $courses];
    }

    public function assignGroup($enrollmentCourseId, $groupId)
    {
        try {
            $enrollmentCourse = \App\Models\EnrollmentCourse::find($enrollmentCourseId);

            if (!$enrollmentCourse || !$groupId) {
                \Filament\Notifications\Notification::make()
                    ->title('خطأ')
                    ->body('حدث خطأ أثناء تعيين المجموعة')
                    ->danger()
                    ->send();
                return;
            }

            $oldGroup = $enrollmentCourse->group;
            $newGroup = \App\Models\Group::find($groupId);

            if (!$newGroup) {
                \Filament\Notifications\Notification::make()
                    ->title('خطأ')
                    ->body('المجموعة المحددة غير موجودة')
                    ->danger()
                    ->send();
                return;
            }

            // إذا كان هناك تبديل مجموعة سابقة
            if ($oldGroup && (int) $oldGroup->id !== (int) $newGroup->id) {
                $transferService = app(\App\Services\Enrollment\StudentGroupTransferService::class);
                
                try {
                    $transferService->validateTransfer($enrollmentCourse->enrollment, $newGroup);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    $firstError = collect($e->errors())->flatten()->first();
                    \Filament\Notifications\Notification::make()
                        ->title('تعذر نقل المجموعة')
                        ->body($firstError)
                        ->danger()
                        ->send();
                    return;
                }

                // تنفيذ النقل الشامل المتوافق مع السياسات
                $transferService->transfer($enrollmentCourse->enrollment, $newGroup, [
                    'transferred_by' => auth()->user(),
                    'reason' => 'تم التغيير من جدول الكورسات بالتسجيل',
                    'progress_warning_acknowledged' => true,
                ]);

                \Filament\Notifications\Notification::make()
                    ->title('تم نقل الطالب بنجاح')
                    ->body('تم نقل الطالب إلى المجموعة الجديدة بنجاح.')
                    ->success()
                    ->send();

                $this->dispatch('$refresh');
                return;
            }

            // تفعيل المجموعة لأول مرة (إن لم تكن محددة من قبل)
            $enrollmentCourse->update([
                'group_id' => $groupId,
                'status' => 'active',
            ]);

            $enrollment = $enrollmentCourse->enrollment;
            if (!$enrollment->group_id) {
                $enrollment->update(['group_id' => $groupId]);
            }
            $enrollment->groups()->syncWithoutDetaching([$groupId => ['enrolled_at' => now()]]);
            $newGroup->syncCapacity();

            \Filament\Notifications\Notification::make()
                ->title('تم تعيين المجموعة بنجاح')
                ->body('تم تفعيل الكورس وتعيين المجموعة بنجاح')
                ->success()
                ->send();

            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
