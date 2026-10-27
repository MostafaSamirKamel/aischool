<?php

namespace App\Filament\Admin\Pages;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Student;
use App\Models\StudentFollowUpContact;
use App\Models\StudentFollowUpTask;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AcademicFollowUpService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcademicFollowUpCenter extends Page
{
    protected string $view = 'filament.admin.pages.academic-follow-up-center';

    protected static ?string $navigationLabel = 'مركز متابعة الطلاب';

    protected static ?string $title = 'مركز متابعة الطلاب اليومي الموحد';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static \UnitEnum|string|null $navigationGroup = 'متابعة الطلاب';

    protected static ?int $navigationSort = 1;

    // Active Navigation Section (5 Core Sections)
    public string $activeSection = 'daily'; // 'daily', 'students', 'cases', 'contacts', 'reports'

    // Filters State
    public ?int $selectedBranchId = null;
    public ?int $selectedCourseId = null;
    public ?int $selectedGroupId = null;
    public string $issueFilter = 'all'; // all, confirmed_absence, repeated_absence, missing_homework, low_homework_score, missed_exam, low_exam_score, overdue_installment
    public string $caseStatusFilter = 'all'; // all, new, contacted
    public ?int $selectedEmployeeId = null;
    public string $searchQuery = '';
    public string $reportDate = '';

    // Legacy Tab support for backward compatibility with tests
    public string $activeTab = 'exams'; // 'exams' or 'homework'
    public ?int $selectedExamId = null;
    public ?int $selectedAssignmentId = null;
    public string $statusFilter = 'all';

    // Modal Visibility States
    public bool $showFollowUpModal = false;
    public bool $showContactLogModal = false;
    public bool $showHistoryModal = false;
    public bool $showReassignModal = false;
    public bool $showStudentProfileModal = false;

    // Active Context for Modals
    public ?int $activeStudentId = null;
    public ?int $activeTaskId = null;
    public string $activeStudentName = '';
    public string $activeParentPhone = '';
    public string $activeStudentPhone = '';
    public string $activeItemTitle = '';
    public string $activeSourceType = 'general';
    public ?int $activeSourceId = null;
    public string $activeAcademicStatus = '';
    public ?int $activeGroupId = null;

    // Create Case Form Fields
    public string $taskPriority = 'normal';
    public ?int $taskAssignedTo = null;
    public string $taskReason = '';

    // Record Contact Attempt Form Fields
    public string $contactMethod = 'phone_call';
    public string $contactResult = 'contacted'; // 'contacted', 'no_answer', 'unreachable'
    public string $contactNotes = '';
    public ?string $nextFollowUpDate = null;

    // Reassign Form Fields
    public ?int $reassignToUserId = null;
    public string $reassignReason = '';

    // Timeline History & Profile Data
    public array $timelineContacts = [];
    public ?array $activeStudentProfile = null;

    /**
     * Authorize user for actions on this page
     */
    protected function authorizeAction(?string $permission = null): void
    {
        $user = Auth::user();
        abort_unless(
            $user && $user->status === 'active' && (
                $user->is_super_admin
                || $user->hasRole('admin')
                || $user->hasRole('branch_manager')
                || ($permission ? ($user->can($permission) || $user->can('view_daily_follow_up_dashboard')) : $user->can('view_daily_follow_up_dashboard'))
            ),
            403,
            'غير مصرح لك بتنفيذ هذا الإجراء'
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (!$user || $user->status !== 'active') return false;

        return $user->is_super_admin
            || $user->hasRole('admin')
            || $user->hasRole('branch_manager')
            || $user->can('view_daily_follow_up_dashboard')
            || $user->can('view_student_academic_monitoring')
            || $user->can('view_exam_followup')
            || $user->can('view_homework_followup');
    }

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless(
            $user && $user->status === 'active' && (
                $user->is_super_admin
                || $user->hasRole('admin')
                || $user->hasRole('branch_manager')
                || $user->can('view_daily_follow_up_dashboard')
                || $user->can('view_student_academic_monitoring')
                || $user->can('view_exam_followup')
                || $user->can('view_homework_followup')
            ),
            403
        );

        $this->selectedBranchId = $user->tenant_id ?? Tenant::first()?->id;
        $this->reportDate = now()->format('Y-m-d');

        // Auto-select first authorized group if present
        $service = app(AcademicFollowUpService::class);
        $authorizedGroupIds = $service->getAuthorizedGroupIds($user, $this->selectedBranchId);

        if (!empty($authorizedGroupIds)) {
            $firstGroup = Group::whereIn('id', $authorizedGroupIds)->first();
            if ($firstGroup) {
                $this->selectedGroupId = $firstGroup->id;
                $this->selectedCourseId = $firstGroup->course_id;
            }
        } elseif ($user->is_super_admin || $user->hasRole('admin') || $user->hasRole('branch_manager')) {
            $firstCourse = Course::where('tenant_id', $this->selectedBranchId)->where('status', 'active')->first();
            if ($firstCourse) {
                $this->selectedCourseId = $firstCourse->id;
                $firstGroup = Group::where('course_id', $firstCourse->id)->where('status', 'active')->first();
                if ($firstGroup) {
                    $this->selectedGroupId = $firstGroup->id;
                }
            }
        }

        // Set default assignee to current user
        $this->taskAssignedTo = $user->id;
    }

    public function setSection(string $section): void
    {
        $this->activeSection = $section;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Get available branches for filter
     */
    public function getBranchesProperty(): Collection
    {
        $user = Auth::user();
        if ($user && ($user->is_super_admin || $user->hasRole('super_admin'))) {
            return Tenant::where('is_active', true)->orderBy('name')->get();
        }

        if ($user && $user->tenant_id) {
            return Tenant::where('id', $user->tenant_id)->get();
        }

        return Tenant::where('is_active', true)->take(1)->get();
    }

    /**
     * Get available courses for filter
     */
    public function getCoursesProperty(): Collection
    {
        if (!$this->selectedBranchId) {
            return collect();
        }

        return Course::where('tenant_id', $this->selectedBranchId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available authorized groups for filter
     */
    public function getGroupsProperty(): Collection
    {
        $user = Auth::user();
        if (!$user || !$this->selectedBranchId) {
            return collect();
        }

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $authorizedGroupIds = $service->getAuthorizedGroupIds($user, $this->selectedBranchId);

        $query = Group::where('tenant_id', $this->selectedBranchId)
            ->where('status', 'active')
            ->whereIn('id', $authorizedGroupIds);

        if ($this->selectedCourseId) {
            $query->where('course_id', $this->selectedCourseId);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get available employees for task assignment
     */
    public function getEmployeesProperty(): Collection
    {
        if (!$this->selectedBranchId) {
            return collect();
        }

        return User::where(function ($q) {
            $q->where('tenant_id', $this->selectedBranchId)
              ->orWhere('is_super_admin', true);
        })
        ->whereHas('roles', function ($q) {
            $q->whereIn('name', ['secretary', 'supervisor', 'admin', 'branch_manager', 'super_admin']);
        })
        ->where('status', 'active')
        ->orderBy('name')
        ->get();
    }

    /**
     * Compute KPI cards metrics from actual data
     */
    public function getKpiMetricsProperty(): array
    {
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $alerts = $service->detectAllAcademicAlerts(
            $user,
            $this->selectedBranchId,
            $this->selectedGroupId,
            $this->selectedCourseId
        );

        $branchId = $this->selectedBranchId ?? $user->tenant_id;
        $authorizedGroupIds = $service->getAuthorizedGroupIds($user, $branchId);

        // Actual Tasks Count
        $tasksQuery = StudentFollowUpTask::where('tenant_id', $branchId)
            ->whereIn('group_id', $authorizedGroupIds);

        $newCasesCount = (clone $tasksQuery)->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending'])->count();
        $contactedCasesCount = (clone $tasksQuery)->whereIn('status', [StudentFollowUpTask::STATUS_CONTACTED, 'completed'])->count();

        // Contact Attempts Today
        $contactsTodayCount = StudentFollowUpContact::where('tenant_id', $branchId)
            ->whereDate('contacted_at', now()->toDateString())
            ->whereHas('task', fn($q) => $q->whereIn('group_id', $authorizedGroupIds))
            ->count();

        return [
            'students_needing_followup' => $alerts->pluck('student_id')->unique()->count(),
            'new_cases' => $newCasesCount,
            'contacted_cases' => $contactedCasesCount,
            'missing_homework' => $alerts->where('issue_type', 'missing_homework')->count(),
            'low_grades' => $alerts->whereIn('issue_type', ['low_homework_score', 'low_exam_score'])->count(),
            'student_absences' => $alerts->whereIn('issue_type', ['confirmed_absence', 'repeated_absence'])->count(),
            'missed_exams' => $alerts->where('issue_type', 'missed_exam')->count(),
            'contacts_today' => $contactsTodayCount,
        ];
    }

    /**
     * Get Daily Alerts Queue list
     */
    public function getAlertsListProperty(): Collection
    {
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        return $service->detectAllAcademicAlerts(
            $user,
            $this->selectedBranchId,
            $this->selectedGroupId,
            $this->selectedCourseId,
            $this->issueFilter,
            $this->searchQuery
        );
    }

    /**
     * Get Student Follow-Up Cases list (Section 3)
     */
    public function getFollowUpCasesProperty(): Collection
    {
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $authorizedGroupIds = $service->getAuthorizedGroupIds($user, $this->selectedBranchId);

        $query = StudentFollowUpTask::where('tenant_id', $this->selectedBranchId)
            ->whereIn('group_id', $authorizedGroupIds)
            ->with(['student.user', 'student.studentParents.parent', 'assignee', 'latestContact', 'contacts'])
            ->orderBy('id', 'desc');

        if ($this->caseStatusFilter === 'new') {
            $query->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending']);
        } elseif ($this->caseStatusFilter === 'contacted') {
            $query->whereIn('status', [StudentFollowUpTask::STATUS_CONTACTED, 'completed']);
        }

        if ($this->selectedEmployeeId) {
            $query->where('assigned_to', $this->selectedEmployeeId);
        }

        if ($this->selectedGroupId) {
            $query->where('group_id', $this->selectedGroupId);
        }

        if ($this->searchQuery) {
            $s = trim($this->searchQuery);
            $query->where(function ($sub) use ($s) {
                $sub->where('title', 'like', "%{$s}%")
                    ->orWhere('reason', 'like', "%{$s}%")
                    ->orWhereHas('student.user', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        return $query->get();
    }

    /**
     * Get Contact History List (Section 4)
     */
    public function getContactLogsProperty(): Collection
    {
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $authorizedGroupIds = $service->getAuthorizedGroupIds($user, $this->selectedBranchId);
        $canViewPhones = $service->canViewPhone($user);

        $query = StudentFollowUpContact::where('tenant_id', $this->selectedBranchId)
            ->whereHas('task', fn($q) => $q->whereIn('group_id', $authorizedGroupIds))
            ->with(['student.user', 'task', 'recordedByUser'])
            ->orderBy('contacted_at', 'desc');

        if ($this->selectedEmployeeId) {
            $query->where('recorded_by', $this->selectedEmployeeId);
        }

        if ($this->searchQuery) {
            $s = trim($this->searchQuery);
            $query->where(function ($sub) use ($s) {
                $sub->where('guardian_name', 'like', "%{$s}%")
                    ->orWhere('guardian_phone', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhereHas('student.user', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        $logs = $query->take(100)->get();
        if (!$canViewPhones) {
            $logs->each(function ($log) {
                $log->guardian_phone = '— (محجوب)';
            });
        }

        return $logs;
    }

    /**
     * Get Students List (Section 2)
     */
    public function getStudentsListProperty(): Collection
    {
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $authorizedGroupIds = $service->getAuthorizedGroupIds($user, $this->selectedBranchId);
        $canViewPhones = $service->canViewPhone($user);

        $query = Student::where('tenant_id', $this->selectedBranchId)
            ->whereHas('enrollments', function ($q) use ($authorizedGroupIds) {
                $q->where('status', 'active')
                  ->where(function ($sub) use ($authorizedGroupIds) {
                      $sub->whereIn('group_id', $authorizedGroupIds)
                          ->orWhereHas('enrollmentCourses', fn($ec) => $ec->whereIn('group_id', $authorizedGroupIds));
                  });
            })
            ->with(['user', 'studentParents.parent', 'enrollments.group.course'])
            ->orderBy('id', 'desc');

        if ($this->selectedGroupId) {
            $gId = $this->selectedGroupId;
            $query->whereHas('enrollments', function ($q) use ($gId) {
                $q->where('status', 'active')
                  ->where(function ($sub) use ($gId) {
                      $sub->where('group_id', $gId)
                          ->orWhereHas('enrollmentCourses', fn($ec) => $ec->where('group_id', $gId));
                  });
            });
        }

        if ($this->searchQuery) {
            $s = trim($this->searchQuery);
            $query->where(function ($sub) use ($s) {
                $sub->where('student_code', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhereHas('user', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        $students = $query->take(80)->get();
        if (!$canViewPhones) {
            $students->each(function ($st) {
                $st->phone = '— (محجوب)';
            });
        }

        return $students;
    }

    /**
     * Get Daily Report Data (Section 5)
     */
    public function getDailyReportProperty(): array
    {
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $date = $this->reportDate ? Carbon::parse($this->reportDate) : now();

        return $service->getDailyReportData(
            $user,
            $date,
            $this->selectedBranchId,
            $this->selectedGroupId,
            $this->selectedCourseId,
            $this->selectedEmployeeId
        );
    }

    // =========================================================================
    // MODAL ACTIONS
    // =========================================================================

    /**
     * Open Follow-Up Case creation modal
     */
    public function openFollowUpModal(
        int $studentId,
        string $sourceType = 'general',
        ?int $sourceId = null,
        string $title = '',
        string $reason = '',
        ?int $groupId = null
    ): void {
        $this->authorizeAction('create_student_followup_task');
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $student = Student::with(['user', 'studentParents.parent'])->find($studentId);
        if (!$student || !$service->isStudentAuthorized($user, $student, $this->selectedBranchId)) {
            $this->showFollowUpModal = false;
            Notification::make()
                ->title('غير مصرح')
                ->body('غير مصرح لك بإنشاء حالة متابعة لهذا الطالب لعدم ارتباطه بمجموعاتك')
                ->danger()
                ->send();
            return;
        }

        $targetGroupId = $groupId ?? $this->selectedGroupId;
        if ($targetGroupId && !$service->isGroupAuthorized($user, $targetGroupId, $this->selectedBranchId)) {
            $this->showFollowUpModal = false;
            Notification::make()
                ->title('غير مصرح')
                ->body('غير مصرح بالوصول للمجموعة الدراسية المحددة')
                ->danger()
                ->send();
            return;
        }

        // Check if active case already exists for exact same student & source
        $existing = StudentFollowUpTask::where('tenant_id', $this->selectedBranchId)
            ->where('student_id', $studentId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending', 'in_progress'])
            ->first();

        if ($existing) {
            Notification::make()
                ->title('تنبيه: توجد حالة متابعة مفتوحة مسبقاً لهذا الطالب لنفس الموضوع')
                ->warning()
                ->send();

            $this->openContactLogModal($existing->id);
            return;
        }

        $canViewPhones = $service->canViewPhone($user);
        $this->activeStudentId = $student->id;
        $this->activeStudentName = $student->user?->name ?? 'طالب';
        $this->activeParentPhone = $canViewPhones ? $service->resolveParentPhone($student) : '— (محجوب)';
        $this->activeStudentPhone = $canViewPhones ? $service->resolveStudentPhone($student) : '— (محجوب)';
        $this->activeSourceType = $sourceType;
        $this->activeSourceId = $sourceId;
        $this->activeGroupId = $targetGroupId;
        $this->activeItemTitle = $title ?: 'متابعة طالب';
        $this->taskReason = $reason;
        $this->taskPriority = 'normal';
        $this->taskAssignedTo = Auth::id();

        $this->showFollowUpModal = true;
    }

    /**
     * Save newly created follow-up case (Status: جديدة)
     */
    public function saveFollowUpTask(): void
    {
        $this->authorizeAction('create_student_followup_task');
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $this->validate([
            'activeStudentId' => 'required|exists:students,id',
            'taskAssignedTo' => 'required|exists:users,id',
            'activeItemTitle' => 'required|string|max:255',
            'taskReason' => 'required|string',
        ]);

        $student = Student::findOrFail($this->activeStudentId);
        if (!$service->isStudentAuthorized($user, $student, $this->selectedBranchId)) {
            abort(403, 'غير مصرح بإنشاء حالة متابعة لهذا الطالب');
        }

        $targetGroupId = $this->activeGroupId ?? $this->selectedGroupId;
        if ($targetGroupId && !$service->isGroupAuthorized($user, $targetGroupId, $this->selectedBranchId)) {
            abort(403, 'غير مصرح بإنشاء حالة متابعة لهذه المجموعة');
        }

        // Validate assignee eligibility
        $assignee = User::findOrFail($this->taskAssignedTo);
        if ($assignee->status !== 'active') {
            abort(422, 'الموظف المختار غير نشط');
        }
        if (!$assignee->is_super_admin && $assignee->tenant_id !== $this->selectedBranchId && $assignee->tenant_id !== $student->tenant_id) {
            abort(422, 'الموظف المختار لا ينتمي لنفس الفرع');
        }

        // Double submission prevention: check if identical task was created in last 10 seconds
        $recentDuplicate = StudentFollowUpTask::where('tenant_id', $this->selectedBranchId)
            ->where('student_id', $student->id)
            ->where('source_type', $this->activeSourceType)
            ->where('source_id', $this->activeSourceId)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($recentDuplicate) {
            $this->showFollowUpModal = false;
            Notification::make()->title('تم إنشاء الحالة مسبقاً')->warning()->send();
            return;
        }

        DB::transaction(function () use ($service, $student, $targetGroupId) {
            $service->createOrGetFollowUpTask(
                $student,
                $this->activeSourceType,
                $this->activeSourceId ?? 0,
                $this->activeItemTitle,
                $this->taskReason,
                $this->taskPriority,
                $this->taskAssignedTo,
                $this->activeAcademicStatus,
                $targetGroupId
            );
        });

        $this->showFollowUpModal = false;

        Notification::make()
            ->title('تم إنشاء حالة المتابعة بنجاح')
            ->body('الحالة الحالية: جديدة — في انتظار التواصل')
            ->success()
            ->send();
    }

    /**
     * Open Record Contact Attempt Modal
     */
    public function openContactLogModal(int $taskId): void
    {
        $this->authorizeAction('record_parent_contact');
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $task = StudentFollowUpTask::with(['student.user', 'student.studentParents.parent'])->find($taskId);
        if (!$task || !$service->isTaskAuthorized($user, $task)) {
            $this->showContactLogModal = false;
            Notification::make()
                ->title('غير مصرح')
                ->body('غير مصرح لك بتسجيل تواصل لهذه الحالة')
                ->danger()
                ->send();
            return;
        }

        $canViewPhones = $service->canViewPhone($user);
        $this->activeTaskId = $task->id;
        $this->activeStudentId = $task->student_id;
        $this->activeStudentName = $task->student?->user?->name ?? 'طالب';
        $this->activeParentPhone = $canViewPhones ? $service->resolveParentPhone($task->student) : '— (محجوب)';
        $this->activeStudentPhone = $canViewPhones ? $service->resolveStudentPhone($task->student) : '— (محجوب)';
        $this->activeItemTitle = $task->title;

        $this->contactMethod = 'phone_call';
        $this->contactResult = 'contacted';
        $this->contactNotes = '';
        $this->nextFollowUpDate = null;

        $this->showContactLogModal = true;
    }

    /**
     * Save Contact Attempt
     * Result == 'contacted' -> Task becomes 'تم التواصل'
     * Result == 'no_answer' / 'unreachable' -> Task remains 'جديدة'
     */
    public function saveContactLog(): void
    {
        $this->authorizeAction('record_parent_contact');
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $this->validate([
            'activeTaskId' => 'required|exists:student_follow_up_tasks,id',
            'contactMethod' => 'required|string',
            'contactResult' => 'required|string',
            'contactNotes' => 'nullable|string',
        ]);

        $task = StudentFollowUpTask::findOrFail($this->activeTaskId);
        if (!$service->isTaskAuthorized($user, $task)) {
            abort(403, 'غير مصرح بتسجيل تواصل على هذه الحالة');
        }

        // Duplicate submission prevention: check for exact contact attempt in last 5 seconds
        $recent = StudentFollowUpContact::where('task_id', $task->id)
            ->where('recorded_by', $user->id)
            ->where('contacted_at', '>=', now()->subSeconds(5))
            ->first();

        if ($recent) {
            $this->showContactLogModal = false;
            Notification::make()->title('تم تسجيل محاولة التواصل مسبقاً')->warning()->send();
            return;
        }

        DB::transaction(function () use ($service, $task, $user) {
            $service->recordContactAttempt(
                $task,
                $this->contactMethod,
                $this->contactResult,
                $this->contactNotes,
                $this->nextFollowUpDate,
                $user
            );
        });

        $this->showContactLogModal = false;

        $resultLabel = match ($this->contactResult) {
            'contacted' => 'تم التواصل وتحديث الحالة إلى: تم التواصل',
            'no_answer' => 'تم تسجيل: لم يرد — تظل الحالة: جديدة',
            'unreachable' => 'تم تسجيل: الرقم غير متاح — تظل الحالة: جديدة',
            default => 'تم تسجيل المحاولة بنجاح',
        };

        Notification::make()
            ->title('تم تسجيل محاولة التواصل')
            ->body($resultLabel)
            ->success()
            ->send();
    }

    /**
     * Open Reassign Modal
     */
    public function openReassignModal(int $taskId): void
    {
        $this->authorizeAction('reassign_follow_up_case');
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $task = StudentFollowUpTask::with(['assignee'])->find($taskId);
        if (!$task || !$service->isTaskAuthorized($user, $task)) {
            $this->showReassignModal = false;
            Notification::make()->title('غير مصرح بإعادة تكليف هذه الحالة')->danger()->send();
            return;
        }

        $this->activeTaskId = $task->id;
        $this->activeItemTitle = $task->title;
        $this->reassignToUserId = null;
        $this->reassignReason = '';

        $this->showReassignModal = true;
    }

    /**
     * Save Reassignment with audit history
     */
    public function saveReassignment(): void
    {
        $this->authorizeAction('reassign_follow_up_case');
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $this->validate([
            'activeTaskId' => 'required|exists:student_follow_up_tasks,id',
            'reassignToUserId' => 'required|exists:users,id',
            'reassignReason' => 'nullable|string',
        ]);

        $task = StudentFollowUpTask::findOrFail($this->activeTaskId);
        if (!$service->isTaskAuthorized($user, $task)) {
            abort(403, 'غير مصرح بإعادة تكليف هذه الحالة');
        }

        $newAssignee = User::findOrFail($this->reassignToUserId);
        if ($newAssignee->status !== 'active') {
            abort(422, 'الموظف المختار غير نشط');
        }
        if (!$newAssignee->is_super_admin && $newAssignee->tenant_id !== $task->tenant_id) {
            abort(422, 'الموظف المختار لا ينتمي لنفس الفرع');
        }

        if ($task->assigned_to === $newAssignee->id) {
            $this->showReassignModal = false;
            Notification::make()->title('الحالة مسندة بالفعل لهذا الموظف')->warning()->send();
            return;
        }

        DB::transaction(function () use ($service, $task, $newAssignee, $user) {
            $service->reassignTask(
                $task,
                $newAssignee,
                $user,
                $this->reassignReason
            );
        });

        $this->showReassignModal = false;

        Notification::make()
            ->title('تمت إعادة تكليف الحالة بنجاح')
            ->body('تم حفظ سجل التكليف السابق مع التاريخ والسبب')
            ->success()
            ->send();
    }

    /**
     * Open History Modal
     */
    public function openHistoryModal(int $taskId): void
    {
        $this->authorizeAction();
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $task = StudentFollowUpTask::with([
            'student.user',
            'contacts.recordedByUser',
            'reassignments.previousAssignee',
            'reassignments.newAssignee',
            'reassignments.reassignedByUser',
        ])->find($taskId);

        if (!$task || !$service->isTaskAuthorized($user, $task)) {
            $this->showHistoryModal = false;
            $this->timelineContacts = [];
            Notification::make()->title('غير مصرح بالاطلاع على سجل هذه الحالة')->danger()->send();
            return;
        }

        $canViewPhones = $service->canViewPhone($user);
        $this->activeTaskId = $task->id;
        $this->activeStudentName = $task->student?->user?->name ?? 'طالب';
        $this->activeItemTitle = $task->title;

        $this->timelineContacts = $task->contacts->map(function ($c) use ($canViewPhones) {
            return [
                'id' => $c->id,
                'guardian_name' => $c->guardian_name,
                'guardian_phone' => $canViewPhones ? $c->guardian_phone : '— (محجوب)',
                'contact_method' => $c->contact_method,
                'contact_result' => $c->contact_result,
                'notes' => $c->notes,
                'contacted_at' => $c->contacted_at ? $c->contacted_at->format('Y-m-d H:i') : null,
                'recorded_by' => $c->recordedByUser?->name,
            ];
        })->toArray();

        $this->showHistoryModal = true;
    }

    /**
     * Open Student Profile Modal
     */
    public function openStudentProfile(int $studentId): void
    {
        $this->authorizeAction();
        $user = Auth::user();
        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);

        $student = Student::find($studentId);
        if (!$student || !$service->isStudentAuthorized($user, $student, $this->selectedBranchId)) {
            $this->showStudentProfileModal = false;
            $this->activeStudentProfile = null;
            Notification::make()
                ->title('غير مصرح')
                ->body('غير مصرح لك بالاطلاع على ملف هذا الطالب لعدم ارتباطه بمجموعاتك')
                ->danger()
                ->send();
            return;
        }

        $profile = $service->getStudentProfile($student, $user);
        if (!$profile) {
            $this->showStudentProfileModal = false;
            $this->activeStudentProfile = null;
            Notification::make()->title('غير مصرح بالوصول لهذا الملف')->danger()->send();
            return;
        }

        $this->activeStudentProfile = $profile;
        $this->activeStudentId = $student->id;
        $this->activeStudentName = $profile['name'];

        $this->showStudentProfileModal = true;
    }

    // =========================================================================
    // LEGACY METHODS (Preserving full compatibility with existing tests)
    // =========================================================================

    public function getExamsProperty(): Collection
    {
        $user = Auth::user();
        if (!$user || !$this->selectedGroupId) {
            return collect();
        }

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $authorizedGroupIds = $service->getAuthorizedGroupIds($user, $this->selectedBranchId);
        if (!in_array($this->selectedGroupId, $authorizedGroupIds)) {
            return collect();
        }

        return Exam::where('group_id', $this->selectedGroupId)
            ->where('tenant_id', $this->selectedBranchId)
            ->orderBy('exam_date', 'desc')
            ->get();
    }

    public function getAssignmentsProperty(): Collection
    {
        $user = Auth::user();
        if (!$user || !$this->selectedGroupId) {
            return collect();
        }

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $authorizedGroupIds = $service->getAuthorizedGroupIds($user, $this->selectedBranchId);
        if (!in_array($this->selectedGroupId, $authorizedGroupIds)) {
            return collect();
        }

        return Assignment::where('group_id', $this->selectedGroupId)
            ->where('tenant_id', $this->selectedBranchId)
            ->orderBy('due_date', 'desc')
            ->get();
    }

    public function getRosterProperty(): Collection
    {
        $user = Auth::user();
        if (!$user) return collect();

        /** @var AcademicFollowUpService $service */
        $service = app(AcademicFollowUpService::class);
        $authorizedGroupIds = $service->getAuthorizedGroupIds($user, $this->selectedBranchId);

        if ($this->activeTab === 'exams') {
            if (!$this->selectedExamId) {
                $firstExam = $this->exams->first();
                if ($firstExam) {
                    $this->selectedExamId = $firstExam->id;
                }
            }

            if (!$this->selectedExamId) {
                return collect();
            }

            $exam = Exam::with('group')->find($this->selectedExamId);
            if (!$exam || !in_array($exam->group_id, $authorizedGroupIds)) {
                return collect();
            }

            $roster = $service->getExamEligibleRoster($exam);
        } else {
            if (!$this->selectedAssignmentId) {
                $firstAssignment = $this->assignments->first();
                if ($firstAssignment) {
                    $this->selectedAssignmentId = $firstAssignment->id;
                }
            }

            if (!$this->selectedAssignmentId) {
                return collect();
            }

            $assignment = Assignment::with('group')->find($this->selectedAssignmentId);
            if (!$assignment || !in_array($assignment->group_id, $authorizedGroupIds)) {
                return collect();
            }

            $roster = $service->getHomeworkEligibleRoster($assignment);
        }

        // Apply phone number visibility masking
        if (!$service->canViewPhone($user)) {
            $roster = $roster->map(function ($row) {
                $row['parent_phone'] = '— (محجوب)';
                $row['student_phone'] = '— (محجوب)';
                return $row;
            });
        }

        return $roster->values();
    }

    public function getMetricsProperty(): array
    {
        $roster = $this->roster;

        $eligible = $roster->count();
        $completed = $roster->whereIn('status', ['completed', 'graded'])->count();
        $overdue = $roster->where('status', 'overdue')->count();
        $awaitingGrading = $roster->whereIn('status', ['awaiting_grading', 'draft_grading'])->count();
        $requiresFollowup = $roster->where('requires_follow_up', true)->count();
        $hasFollowup = $roster->where('has_follow_up', true)->count();

        return [
            'eligible' => $eligible,
            'completed' => $completed,
            'overdue' => $overdue,
            'awaiting_grading' => $awaitingGrading,
            'requires_followup' => $requiresFollowup,
            'has_followup' => $hasFollowup,
        ];
    }
}
