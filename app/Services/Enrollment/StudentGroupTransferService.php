<?php

namespace App\Services\Enrollment;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Group;
use App\Models\StudentGroupTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentGroupTransferService
{
    /**
     * Check if user is authorized to perform student group transfer.
     */
    public function canUserTransfer(?User $user, Enrollment $enrollment): bool
    {
        if (!$user) {
            return false;
        }

        // Branch isolation check: user cannot transfer an enrollment belonging to another branch
        if (!empty($user->tenant_id) && !empty($enrollment->tenant_id) && (int) $user->tenant_id !== (int) $enrollment->tenant_id) {
            return false;
        }

        // Must possess dedicated transfer_student_group permission
        return $user->can('transfer_student_group');
    }

    /**
     * Calculate academic progress difference between current group/student and target group.
     */
    public function getAcademicProgressDifference(Enrollment $enrollment, Group $targetGroup): array
    {
        $currentGroupId = $enrollment->group_id 
            ?? $enrollment->enrollmentCourses()->where('course_id', $targetGroup->course_id)->value('group_id')
            ?? $enrollment->groups()->first()?->id;

        $studentAttendedSessions = 0;
        if ($currentGroupId) {
            $studentAttendedSessions = AttendanceRecord::where('student_id', $enrollment->student_id)
                ->whereIn('status', ['present', 'late'])
                ->whereHas('session', function ($q) use ($currentGroupId) {
                    $q->where('group_id', $currentGroupId);
                })
                ->count();
        }

        $targetGroupCompletedSessions = AttendanceSession::where('group_id', $targetGroup->id)->count();

        $difference = $targetGroupCompletedSessions - $studentAttendedSessions;
        $hasDifference = ($difference !== 0);

        $warningMessage = null;
        if ($hasDifference) {
            if ($difference > 0) {
                $warningMessage = "تنبيه: المجموعة الجديدة متقدمة في السيشنز عن تقدم الطالب الحالي (أكملت {$targetGroupCompletedSessions} جلسة بينما حضر الطالب {$studentAttendedSessions} جلسة). قد يحتاج الطالب إلى مراجعة بعض المحتوى السابق. هل تريد تأكيد النقل؟";
            } else {
                $warningMessage = "تنبيه: المجموعة الجديدة أقل في عدد الجلسات المنجزة ({$targetGroupCompletedSessions} جلسة) من جلسات الطالب السابقة ({$studentAttendedSessions} جلسة). هل تريد تأكيد النقل؟";
            }
        }

        return [
            'has_difference' => $hasDifference,
            'current_group_id' => $currentGroupId,
            'student_attended_sessions' => $studentAttendedSessions,
            'target_completed_sessions' => $targetGroupCompletedSessions,
            'difference' => $difference,
            'warning_message' => $warningMessage,
        ];
    }

    /**
     * Validate transfer request according to strict business policies.
     *
     * @throws ValidationException
     */
    public function validateTransfer(Enrollment $enrollment, Group $targetGroup, ?User $user = null): array
    {
        $user = $user ?? auth()->user();

        // 1. Enrollment status: Canceled enrollments cannot be transferred (Case K)
        if ($enrollment->status === 'cancelled') {
            throw ValidationException::withMessages([
                'group_id' => 'لا يمكن نقل طالب لتسجيل ملغي.',
            ]);
        }

        // 2. Authorization check: Independent permission transfer_student_group required
        if (!$this->canUserTransfer($user, $enrollment)) {
            throw ValidationException::withMessages([
                'group_id' => 'غير مصرح لك بنقل الطالب إلى مجموعة أخرى. يلزم صلاحية (transfer_student_group).',
            ]);
        }

        // 3. Rule A — Same Branch Only
        // Cross-branch transfers are strictly prohibited, including for Super Admin
        $enrollmentTenantId = $enrollment->tenant_id;
        $targetTenantId = $targetGroup->tenant_id;

        if ($enrollmentTenantId && $targetTenantId && (int) $enrollmentTenantId !== (int) $targetTenantId) {
            throw ValidationException::withMessages([
                'group_id' => 'غير مسموح بالنقل بين الفروع المختلفة. يسمح بالنقل فقط بين مجموعات نفس الفرع.',
            ]);
        }

        // 4. Rule B — Same Course and Level
        // The target group must belong to the course(s) associated with this enrollment
        $enrollmentCourseIds = $enrollment->courses()->pluck('courses.id')->toArray();
        if (empty($enrollmentCourseIds)) {
            $enrollmentCourseIds = $enrollment->enrollmentCourses()->pluck('course_id')->toArray();
        }
        if (empty($enrollmentCourseIds) && $enrollment->group_id && $enrollment->group) {
            $enrollmentCourseIds = [$enrollment->group->course_id];
        }

        if (!empty($enrollmentCourseIds) && !in_array($targetGroup->course_id, $enrollmentCourseIds)) {
            throw ValidationException::withMessages([
                'group_id' => 'المجموعة الجديدة لا تنتمي لنفس المقرر الدراسي المسجل به الطالب.',
            ]);
        }

        // 5. Rule C — Active Eligible Group
        if ($targetGroup->status !== 'active') {
            throw ValidationException::withMessages([
                'group_id' => 'المجموعة المحددة غير نشطة ولا يمكن استقبال طلاب جدد بها.',
            ]);
        }

        // 6. Capacity check: target group must have capacity
        if ($targetGroup->isFull()) {
            throw ValidationException::withMessages([
                'group_id' => 'المجموعة المحددة مكتملة العدد ولا يوجد بها مقاعد شاغرة.',
            ]);
        }

        return $this->getAcademicProgressDifference($enrollment, $targetGroup);
    }

    /**
     * Atomically transfer a student's enrollment to a new group.
     *
     * @param Enrollment $enrollment
     * @param Group $targetGroup
     * @param array $options ['transferred_by', 'reason', 'progress_warning_acknowledged']
     * @return StudentGroupTransfer|null
     * @throws \Throwable
     */
    public function transfer(Enrollment $enrollment, Group $targetGroup, array $options = []): ?StudentGroupTransfer
    {
        $currentGroupId = $enrollment->group_id 
            ?? $enrollment->enrollmentCourses()->where('course_id', $targetGroup->course_id)->value('group_id')
            ?? $enrollment->groups()->first()?->id;

        // Case A / J: No change in group
        if ($currentGroupId && (int) $currentGroupId === (int) $targetGroup->id) {
            return null;
        }

        $user = isset($options['transferred_by']) && $options['transferred_by'] instanceof User 
            ? $options['transferred_by'] 
            : (isset($options['transferred_by']) ? User::find($options['transferred_by']) : auth()->user());

        // Validate transfer rules
        $progressInfo = $this->validateTransfer($enrollment, $targetGroup, $user);

        return DB::transaction(function () use ($enrollment, $targetGroup, $currentGroupId, $user, $options, $progressInfo) {
            // Lock target group row to protect against concurrent transfer race conditions
            $lockedTargetGroup = Group::where('id', $targetGroup->id)->lockForUpdate()->first();
            if (!$lockedTargetGroup) {
                throw new \Exception('المجموعة المحددة غير موجودة.');
            }

            if ($lockedTargetGroup->status !== 'active') {
                throw ValidationException::withMessages([
                    'group_id' => 'المجموعة المحددة غير نشطة.',
                ]);
            }

            // Accurate occupancy calculation under lock
            $activeCount = $lockedTargetGroup->activeStudents()->count();
            if ($lockedTargetGroup->max_students && $activeCount >= $lockedTargetGroup->max_students) {
                throw ValidationException::withMessages([
                    'group_id' => 'المجموعة المحددة مكتملة العدد ولا يوجد بها مقاعد شاغرة.',
                ]);
            }

            // 1. Update enrollment group_id
            $enrollment->update([
                'group_id' => $lockedTargetGroup->id,
            ]);

            // 2. Update EnrollmentCourse group_id for this course
            $enrollmentCourse = $enrollment->enrollmentCourses()
                ->where('course_id', $lockedTargetGroup->course_id)
                ->first();

            if ($enrollmentCourse) {
                $enrollmentCourse->update([
                    'group_id' => $lockedTargetGroup->id,
                    'status' => 'active',
                ]);
            } else {
                // If not found, update or create enrollment course
                $enrollment->enrollmentCourses()->updateOrCreate(
                    [
                        'course_id' => $lockedTargetGroup->course_id,
                    ],
                    [
                        'group_id' => $lockedTargetGroup->id,
                        'status' => 'active',
                        'course_price' => $lockedTargetGroup->course?->price ?? 0,
                    ]
                );
            }

            // 3. Update enrollment_group pivot table
            if ($currentGroupId) {
                $enrollment->groups()->detach($currentGroupId);
            }
            $enrollment->groups()->syncWithoutDetaching([
                $lockedTargetGroup->id => ['enrolled_at' => now()],
            ]);

            // 4. Update capacity counts for both groups
            if ($currentGroupId) {
                $oldGroup = Group::find($currentGroupId);
                if ($oldGroup) {
                    $oldGroup->syncCapacity();
                }
            }
            $lockedTargetGroup->syncCapacity();

            // 5. Create transfer audit record
            $transferRecord = StudentGroupTransfer::create([
                'tenant_id' => $enrollment->tenant_id,
                'student_id' => $enrollment->student_id,
                'enrollment_id' => $enrollment->id,
                'from_group_id' => $currentGroupId,
                'to_group_id' => $lockedTargetGroup->id,
                'transferred_by' => $user?->id ?? auth()->id(),
                'reason' => $options['reason'] ?? null,
                'progress_warning_acknowledged' => !empty($options['progress_warning_acknowledged']),
                'metadata' => [
                    'student_attended_sessions_old' => $progressInfo['student_attended_sessions'] ?? 0,
                    'target_group_completed_sessions' => $progressInfo['target_completed_sessions'] ?? 0,
                    'course_id' => $lockedTargetGroup->course_id,
                    'course_name' => $lockedTargetGroup->course?->name,
                    'ip_address' => request()->ip() ?? null,
                ],
                'transferred_at' => now(),
            ]);

            return $transferRecord;
        });
    }
}
