<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\InstructorEvaluation;
use App\Models\InstructorSupervisionNote;
use App\Models\SessionSchedule;
use App\Models\StudentFollowUpTask;
use App\Models\SupervisionGroupAssignment;
use App\Models\SupervisionInstructorAssignment;
use App\Models\SupervisionReport;
use App\Models\SupervisionReportVersion;
use App\Models\SupervisoryObservation;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EducationalSupervisionService
{
    /**
     * Assign groups explicitly to a supervisor or secretary.
     */
    public function assignGroups(User $user, array $groupIds, ?User $assignedBy = null): void
    {
        $tenantId = $user->tenant_id;
        $assignedById = $assignedBy?->id ?? auth()->id();

        if ($tenantId) {
            $invalidGroups = Group::whereIn('id', $groupIds)
                ->where('tenant_id', '!=', $tenantId)
                ->pluck('id')
                ->toArray();

            if (!empty($invalidGroups)) {
                throw ValidationException::withMessages([
                    'groups' => 'لا يمكن تكليف مجموعات من فرع أو مؤسسة أخرى.',
                ]);
            }
        }

        DB::transaction(function () use ($user, $groupIds, $tenantId, $assignedById) {
            SupervisionGroupAssignment::where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->whereNotIn('group_id', $groupIds)
                ->update(['revoked_at' => now()]);

            foreach ($groupIds as $gId) {
                $existing = SupervisionGroupAssignment::where('user_id', $user->id)
                    ->where('group_id', $gId)
                    ->whereNull('revoked_at')
                    ->first();

                if (!$existing) {
                    $group = Group::find($gId);
                    SupervisionGroupAssignment::create([
                        'tenant_id' => $group?->tenant_id ?? $tenantId,
                        'user_id' => $user->id,
                        'group_id' => $gId,
                        'assigned_by' => $assignedById,
                        'assigned_at' => now(),
                    ]);
                }
            }
        });
    }

    /**
     * Assign instructors explicitly to a supervisor or secretary.
     */
    public function assignInstructors(User $user, array $instructorIds, ?User $assignedBy = null): void
    {
        $tenantId = $user->tenant_id;
        $assignedById = $assignedBy?->id ?? auth()->id();

        if ($tenantId) {
            $invalidInstructors = Instructor::whereIn('id', $instructorIds)
                ->where('tenant_id', '!=', $tenantId)
                ->pluck('id')
                ->toArray();

            if (!empty($invalidInstructors)) {
                throw ValidationException::withMessages([
                    'instructors' => 'لا يمكن تكليف معلمين من فرع أو مؤسسة أخرى.',
                ]);
            }
        }

        DB::transaction(function () use ($user, $instructorIds, $tenantId, $assignedById) {
            SupervisionInstructorAssignment::where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->whereNotIn('instructor_id', $instructorIds)
                ->update(['revoked_at' => now()]);

            foreach ($instructorIds as $iId) {
                $existing = SupervisionInstructorAssignment::where('user_id', $user->id)
                    ->where('instructor_id', $iId)
                    ->whereNull('revoked_at')
                    ->first();

                if (!$existing) {
                    $inst = Instructor::find($iId);
                    SupervisionInstructorAssignment::create([
                        'tenant_id' => $inst?->tenant_id ?? $tenantId,
                        'user_id' => $user->id,
                        'instructor_id' => $iId,
                        'assigned_by' => $assignedById,
                        'assigned_at' => now(),
                    ]);
                }
            }
        });
    }

    /**
     * Revoke group assignment explicitly.
     */
    public function revokeGroup(User $user, int $groupId): void
    {
        SupervisionGroupAssignment::where('user_id', $user->id)
            ->where('group_id', $groupId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * Revoke instructor assignment explicitly.
     */
    public function revokeInstructor(User $user, int $instructorId): void
    {
        SupervisionInstructorAssignment::where('user_id', $user->id)
            ->where('instructor_id', $instructorId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * Check if user is the designated Branch Educational Supervisor.
     */
    public function isBranchDesignatedSupervisor(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        try {
            return $user->can('branch_educational_supervisor') || $user->hasPermissionTo('branch_educational_supervisor');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get active supervised group IDs for an employee.
     * Enforces branch-wide scope for designated Branch Educational Supervisor,
     * while strictly maintaining explicit assignment isolation for limited supervisors / secretaries.
     */
    public function getSupervisedGroupIds(User $user): array
    {
        // Super admin has full access or scoped to tenant
        if ($user->is_super_admin && !$user->tenant_id) {
            return Group::pluck('id')->toArray();
        }

        // Branch Educational Supervisor: All groups in their designated branch
        if ($this->isBranchDesignatedSupervisor($user) && $user->tenant_id) {
            return Group::where('tenant_id', $user->tenant_id)->pluck('id')->toArray();
        }

        // For limited supervisors and secretaries: explicitly assigned groups + legacy supervisor_id groups
        $assignedGroupIds = SupervisionGroupAssignment::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->pluck('group_id')
            ->toArray();

        $supervisorProfile = \App\Models\Supervisor::where('user_id', $user->id)->first();
        $legacyGroupIds = $supervisorProfile
            ? Group::where('supervisor_id', $supervisorProfile->id)
                ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
                ->pluck('id')
                ->toArray()
            : [];

        return array_values(array_unique(array_merge($assignedGroupIds, $legacyGroupIds)));
    }

    /**
     * Get active supervised instructor IDs for an employee.
     * Enforces branch-wide scope for designated Branch Educational Supervisor,
     * while strictly maintaining explicit assignment isolation for limited supervisors / secretaries.
     */
    public function getSupervisedInstructorIds(User $user): array
    {
        if ($user->is_super_admin && !$user->tenant_id) {
            return Instructor::pluck('id')->toArray();
        }

        // Branch Educational Supervisor: All instructors in their designated branch
        if ($this->isBranchDesignatedSupervisor($user) && $user->tenant_id) {
            return Instructor::where('tenant_id', $user->tenant_id)->pluck('id')->toArray();
        }

        // For limited supervisors and secretaries: ONLY explicitly assigned instructors
        return SupervisionInstructorAssignment::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->pluck('instructor_id')
            ->toArray();
    }

    /**
     * Verify whether a user is authorized to supervise a specific group.
     */
    public function canSuperviseGroup(User $user, Group|int $group): bool
    {
        $groupId = $group instanceof Group ? $group->id : $group;
        $targetGroup = $group instanceof Group ? $group : Group::find($groupId);

        if (!$targetGroup) {
            return false;
        }

        // Strict cross-tenant protection
        if ($user->tenant_id && $targetGroup->tenant_id && $user->tenant_id !== $targetGroup->tenant_id) {
            return false;
        }

        if ($user->is_super_admin) {
            return true;
        }

        // Branch-wide supervisor in the same branch
        if ($this->isBranchDesignatedSupervisor($user) && $user->tenant_id === $targetGroup->tenant_id) {
            return true;
        }

        // Explicit assignment check
        return in_array($groupId, $this->getSupervisedGroupIds($user), true);
    }

    /**
     * Verify whether a user is authorized to supervise a specific instructor.
     */
    public function canSuperviseInstructor(User $user, Instructor|int $instructor): bool
    {
        $instructorId = $instructor instanceof Instructor ? $instructor->id : $instructor;
        $targetInstructor = $instructor instanceof Instructor ? $instructor : Instructor::find($instructorId);

        if (!$targetInstructor) {
            return false;
        }

        // Strict cross-tenant protection
        if ($user->tenant_id && $targetInstructor->tenant_id && $user->tenant_id !== $targetInstructor->tenant_id) {
            return false;
        }

        if ($user->is_super_admin) {
            return true;
        }

        // Branch-wide supervisor in the same branch
        if ($this->isBranchDesignatedSupervisor($user) && $user->tenant_id === $targetInstructor->tenant_id) {
            return true;
        }

        // Explicit assignment check
        return in_array($instructorId, $this->getSupervisedInstructorIds($user), true);
    }

    /**
     * Calculate all 13 daily stats & metrics for Educational Supervision Dashboard.
     * All metrics derived strictly from the authorized branch/assignment scope using real records.
     */
    public function getDashboardMetrics(User $user): array
    {
        $groupIds = $this->getSupervisedGroupIds($user);
        $instructorIds = $this->getSupervisedInstructorIds($user);
        $tenantId = $user->tenant_id;
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::today()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::today()->endOfMonth()->toDateString();
        $currentMonthKey = Carbon::today()->format('Y-m');

        if (empty($groupIds) && empty($instructorIds)) {
            return [
                'assigned_groups_count' => 0,
                'assigned_instructors_count' => 0,
                'today_scheduled_sessions' => 0,
                'completed_sessions' => 0,
                'postponed_sessions' => 0,
                'canceled_sessions' => 0,
                'sessions_missing_attendance' => 0,
                'confirmed_student_absences' => 0,
                'homework_awaiting_grading' => 0,
                'homework_overdue_grading' => 0,
                'exams_awaiting_actions' => 0,
                'published_low_scores' => 0,
                'open_observations' => 0,
                'observations_waiting_review' => 0,
                'overdue_observations' => 0,
                'evaluations_due_this_month' => 0,
            ];
        }

        // 1. Scheduled sessions today
        $todayScheduled = SessionSchedule::whereIn('group_id', $groupIds)
            ->whereDate('date', $today)
            ->whereNotIn('status', ['cancelled', 'postponed'])
            ->count();

        // 2. Completed sessions today
        $completedSessions = AttendanceSession::whereIn('group_id', $groupIds)
            ->whereDate('attendance_date', $today)
            ->count();

        $todayScheduled = max($todayScheduled, $completedSessions);

        // 3. Postponed sessions today
        $postponedSessions = SessionSchedule::whereIn('group_id', $groupIds)
            ->whereDate('date', $today)
            ->whereIn('status', ['postponed', 'rescheduled'])
            ->count();

        // 4. Canceled sessions today
        $canceledSessions = SessionSchedule::whereIn('group_id', $groupIds)
            ->whereDate('date', $today)
            ->where('status', 'cancelled')
            ->count();

        // 5. Sessions with attendance not recorded
        // CRITICAL RULE: Attendance not recorded is NOT student absence!
        $missingAttendance = AttendanceSession::whereIn('group_id', $groupIds)
            ->whereDoesntHave('records')
            ->count();

        $scheduleMissingAttendance = SessionSchedule::whereIn('group_id', $groupIds)
            ->whereDate('date', '<=', $today)
            ->whereNotIn('status', ['cancelled', 'postponed'])
            ->where('attendance_taken', false)
            ->count();

        $totalMissingAttendance = max($missingAttendance, $scheduleMissingAttendance);

        // 6. Confirmed student absences
        // Valid attendance record explicitly marking status = 'absent'
        $confirmedAbsences = AttendanceRecord::whereHas('attendanceSession', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })
        ->where('status', 'absent')
        ->count();

        // 7. Homework awaiting grading
        $homeworkAwaitingGrading = AssignmentSubmission::whereHas('assignment', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })
        ->whereNotNull('submitted_at')
        ->whereNull('score')
        ->count();

        // 8. Homework overdue for grading (submitted > 48 hours ago without grading)
        $homeworkOverdueGrading = AssignmentSubmission::whereHas('assignment', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })
        ->whereNotNull('submitted_at')
        ->whereNull('score')
        ->where('submitted_at', '<=', now()->subHours(48))
        ->count();

        // 9. Examinations awaiting permitted actions (submitted attempts awaiting scoring/review)
        $examsAwaitingActions = ExamAttempt::whereHas('exam', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })
        ->where('status', 'submitted')
        ->whereNull('total_score')
        ->count();

        // 10. Published low academic scores (STRICTLY below 50% on official published homework or exams)
        // Rule: Draft grades must never be treated as official published grades.
        $publishedLowHomework = AssignmentSubmission::whereHas('assignment', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })
        ->whereNotNull('assignment_submissions.published_at')
        ->whereNotNull('assignment_submissions.score')
        ->join('assignments', 'assignments.id', '=', 'assignment_submissions.assignment_id')
        ->whereRaw('assignment_submissions.score < (assignments.max_score * 0.5)')
        ->count();

        $publishedLowExams = ExamAttempt::whereHas('exam', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })
        ->whereNotNull('published_at')
        ->whereNotNull('total_score')
        ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
        ->whereRaw('exam_attempts.total_score < (exams.total_marks * 0.5)')
        ->count();

        $publishedLowScores = $publishedLowHomework + $publishedLowExams;

        // 11. Open supervisory observations ('new')
        $openObservations = SupervisoryObservation::whereIn('instructor_id', $instructorIds)
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', SupervisoryObservation::STATUS_NEW)
            ->count();

        // 12. Observations waiting for supervisor review
        $waitingReviewObservations = SupervisoryObservation::whereIn('instructor_id', $instructorIds)
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW)
            ->count();

        // 13. Overdue supervisory observations (past expected resolution date and not completed)
        $overdueObservations = SupervisoryObservation::whereIn('instructor_id', $instructorIds)
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', '!=', SupervisoryObservation::STATUS_COMPLETED)
            ->whereNotNull('expected_resolution_date')
            ->where('expected_resolution_date', '<', $today)
            ->count();

        // 14. Instructor evaluations due this month
        $evaluatedInstructorIdsThisMonth = InstructorEvaluation::whereIn('instructor_id', $instructorIds)
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('evaluation_type', InstructorEvaluation::TYPE_MONTHLY)
            ->where('evaluation_period', $currentMonthKey)
            ->where('status', InstructorEvaluation::STATUS_PUBLISHED)
            ->pluck('instructor_id')
            ->toArray();

        $evaluationsDueCount = max(0, count($instructorIds) - count(array_unique($evaluatedInstructorIdsThisMonth)));

        return [
            'assigned_groups_count' => count($groupIds),
            'assigned_instructors_count' => count($instructorIds),
            'today_scheduled_sessions' => $todayScheduled,
            'completed_sessions' => $completedSessions,
            'postponed_sessions' => $postponedSessions,
            'canceled_sessions' => $canceledSessions,
            'sessions_missing_attendance' => $totalMissingAttendance,
            'confirmed_student_absences' => $confirmedAbsences,
            'homework_awaiting_grading' => $homeworkAwaitingGrading,
            'homework_overdue_grading' => $homeworkOverdueGrading,
            'exams_awaiting_actions' => $examsAwaitingActions,
            'published_low_scores' => $publishedLowScores,
            'open_observations' => $openObservations,
            'observations_waiting_review' => $waitingReviewObservations,
            'overdue_observations' => $overdueObservations,
            'evaluations_due_this_month' => $evaluationsDueCount,
        ];
    }

    /**
     * Generate Daily Priority Queue for the supervisor.
     * Identifies immediate review items, responsible instructor, affected group, reason,
     * existing observation (if any), and next authorized action.
     */
    public function getDailyPriorityQueue(User $user): Collection
    {
        $groupIds = $this->getSupervisedGroupIds($user);
        $instructorIds = $this->getSupervisedInstructorIds($user);
        $queue = collect();

        if (empty($groupIds) && empty($instructorIds)) {
            return $queue;
        }

        // Priority 1: Observations waiting for supervisor review
        $waitingReview = SupervisoryObservation::with(['instructor.user', 'group'])
            ->whereIn('instructor_id', $instructorIds)
            ->where('status', SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW)
            ->orderBy('instructor_responded_at', 'asc')
            ->get();

        foreach ($waitingReview as $obs) {
            $queue->push([
                'type' => 'observation_review',
                'priority' => 'urgent',
                'title' => "مراجعة إنجاز ملاحظة: {$obs->title}",
                'instructor_name' => $obs->instructor?->user?->name ?? 'غير محدد',
                'instructor_id' => $obs->instructor_id,
                'group_name' => $obs->group?->name ?? '—',
                'group_id' => $obs->group_id,
                'reason' => 'قام المهندس بالرد وطلب اعتماد الإنجاز.',
                'existing_observation_code' => $obs->observation_code,
                'observation_id' => $obs->id,
                'next_action' => 'التحقق واعتماد الإغلاق أو الإعادة للتصحيح',
                'route' => "/supervisor/supervisory-observations/{$obs->id}",
            ]);
        }

        // Priority 2: Overdue supervisory observations
        $overdueObs = SupervisoryObservation::with(['instructor.user', 'group'])
            ->whereIn('instructor_id', $instructorIds)
            ->where('status', SupervisoryObservation::STATUS_NEW)
            ->whereNotNull('expected_resolution_date')
            ->where('expected_resolution_date', '<', Carbon::today()->toDateString())
            ->get();

        foreach ($overdueObs as $obs) {
            $queue->push([
                'type' => 'observation_overdue',
                'priority' => 'urgent',
                'title' => "ملاحظة متأخرة تجاوزت موعد المراجعة: {$obs->title}",
                'instructor_name' => $obs->instructor?->user?->name ?? 'غير محدد',
                'instructor_id' => $obs->instructor_id,
                'group_name' => $obs->group?->name ?? '—',
                'group_id' => $obs->group_id,
                'reason' => "تاريخ الاستحقاق كان {$obs->expected_resolution_date?->format('Y-m-d')}",
                'existing_observation_code' => $obs->observation_code,
                'observation_id' => $obs->id,
                'next_action' => 'التواصل مع المهندس واستعجال التصحيح',
                'route' => "/supervisor/supervisory-observations/{$obs->id}",
            ]);
        }

        // Priority 3: Completed/Past Sessions with missing attendance
        $missingSessions = AttendanceSession::with(['group.instructor.user', 'records'])
            ->whereIn('group_id', $groupIds)
            ->whereDoesntHave('records')
            ->orderBy('attendance_date', 'desc')
            ->take(10)
            ->get();

        foreach ($missingSessions as $session) {
            $existingObs = SupervisoryObservation::where('attendance_session_id', $session->id)
                ->where('status', '!=', SupervisoryObservation::STATUS_COMPLETED)
                ->first();

            $queue->push([
                'type' => 'missing_attendance',
                'priority' => 'warning',
                'title' => "جلسة بتاريخ {$session->attendance_date?->format('Y-m-d')} لم يُسجّل حضورها",
                'instructor_name' => $session->group?->instructor?->user?->name ?? 'غير محدد',
                'instructor_id' => $session->group?->instructor_id,
                'group_name' => $session->group?->name ?? '—',
                'group_id' => $session->group_id,
                'reason' => 'انتهت الجلسة دون حفظ سجلات حضور وغياب الطلاب',
                'existing_observation_code' => $existingObs?->observation_code,
                'observation_id' => $existingObs?->id,
                'next_action' => $existingObs ? 'متابعة الملاحظة القائمة' : 'إنشاء توجيه إشرافي لتسجيل الحضور',
                'route' => $existingObs ? "/supervisor/supervisory-observations/{$existingObs->id}" : "/supervisor/supervisory-observations/create?session_id={$session->id}&instructor_id={$session->group?->instructor_id}&group_id={$session->group_id}",
            ]);
        }

        // Priority 4: Overdue homework grading (> 48h)
        $overdueSubmissions = AssignmentSubmission::with(['assignment.group.instructor.user'])
            ->whereHas('assignment', fn($q) => $q->whereIn('group_id', $groupIds))
            ->whereNotNull('submitted_at')
            ->whereNull('score')
            ->where('submitted_at', '<=', now()->subHours(48))
            ->take(10)
            ->get();

        foreach ($overdueSubmissions as $sub) {
            $assignment = $sub->assignment;
            $group = $assignment?->group;
            $instructor = $group?->instructor;

            $queue->push([
                'type' => 'overdue_grading',
                'priority' => 'warning',
                'title' => "واجب متأخر في التصحيح: {$assignment?->title}",
                'instructor_name' => $instructor?->user?->name ?? 'غير محدد',
                'instructor_id' => $instructor?->id,
                'group_name' => $group?->name ?? '—',
                'group_id' => $group?->id,
                'reason' => "تم تسليم الواجب في {$sub->submitted_at?->diffForHumans()}",
                'existing_observation_code' => null,
                'observation_id' => null,
                'next_action' => 'توجيه المهندس لإتمام تصحيح الواجبات',
                'route' => "/supervisor/supervisory-observations/create?assignment_id={$assignment?->id}&instructor_id={$instructor?->id}&group_id={$group?->id}",
            ]);
        }

        return $queue;
    }

    /**
     * Read-Only Educational Alert Aggregation Service.
     * Aggregates live academic conditions without creating records.
     * CRITICAL RESTRICTION: Automatic alerts must NOT automatically create supervisory observations or student follow-up tasks!
     */
    public function getEducationalAlerts(User $user): Collection
    {
        $groupIds = $this->getSupervisedGroupIds($user);
        $instructorIds = $this->getSupervisedInstructorIds($user);
        $alerts = collect();

        if (empty($groupIds) && empty($instructorIds)) {
            return $alerts;
        }

        // 1. Attendance Not Recorded Alerts
        $unrecordedSessions = AttendanceSession::with(['group.instructor.user'])
            ->whereIn('group_id', $groupIds)
            ->whereDoesntHave('records')
            ->orderBy('attendance_date', 'desc')
            ->take(15)
            ->get();

        foreach ($unrecordedSessions as $s) {
            $alerts->push([
                'category' => 'attendance_not_recorded',
                'category_label' => 'تأخر تسجيل الحضور',
                'badge' => 'danger',
                'title' => "جلسة حضور غير مسجلة لمجموعة {$s->group?->name}",
                'date' => $s->attendance_date?->format('Y-m-d'),
                'instructor_name' => $s->group?->instructor?->user?->name ?? 'غير محدد',
                'group_name' => $s->group?->name ?? '—',
                'description' => 'انتهت الجلسة ولم يتم حفظ سجل الحضور والغياب للمجموعة.',
                'action_label' => 'إنشاء ملاحظة إشرافية',
                'action_url' => "/supervisor/supervisory-observations/create?session_id={$s->id}",
            ]);
        }

        // 2. Confirmed Student Absences (Explicit record with status = 'absent')
        $recentAbsences = AttendanceRecord::with(['attendanceSession.group', 'student'])
            ->whereHas('attendanceSession', fn($q) => $q->whereIn('group_id', $groupIds))
            ->where('status', 'absent')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        foreach ($recentAbsences as $rec) {
            $session = $rec->attendanceSession;
            $alerts->push([
                'category' => 'confirmed_absence',
                'category_label' => 'غياب طالب مؤكد',
                'badge' => 'warning',
                'title' => "غياب مؤكد للطالب {$rec->student?->name}",
                'date' => $session?->attendance_date?->format('Y-m-d'),
                'instructor_name' => $session?->group?->instructor?->user?->name ?? 'غير محدد',
                'group_name' => $session?->group?->name ?? '—',
                'description' => "غياب مؤكد ومسجل في جلسة {$session?->attendance_date?->format('Y-m-d')}.",
                'action_label' => 'معاينة ملف المتابعة الأكاديمية',
                'action_url' => "/supervisor/student-academic-profiles/{$rec->student_id}",
            ]);
        }

        // 3. Overdue Homework Grading (> 48h)
        $overdueHomework = AssignmentSubmission::with(['assignment.group.instructor.user'])
            ->whereHas('assignment', fn($q) => $q->whereIn('group_id', $groupIds))
            ->whereNotNull('submitted_at')
            ->whereNull('score')
            ->where('submitted_at', '<=', now()->subHours(48))
            ->take(15)
            ->get();

        foreach ($overdueHomework as $hw) {
            $alerts->push([
                'category' => 'overdue_homework_grading',
                'category_label' => 'تأخر تصحيح الواجبات',
                'badge' => 'warning',
                'title' => "واجب ينتظر التصحيح: {$hw->assignment?->title}",
                'date' => $hw->submitted_at?->format('Y-m-d H:i'),
                'instructor_name' => $hw->assignment?->group?->instructor?->user?->name ?? 'غير محدد',
                'group_name' => $hw->assignment?->group?->name ?? '—',
                'description' => "مضى على تسليم الواجب أكثر من 48 ساعة دون رصد الدرجة.",
                'action_label' => 'توجيه إشرافي للمهندس',
                'action_url' => "/supervisor/supervisory-observations/create?assignment_id={$hw->assignment_id}",
            ]);
        }

        // 4. Low Academic Scores (< 50% max score on published homework or exams)
        $lowHomework = AssignmentSubmission::with(['assignment.group', 'student'])
            ->whereHas('assignment', fn($q) => $q->whereIn('group_id', $groupIds))
            ->whereNotNull('assignment_submissions.published_at')
            ->whereNotNull('assignment_submissions.score')
            ->join('assignments', 'assignments.id', '=', 'assignment_submissions.assignment_id')
            ->whereRaw('assignment_submissions.score < (assignments.max_score * 0.5)')
            ->select('assignment_submissions.*')
            ->take(15)
            ->get();

        foreach ($lowHomework as $lh) {
            $maxScore = $lh->assignment?->max_score ?? 10;
            $alerts->push([
                'category' => 'low_academic_score',
                'category_label' => 'درجة أكاديمية منخفضة (< 50%)',
                'badge' => 'danger',
                'title' => "درجة منخفضة في الواجب: {$lh->assignment?->title} ({$lh->score} من {$maxScore})",
                'date' => $lh->published_at?->format('Y-m-d'),
                'instructor_name' => $lh->assignment?->group?->instructor?->user?->name ?? 'غير محدد',
                'group_name' => $lh->assignment?->group?->name ?? '—',
                'description' => "حصل الطالب {$lh->student?->name} على درجة رسمية منشورة {$lh->score}/{$maxScore} (أقل من 50%).",
                'action_label' => 'معاينة المتابعة الأكاديمية',
                'action_url' => "/supervisor/student-academic-profiles/{$lh->student_id}",
            ]);
        }

        // 5. Supervisor Review Required (Observations waiting review)
        $waitingObs = SupervisoryObservation::with(['instructor.user', 'group'])
            ->whereIn('instructor_id', $instructorIds)
            ->where('status', SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW)
            ->get();

        foreach ($waitingObs as $w) {
            $alerts->push([
                'category' => 'supervisor_review_required',
                'category_label' => 'مطلوب مراجعة المشرف',
                'badge' => 'info',
                'title' => "قام المهندس بإنجاز الملاحظة: {$w->title}",
                'date' => $w->instructor_responded_at?->format('Y-m-d H:i'),
                'instructor_name' => $w->instructor?->user?->name ?? 'غير محدد',
                'group_name' => $w->group?->name ?? '—',
                'description' => 'طلب المهندس مراجعة واعتماد إنجاز الإجراء التصحيحي.',
                'action_label' => 'مراجعة واعتماد الملاحظة',
                'action_url' => "/supervisor/supervisory-observations/{$w->id}",
            ]);
        }

        return $alerts;
    }

    /**
     * Generate or fetch weekly educational supervision report DRAFT for a branch.
     * IDEMPOTENT: Prevents duplicate drafts for the same branch and week period.
     */
    public function generateWeeklyReportDraft(Tenant|int $branch, string $weekKey, User $actor): SupervisionReport
    {
        $tenantId = $branch instanceof Tenant ? $branch->id : $branch;

        // Idempotency check: if draft already exists, return it
        $existing = SupervisionReport::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('report_type', SupervisionReport::TYPE_WEEKLY)
            ->where('period_key', $weekKey)
            ->where('status', SupervisionReport::STATUS_DRAFT)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Parse week dates
        if (preg_match('/^(\d{4})-W(\d{1,2})$/', $weekKey, $matches)) {
            $year = (int) $matches[1];
            $week = (int) $matches[2];
            $periodStart = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $periodEnd = Carbon::now()->setISODate($year, $week)->endOfWeek();
        } else {
            $periodStart = Carbon::now()->startOfWeek();
            $periodEnd = Carbon::now()->endOfWeek();
            $weekKey = Carbon::now()->format('o-\\WW');
        }

        // Collect branch real metrics for the week
        $groupIds = Group::withoutGlobalScopes()->where('tenant_id', $tenantId)->pluck('id')->toArray();
        $instructorIds = Instructor::withoutGlobalScopes()->where('tenant_id', $tenantId)->pluck('id')->toArray();

        $metrics = $this->calculatePeriodMetrics($tenantId, $groupIds, $instructorIds, $periodStart, $periodEnd);

        return SupervisionReport::create([
            'tenant_id' => $tenantId,
            'report_type' => SupervisionReport::TYPE_WEEKLY,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'period_key' => $weekKey,
            'version' => 1,
            'metrics_snapshot' => $metrics,
            'summary' => "مسودة التقرير الإشرافي الأسبوعي للأسبوع {$weekKey}.",
            'status' => SupervisionReport::STATUS_DRAFT,
            'prepared_by' => $actor->id,
        ]);
    }

    /**
     * Generate or fetch monthly educational supervision report DRAFT for a branch.
     * IDEMPOTENT: Prevents duplicate drafts for the same branch and month period.
     */
    public function generateMonthlyReportDraft(Tenant|int $branch, string $monthKey, User $actor): SupervisionReport
    {
        $tenantId = $branch instanceof Tenant ? $branch->id : $branch;

        // Idempotency check
        $existing = SupervisionReport::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('report_type', SupervisionReport::TYPE_MONTHLY)
            ->where('period_key', $monthKey)
            ->where('status', SupervisionReport::STATUS_DRAFT)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Parse month dates
        try {
            $periodStart = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth();
            $periodEnd = Carbon::createFromFormat('Y-m', $monthKey)->endOfMonth();
        } catch (\Throwable $e) {
            $periodStart = Carbon::now()->startOfMonth();
            $periodEnd = Carbon::now()->endOfMonth();
            $monthKey = Carbon::now()->format('Y-m');
        }

        $groupIds = Group::withoutGlobalScopes()->where('tenant_id', $tenantId)->pluck('id')->toArray();
        $instructorIds = Instructor::withoutGlobalScopes()->where('tenant_id', $tenantId)->pluck('id')->toArray();

        $metrics = $this->calculatePeriodMetrics($tenantId, $groupIds, $instructorIds, $periodStart, $periodEnd);

        // Add monthly evaluation metrics
        $evaluationsCount = InstructorEvaluation::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('evaluation_type', InstructorEvaluation::TYPE_MONTHLY)
            ->where('evaluation_period', $monthKey)
            ->where('status', InstructorEvaluation::STATUS_PUBLISHED)
            ->count();

        $metrics['published_evaluations_count'] = $evaluationsCount;
        $metrics['total_instructors_count'] = count($instructorIds);

        return SupervisionReport::create([
            'tenant_id' => $tenantId,
            'report_type' => SupervisionReport::TYPE_MONTHLY,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'period_key' => $monthKey,
            'version' => 1,
            'metrics_snapshot' => $metrics,
            'summary' => "مسودة التقرير الإشرافي الشهري لشهر {$monthKey}.",
            'status' => SupervisionReport::STATUS_DRAFT,
            'prepared_by' => $actor->id,
        ]);
    }

    /**
     * Calculate aggregated operational metrics for a date period within branch.
     */
    protected function calculatePeriodMetrics(int $tenantId, array $groupIds, array $instructorIds, Carbon $start, Carbon $end): array
    {
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        $scheduledSessions = SessionSchedule::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'postponed'])
            ->count();

        $completedSessions = AttendanceSession::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->count();

        $postponedSessions = SessionSchedule::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['postponed', 'rescheduled'])
            ->count();

        $canceledSessions = SessionSchedule::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'cancelled')
            ->count();

        $missingAttendance = AttendanceSession::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereDoesntHave('records')
            ->count();

        $confirmedAbsences = AttendanceRecord::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereHas('attendanceSession', fn($q) => $q->whereBetween('attendance_date', [$startDate, $endDate]))
            ->where('status', 'absent')
            ->count();

        $homeworkAwaitingGrading = AssignmentSubmission::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('submitted_at', [$start, $end])
            ->whereNull('score')
            ->count();

        $newObservations = SupervisoryObservation::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $completedObservations = SupervisoryObservation::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('approved_at', [$start, $end])
            ->where('status', SupervisoryObservation::STATUS_COMPLETED)
            ->count();

        return [
            'scheduled_sessions' => $scheduledSessions,
            'completed_sessions' => $completedSessions,
            'postponed_sessions' => $postponedSessions,
            'canceled_sessions' => $canceledSessions,
            'missing_attendance' => $missingAttendance,
            'confirmed_absences' => $confirmedAbsences,
            'homework_awaiting_grading' => $homeworkAwaitingGrading,
            'new_observations_count' => $newObservations,
            'completed_observations_count' => $completedObservations,
            'period_start' => $startDate,
            'period_end' => $endDate,
        ];
    }

    /**
     * Supervisor explicitly approves publication of weekly or monthly supervision report.
     * CRITICAL RULE: Reports must NEVER publish automatically without supervisor approval!
     */
    public function publishSupervisionReport(SupervisionReport $report, User $supervisor): SupervisionReport
    {
        if ($report->tenant_id && $supervisor->tenant_id && $report->tenant_id !== $supervisor->tenant_id && !$supervisor->is_super_admin) {
            throw new AuthorizationException('غير مصرح لك بنشر تقرير لفرع آخر.');
        }

        $report->recordVersionSnapshot('نشر التقرير واعتماده للمركز الرئيسي', $supervisor);
        $report->publish($supervisor);

        return $report;
    }

    /**
     * Management requests revision on a published report with a mandatory reason.
     */
    public function requestReportRevision(SupervisionReport $report, User $manager, string $reason): SupervisionReport
    {
        $report->requestRevision($manager, $reason);
        return $report;
    }

    /**
     * Management requests clarification on a published report.
     */
    public function requestReportClarification(SupervisionReport $report, User $manager, string $question): SupervisionReport
    {
        $report->addClarificationRequest($manager, $question);
        return $report;
    }

    /**
     * Management adds review comment to a published report.
     */
    public function addManagementComment(SupervisionReport $report, User $manager, string $comment): SupervisionReport
    {
        $report->addManagementComment($manager, $comment);
        return $report;
    }

    /**
     * Supervisor responds to management clarification request.
     */
    public function respondToClarification(SupervisionReport $report, User $supervisor, string $response): SupervisionReport
    {
        $report->respondToClarification($supervisor, $response);
        return $report;
    }

    /**
     * Get Instructor Supervision Profile (Aggregated read-only educational oversight view).
     * Strictly EXCLUDES all salary, vault, fee, and financial records.
     */
    public function getInstructorSupervisionProfile(User $supervisor, int $instructorId): array
    {
        $instructor = Instructor::with(['user', 'department'])->findOrFail($instructorId);

        if (!$this->canSuperviseInstructor($supervisor, $instructor)) {
            throw new AuthorizationException('غير مصرح لك بالاطلاع على الملف الإشرافي لهذا المعلم.');
        }

        $assignedGroupIds = $this->getSupervisedGroupIds($supervisor);

        $monitoredGroups = Group::where('instructor_id', $instructor->id)
            ->whereIn('id', $assignedGroupIds)
            ->get();

        $monitoredGroupIds = $monitoredGroups->pluck('id')->toArray();

        $completedSessions = AttendanceSession::whereIn('group_id', $monitoredGroupIds)->count();
        $missingAttendance = AttendanceSession::whereIn('group_id', $monitoredGroupIds)->whereDoesntHave('records')->count();

        $openObservations = SupervisoryObservation::where('instructor_id', $instructor->id)
            ->where('status', '!=', SupervisoryObservation::STATUS_COMPLETED)
            ->get();

        $completedObservations = SupervisoryObservation::where('instructor_id', $instructor->id)
            ->where('status', SupervisoryObservation::STATUS_COMPLETED)
            ->get();

        $publishedEvaluations = InstructorEvaluation::where('instructor_id', $instructor->id)
            ->where('status', InstructorEvaluation::STATUS_PUBLISHED)
            ->orderBy('created_at', 'desc')
            ->get();

        $latestMonthlyEvaluation = $publishedEvaluations->firstWhere('evaluation_type', InstructorEvaluation::TYPE_MONTHLY);

        return [
            'instructor' => $instructor,
            'name' => $instructor->user?->name ?? 'معلم غير محدد',
            'email' => $instructor->user?->email,
            'phone' => $instructor->user?->phone,
            'specialization' => $instructor->specialization ?? '—',
            'department' => $instructor->department?->name ?? '—',
            'monitored_groups' => $monitoredGroups,
            'monitored_groups_count' => count($monitoredGroupIds),
            'completed_sessions' => $completedSessions,
            'sessions_missing_attendance' => $missingAttendance,
            'open_observations' => $openObservations,
            'completed_observations' => $completedObservations,
            'published_evaluations' => $publishedEvaluations,
            'latest_monthly_evaluation' => $latestMonthlyEvaluation,
        ];
    }

    /**
     * Get Group Supervision Profile (Aggregated read-only educational oversight view).
     * Strictly preserves student history and excludes financial information.
     */
    public function getGroupSupervisionProfile(User $supervisor, int $groupId): array
    {
        $group = Group::with(['course', 'instructor.user', 'students'])->findOrFail($groupId);

        if (!$this->canSuperviseGroup($supervisor, $group)) {
            throw new AuthorizationException('غير مصرح لك بالاطلاع على الملف الإشرافي لهذه المجموعة.');
        }

        $sessions = AttendanceSession::with('records')
            ->where('group_id', $group->id)
            ->orderBy('attendance_date', 'desc')
            ->get();

        $completedSessionsCount = $sessions->count();
        $missingAttendanceCount = $sessions->where(fn($s) => $s->records->isEmpty())->count();

        $assignments = Assignment::with(['submissions'])
            ->where('group_id', $group->id)
            ->get();

        $exams = Exam::with(['attempts'])
            ->where('group_id', $group->id)
            ->get();

        $observations = SupervisoryObservation::where('group_id', $group->id)->get();

        return [
            'group' => $group,
            'course' => $group->course,
            'instructor' => $group->instructor,
            'students_count' => $group->students->count(),
            'completed_sessions_count' => $completedSessionsCount,
            'missing_attendance_count' => $missingAttendanceCount,
            'assignments_count' => $assignments->count(),
            'exams_count' => $exams->count(),
            'observations' => $observations,
        ];
    }
}
