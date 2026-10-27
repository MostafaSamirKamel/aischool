<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Student;
use App\Models\StudentFollowUpContact;
use App\Models\StudentFollowUpTask;
use App\Models\StudentFollowUpTaskReassignment;
use App\Models\StudentGroupTransfer;
use App\Models\SupervisionGroupAssignment;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcademicFollowUpService
{
    /**
     * Resolve eligible students for a specific exam in a group, taking transfers into account.
     */
    public function getExamEligibleRoster(Exam $exam): Collection
    {
        $group = $exam->group;
        if (!$group) {
            return collect();
        }

        $tenantId = $exam->tenant_id;
        $examDate = $exam->exam_date ? Carbon::parse($exam->exam_date) : now();

        // 1. Current active enrolled students in this group
        $studentsQuery = Student::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('enrollments', function ($q) use ($group) {
                $q->where('status', 'active')
                  ->where(function ($sub) use ($group) {
                      $sub->where('group_id', $group->id)
                          ->orWhereHas('enrollmentCourses', fn($ec) => $ec->where('group_id', $group->id)->where('status', 'active'));
                  });
            })
            ->with(['user', 'studentParents.parent']);

        $students = $studentsQuery->get();

        // 2. Filter out students who transferred INTO this group AFTER the exam date
        $filteredStudents = $students->filter(function (Student $student) use ($group, $examDate) {
            $transferIn = StudentGroupTransfer::where('student_id', $student->id)
                ->where('to_group_id', $group->id)
                ->orderBy('transferred_at', 'desc')
                ->first();

            if ($transferIn && $transferIn->transferred_at) {
                $transferredAt = Carbon::parse($transferIn->transferred_at);
                if ($transferredAt->gt($examDate)) {
                    $hasAttempt = ExamAttempt::where('exam_id', $exam->id)
                        ->where('student_id', $student->id)
                        ->exists();

                    return $hasAttempt;
                }
            }

            return true;
        });

        // 3. Include any students who transferred OUT of this group AFTER taking the exam
        $transferredOutStudentIds = ExamAttempt::where('exam_id', $exam->id)
            ->whereNotIn('student_id', $filteredStudents->pluck('id'))
            ->pluck('student_id')
            ->unique();

        if ($transferredOutStudentIds->isNotEmpty()) {
            $transferredStudents = Student::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('id', $transferredOutStudentIds)
                ->with(['user', 'studentParents.parent'])
                ->get();

            $filteredStudents = $filteredStudents->concat($transferredStudents);
        }

        // 4. Preload attempts for this exam
        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->whereIn('student_id', $filteredStudents->pluck('id'))
            ->orderBy('completed_at', 'desc')
            ->get()
            ->groupBy('student_id');

        // 5. Preload active follow-up tasks for this exam
        $tasks = StudentFollowUpTask::where('tenant_id', $tenantId)
            ->where('source_type', 'exam')
            ->where('source_id', $exam->id)
            ->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending', 'in_progress'])
            ->with(['latestContact', 'contacts', 'assignee'])
            ->get()
            ->keyBy('student_id');

        // 6. Map to roster rows
        return $filteredStudents->map(function (Student $student) use ($exam, $attempts, $tasks) {
            $studentAttempts = $attempts->get($student->id, collect());
            $officialAttempt = $studentAttempts->first();

            $task = $tasks->get($student->id);

            return $this->buildExamStudentRow($student, $exam, $officialAttempt, $task);
        });
    }

    /**
     * Resolve eligible students for a specific homework assignment in a group, transfer-aware.
     */
    public function getHomeworkEligibleRoster(Assignment $assignment): Collection
    {
        $group = $assignment->group;
        if (!$group) {
            return collect();
        }

        $tenantId = $assignment->tenant_id;
        $dueDate = $assignment->due_date ? Carbon::parse($assignment->due_date)->endOfDay() : now()->endOfDay();

        // 1. Current active enrolled students
        $students = Student::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('enrollments', function ($q) use ($group) {
                $q->where('status', 'active')
                  ->where(function ($sub) use ($group) {
                      $sub->where('group_id', $group->id)
                          ->orWhereHas('enrollmentCourses', fn($ec) => $ec->where('group_id', $group->id)->where('status', 'active'));
                  });
            })
            ->with(['user', 'studentParents.parent'])
            ->get();

        // 2. Filter out students who transferred INTO this group AFTER the assignment due date
        $filteredStudents = $students->filter(function (Student $student) use ($group, $dueDate, $assignment) {
            $transferIn = StudentGroupTransfer::where('student_id', $student->id)
                ->where('to_group_id', $group->id)
                ->orderBy('transferred_at', 'desc')
                ->first();

            if ($transferIn && $transferIn->transferred_at) {
                $transferredAt = Carbon::parse($transferIn->transferred_at);
                if ($transferredAt->gt($dueDate)) {
                    $hasSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
                        ->where('student_id', $student->id)
                        ->exists();

                    return $hasSubmission;
                }
            }

            return true;
        });

        // 3. Include students who transferred OUT but had submissions in this assignment
        $transferredOutStudentIds = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->whereNotIn('student_id', $filteredStudents->pluck('id'))
            ->pluck('student_id')
            ->unique();

        if ($transferredOutStudentIds->isNotEmpty()) {
            $transferredStudents = Student::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('id', $transferredOutStudentIds)
                ->with(['user', 'studentParents.parent'])
                ->get();

            $filteredStudents = $filteredStudents->concat($transferredStudents);
        }

        // 4. Preload submissions
        $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->whereIn('student_id', $filteredStudents->pluck('id'))
            ->with('grader')
            ->get()
            ->keyBy('student_id');

        // 5. Preload active tasks
        $tasks = StudentFollowUpTask::where('tenant_id', $tenantId)
            ->where('source_type', 'homework')
            ->where('source_id', $assignment->id)
            ->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending', 'in_progress'])
            ->with(['latestContact', 'contacts', 'assignee'])
            ->get()
            ->keyBy('student_id');

        // 6. Map to roster rows
        return $filteredStudents->map(function (Student $student) use ($assignment, $submissions, $tasks) {
            $submission = $submissions->get($student->id);
            $task = $tasks->get($student->id);

            return $this->buildHomeworkStudentRow($student, $assignment, $submission, $task);
        });
    }

    /**
     * Build row structure for Exam Roster
     */
    protected function buildExamStudentRow(Student $student, Exam $exam, ?ExamAttempt $attempt, ?StudentFollowUpTask $task): array
    {
        $parentPhone = $this->resolveParentPhone($student);
        $studentPhone = $this->resolveStudentPhone($student);

        $now = now();
        $examDate = $exam->exam_date ? Carbon::parse($exam->exam_date) : null;
        $endsAt = $exam->ends_at ?? null;

        $hasEnded = $endsAt ? $now->gte($endsAt) : ($examDate ? $now->gte($examDate->copy()->endOfDay()) : false);
        $isAvailable = method_exists($exam, 'isAvailableNow') ? $exam->isAvailableNow() : false;

        $status = 'not_started';
        $statusLabel = 'لم يبدأ بعد';
        $statusBadge = 'gray';
        $scoreDisplay = '—';
        $isOverdue = false;
        $isLowGrade = false;

        $maxScore = (float) $exam->total_marks > 0 ? (float) $exam->total_marks : 100;

        if ($exam->isCancelled()) {
            $status = 'cancelled';
            $statusLabel = 'ملغي';
            $statusBadge = 'gray';
        } elseif ($attempt) {
            if ($attempt->isCompleted()) {
                $status = 'completed';
                $statusLabel = 'تم الحل';
                $statusBadge = 'success';

                if ($attempt->score !== null) {
                    $scoreVal = (float) $attempt->score;
                    $scoreDisplay = round($scoreVal, 1) . ' / ' . round($maxScore, 1);

                    // Strict low-grade rule: score * 2 < total_marks (< 50%)
                    if (($scoreVal * 2) < $maxScore) {
                        $isLowGrade = true;
                        $statusBadge = 'danger';
                    }
                } else {
                    $status = 'awaiting_grading';
                    $statusLabel = 'بانتظار التصحيح';
                    $statusBadge = 'warning';
                    $scoreDisplay = 'بانتظار التصحيح';
                }
            } else {
                $status = 'in_progress';
                $statusLabel = 'جاري الحل';
                $statusBadge = 'info';
            }
        } else {
            // No attempt
            if ($hasEnded) {
                $status = 'overdue';
                $statusLabel = 'انتهى الموعد ولم يحل';
                $statusBadge = 'danger';
                $isOverdue = true;
            } elseif ($isAvailable) {
                $status = 'available';
                $statusLabel = 'متاح ولم يبدأ';
                $statusBadge = 'warning';
            } else {
                $status = 'upcoming';
                $statusLabel = 'لم يبدأ بعد';
                $statusBadge = 'gray';
            }
        }

        $requiresFollowUp = ($isOverdue || $isLowGrade) && !$task;
        $hasFollowUp = $task !== null;

        return [
            'student_id' => $student->id,
            'student_code' => $student->student_code ?? "STD-{$student->id}",
            'student_name' => $student->user?->name ?? 'طالب بدون اسم',
            'parent_phone' => $parentPhone,
            'student_phone' => $studentPhone,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_badge' => $statusBadge,
            'score_display' => $scoreDisplay,
            'raw_score' => $attempt?->score,
            'max_score' => $maxScore,
            'is_overdue' => $isOverdue,
            'is_low_grade' => $isLowGrade,
            'requires_follow_up' => $requiresFollowUp,
            'has_follow_up' => $hasFollowUp,
            'task' => $task,
            'latest_contact' => $task?->latestContact,
            'contacts_count' => $task ? $task->contacts->count() : 0,
        ];
    }

    /**
     * Build row structure for Homework Roster
     */
    protected function buildHomeworkStudentRow(Student $student, Assignment $assignment, ?AssignmentSubmission $submission, ?StudentFollowUpTask $task): array
    {
        $parentPhone = $this->resolveParentPhone($student);
        $studentPhone = $this->resolveStudentPhone($student);

        $now = now();
        $dueDate = $assignment->due_date ? Carbon::parse($assignment->due_date)->endOfDay() : null;
        $isPastDeadline = $dueDate ? $now->gt($dueDate) : false;

        $maxScore = (float) $assignment->max_score > 0 ? (float) $assignment->max_score : 10;

        $status = 'not_submitted';
        $statusLabel = 'لم يسلّم';
        $statusBadge = 'warning';
        $scoreDisplay = '—';
        $isOverdue = false;
        $isLate = false;
        $isLowGrade = false;

        if ($submission && $submission->submitted_at) {
            $submittedAt = Carbon::parse($submission->submitted_at);
            $isLate = $dueDate && $submittedAt->gt($dueDate);

            if ($submission->isPublished()) {
                $status = 'graded';
                $statusLabel = $isLate ? 'تم التصحيح (تسليم متأخر)' : 'تم التصحيح';
                $statusBadge = 'success';
                $scoreVal = (float) $submission->score;
                $scoreDisplay = round($scoreVal, 1) . ' / ' . round($maxScore, 1);

                // Strict low-grade rule: score * 2 < max_score (< 50%)
                if (($scoreVal * 2) < $maxScore) {
                    $isLowGrade = true;
                    $statusBadge = 'danger';
                }
            } elseif ($submission->isDraft()) {
                $status = 'draft_grading';
                $statusLabel = 'بانتظار اعتماد الدرجة';
                $statusBadge = 'warning';
                $scoreDisplay = 'بانتظار اعتماد الدرجة';
            } else {
                $status = 'awaiting_grading';
                $statusLabel = $isLate ? 'تم التسليم متأخراً (بانتظار التصحيح)' : 'بانتظار التصحيح';
                $statusBadge = 'warning';
                $scoreDisplay = 'بانتظار التصحيح';
            }
        } else {
            // Not submitted
            if ($isPastDeadline) {
                $status = 'overdue';
                $statusLabel = 'متأخر ولم يسلّم';
                $statusBadge = 'danger';
                $isOverdue = true;
            } else {
                $status = 'pending_submission';
                $statusLabel = 'لم يسلّم (ضمن المهلة)';
                $statusBadge = 'gray';
            }
        }

        $requiresFollowUp = ($isOverdue || $isLowGrade) && !$task;
        $hasFollowUp = $task !== null;

        return [
            'student_id' => $student->id,
            'student_code' => $student->student_code ?? "STD-{$student->id}",
            'student_name' => $student->user?->name ?? 'طالب بدون اسم',
            'parent_phone' => $parentPhone,
            'student_phone' => $studentPhone,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_badge' => $statusBadge,
            'score_display' => $scoreDisplay,
            'raw_score' => $submission?->score,
            'max_score' => $maxScore,
            'is_overdue' => $isOverdue,
            'is_late' => $isLate,
            'is_low_grade' => $isLowGrade,
            'requires_follow_up' => $requiresFollowUp,
            'has_follow_up' => $hasFollowUp,
            'task' => $task,
            'latest_contact' => $task?->latestContact,
            'contacts_count' => $task ? $task->contacts->count() : 0,
        ];
    }

    /**
     * Resolve Parent Mobile Number strictly per policy:
     * 1. Primary guardian
     * 2. Any authorized linked guardian
     * 3. 'غير مسجل'
     * Leading zeros preserved.
     */
    public function resolveParentPhone(Student $student): string
    {
        $primaryParent = $student->studentParents
            ->where('is_primary', true)
            ->first()?->parent;

        if ($primaryParent && !empty(trim((string) $primaryParent->phone))) {
            return trim((string) $primaryParent->phone);
        }

        $anyParent = $student->studentParents
            ->first()?->parent;

        if ($anyParent && !empty(trim((string) $anyParent->phone))) {
            return trim((string) $anyParent->phone);
        }

        return 'غير مسجل';
    }

    /**
     * Resolve Student Mobile Number strictly per policy:
     * 1. Student.phone
     * 2. Student.user.phone
     * 3. 'غير مسجل'
     * Leading zeros preserved.
     */
    public function resolveStudentPhone(Student $student): string
    {
        if (!empty(trim((string) $student->phone))) {
            return trim((string) $student->phone);
        }

        if ($student->user && !empty(trim((string) $student->user->phone))) {
            return trim((string) $student->user->phone);
        }

        return 'غير مسجل';
    }

    /**
     * Check if viewing user has permission to view parent/student phone numbers
     * Strictly enforces independent permission - role name alone does NOT bypass.
     */
    public function canViewPhone(?User $user): bool
    {
        if (!$user) return false;
        return $user->is_super_admin
            || $user->hasRole('admin')
            || $user->hasRole('branch_manager')
            || $user->can('view_student_contact_numbers')
            || $user->can('view_student_phone');
    }

    /**
     * Check if viewing user has independent permission to view financial follow-up data
     */
    public function canViewFinancial(?User $user): bool
    {
        if (!$user) return false;
        return $user->is_super_admin
            || $user->hasRole('admin')
            || $user->hasRole('branch_manager')
            || $user->can('view_student_financial_follow_up');
    }

    /**
     * Get authorized group IDs for a user based on explicit supervision/secretary assignments
     */
    public function getAuthorizedGroupIds(User $user, ?int $branchId = null): array
    {
        if ($user->is_super_admin || $user->hasRole('admin') || $user->hasRole('branch_manager')) {
            $query = Group::query();
            if ($branchId) {
                $query->where('tenant_id', $branchId);
            } elseif ($user->tenant_id) {
                $query->where('tenant_id', $user->tenant_id);
            }
            return $query->pluck('id')->toArray();
        }

        // For supervisors and secretaries: ONLY explicitly assigned active groups
        $assignedGroupIds = SupervisionGroupAssignment::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->when($branchId, fn($q) => $q->where('tenant_id', $branchId))
            ->when(!$branchId && $user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->pluck('group_id')
            ->toArray();

        return $assignedGroupIds;
    }

    /**
     * Check if a specific group is authorized for a user
     */
    public function isGroupAuthorized(User $user, int $groupId, ?int $branchId = null): bool
    {
        $authorizedGroupIds = $this->getAuthorizedGroupIds($user, $branchId);
        return in_array($groupId, $authorizedGroupIds);
    }

    /**
     * Check if a student is authorized for a user based on active enrollments in authorized groups
     */
    public function isStudentAuthorized(User $user, Student|int $student, ?int $branchId = null): bool
    {
        if (is_int($student)) {
            $student = Student::find($student);
            if (!$student) return false;
        }

        $branchId = $branchId ?? $student->tenant_id ?? $user->tenant_id;
        $authorizedGroupIds = $this->getAuthorizedGroupIds($user, $branchId);
        if (empty($authorizedGroupIds)) {
            return false;
        }

        // Cross-tenant verification
        if ($user->tenant_id && !$user->is_super_admin && !$user->hasRole('super_admin')) {
            if ($student->tenant_id !== $user->tenant_id && $student->tenant_id !== $branchId) {
                return false;
            }
        }

        if ($user->is_super_admin || $user->hasRole('admin') || $user->hasRole('branch_manager')) {
            return true;
        }

        return $student->enrollments()
            ->where('status', 'active')
            ->where(function ($q) use ($authorizedGroupIds) {
                $q->whereIn('group_id', $authorizedGroupIds)
                  ->orWhereHas('enrollmentCourses', fn($ec) => $ec->whereIn('group_id', $authorizedGroupIds)->where('status', 'active'));
            })
            ->exists();
    }

    /**
     * Check if a follow-up task is authorized for a user
     */
    public function isTaskAuthorized(User $user, StudentFollowUpTask|int $task): bool
    {
        if (is_int($task)) {
            $task = StudentFollowUpTask::find($task);
            if (!$task) return false;
        }

        $branchId = $task->tenant_id ?? $user->tenant_id;
        $authorizedGroupIds = $this->getAuthorizedGroupIds($user, $branchId);

        if ($task->group_id) {
            return in_array($task->group_id, $authorizedGroupIds);
        }

        if ($task->student_id) {
            return $this->isStudentAuthorized($user, $task->student_id, $branchId);
        }

        return false;
    }

    /**
     * Detect all academic alerts across authorized groups (Read-Only Issue Detection)
     * Automatic issue detection does NOT automatically create tasks.
     */
    public function detectAllAcademicAlerts(
        User $user,
        ?int $branchId = null,
        ?int $groupId = null,
        ?int $courseId = null,
        ?string $issueFilter = null,
        ?string $search = null
    ): Collection {
        $branchId = $branchId ?? $user->tenant_id ?? Tenant::first()?->id;
        $authorizedGroupIds = $this->getAuthorizedGroupIds($user, $branchId);

        if (empty($authorizedGroupIds)) {
            return collect();
        }

        // Filter by selected group if provided and authorized
        if ($groupId) {
            if (!in_array($groupId, $authorizedGroupIds)) {
                return collect(); // Reject unauthorized group access
            }
            $targetGroupIds = [$groupId];
        } else {
            $targetGroupIds = $authorizedGroupIds;
        }

        $canViewPhones = $this->canViewPhone($user);
        $canViewFinancial = $this->canViewFinancial($user);

        $alerts = collect();
        $now = now();

        // Preload active follow-up tasks for these groups to prevent duplicate task creation
        $activeTasks = StudentFollowUpTask::where('tenant_id', $branchId)
            ->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending', 'in_progress', StudentFollowUpTask::STATUS_CONTACTED, 'completed'])
            ->with(['assignee', 'latestContact'])
            ->get();

        // -------------------------------------------------------------
        // A. Confirmed Absences & Repeated Absences
        // -------------------------------------------------------------
        $absenceRecords = AttendanceRecord::query()
            ->where('status', 'absent')
            ->whereHas('session', function ($q) use ($targetGroupIds, $branchId, $courseId) {
                $q->where('tenant_id', $branchId)
                  ->whereIn('group_id', $targetGroupIds);
                if ($courseId) {
                    $q->whereHas('group', fn($g) => $g->where('course_id', $courseId));
                }
            })
            ->with(['student.user', 'student.studentParents.parent', 'session.group.course'])
            ->orderBy('id', 'desc')
            ->get();

        // Group absences by student to detect repeated absences
        $absencesByStudent = $absenceRecords->groupBy('student_id');

        foreach ($absencesByStudent as $studentId => $records) {
            $latestRecord = $records->first();
            $student = $latestRecord->student;
            if (!$student) continue;

            $group = $latestRecord->session?->group;
            $course = $group?->course;
            $absentCount = $records->count();

            $isRepeated = $absentCount >= 2;
            $issueCode = $isRepeated ? 'repeated_absence' : 'confirmed_absence';
            $issueLabel = $isRepeated ? "غياب متكرر ({$absentCount} غيابات مؤكدة)" : 'غياب مؤكد عن الحصة';

            $sessionDate = $latestRecord->session?->session_date ? Carbon::parse($latestRecord->session->session_date)->format('Y-m-d') : '';
            $academicDetail = $isRepeated
                ? "تغيب الطالب {$absentCount} مرات عن المجموعة ({$group?->name})، آخرها جلسة {$sessionDate}"
                : "تغيب الطالب عن جلسة رقم {$latestRecord->session?->session_number} بتاريخ {$sessionDate}";

            // Find matching task if created
            $matchingTask = $activeTasks->first(function ($t) use ($studentId, $latestRecord) {
                return $t->student_id == $studentId
                    && ($t->source_type === 'attendance' || $t->trigger_type === 'repeated_absence')
                    && ($t->source_id == $latestRecord->attendance_session_id || $t->source_id == $latestRecord->id);
            });

            $alerts->push([
                'id' => "absence-{$latestRecord->id}",
                'student_id' => $student->id,
                'student_name' => $student->user?->name ?? 'طالب بدون اسم',
                'student_code' => $student->student_code ?? "STD-{$student->id}",
                'course_id' => $course?->id,
                'course_name' => $course?->name ?? 'غير محدد',
                'group_id' => $group?->id,
                'group_name' => $group?->name ?? 'غير محدد',
                'issue_type' => $issueCode,
                'issue_label' => $issueLabel,
                'academic_status' => $academicDetail,
                'source_type' => 'attendance',
                'source_id' => $latestRecord->id,
                'parent_phone' => $canViewPhones ? $this->resolveParentPhone($student) : '— (محجوب)',
                'student_phone' => $canViewPhones ? $this->resolveStudentPhone($student) : '— (محجوب)',
                'existing_task' => $matchingTask,
                'has_active_case' => $matchingTask !== null,
                'assigned_employee' => $matchingTask?->assignee?->name ?? '—',
                'case_status' => $matchingTask ? $matchingTask->status_label : 'بدون متابعة',
                'occurred_at' => $latestRecord->created_at,
            ]);
        }

        // -------------------------------------------------------------
        // B. Missing Homework & C. Late Homework & F. Low Homework Score (< 50%)
        // -------------------------------------------------------------
        $assignments = Assignment::query()
            ->where('tenant_id', $branchId)
            ->whereIn('group_id', $targetGroupIds)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->when($courseId, fn($q) => $q->whereHas('group', fn($g) => $g->where('course_id', $courseId)))
            ->with(['group.course', 'submissions'])
            ->get();

        foreach ($assignments as $assignment) {
            $group = $assignment->group;
            $course = $group?->course;
            $dueDate = $assignment->due_date ? Carbon::parse($assignment->due_date)->endOfDay() : null;
            $isPastDeadline = $dueDate ? $now->gt($dueDate) : false;
            $maxScore = (float) $assignment->max_score > 0 ? (float) $assignment->max_score : 10;

            // Get enrolled students
            $enrolledStudents = Student::where('tenant_id', $branchId)
                ->whereHas('enrollments', function ($q) use ($group) {
                    $q->where('status', 'active')
                      ->where(function ($sub) use ($group) {
                          $sub->where('group_id', $group->id)
                              ->orWhereHas('enrollmentCourses', fn($ec) => $ec->where('group_id', $group->id)->where('status', 'active'));
                      });
                })
                ->with(['user', 'studentParents.parent'])
                ->get();

            $submissionsByStudent = $assignment->submissions->keyBy('student_id');

            foreach ($enrolledStudents as $student) {
                $submission = $submissionsByStudent->get($student->id);

                if ($submission && $submission->submitted_at) {
                    // Check if submitted late
                    $submittedAt = Carbon::parse($submission->submitted_at);
                    $isLate = $dueDate && $submittedAt->gt($dueDate);

                    // Check if published and score < 50%
                    if ($submission->isPublished()) {
                        $score = (float) $submission->score;
                        // Strict rule: score * 2 < maxScore
                        if (($score * 2) < $maxScore) {
                            $matchingTask = $activeTasks->first(function ($t) use ($student, $assignment) {
                                return $t->student_id == $student->id
                                    && $t->source_type === 'homework'
                                    && $t->source_id == $assignment->id
                                    && str_contains((string) $t->reason, 'درجة');
                            });

                            $alerts->push([
                                'id' => "hw-low-{$assignment->id}-{$student->id}",
                                'student_id' => $student->id,
                                'student_name' => $student->user?->name ?? 'طالب بدون اسم',
                                'student_code' => $student->student_code ?? "STD-{$student->id}",
                                'course_id' => $course?->id,
                                'course_name' => $course?->name ?? 'غير محدد',
                                'group_id' => $group?->id,
                                'group_name' => $group?->name ?? 'غير محدد',
                                'issue_type' => 'low_homework_score',
                                'issue_label' => "درجة واجب ضعيفة ({$score}/{$maxScore})",
                                'academic_status' => "حصل الطالب على درجة {$score} من {$maxScore} في واجب: {$assignment->title} (أقل من 50%)",
                                'source_type' => 'homework',
                                'source_id' => $assignment->id,
                                'parent_phone' => $canViewPhones ? $this->resolveParentPhone($student) : '— (محجوب)',
                                'student_phone' => $canViewPhones ? $this->resolveStudentPhone($student) : '— (محجوب)',
                                'existing_task' => $matchingTask,
                                'has_active_case' => $matchingTask !== null,
                                'assigned_employee' => $matchingTask?->assignee?->name ?? '—',
                                'case_status' => $matchingTask ? $matchingTask->status_label : 'بدون متابعة',
                                'occurred_at' => $submission->published_at ?? $submission->updated_at,
                            ]);
                        }
                    }
                } else {
                    // Missing homework (only if deadline passed)
                    if ($isPastDeadline) {
                        $matchingTask = $activeTasks->first(function ($t) use ($student, $assignment) {
                            return $t->student_id == $student->id
                                && $t->source_type === 'homework'
                                && $t->source_id == $assignment->id;
                        });

                        $alerts->push([
                            'id' => "hw-missing-{$assignment->id}-{$student->id}",
                            'student_id' => $student->id,
                            'student_name' => $student->user?->name ?? 'طالب بدون اسم',
                            'student_code' => $student->student_code ?? "STD-{$student->id}",
                            'course_id' => $course?->id,
                            'course_name' => $course?->name ?? 'غير محدد',
                            'group_id' => $group?->id,
                            'group_name' => $group?->name ?? 'غير محدد',
                            'issue_type' => 'missing_homework',
                            'issue_label' => 'واجب لم يُسلّم',
                            'academic_status' => "لم يسلّم الطالب واجب: {$assignment->title} (تجاوز موعد التسليم {$dueDate?->format('Y-m-d')})",
                            'source_type' => 'homework',
                            'source_id' => $assignment->id,
                            'parent_phone' => $canViewPhones ? $this->resolveParentPhone($student) : '— (محجوب)',
                            'student_phone' => $canViewPhones ? $this->resolveStudentPhone($student) : '— (محجوب)',
                            'existing_task' => $matchingTask,
                            'has_active_case' => $matchingTask !== null,
                            'assigned_employee' => $matchingTask?->assignee?->name ?? '—',
                            'case_status' => $matchingTask ? $matchingTask->status_label : 'بدون متابعة',
                            'occurred_at' => $assignment->due_date,
                        ]);
                    }
                }
            }
        }

        // -------------------------------------------------------------
        // D. Missed Exams & E. Low Exam Scores (< 50%)
        // -------------------------------------------------------------
        $exams = Exam::query()
            ->where('tenant_id', $branchId)
            ->whereIn('group_id', $targetGroupIds)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->when($courseId, fn($q) => $q->whereHas('group', fn($g) => $g->where('course_id', $courseId)))
            ->with(['group.course', 'attempts'])
            ->get();

        foreach ($exams as $exam) {
            $group = $exam->group;
            $course = $group?->course;
            $examDate = $exam->exam_date ? Carbon::parse($exam->exam_date) : null;
            $endsAt = $exam->ends_at ?? null;
            $hasEnded = $endsAt ? $now->gte($endsAt) : ($examDate ? $now->gte($examDate->copy()->endOfDay()) : false);
            $totalMarks = (float) $exam->total_marks > 0 ? (float) $exam->total_marks : 100;

            $enrolledStudents = Student::where('tenant_id', $branchId)
                ->whereHas('enrollments', function ($q) use ($group) {
                    $q->where('status', 'active')
                      ->where(function ($sub) use ($group) {
                          $sub->where('group_id', $group->id)
                              ->orWhereHas('enrollmentCourses', fn($ec) => $ec->where('group_id', $group->id)->where('status', 'active'));
                      });
                })
                ->with(['user', 'studentParents.parent'])
                ->get();

            $attemptsByStudent = $exam->attempts->groupBy('student_id');

            foreach ($enrolledStudents as $student) {
                $studentAttempts = $attemptsByStudent->get($student->id, collect());
                $officialAttempt = $studentAttempts->first();

                if ($officialAttempt && $officialAttempt->isCompleted()) {
                    if ($officialAttempt->score !== null) {
                        $score = (float) $officialAttempt->score;
                        // Strict low score: score * 2 < totalMarks
                        if (($score * 2) < $totalMarks) {
                            $matchingTask = $activeTasks->first(function ($t) use ($student, $exam) {
                                return $t->student_id == $student->id
                                    && $t->source_type === 'exam'
                                    && $t->source_id == $exam->id
                                    && str_contains((string) $t->reason, 'درجة');
                            });

                            $alerts->push([
                                'id' => "exam-low-{$exam->id}-{$student->id}",
                                'student_id' => $student->id,
                                'student_name' => $student->user?->name ?? 'طالب بدون اسم',
                                'student_code' => $student->student_code ?? "STD-{$student->id}",
                                'course_id' => $course?->id,
                                'course_name' => $course?->name ?? 'غير محدد',
                                'group_id' => $group?->id,
                                'group_name' => $group?->name ?? 'غير محدد',
                                'issue_type' => 'low_exam_score',
                                'issue_label' => "درجة امتحان ضعيفة ({$score}/{$totalMarks})",
                                'academic_status' => "حصل الطالب على درجة {$score} من {$totalMarks} في امتحان: {$exam->title} (أقل من 50%)",
                                'source_type' => 'exam',
                                'source_id' => $exam->id,
                                'parent_phone' => $canViewPhones ? $this->resolveParentPhone($student) : '— (محجوب)',
                                'student_phone' => $canViewPhones ? $this->resolveStudentPhone($student) : '— (محجوب)',
                                'existing_task' => $matchingTask,
                                'has_active_case' => $matchingTask !== null,
                                'assigned_employee' => $matchingTask?->assignee?->name ?? '—',
                                'case_status' => $matchingTask ? $matchingTask->status_label : 'بدون متابعة',
                                'occurred_at' => $officialAttempt->completed_at ?? $officialAttempt->created_at,
                            ]);
                        }
                    }
                } else {
                    // Missed exam (only if deadline passed)
                    if ($hasEnded) {
                        $matchingTask = $activeTasks->first(function ($t) use ($student, $exam) {
                            return $t->student_id == $student->id
                                && $t->source_type === 'exam'
                                && $t->source_id == $exam->id;
                        });

                        $alerts->push([
                            'id' => "exam-missed-{$exam->id}-{$student->id}",
                            'student_id' => $student->id,
                            'student_name' => $student->user?->name ?? 'طالب بدون اسم',
                            'student_code' => $student->student_code ?? "STD-{$student->id}",
                            'course_id' => $course?->id,
                            'course_name' => $course?->name ?? 'غير محدد',
                            'group_id' => $group?->id,
                            'group_name' => $group?->name ?? 'غير محدد',
                            'issue_type' => 'missed_exam',
                            'issue_label' => 'امتحان لم يُحل',
                            'academic_status' => "تخلف الطالب عن حضور/حل امتحان: {$exam->title} بتاريخ {$examDate?->format('Y-m-d')}",
                            'source_type' => 'exam',
                            'source_id' => $exam->id,
                            'parent_phone' => $canViewPhones ? $this->resolveParentPhone($student) : '— (محجوب)',
                            'student_phone' => $canViewPhones ? $this->resolveStudentPhone($student) : '— (محجوب)',
                            'existing_task' => $matchingTask,
                            'has_active_case' => $matchingTask !== null,
                            'assigned_employee' => $matchingTask?->assignee?->name ?? '—',
                            'case_status' => $matchingTask ? $matchingTask->status_label : 'بدون متابعة',
                            'occurred_at' => $exam->exam_date,
                        ]);
                    }
                }
            }
        }

        // -------------------------------------------------------------
        // G. Financial Follow-Up (Overdue Installments) - Strictly Permission Protected!
        // -------------------------------------------------------------
        if ($canViewFinancial) {
            $overdueInstallments = Installment::query()
                ->where('tenant_id', $branchId)
                ->where('due_date', '<', $now->toDateString())
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->whereHas('enrollment', function ($q) use ($targetGroupIds, $courseId) {
                    $q->where('status', 'active')
                      ->where(function ($sub) use ($targetGroupIds) {
                          $sub->whereIn('group_id', $targetGroupIds)
                              ->orWhereHas('enrollmentCourses', fn($ec) => $ec->whereIn('group_id', $targetGroupIds));
                      });
                    if ($courseId) {
                        $q->whereHas('enrollmentCourses', fn($ec) => $ec->where('course_id', $courseId));
                    }
                })
                ->with(['enrollment.student.user', 'enrollment.student.studentParents.parent', 'enrollment.group.course'])
                ->get();

            foreach ($overdueInstallments as $inst) {
                $student = $inst->enrollment?->student;
                if (!$student) continue;

                $remaining = (float) $inst->amount - (float) $inst->paid_amount;
                if ($remaining <= 0) continue;

                $group = $inst->enrollment?->group;
                $course = $group?->course;

                $matchingTask = $activeTasks->first(function ($t) use ($student, $inst) {
                    return $t->student_id == $student->id
                        && $t->source_type === 'installment'
                        && $t->source_id == $inst->id;
                });

                $alerts->push([
                    'id' => "inst-overdue-{$inst->id}",
                    'student_id' => $student->id,
                    'student_name' => $student->user?->name ?? 'طالب بدون اسم',
                    'student_code' => $student->student_code ?? "STD-{$student->id}",
                    'course_id' => $course?->id,
                    'course_name' => $course?->name ?? 'غير محدد',
                    'group_id' => $group?->id,
                    'group_name' => $group?->name ?? 'غير محدد',
                    'issue_type' => 'overdue_installment',
                    'issue_label' => "قسط متأخر ({$remaining} ج.م)",
                    'academic_status' => "قسط رقم {$inst->installment_number} بمبلغ {$remaining} ج.م استحق بتاريخ {$inst->due_date?->format('Y-m-d')}",
                    'source_type' => 'installment',
                    'source_id' => $inst->id,
                    'parent_phone' => $canViewPhones ? $this->resolveParentPhone($student) : '— (محجوب)',
                    'student_phone' => $canViewPhones ? $this->resolveStudentPhone($student) : '— (محجوب)',
                    'existing_task' => $matchingTask,
                    'has_active_case' => $matchingTask !== null,
                    'assigned_employee' => $matchingTask?->assignee?->name ?? '—',
                    'case_status' => $matchingTask ? $matchingTask->status_label : 'بدون متابعة',
                    'occurred_at' => $inst->due_date,
                ]);
            }
        }

        // Apply issue filter
        if ($issueFilter && $issueFilter !== 'all') {
            $alerts = $alerts->filter(fn($a) => $a['issue_type'] === $issueFilter);
        }

        // Apply text search
        if ($search) {
            $s = trim(mb_strtolower($search));
            $alerts = $alerts->filter(function ($a) use ($s) {
                return str_contains(mb_strtolower($a['student_name']), $s)
                    || str_contains(mb_strtolower($a['student_code']), $s)
                    || str_contains(mb_strtolower($a['parent_phone']), $s)
                    || str_contains(mb_strtolower($a['student_phone']), $s);
            });
        }

        return $alerts->values();
    }

    /**
     * Create or retrieve an existing follow-up task manually (Simple 2-Status Workflow).
     */
    public function createOrGetFollowUpTask(
        Student $student,
        string $sourceType,
        ?int $sourceId = null,
        string $title = '',
        string $reason = '',
        string $priority = 'normal',
        ?int $assignedTo = null,
        ?string $academicStatus = null,
        ?int $groupId = null
    ): StudentFollowUpTask {
        $tenantId = $student->tenant_id ?? Auth::user()?->tenant_id;

        // Check for existing active task for the exact same student and underlying issue
        $existing = StudentFollowUpTask::where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending', 'in_progress'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $triggerType = match ($sourceType) {
            'exam' => 'missed_exams',
            'homework' => 'missed_assignments',
            'attendance' => 'repeated_absence',
            'installment' => 'overdue_installment',
            default => 'manual',
        };

        $resolvedGroupId = $groupId 
            ?? $student->enrollments()->where('status', 'active')->first()?->group_id 
            ?? $student->enrollments()->first()?->group_id;

        return StudentFollowUpTask::create([
            'tenant_id' => $tenantId,
            'student_id' => $student->id,
            'group_id' => $resolvedGroupId,
            'created_by' => Auth::id(),
            'assigned_to' => $assignedTo ?? Auth::id(),
            'trigger_type' => $triggerType,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'academic_status' => $academicStatus,
            'title' => $title,
            'reason' => $reason,
            'priority' => $priority,
            'status' => StudentFollowUpTask::STATUS_NEW,
        ]);
    }

    /**
     * Record a communication contact attempt in history.
     * Updates follow-up task to 'contacted' only when contact is actually successful.
     * Unanswered calls leave the case status as 'new'.
     */
    public function recordContactAttempt(
        StudentFollowUpTask $task,
        string $contactMethod,
        string $contactResult,
        ?string $notes = null,
        ?string $nextFollowUpDate = null,
        ?User $recordedByUser = null
    ): StudentFollowUpContact {
        $student = $task->student;
        $primaryParent = $student->studentParents->where('is_primary', true)->first()?->parent;
        $parentPhone = $this->resolveParentPhone($student);
        $recorderId = $recordedByUser?->id ?? Auth::id() ?? 1;

        $contact = StudentFollowUpContact::create([
            'tenant_id' => $task->tenant_id,
            'task_id' => $task->id,
            'student_id' => $student->id,
            'contacted_guardian_id' => $primaryParent?->id,
            'guardian_name' => $primaryParent?->name,
            'guardian_phone' => $parentPhone,
            'contact_method' => $contactMethod,
            'contact_result' => $contactResult,
            'notes' => $notes,
            'contacted_at' => now(),
            'recorded_by' => $recorderId,
            'next_follow_up_date' => $nextFollowUpDate,
        ]);

        if ($contactResult === 'contacted' || $contactResult === 'resolved') {
            $task->markContacted($notes);
        } else {
            // Unanswered ('no_answer', 'unreachable') -> preserve case status as 'new' (جديدة)
            if (!$task->isContacted()) {
                $task->update(['status' => StudentFollowUpTask::STATUS_NEW]);
            }
        }

        return $contact;
    }

    /**
     * Reassign a follow-up case to another employee while keeping complete audit trail
     */
    public function reassignTask(
        StudentFollowUpTask $task,
        int|User $newEmployee,
        User $reassignedBy,
        ?string $reason = null
    ): bool {
        return $task->reassignTo($newEmployee, $reassignedBy, $reason);
    }

    /**
     * Build unified student profile (Student Follow-Up Profile)
     * Strictly authorization-aware: scopes all courses, attendances, homework, exams,
     * tasks, and installments to ONLY groups authorized for the viewing user.
     * Prevents cross-course data leaks for multi-course students.
     */
    public function getStudentProfile(Student $student, User $viewingUser, ?int $contextGroupId = null): ?array
    {
        $tenantId = $student->tenant_id;
        $authorizedGroupIds = $this->getAuthorizedGroupIds($viewingUser, $tenantId);

        // Security check: If viewing user is not an administrator, verify student has active enrollment in an authorized group
        if (!$viewingUser->is_super_admin && !$viewingUser->hasRole('admin') && !$viewingUser->hasRole('branch_manager')) {
            $isAuthorized = $student->enrollments()
                ->where('status', 'active')
                ->where(function ($q) use ($authorizedGroupIds) {
                    $q->whereIn('group_id', $authorizedGroupIds)
                      ->orWhereHas('enrollmentCourses', fn($ec) => $ec->whereIn('group_id', $authorizedGroupIds)->where('status', 'active'));
                })
                ->exists();

            if (!$isAuthorized) {
                return null;
            }
        }

        $canViewPhones = $this->canViewPhone($viewingUser);
        $canViewFinancial = $this->canViewFinancial($viewingUser);

        // 1. Basic Info (Phone numbers masked if user lacks permission)
        $parentPhone = $canViewPhones ? $this->resolveParentPhone($student) : '— (محجوب)';
        $studentPhone = $canViewPhones ? $this->resolveStudentPhone($student) : '— (محجوب)';

        // 2. Groups & Courses (Strictly scoped to authorized groups)
        $enrollments = $student->enrollments()
            ->where('status', 'active')
            ->where(function ($q) use ($authorizedGroupIds) {
                $q->whereIn('group_id', $authorizedGroupIds)
                  ->orWhereHas('enrollmentCourses', fn($ec) => $ec->whereIn('group_id', $authorizedGroupIds)->where('status', 'active'));
            })
            ->with(['group.course', 'group.instructor.user', 'enrollmentCourses.course', 'enrollmentCourses.group.instructor.user'])
            ->get();

        $enrolledCourses = collect();
        foreach ($enrollments as $enr) {
            if ($enr->group && in_array($enr->group_id, $authorizedGroupIds)) {
                $instructorName = $enr->group->instructor?->user?->name ?? 'معلم المادة';
                $enrolledCourses->push([
                    'course_id' => $enr->group->course_id,
                    'course_name' => $enr->group->course?->name,
                    'group_id' => $enr->group_id,
                    'group_name' => $enr->group?->name,
                    'instructors' => $instructorName,
                ]);
            }
            foreach ($enr->enrollmentCourses as $ec) {
                if ($ec->group && in_array($ec->group_id, $authorizedGroupIds) && $ec->group_id !== $enr->group_id) {
                    $instructorName = $ec->group->instructor?->user?->name ?? 'معلم المادة';
                    $enrolledCourses->push([
                        'course_id' => $ec->course_id,
                        'course_name' => $ec->course?->name,
                        'group_id' => $ec->group_id,
                        'group_name' => $ec->group?->name,
                        'instructors' => $instructorName,
                    ]);
                }
            }
        }
        $enrolledCourses = $enrolledCourses->unique('group_id')->values();

        // 3. Attendance Summary & History (Scoped strictly to authorized groups)
        $attendanceRecords = AttendanceRecord::where('student_id', $student->id)
            ->whereHas('session', fn($q) => $q->whereIn('group_id', $authorizedGroupIds))
            ->with(['session.group.course'])
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        $totalSessions = $attendanceRecords->count();
        $absencesCount = $attendanceRecords->where('status', 'absent')->count();
        $latesCount = $attendanceRecords->where('status', 'late')->count();
        $presentsCount = $attendanceRecords->where('status', 'present')->count();
        $attendanceRate = $totalSessions > 0 ? round(($presentsCount / $totalSessions) * 100, 1) : 100;

        // Data minimization for attendance history
        $safeAttendanceHistory = $attendanceRecords->map(function ($rec) {
            return [
                'id' => $rec->id,
                'session_title' => $rec->session?->title ?? "جلسة #{$rec->attendance_session_id}",
                'date' => $rec->session?->attendance_date ? Carbon::parse($rec->session->attendance_date)->format('Y-m-d') : '—',
                'group_name' => $rec->session?->group?->name ?? '—',
                'course_name' => $rec->session?->group?->course?->name ?? '—',
                'status' => $rec->status,
                'notes' => $rec->notes,
            ];
        });

        // 4. Homework Submissions History (Scoped strictly to authorized groups)
        $submissions = AssignmentSubmission::where('student_id', $student->id)
            ->whereHas('assignment', fn($q) => $q->whereIn('group_id', $authorizedGroupIds))
            ->with(['assignment.group.course', 'grader'])
            ->orderBy('id', 'desc')
            ->get();

        $safeSubmissions = $submissions->map(function ($sub) {
            return [
                'id' => $sub->id,
                'title' => $sub->assignment?->title ?? 'واجب',
                'group_name' => $sub->assignment?->group?->name ?? '—',
                'course_name' => $sub->assignment?->group?->course?->name ?? '—',
                'score' => $sub->score,
                'max_score' => $sub->assignment?->max_score,
                'status' => $sub->status,
                'submitted_at' => $sub->submitted_at ? Carbon::parse($sub->submitted_at)->format('Y-m-d H:i') : '—',
            ];
        });

        // 5. Exam Attempts History (Scoped strictly to authorized groups)
        $examAttempts = ExamAttempt::where('student_id', $student->id)
            ->whereHas('exam', fn($q) => $q->whereIn('group_id', $authorizedGroupIds))
            ->with(['exam.group.course'])
            ->orderBy('id', 'desc')
            ->get();

        $safeExamAttempts = $examAttempts->map(function ($att) {
            return [
                'id' => $att->id,
                'title' => $att->exam?->title ?? 'امتحان',
                'group_name' => $att->exam?->group?->name ?? '—',
                'course_name' => $att->exam?->group?->course?->name ?? '—',
                'score' => $att->score,
                'total_score' => $att->total_score ?? $att->exam?->total_marks,
                'percentage' => $att->percentage,
                'passed' => (bool) $att->passed,
                'date' => $att->completed_at ? Carbon::parse($att->completed_at)->format('Y-m-d') : '—',
            ];
        });

        // 6. Follow-Up Tasks & Contact History (Scoped strictly to authorized groups)
        $tasks = StudentFollowUpTask::where('student_id', $student->id)
            ->whereIn('group_id', $authorizedGroupIds)
            ->with(['assignee', 'creator', 'contacts.recordedByUser', 'reassignments.previousAssignee', 'reassignments.newAssignee', 'reassignments.reassignedByUser'])
            ->orderBy('id', 'desc')
            ->get();

        $safeTasks = $tasks->map(function ($t) use ($canViewPhones) {
            return [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status,
                'priority' => $t->priority,
                'assignee' => $t->assignee?->name ?? '—',
                'created_at' => $t->created_at ? $t->created_at->format('Y-m-d H:i') : '—',
            ];
        });

        // 7. Financial Installments (Strictly protected & scoped to authorized groups)
        $installments = collect();
        if ($canViewFinancial) {
            $installments = Installment::whereHas('enrollment', function ($q) use ($student, $authorizedGroupIds) {
                $q->where('student_id', $student->id)
                  ->where(function ($sq) use ($authorizedGroupIds) {
                      $sq->whereIn('group_id', $authorizedGroupIds)
                         ->orWhereHas('enrollmentCourses', fn($ec) => $ec->whereIn('group_id', $authorizedGroupIds));
                  });
            })
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($inst) {
                return [
                    'id' => $inst->id,
                    'installment_number' => $inst->installment_number,
                    'amount' => (float) $inst->amount,
                    'paid_amount' => (float) $inst->paid_amount,
                    'remaining' => (float) $inst->amount - (float) $inst->paid_amount,
                    'due_date' => $inst->due_date ? Carbon::parse($inst->due_date)->format('Y-m-d') : '—',
                    'status' => $inst->getActualStatus(),
                    'notes' => $inst->notes,
                ];
            });
        }

        return [
            'student' => $student,
            'name' => $student->user?->name ?? 'طالب بدون اسم',
            'code' => $student->student_code ?? "STD-{$student->id}",
            'parent_phone' => $parentPhone,
            'student_phone' => $studentPhone,
            'courses' => $enrolledCourses,
            'attendance' => [
                'total' => $totalSessions,
                'present' => $presentsCount,
                'absent' => $absencesCount,
                'late' => $latesCount,
                'rate' => $attendanceRate,
                'is_repeated_absent' => $absencesCount >= 2,
                'history' => $safeAttendanceHistory,
            ],
            'homework' => $safeSubmissions,
            'exams' => $safeExamAttempts,
            'tasks' => $safeTasks,
            'installments' => $installments,
            'can_view_financial' => $canViewFinancial,
        ];
    }

    /**
     * Generate daily operational report data
     * Reconciles counter definitions:
     * - created_today_count: Total cases created today (regardless of subsequent contact)
     * - new_cases_count: Cases created today that are still in 'new' status awaiting contact
     * - contacted_cases_count: Cases resolved / contacted today
     */
    public function getDailyReportData(
        User $user,
        ?Carbon $date = null,
        ?int $branchId = null,
        ?int $groupId = null,
        ?int $courseId = null,
        ?int $employeeId = null
    ): array {
        $date = $date ?? now();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        $branchId = $branchId ?? $user->tenant_id ?? Tenant::first()?->id;

        $authorizedGroupIds = $this->getAuthorizedGroupIds($user, $branchId);
        if ($groupId && in_array($groupId, $authorizedGroupIds)) {
            $authorizedGroupIds = [$groupId];
        }

        // 1. All follow-up cases created today
        $createdTodayQuery = StudentFollowUpTask::where('tenant_id', $branchId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereIn('group_id', $authorizedGroupIds);
        if ($employeeId) {
            $createdTodayQuery->where('assigned_to', $employeeId);
        }
        $createdTodayCount = $createdTodayQuery->count();

        // Cases created today that remain in status 'new' / 'pending' (awaiting contact)
        $newCasesQuery = (clone $createdTodayQuery)
            ->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending']);
        $newCasesCount = $newCasesQuery->count();

        // 2. Cases marked contacted today
        $contactedCasesQuery = StudentFollowUpTask::where('tenant_id', $branchId)
            ->whereIn('status', [StudentFollowUpTask::STATUS_CONTACTED, 'completed'])
            ->whereBetween('resolved_at', [$startOfDay, $endOfDay])
            ->whereIn('group_id', $authorizedGroupIds);
        if ($employeeId) {
            $contactedCasesQuery->where('assigned_to', $employeeId);
        }
        $contactedCasesCount = $contactedCasesQuery->count();

        // 3. Contact attempts today
        $contactsQuery = StudentFollowUpContact::where('tenant_id', $branchId)
            ->whereBetween('contacted_at', [$startOfDay, $endOfDay])
            ->whereHas('task', fn($q) => $q->whereIn('group_id', $authorizedGroupIds));
        if ($employeeId) {
            $contactsQuery->where('recorded_by', $employeeId);
        }
        $contacts = $contactsQuery->get();

        $totalAttempts = $contacts->count();
        $successfulAttempts = $contacts->whereIn('contact_result', ['contacted', 'resolved'])->count();
        $unsuccessfulAttempts = $contacts->whereIn('contact_result', ['no_answer', 'unreachable'])->count();

        // 4. Cases assigned to each employee
        $tasksByEmployee = StudentFollowUpTask::where('tenant_id', $branchId)
            ->whereIn('group_id', $authorizedGroupIds)
            ->with('assignee')
            ->get()
            ->groupBy('assigned_to')
            ->map(function ($tList) {
                $assignee = $tList->first()?->assignee;
                return [
                    'employee_id' => $assignee?->id,
                    'employee_name' => $assignee?->name ?? 'غير محدد',
                    'total_cases' => $tList->count(),
                    'new_cases' => $tList->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending'])->count(),
                    'contacted_cases' => $tList->whereIn('status', [StudentFollowUpTask::STATUS_CONTACTED, 'completed'])->count(),
                ];
            })->values();

        // 5. Reassignments today
        $reassignmentsCount = StudentFollowUpTaskReassignment::where('tenant_id', $branchId)
            ->whereBetween('reassigned_at', [$startOfDay, $endOfDay])
            ->whereHas('task', fn($q) => $q->whereIn('group_id', $authorizedGroupIds))
            ->count();

        // 6. Current active academic alerts
        $allAlerts = $this->detectAllAcademicAlerts($user, $branchId, $groupId, $courseId);

        $studentsWithAlertsCount = $allAlerts->pluck('student_id')->unique()->count();
        $lowHomeworkCount = $allAlerts->where('issue_type', 'low_homework_score')->count();
        $lowExamCount = $allAlerts->where('issue_type', 'low_exam_score')->count();
        $missingHomeworkCount = $allAlerts->where('issue_type', 'missing_homework')->count();
        $missedExamCount = $allAlerts->where('issue_type', 'missed_exam')->count();
        $confirmedAbsenceCount = $allAlerts->whereIn('issue_type', ['confirmed_absence', 'repeated_absence'])->count();

        return [
            'date' => $date->format('Y-m-d'),
            'created_today_count' => $createdTodayCount,
            'new_cases_count' => $newCasesCount,
            'contacted_cases_count' => $contactedCasesCount,
            'total_contact_attempts' => $totalAttempts,
            'successful_contacts' => $successfulAttempts,
            'unsuccessful_contacts' => $unsuccessfulAttempts,
            'tasks_by_employee' => $tasksByEmployee,
            'reassignments_count' => $reassignmentsCount,
            'students_with_alerts_count' => $studentsWithAlertsCount,
            'low_homework_count' => $lowHomeworkCount,
            'low_exam_count' => $lowExamCount,
            'missing_homework_count' => $missingHomeworkCount,
            'missed_exam_count' => $missedExamCount,
            'confirmed_absence_count' => $confirmedAbsenceCount,
        ];
    }
}
