<?php

namespace App\Filament\Instructor\Pages;

use App\Models\AcademicGradeCorrection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\ExamRetakeGrant;
use App\Models\Group;
use App\Models\HomeworkDeadlineExtension;
use App\Models\HomeworkResubmissionGrant;
use App\Models\Instructor;
use App\Models\SessionSchedule;
use App\Models\Student;
use App\Services\AcademicCenterService;
use App\Services\HomeworkService;
use Carbon\Carbon;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\WithPagination;

class HomeworkReviewCenter extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'المركز الأكاديمي الشامل';
    protected static ?string $title = 'المركز الأكاديمي للمجموعة (الواجبات، الامتحانات، التسليمات، النتائج)';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.instructor.pages.homework-review-center';

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament/instructor/navigation.teaching') ?: 'التدريس';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->is_super_admin || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin', 'supervisor']))) {
            return true;
        }

        return (bool) $user->instructor;
    }

    // Four Main Tabs: homework, exams, submissions, results
    public string $activeTab = 'homework';

    // Global Group/Course Selection
    public ?int $selectedCourseId = null;
    public ?int $selectedGroupId = null;
    public ?int $selectedSessionId = null;

    // Tab 1 Filters: Homework
    public string $searchAssignment = '';
    public string $dateFilter = 'all'; // all, today, overdue, upcoming
    public string $statusFilter = 'all'; // all, needs_review, overdue, drafts, graded
    public string $sortBy = 'priority'; // priority, newest, oldest, deadline

    // Tab 1 Drilldown: Selected Assignment & Roster
    public ?int $selectedAssignmentId = null;
    public string $studentSearch = '';
    public string $studentPunctualityFilter = 'all';
    public string $studentGradingFilter = 'all';

    // Tab 1 Modals
    public bool $isHomeworkModalOpen = false;
    public ?int $hw_id = null;
    public string $hw_title = '';
    public string $hw_description = '';
    public ?int $hw_session_id = null;
    public ?string $hw_due_date = null;
    public float $hw_max_score = 10.0;
    public string $hw_status = 'published';

    public bool $isExtensionModalOpen = false;
    public string $extensionScope = 'group'; // 'group' or 'student'
    public ?int $extensionStudentId = null;
    public string $extensionStudentName = '';
    public string $extensionNewDueDate = '';
    public string $extensionReason = '';

    public bool $isResubmissionModalOpen = false;
    public ?int $resubmissionStudentId = null;
    public string $resubmissionStudentName = '';
    public string $resubmissionExpiresAt = '';
    public string $resubmissionReason = '';

    public bool $isGradeCorrectionModalOpen = false;
    public ?int $correctionSubmissionId = null;
    public ?float $correctionOldScore = null;
    public ?float $correctionNewScore = null;
    public string $correctionReason = '';

    // Tab 2: Exams State & Modals
    public string $searchExam = '';
    public ?int $selectedExamId = null;
    public string $examStudentFilter = 'all'; // all, submitted, corrected, pending
    public string $examStudentSearch = '';

    public bool $isExamModalOpen = false;
    public ?int $exam_id = null;
    public string $exam_title = '';
    public string $exam_type = 'quiz';
    public ?int $exam_session_id = null;
    public ?string $exam_date = null;
    public int $exam_duration = 60;
    public float $exam_total_marks = 100.0;
    public float $exam_passing_marks = 50.0;
    public string $exam_instructions = '';
    public string $exam_status = 'scheduled';
    public array $exam_questions = [];

    public bool $isRetakeModalOpen = false;
    public ?int $retakeExamId = null;
    public ?int $retakeStudentId = null;
    public string $retakeStudentName = '';
    public string $retakeExpiresAt = '';
    public string $retakeReason = '';

    public bool $isStudentAttemptsModalOpen = false;
    public ?int $viewAttemptsStudentId = null;
    public ?Student $viewAttemptsStudent = null;
    public ?Exam $viewAttemptsExam = null;

    // Tab 3: All Group Submissions Filter
    public string $submissionsSearchStudent = '';
    public string $submissionsFilterSession = 'all';
    public string $submissionsFilterAssignment = 'all';
    public string $submissionsFilterStatus = 'all'; // all, awaiting_grading, graded, late

    // Tab 4: Results Filter & Modal
    public string $resultsSearchStudent = '';
    public bool $isStudentProfileModalOpen = false;
    public ?int $profileStudentId = null;
    public ?Student $profileStudent = null;

    // Review Modal & Image Lightbox (Used in Tab 1 & Tab 3)
    public bool $isReviewOpen = false;
    public ?int $selectedStudentId = null;
    public ?Student $selectedStudent = null;
    public ?int $selectedSubmissionId = null;
    public ?AssignmentSubmission $selectedSubmission = null;
    public $score = null;
    public string $instructor_feedback = '';
    public int $activeImageIndex = 0;
    public bool $lightboxOpen = false;
    public float $imageZoom = 1.0;

    protected $queryString = [
        'activeTab' => ['except' => 'homework', 'as' => 'tab'],
        'selectedAssignmentId' => ['except' => null, 'as' => 'assignment'],
        'selectedSessionId' => ['except' => null, 'as' => 'session'],
        'selectedGroupId' => ['except' => null, 'as' => 'group'],
        'selectedCourseId' => ['except' => null, 'as' => 'course'],
        'selectedExamId' => ['except' => null, 'as' => 'exam'],
    ];

    public function mount($group = null, $session = null, $assignment = null): void
    {
        $tab = request()->query('tab', $this->activeTab);
        if (in_array($tab, ['homework', 'exams', 'submissions', 'results'])) {
            $this->activeTab = $tab;
        }

        $groupId = $group ?? request()->query('group', $this->selectedGroupId);
        if ($groupId) {
            $this->selectedGroupId = (int) $groupId;
        }

        $sessionId = $session ?? request()->query('session', $this->selectedSessionId);
        if ($sessionId) {
            $this->selectedSessionId = (int) $sessionId;
        }

        $assignmentId = $assignment ?? request()->query('assignment', $this->selectedAssignmentId);
        if ($assignmentId) {
            $this->selectedAssignmentId = (int) $assignmentId;
            $asg = $this->getAuthorizedAssignmentsQuery()->find($assignmentId);
            if ($asg) {
                $this->selectAssignment($asg->id);
            }
        }

        $examId = request()->query('exam', $this->selectedExamId);
        if ($examId) {
            $this->selectedExamId = (int) $examId;
        }
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['homework', 'exams', 'submissions', 'results'])) {
            $this->activeTab = $tab;
        }
    }

    protected function getInstructorId(): ?int
    {
        $user = auth()->user();
        return $user?->instructor?->id;
    }

    public function getAuthorizedAssignmentsQuery(): Builder
    {
        $user = auth()->user();
        $instructorId = $this->getInstructorId();

        $query = Assignment::query()
            ->with(['group.course', 'instructor.user']);

        if ($user && $user->tenant_id) {
            $query->where('assignments.tenant_id', $user->tenant_id);
        }

        $isAdmin = $user?->is_super_admin || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin', 'supervisor']));

        if (!$isAdmin) {
            if (!$instructorId) {
                return $query->whereRaw('1 = 0');
            }
            $query->where(function (Builder $q) use ($instructorId) {
                $q->where('assignments.instructor_id', $instructorId)
                  ->orWhereHas('group', fn($g) => $g->where('instructor_id', $instructorId));
            });
        }

        return $query;
    }

    public function getActiveGroupProperty(): ?Group
    {
        if (!$this->selectedGroupId) {
            // If instructor has only 1 active group, auto-select it
            $groups = $this->groups;
            if ($groups->count() === 1) {
                $this->selectedGroupId = $groups->first()->id;
            } else {
                return null;
            }
        }

        return Group::withoutGlobalScopes()
            ->with(['course', 'instructor.user', 'attendanceSessions' => fn($q) => $q->orderBy('attendance_date')])
            ->find($this->selectedGroupId);
    }

    public function getCoursesProperty(): Collection
    {
        $instructorId = $this->getInstructorId();
        $isAdmin = auth()->user()?->is_super_admin || (method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['admin', 'super_admin', 'supervisor']));

        return Course::query()
            ->when(!$isAdmin && $instructorId, function ($q) use ($instructorId) {
                $q->whereHas('groups', fn($g) => $g->where('instructor_id', $instructorId));
            })
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getGroupsProperty(): Collection
    {
        $instructorId = $this->getInstructorId();
        $isAdmin = auth()->user()?->is_super_admin || (method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['admin', 'super_admin', 'supervisor']));

        return Group::query()
            ->when($this->selectedCourseId, fn($q) => $q->where('course_id', $this->selectedCourseId))
            ->when(!$isAdmin && $instructorId, fn($q) => $q->where('instructor_id', $instructorId))
            ->where('status', 'active')
            ->with(['course'])
            ->orderBy('name')
            ->get();
    }

    public function getHeaderStatsProperty(): array
    {
        if (!$this->activeGroup) {
            return [
                'sessions_count' => 0,
                'homework_count' => 0,
                'pending_grading_count' => 0,
                'exams_count' => 0,
                'exams_needing_review' => 0,
            ];
        }

        return app(AcademicCenterService::class)->getGroupAcademicHeaderStats($this->activeGroup);
    }

    public function getKpiMetricsProperty(): array
    {
        $assignments = $this->getAuthorizedAssignmentsQuery()->get();
        $total = $assignments->count();

        $needsReview = 0;
        $missing = 0;
        $overdue = 0;
        $drafts = 0;
        $graded = 0;

        foreach ($assignments as $asg) {
            $stats = $asg->computed_stats;
            $needsReview += $stats['awaiting_review'] ?? 0;
            $missing += $stats['missing'] ?? 0;
            $overdue += $stats['overdue'] ?? 0;
            $drafts += $stats['drafts'] ?? 0;
            $graded += $stats['graded'] ?? 0;
        }

        return [
            'total_assignments' => $total,
            'needs_review' => $needsReview,
            'missing_submissions' => $missing,
            'overdue_students' => $overdue,
            'drafts_awaiting_approval' => $drafts,
            'graded_assignments' => $graded,
        ];
    }

    // =========================================================================
    // TAB 1: HOMEWORK (الواجبات)
    // =========================================================================

    public function getGroupSessionsProperty(): Collection
    {
        if (!$this->activeGroup) {
            return collect();
        }

        $group = $this->activeGroup;
        $attendanceSessions = AttendanceSession::where('group_id', $group->id)
            ->with(['assignments' => fn($q) => $q->withCount('submissions')])
            ->orderBy('attendance_date')
            ->get();

        if ($attendanceSessions->isNotEmpty()) {
            return $attendanceSessions->values()->map(function ($sess, $idx) {
                $assignments = $sess->assignments;
                $asgCount = $assignments->count();
                $pendingCount = 0;
                foreach ($assignments as $asg) {
                    $pendingCount += AssignmentSubmission::where('assignment_id', $asg->id)
                        ->whereNotNull('submitted_at')
                        ->where(fn($q) => $q->whereNull('score')->orWhere('graded', false))
                        ->count();
                }

                return [
                    'id' => $sess->id,
                    'type' => 'attendance_session',
                    'session_number' => $idx + 1,
                    'title' => $sess->session_notes ?: "السيشن رقم " . ($idx + 1),
                    'date' => $sess->attendance_date ? Carbon::parse($sess->attendance_date) : null,
                    'assignments_count' => $asgCount,
                    'pending_count' => $pendingCount,
                    'assignments' => $assignments,
                    'status' => $sess->status,
                ];
            });
        }

        // Fallback to session schedules
        $schedules = SessionSchedule::where('group_id', $group->id)
            ->with(['assignments' => fn($q) => $q->withCount('submissions')])
            ->orderBy('date')
            ->get();

        return $schedules->values()->map(function ($sched, $idx) {
            $assignments = $sched->assignments;
            $asgCount = $assignments->count();
            $pendingCount = 0;
            foreach ($assignments as $asg) {
                $pendingCount += AssignmentSubmission::where('assignment_id', $asg->id)
                    ->whereNotNull('submitted_at')
                    ->where(fn($q) => $q->whereNull('score')->orWhere('graded', false))
                    ->count();
            }

            return [
                'id' => $sched->id,
                'type' => 'session_schedule',
                'session_number' => $idx + 1,
                'title' => $sched->title ?: "السيشن رقم " . ($idx + 1),
                'date' => $sched->date ? Carbon::parse($sched->date) : null,
                'assignments_count' => $asgCount,
                'pending_count' => $pendingCount,
                'assignments' => $assignments,
                'status' => $sched->status,
            ];
        });
    }

    public function getGroupUnlinkedAssignmentsProperty(): Collection
    {
        if (!$this->activeGroup) {
            return collect();
        }

        return Assignment::where('group_id', $this->activeGroup->id)
            ->whereNull('attendance_session_id')
            ->whereNull('session_schedule_id')
            ->withCount('submissions')
            ->orderBy('due_date', 'desc')
            ->get();
    }

    public function getActiveSessionProperty(): ?array
    {
        if (!$this->selectedSessionId) {
            return null;
        }

        return $this->groupSessions->firstWhere('id', $this->selectedSessionId);
    }

    public function selectSession(int $sessionId): void
    {
        $this->selectedSessionId = $sessionId;
        $this->selectedAssignmentId = null;
    }

    public function backToSessions(): void
    {
        $this->selectedSessionId = null;
        $this->selectedAssignmentId = null;
    }

    public function getActiveAssignmentProperty(): ?Assignment
    {
        if (!$this->selectedAssignmentId) {
            return null;
        }

        return Assignment::withoutGlobalScopes()
            ->with(['group.course', 'attendanceSession', 'sessionSchedule'])
            ->find($this->selectedAssignmentId);
    }

    public function selectAssignment(int $assignmentId): void
    {
        $asg = $this->getAuthorizedAssignmentsQuery()->find($assignmentId);
        if (!$asg) {
            Notification::make()->title('غير مصرح لك باستعراض هذا الواجب')->danger()->send();
            $this->selectedAssignmentId = null;
            return;
        }

        $this->selectedAssignmentId = $assignmentId;
        $this->selectedGroupId = $asg->group_id;
        if ($asg->attendance_session_id) {
            $this->selectedSessionId = $asg->attendance_session_id;
        } elseif ($asg->session_schedule_id) {
            $this->selectedSessionId = $asg->session_schedule_id;
        }
    }

    public function backToAssignments(): void
    {
        $this->selectedAssignmentId = null;
    }

    public function getAssignmentsListProperty(): Collection
    {
        $query = Assignment::withoutGlobalScopes()
            ->with(['group.course', 'attendanceSession', 'sessionSchedule']);

        if ($this->selectedCourseId) {
            $query->whereHas('group', fn($q) => $q->where('course_id', $this->selectedCourseId));
        }

        if ($this->selectedGroupId) {
            $query->where('group_id', $this->selectedGroupId);
        } else {
            $instructor = auth()->user()?->instructor;
            if ($instructor) {
                $query->whereHas('group', fn($q) => $q->where('instructor_id', $instructor->id));
            }
        }

        return $query->orderBy('due_date', 'desc')->get();
    }

    public function getStudentRosterProperty(): Collection
    {
        $asg = $this->activeAssignment;
        if (!$asg) {
            return collect();
        }

        $service = app(HomeworkService::class);
        $roster = $service->getGroupStudentsWithSubmissions($asg);

        // Apply student search
        if (!empty($this->studentSearch)) {
            $term = trim($this->studentSearch);
            $roster = $roster->filter(function ($item) use ($term) {
                return str_contains($item['student_name'], $term)
                    || str_contains($item['student_code'], $term);
            });
        }

        return $roster->values();
    }

    // Homework Create / Edit Actions
    public function openCreateHomeworkModal(): void
    {
        $this->hw_id = null;
        $this->hw_title = '';
        $this->hw_description = '';
        $this->hw_session_id = $this->selectedSessionId;
        $this->hw_due_date = now()->addDays(7)->format('Y-m-d');
        $this->hw_max_score = 10.0;
        $this->hw_status = 'published';
        $this->isHomeworkModalOpen = true;
    }

    public function openEditHomeworkModal(int $assignmentId): void
    {
        $asg = Assignment::withoutGlobalScopes()->findOrFail($assignmentId);
        $this->hw_id = $asg->id;
        $this->hw_title = $asg->title;
        $this->hw_description = $asg->description ?? '';
        $this->hw_session_id = $asg->attendance_session_id ?: $asg->session_schedule_id;
        $this->hw_due_date = $asg->due_date ? Carbon::parse($asg->due_date)->format('Y-m-d') : '';
        $this->hw_max_score = (float) ($asg->max_score ?: 10.0);
        $this->hw_status = $asg->status ?: 'published';
        $this->isHomeworkModalOpen = true;
    }

    public function closeHomeworkModal(): void
    {
        $this->isHomeworkModalOpen = false;
        $this->hw_id = null;
    }

    public function saveHomework(): void
    {
        $this->validate([
            'hw_title' => 'required|string|max:255',
            'hw_max_score' => 'required|numeric|min:1|max:100',
            'hw_due_date' => 'required|date',
        ]);

        if (!$this->activeGroup) {
            Notification::make()->title('يرجى اختيار المجموعة أولاً')->danger()->send();
            return;
        }

        $group = $this->activeGroup;
        $data = [
            'tenant_id' => $group->tenant_id,
            'group_id' => $group->id,
            'instructor_id' => $group->instructor_id ?: auth()->user()->instructor?->id,
            'title' => trim($this->hw_title),
            'description' => trim($this->hw_description),
            'due_date' => $this->hw_due_date,
            'max_score' => $this->hw_max_score,
            'status' => $this->hw_status,
            'attendance_session_id' => $this->hw_session_id,
        ];

        if ($this->hw_id) {
            $asg = Assignment::withoutGlobalScopes()->findOrFail($this->hw_id);
            // Protect homework if submissions exist
            $hasSubmissions = $asg->submissions()->whereNotNull('submitted_at')->exists();
            if ($hasSubmissions && (float) $asg->max_score !== (float) $this->hw_max_score) {
                Notification::make()->title('لا يمكن تغيير الدرجة الكلية لواجب عليه تسليمات فعلية من الطلاب حفاظاً على نزاهة الدرجات.')
                    ->danger()->send();
                return;
            }
            $asg->update($data);
            Notification::make()->title('تم تحديث بيانات الواجب بنجاح')->success()->send();
        } else {
            $asg = Assignment::create($data);
            Notification::make()->title('تم إنشاء الواجب وربطه بالسيشن بنجاح! 🚀')->success()->send();
            $this->selectedAssignmentId = $asg->id;
        }

        $this->closeHomeworkModal();
    }

    // Deadline Extension
    public function openExtensionModal(string $scope, ?int $studentId = null, string $studentName = ''): void
    {
        $this->extensionScope = $scope;
        $this->extensionStudentId = $studentId;
        $this->extensionStudentName = $studentName;
        $this->extensionNewDueDate = now()->addDays(3)->format('Y-m-d\TH:i');
        $this->extensionReason = '';
        $this->isExtensionModalOpen = true;
    }

    public function saveExtension(): void
    {
        $this->validate([
            'extensionNewDueDate' => 'required|date',
            'extensionReason' => 'required|string|min:3',
        ]);

        $asg = $this->activeAssignment;
        if (!$asg) return;

        $service = app(AcademicCenterService::class);
        $newDue = Carbon::parse($this->extensionNewDueDate);

        if ($this->extensionScope === 'student' && $this->extensionStudentId) {
            $student = Student::withoutGlobalScopes()->findOrFail($this->extensionStudentId);
            $service->reopenStudentSubmission($asg, $student, $newDue, $this->extensionReason, auth()->user());
            Notification::make()->title("تم فتح استثناء تسليم للطالب: {$student->user?->name}")->success()->send();
        } else {
            $service->extendGroupDeadline($asg, $newDue, $this->extensionReason, auth()->user());
            Notification::make()->title('تم تمديد الموعد النهائي للمجموعة بأكملها بنجاح')->success()->send();
        }

        $this->isExtensionModalOpen = false;
    }

    // Resubmission Grant
    public function openResubmissionModal(int $studentId, string $studentName): void
    {
        $this->resubmissionStudentId = $studentId;
        $this->resubmissionStudentName = $studentName;
        $this->resubmissionExpiresAt = now()->addDays(3)->format('Y-m-d\TH:i');
        $this->resubmissionReason = '';
        $this->isResubmissionModalOpen = true;
    }

    public function saveResubmissionGrant(): void
    {
        $this->validate([
            'resubmissionExpiresAt' => 'required|date',
            'resubmissionReason' => 'required|string|min:3',
        ]);

        $asg = $this->activeAssignment;
        if (!$asg || !$this->resubmissionStudentId) return;

        $student = Student::withoutGlobalScopes()->findOrFail($this->resubmissionStudentId);
        $expires = Carbon::parse($this->resubmissionExpiresAt);

        app(AcademicCenterService::class)->grantHomeworkResubmission(
            $asg,
            $student,
            $expires,
            $this->resubmissionReason,
            auth()->user()
        );

        Notification::make()
            ->title("تم منح إذن إعادة تسليم للطالب: {$student->user?->name} بنجاح! 🔄")
            ->body('يحق للطالب تقديم تسليم جديد إضافي لمرة واحدة حتى موعد الانتهاء المحدد.')
            ->success()
            ->send();

        $this->isResubmissionModalOpen = false;
    }

    // Grade Correction Modal
    public function openGradeCorrectionModal(int $submissionId, float $currentScore): void
    {
        $this->correctionSubmissionId = $submissionId;
        $this->correctionOldScore = $currentScore;
        $this->correctionNewScore = $currentScore;
        $this->correctionReason = '';
        $this->isGradeCorrectionModalOpen = true;
    }

    public function saveGradeCorrection(): void
    {
        $this->validate([
            'correctionNewScore' => 'required|numeric|min:0',
            'correctionReason' => 'required|string|min:3',
        ]);

        $sub = AssignmentSubmission::withoutGlobalScopes()->findOrFail($this->correctionSubmissionId);
        app(AcademicCenterService::class)->correctPublishedGrade(
            $sub,
            (float) $this->correctionNewScore,
            $this->correctionReason,
            auth()->user()
        );

        Notification::make()
            ->title('تم تعديل الدرجة المنشورة وتوثيق السبب في سجل التدقيق بنجاح!')
            ->success()
            ->send();

        $this->isGradeCorrectionModalOpen = false;
        if ($this->selectedSubmission && $this->selectedSubmission->id === $sub->id) {
            $this->selectedSubmission->refresh();
            $this->score = $this->selectedSubmission->score;
        }
    }

    // =========================================================================
    // TAB 2: EXAMS (الامتحانات)
    // =========================================================================

    public function getGroupExamsProperty(): Collection
    {
        if (!$this->activeGroup) {
            return collect();
        }

        return Exam::where('group_id', $this->activeGroup->id)
            ->with(['attendanceSession', 'attempts'])
            ->withCount(['questions', 'attempts'])
            ->when(!empty($this->searchExam), fn($q) => $q->where('title', 'like', '%' . trim($this->searchExam) . '%'))
            ->orderBy('exam_date', 'desc')
            ->get();
    }

    public function getActiveExamProperty(): ?Exam
    {
        if (!$this->selectedExamId) {
            return null;
        }

        return Exam::withoutGlobalScopes()
            ->with(['group.course', 'attendanceSession', 'questions.options', 'attempts'])
            ->find($this->selectedExamId);
    }

    public function selectExam(int $examId): void
    {
        $this->selectedExamId = $examId;
    }

    public function backToExamsList(): void
    {
        $this->selectedExamId = null;
    }

    public function togglePublishExam(int $examId): void
    {
        $exam = Exam::withoutGlobalScopes()->findOrFail($examId);
        if ($exam->published_at) {
            $exam->update(['published_at' => null]);
            Notification::make()->title('تم حجب نتائج الامتحان عن الطلاب وأولياء الأمور')->warning()->send();
        } else {
            $exam->update(['published_at' => now(), 'status' => 'completed']);
            Notification::make()->title('تم اعتماد ونشر نتائج الامتحان للطلاب وأولياء الأمور رسمياً! 🌟')->success()->send();
        }
    }

    public function openCreateExamModal(): void
    {
        $this->exam_id = null;
        $this->exam_title = '';
        $this->exam_type = 'quiz';
        $this->exam_session_id = null;
        $this->exam_date = now()->addDays(3)->format('Y-m-d\TH:i');
        $this->exam_duration = 60;
        $this->exam_total_marks = 100.0;
        $this->exam_passing_marks = 50.0;
        $this->exam_instructions = '';
        $this->exam_status = 'scheduled';
        $this->exam_questions = [];
        $this->isExamModalOpen = true;
    }

    public function openEditExamModal(int $examId): void
    {
        $exam = Exam::withoutGlobalScopes()->with('questions.options')->findOrFail($examId);
        $this->exam_id = $exam->id;
        $this->exam_title = $exam->title;
        $this->exam_type = $exam->type;
        $this->exam_session_id = $exam->attendance_session_id;
        $this->exam_date = $exam->exam_date ? Carbon::parse($exam->exam_date)->format('Y-m-d\TH:i') : '';
        $this->exam_duration = (int) ($exam->duration ?: 60);
        $this->exam_total_marks = (float) ($exam->total_marks ?: 100.0);
        $this->exam_passing_marks = (float) ($exam->passing_marks ?: 50.0);
        $this->exam_instructions = $exam->instructions ?: '';
        $this->exam_status = $exam->status;
        $this->isExamModalOpen = true;
    }

    public function closeExamModal(): void
    {
        $this->isExamModalOpen = false;
        $this->exam_id = null;
    }

    public function saveExam(): void
    {
        $this->validate([
            'exam_title' => 'required|string|max:255',
            'exam_type' => 'required|string',
            'exam_date' => 'required|date',
            'exam_duration' => 'required|integer|min:5|max:300',
            'exam_total_marks' => 'required|numeric|min:1',
            'exam_passing_marks' => 'required|numeric|min:0',
        ]);

        if (!$this->activeGroup) {
            Notification::make()->title('يرجى اختيار المجموعة أولاً')->danger()->send();
            return;
        }

        $group = $this->activeGroup;
        $data = [
            'tenant_id' => $group->tenant_id,
            'course_id' => $group->course_id,
            'group_id' => $group->id,
            'instructor_id' => $group->instructor_id ?: auth()->user()->instructor?->id,
            'title' => trim($this->exam_title),
            'type' => $this->exam_type,
            'attendance_session_id' => $this->exam_session_id, // Optional session link!
            'exam_date' => $this->exam_date,
            'duration' => $this->exam_duration,
            'total_marks' => $this->exam_total_marks,
            'passing_marks' => $this->exam_passing_marks,
            'instructions' => $this->exam_instructions,
            'status' => $this->exam_status,
        ];

        if ($this->exam_id) {
            $exam = Exam::withoutGlobalScopes()->findOrFail($this->exam_id);
            if ($exam->hasStartedAttempts()) {
                Notification::make()->title('لا يمكن تعديل إعدادات الامتحان بعد بدء الطلاب في إجراء محاولات عليه.')
                    ->danger()->send();
                return;
            }
            $exam->update($data);
            Notification::make()->title('تم تحديث بيانات الامتحان بنجاح')->success()->send();
        } else {
            $exam = Exam::create($data);
            Notification::make()->title('تم إنشاء الامتحان بنجاح! 📝')->success()->send();
            $this->selectedExamId = $exam->id;
        }

        $this->closeExamModal();
    }

    public function getExamStudentRosterProperty(): Collection
    {
        $exam = $this->activeExam;
        if (!$exam || !$this->activeGroup) {
            return collect();
        }

        $groupId = $this->activeGroup->id;
        $students = Student::withoutGlobalScopes()
            ->whereHas('enrollments', function ($q) use ($groupId) {
                $q->where('status', 'active')
                  ->where(function ($sub) use ($groupId) {
                      $sub->where('group_id', $groupId)
                          ->orWhereHas('enrollmentCourses', fn($ec) => $ec->where('group_id', $groupId));
                  });
            })
            ->with(['user'])
            ->get();

        $attemptsByStudent = ExamAttempt::where('exam_id', $exam->id)
            ->get()
            ->groupBy('student_id');

        return $students->map(function ($student) use ($exam, $attemptsByStudent) {
            $studentAttempts = $attemptsByStudent->get($student->id, collect());
            $completedAttempts = $studentAttempts->whereNotNull('completed_at');
            $inProgress = $studentAttempts->whereNull('completed_at')->first();

            $officialAttempt = $completedAttempts->sortByDesc('score')->first();
            $officialScore = $officialAttempt ? (float) $officialAttempt->score : null;

            // Status label & badge
            if ($inProgress) {
                $status = 'in_progress';
                $statusLabel = 'جاري أداء الاختبار';
                $statusBadge = 'warning';
            } elseif ($completedAttempts->isNotEmpty()) {
                $status = 'completed';
                $statusLabel = 'تم إتمام الاختبار';
                $statusBadge = 'success';
            } else {
                $status = 'not_started';
                $statusLabel = 'لم يبدأ بعد';
                $statusBadge = 'gray';
            }

            return [
                'student' => $student,
                'student_id' => $student->id,
                'student_name' => $student->user?->name ?? 'طالب غير محدد',
                'student_code' => $student->student_code ?? "STD-{$student->id}",
                'attempts_count' => $studentAttempts->count(),
                'status' => $status,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
                'official_score' => $officialScore,
                'score_display' => $officialScore !== null ? round($officialScore, 1) . ' / ' . round((float) $exam->total_marks, 1) : '—',
                'attempts' => $studentAttempts,
            ];
        });
    }

    public function openRetakeModal(int $examId, int $studentId, string $studentName): void
    {
        $this->retakeExamId = $examId;
        $this->retakeStudentId = $studentId;
        $this->retakeStudentName = $studentName;
        $this->retakeExpiresAt = now()->addDays(3)->format('Y-m-d\TH:i');
        $this->retakeReason = '';
        $this->isRetakeModalOpen = true;
    }

    public function saveRetakeGrant(): void
    {
        $this->validate([
            'retakeExpiresAt' => 'required|date',
            'retakeReason' => 'required|string|min:3',
        ]);

        $exam = Exam::withoutGlobalScopes()->findOrFail($this->retakeExamId);
        $student = Student::withoutGlobalScopes()->findOrFail($this->retakeStudentId);
        $expires = Carbon::parse($this->retakeExpiresAt);

        app(AcademicCenterService::class)->grantExamRetake(
            $exam,
            $student,
            $expires,
            $this->retakeReason,
            auth()->user()
        );

        Notification::make()
            ->title("تم منح إذن إعادة الامتحان للطالب: {$student->user?->name} بنجاح! 🔄")
            ->body('يحق للطالب دخول محاولة اختبار جديدة لمرة واحدة حتى موعد الصلاحية المحدد.')
            ->success()
            ->send();

        $this->isRetakeModalOpen = false;
    }

    public function openStudentAttemptsModal(int $examId, int $studentId): void
    {
        $this->viewAttemptsExam = Exam::withoutGlobalScopes()->with('questions')->find($examId);
        $this->viewAttemptsStudent = Student::withoutGlobalScopes()->with('user')->find($studentId);
        $this->viewAttemptsStudentId = $studentId;
        $this->isStudentAttemptsModalOpen = true;
    }

    public function closeStudentAttemptsModal(): void
    {
        $this->isStudentAttemptsModalOpen = false;
        $this->viewAttemptsStudent = null;
        $this->viewAttemptsExam = null;
    }

    // =========================================================================
    // TAB 3: ALL GROUP SUBMISSIONS (التسليمات)
    // =========================================================================

    public function getAllGroupSubmissionsProperty(): Collection
    {
        if (!$this->activeGroup) {
            return collect();
        }

        $query = AssignmentSubmission::withoutGlobalScopes()
            ->whereHas('assignment', fn($q) => $q->where('group_id', $this->activeGroup->id))
            ->with(['student.user', 'assignment.attendanceSession', 'grader'])
            ->whereNotNull('submitted_at');

        if (!empty($this->submissionsSearchStudent)) {
            $term = trim($this->submissionsSearchStudent);
            $query->whereHas('student.user', fn($q) => $q->where('name', 'like', "%{$term}%"));
        }

        if ($this->submissionsFilterSession !== 'all') {
            $query->whereHas('assignment', fn($q) => $q->where('attendance_session_id', $this->submissionsFilterSession));
        }

        if ($this->submissionsFilterAssignment !== 'all') {
            $query->where('assignment_id', $this->submissionsFilterAssignment);
        }

        if ($this->submissionsFilterStatus === 'awaiting_grading') {
            $query->where(fn($q) => $q->whereNull('score')->orWhere('graded', false));
        } elseif ($this->submissionsFilterStatus === 'graded') {
            $query->where('graded', true)->whereNotNull('score');
        }

        return $query->orderByDesc('submitted_at')->get();
    }

    // =========================================================================
    // TAB 4: RESULTS BY STUDENT (النتائج)
    // =========================================================================

    public function getGroupStudentsResultsProperty(): Collection
    {
        if (!$this->activeGroup) {
            return collect();
        }

        $groupId = $this->activeGroup->id;
        $students = Student::withoutGlobalScopes()
            ->whereHas('enrollments', function ($q) use ($groupId) {
                $q->where('status', 'active')
                  ->where(function ($sub) use ($groupId) {
                      $sub->where('group_id', $groupId)
                          ->orWhereHas('enrollmentCourses', fn($ec) => $ec->where('group_id', $groupId));
                  });
            })
            ->with(['user'])
            ->get();

        if (!empty($this->resultsSearchStudent)) {
            $term = trim($this->resultsSearchStudent);
            $students = $students->filter(fn($s) => str_contains($s->user?->name ?? '', $term) || str_contains($s->student_code ?? '', $term));
        }

        $totalHwCount = Assignment::where('group_id', $groupId)->count();
        $totalExamCount = Exam::where('group_id', $groupId)->count();

        return $students->map(function ($student) use ($groupId, $totalHwCount, $totalExamCount) {
            // Homework stats
            $hwSubmissions = AssignmentSubmission::whereHas('assignment', fn($q) => $q->where('group_id', $groupId))
                ->where('student_id', $student->id)
                ->where('graded', true)
                ->whereNotNull('score')
                ->where('is_official', true)
                ->get();

            $hwCompletedCount = $hwSubmissions->count();
            $hwAvgScore = $hwCompletedCount > 0 ? round($hwSubmissions->avg('score'), 1) : 0;

            // Exam stats
            $examAttempts = ExamAttempt::whereHas('exam', fn($q) => $q->where('group_id', $groupId))
                ->where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->whereNotNull('score')
                ->where('is_official', true)
                ->get();

            $examCompletedCount = $examAttempts->count();
            $examAvgScore = $examCompletedCount > 0 ? round($examAttempts->avg('percentage'), 1) : 0;

            // Low score warning (strictly below 50%)
            $hasLowHw = $hwSubmissions->contains(function ($s) {
                $max = (float) ($s->assignment?->max_score ?: 10);
                return ($s->score * 2) < $max;
            });
            $hasLowExam = $examAttempts->contains(function ($a) {
                return (float) $a->percentage < 50.0;
            });

            return [
                'student' => $student,
                'student_id' => $student->id,
                'name' => $student->user?->name ?? 'طالب غير محدد',
                'code' => $student->student_code ?? "STD-{$student->id}",
                'hw_completed' => $hwCompletedCount,
                'hw_total' => $totalHwCount,
                'hw_avg' => $hwAvgScore,
                'exam_completed' => $examCompletedCount,
                'exam_total' => $totalExamCount,
                'exam_avg' => $examAvgScore,
                'has_alert' => $hasLowHw || $hasLowExam,
            ];
        });
    }

    public function openStudentAcademicProfile(int $studentId): void
    {
        $this->profileStudentId = $studentId;
        $this->profileStudent = Student::withoutGlobalScopes()->with(['user'])->find($studentId);
        $this->isStudentProfileModalOpen = true;
    }

    public function closeStudentAcademicProfile(): void
    {
        $this->isStudentProfileModalOpen = false;
        $this->profileStudent = null;
    }

    // =========================================================================
    // ANSWER VIEWER & GRADING ENGINE (SHARED BY TABS 1 & 3)
    // =========================================================================

    public function openReview(int $studentId, ?int $submissionId = null): void
    {
        $this->selectedStudentId = $studentId;
        $this->selectedStudent = Student::withoutGlobalScopes()->with('user')->find($studentId);

        if ($submissionId) {
            $this->selectedSubmissionId = $submissionId;
            $this->selectedSubmission = AssignmentSubmission::withoutGlobalScopes()
                ->with(['student.user', 'assignment'])
                ->find($submissionId);
        } else {
            $this->selectedSubmission = AssignmentSubmission::withoutGlobalScopes()
                ->where('assignment_id', $this->selectedAssignmentId)
                ->where('student_id', $studentId)
                ->orderByDesc('attempt_number')
                ->first();
            $this->selectedSubmissionId = $this->selectedSubmission?->id;
        }

        $this->score = $this->selectedSubmission?->score;
        $this->instructor_feedback = $this->selectedSubmission?->instructor_feedback ?? '';
        $this->activeImageIndex = 0;
        $this->imageZoom = 1.0;
        $this->isReviewOpen = true;
    }

    public function closeReview(): void
    {
        $this->isReviewOpen = false;
        $this->selectedStudent = null;
        $this->selectedStudentId = null;
        $this->selectedSubmission = null;
        $this->selectedSubmissionId = null;
        $this->score = null;
        $this->instructor_feedback = '';
        $this->lightboxOpen = false;
        $this->imageZoom = 1.0;
    }

    public function setActiveImage(int $index): void
    {
        $this->activeImageIndex = $index;
        $this->imageZoom = 1.0;
    }

    public function nextImage(): void
    {
        if (!$this->selectedSubmission) return;
        $count = $this->selectedSubmission->image_count;
        if ($count > 1) {
            $this->activeImageIndex = ($this->activeImageIndex + 1) % $count;
            $this->imageZoom = 1.0;
        }
    }

    public function prevImage(): void
    {
        if (!$this->selectedSubmission) return;
        $count = $this->selectedSubmission->image_count;
        if ($count > 1) {
            $this->activeImageIndex = ($this->activeImageIndex - 1 + $count) % $count;
            $this->imageZoom = 1.0;
        }
    }

    public function zoomIn(): void
    {
        $this->imageZoom = min(3.0, round($this->imageZoom + 0.25, 2));
    }

    public function zoomOut(): void
    {
        $this->imageZoom = max(0.5, round($this->imageZoom - 0.25, 2));
    }

    public function resetZoom(): void
    {
        $this->imageZoom = 1.0;
    }

    public function openLightbox(int $index = 0): void
    {
        $this->activeImageIndex = $index;
        $this->lightboxOpen = true;
    }

    public function closeLightbox(): void
    {
        $this->lightboxOpen = false;
    }

    public function markCorrected(): void
    {
        $this->publishGrade();
    }

    public function saveDraft(): void
    {
        if (!$this->selectedSubmission) {
            Notification::make()->title('لا يوجد تسليم محدد لحفظ مسودته')->danger()->send();
            return;
        }

        $assignment = $this->selectedSubmission->assignment;
        $maxScore = (float) ($assignment?->max_score ?? 10);

        if ($this->score !== null && $this->score !== '') {
            $numericScore = (float) $this->score;
            if ($numericScore < 0 || $numericScore > $maxScore) {
                Notification::make()->title("الدرجة يجب أن تكون بين 0 و {$maxScore}")->danger()->send();
                return;
            }
            $this->selectedSubmission->score = $numericScore;
        }

        $this->selectedSubmission->instructor_feedback = $this->instructor_feedback;
        $this->selectedSubmission->status = 'submitted';
        $this->selectedSubmission->graded = false;
        $this->selectedSubmission->published_at = null;
        $this->selectedSubmission->save();

        Notification::make()
            ->title('تم حفظ مسودة التقييم بنجاح 📝')
            ->body('تم حفظ الدرجة والملاحظات كمسودة غير معتمدة ولم تنشر للطالب بعد.')
            ->info()
            ->send();
    }

    public function publishGrade(): void
    {
        if (!$this->selectedSubmission) {
            Notification::make()->title('لا يوجد تسليم محدد لحفظ درجته')->danger()->send();
            return;
        }

        $assignment = $this->selectedSubmission->assignment;
        $maxScore = (float) ($assignment?->max_score ?? 10);

        if ($this->score === null || $this->score === '') {
            Notification::make()->title('يرجى إدخال الدرجة أولاً')->warning()->send();
            return;
        }

        $numericScore = (float) $this->score;
        if ($numericScore < 0 || $numericScore > $maxScore) {
            Notification::make()->title("الدرجة يجب أن تكون بين 0 و {$maxScore}")->danger()->send();
            return;
        }

        try {
            $service = app(AcademicCenterService::class);
            $this->selectedSubmission = $service->gradeHomework(
                $this->selectedSubmission,
                $numericScore,
                $this->instructor_feedback,
                auth()->user()
            );

            Notification::make()
                ->title('تم اعتماد ونشر النتيجة رسمياً! 🌟')
                ->body("أصبحت الدرجة ({$this->score} / {$maxScore}) منشورة فوراً للطالب وولي الأمر.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()->title('خطأ في اعتماد النتيجة')->body($e->getMessage())->danger()->send();
        }
    }

    public function getNextPendingStudentProperty(): ?array
    {
        if (!$this->selectedStudentId) {
            return null;
        }

        $roster = $this->studentRoster;
        return $roster->first(function ($row) {
            return $row['has_submission'] === true 
                && $row['review_status'] === 'awaiting_grading' 
                && $row['student_id'] != $this->selectedStudentId;
        });
    }

    public function nextStudent(): void
    {
        $next = $this->nextPendingStudent;
        if ($next) {
            $this->openReview($next['student_id'], $next['submission']?->id);
            Notification::make()
                ->title('الانتقال إلى الطالب التالي')
                ->body("جاري تصحيح حل الطالب: {$next['student_name']}")
                ->info()
                ->send();
        } else {
            $this->closeReview();
            Notification::make()
                ->title('تم تصحيح جميع التسليمات المتاحة لهذا الواجب! 🎉')
                ->success()
                ->send();
        }
    }
}
