<?php

namespace App\Filament\Instructor\Resources\Assignments\Pages;

use App\Filament\Instructor\Resources\Assignments\AssignmentResource;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Services\HomeworkService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class ManageAssignmentSubmissions extends Page
{
    protected static string $resource = AssignmentResource::class;

    protected string $view = 'filament.instructor.resources.assignments.pages.manage-assignment-submissions';

    public mixed $record = null;

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->is_super_admin || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin', 'supervisor']))) {
            return true;
        }
        return (bool) $user->instructor;
    }

    public Collection $groupStudents;

    public ?int $selectedSubmissionId = null;
    public ?int $selectedStudentId = null;
    public ?AssignmentSubmission $selectedSubmission = null;
    public ?Student $selectedStudent = null;

    public $score = null;
    public string $instructor_feedback = '';
    public int $activeImageIndex = 0;
    public bool $isReviewOpen = false;

    public function mount(int|string $record): void
    {
        if ($record instanceof Assignment) {
            $this->record = $record;
        } else {
            $this->record = Assignment::with(['group.course', 'instructor.user'])->findOrFail($record);
        }

        $user = auth()->user();
        $instructorId = $user?->instructor?->id;

        // Authorization check: instructor owns the assignment or is admin/supervisor
        $isAuthorized = false;
        if ($user?->is_super_admin || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin', 'supervisor']))) {
            $isAuthorized = true;
        } elseif ($instructorId && ((int) $this->record->instructor_id === (int) $instructorId || (int) $this->record->group?->instructor_id === (int) $instructorId)) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'غير مصرح لك باستعراض تسليمات هذا الواجب.');
        }

        $this->loadSubmissions();

        // Check if query string requested a specific submission
        $querySubmissionId = request()->query('submission');
        if ($querySubmissionId) {
            $submission = AssignmentSubmission::where('assignment_id', $this->record->id)->find($querySubmissionId);
            if ($submission) {
                $this->openReview($submission->student_id, $submission->id);
            }
        }
    }

    public function loadSubmissions(): void
    {
        $this->groupStudents = app(HomeworkService::class)->getGroupStudentsWithSubmissions($this->record);
    }

    public function getTitle(): string
    {
        return 'تسليمات واجب: ' . ($this->record->title ?? '');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.instructor.resources.assignments.index') => 'الواجبات',
            '#' => 'تسليمات: ' . ($this->record->title ?? ''),
        ];
    }

    public function openReview(int $studentId, ?int $submissionId = null): void
    {
        $this->selectedStudentId = $studentId;
        $this->selectedStudent = Student::with('user')->find($studentId);

        if ($submissionId) {
            $this->selectedSubmission = AssignmentSubmission::with('student.user')->find($submissionId);
        } else {
            $this->selectedSubmission = AssignmentSubmission::where('assignment_id', $this->record->id)
                ->where('student_id', $studentId)
                ->first();
        }

        if ($this->selectedSubmission) {
            $this->selectedSubmissionId = $this->selectedSubmission->id;
            $this->score = $this->selectedSubmission->score !== null ? (float) $this->selectedSubmission->score : null;
            $this->instructor_feedback = (string) ($this->selectedSubmission->instructor_feedback ?? '');
        } else {
            $this->selectedSubmissionId = null;
            $this->score = null;
            $this->instructor_feedback = '';
        }

        $this->activeImageIndex = 0;
        $this->isReviewOpen = true;
    }

    public function closeReview(): void
    {
        $this->isReviewOpen = false;
        $this->selectedSubmission = null;
        $this->selectedSubmissionId = null;
        $this->selectedStudent = null;
        $this->selectedStudentId = null;
        $this->score = null;
        $this->instructor_feedback = '';
        $this->activeImageIndex = 0;
    }

    public function setActiveImage(int $index): void
    {
        $this->activeImageIndex = $index;
    }

    public function nextImage(): void
    {
        if (!$this->selectedSubmission) return;
        $count = $this->selectedSubmission->image_count;
        if ($count > 0) {
            $this->activeImageIndex = ($this->activeImageIndex + 1) % $count;
        }
    }

    public function prevImage(): void
    {
        if (!$this->selectedSubmission) return;
        $count = $this->selectedSubmission->image_count;
        if ($count > 0) {
            $this->activeImageIndex = ($this->activeImageIndex - 1 + $count) % $count;
        }
    }

    public float $imageZoom = 1.0;

    public function zoomIn(): void
    {
        $this->imageZoom = min(round($this->imageZoom + 0.25, 2), 3.0);
    }

    public function zoomOut(): void
    {
        $this->imageZoom = max(round($this->imageZoom - 0.25, 2), 0.5);
    }

    public function resetZoom(): void
    {
        $this->imageZoom = 1.0;
    }

    public function getNextPendingStudentProperty(): ?array
    {
        if (!$this->selectedStudentId || !$this->groupStudents) {
            return null;
        }

        return $this->groupStudents->first(function ($row) {
            return ($row['has_submission'] ?? false) === true
                && ($row['review_status'] ?? '') === 'awaiting_grading'
                && ($row['student_id'] ?? null) != $this->selectedStudentId;
        });
    }

    public function nextStudent(): void
    {
        $next = $this->nextPendingStudent;
        if ($next) {
            $this->openReview($next['student_id'], $next['submission']?->id);
        } else {
            Notification::make()
                ->title('تم الانتهاء من تصحيح كافة الحلول المسلّمة! 🎉')
                ->body('لا توجد حلول أخرى بانتظار التصحيح في هذا الواجب حالياً.')
                ->success()
                ->send();
        }
    }

    public function saveDraft(): void
    {
        if (!$this->selectedSubmission) {
            Notification::make()->title('لا يوجد تسليم لهذا الطالب لحفظ التقييم له')->warning()->send();
            return;
        }

        $maxScore = (float) ($this->record->max_score > 0 ? $this->record->max_score : 10);

        $this->validate([
            'score' => "nullable|numeric|min:0|max:{$maxScore}",
            'instructor_feedback' => 'nullable|string|max:2000',
        ], [
            'score.numeric' => 'يجب أن تكون الدرجة قيمة رقمية.',
            'score.min' => 'الدرجة لا يمكن أن تقل عن 0.',
            'score.max' => "الدرجة لا يمكن أن تزيد عن {$maxScore}.",
        ]);

        try {
            $homeworkService = app(HomeworkService::class);
            $this->selectedSubmission = $homeworkService->saveDraftGrade(
                $this->selectedSubmission,
                $this->score !== null && $this->score !== '' ? (float) $this->score : null,
                $this->instructor_feedback,
                auth()->user()
            );

            $this->loadSubmissions();

            Notification::make()
                ->title('تم حفظ مسودة التقييم بنجاح')
                ->body('تم حفظ الدرجة والملاحظات كمسودة ولن تظهر للطالب حتى تقوم باعتمادها ونشرها.')
                ->info()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في حفظ المسودة')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Primary action: Instant Grading & Publication (تم التصحيح ✓)
     */
    public function markCorrected(): void
    {
        $this->publishGrade();
    }

    public function publishGrade(): void
    {
        if (!$this->selectedSubmission) {
            Notification::make()->title('لا يوجد تسليم لهذا الطالب لاعتماد التقييم له')->warning()->send();
            return;
        }

        $maxScore = (float) ($this->record->max_score > 0 ? $this->record->max_score : 10);

        $this->validate([
            'score' => "required|numeric|min:0|max:{$maxScore}",
            'instructor_feedback' => 'nullable|string|max:2000',
        ], [
            'score.required' => 'يرجى إدخال الدرجة أولاً قبل النقر على تم التصحيح.',
            'score.numeric' => "يجب أن تكون الدرجة قيمة رقمية بين 0 و {$maxScore}.",
            'score.min' => 'الدرجة لا يمكن أن تقل عن 0.',
            'score.max' => "الدرجة لا يمكن أن تزيد عن {$maxScore}.",
        ]);

        try {
            $homeworkService = app(HomeworkService::class);
            $this->selectedSubmission = $homeworkService->publishGrade(
                $this->selectedSubmission,
                (float) $this->score,
                $this->instructor_feedback,
                auth()->user()
            );

            // Academic Alert rule: strictly below 50% (< 50%)
            $scoreVal = (float) $this->score;
            if (($scoreVal * 2) < $maxScore) {
                \Illuminate\Support\Facades\Log::info("Academic Alert: Student {$this->selectedStudentId} scored {$scoreVal}/{$maxScore} (< 50%) on assignment {$this->record->id}");
            }

            $this->loadSubmissions();

            Notification::make()
                ->title('تم اعتماد ونشر التقييم بنجاح')
                ->body("تم التصحيح وحفظ النتيجة ونشرها فوراً! أصبحت الدرجة ({$this->score} / {$maxScore}) وملاحظاتك معتمدة ومنشورة للطالب وولي الأمر رسمياً.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في اعتماد النتيجة')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
