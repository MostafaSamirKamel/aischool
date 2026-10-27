<?php

namespace App\Filament\Student\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use App\Services\HomeworkService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

class MyAssignments extends Page
{
    use WithFileUploads;

    protected string $view = 'filament.student.pages.my-assignments';
    protected static ?string $title = 'واجباتي والتسليمات';
    protected static ?string $navigationLabel = 'واجباتي';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?int $navigationSort = 7;

    // Active assignment modal state
    public ?int $activeAssignmentId = null;

    // File upload state (max 5 images)
    public array $newImages = [];
    public ?string $submissionNotes = '';
    public bool $isSubmitting = false;

    // Lightbox image viewer
    public ?int $lightboxIndex = null;

    public function getTitle(): string
    {
        return __('واجباتي والتسليمات');
    }

    public function getHeading(): string
    {
        return __('واجباتي والتسليمات');
    }

    /**
     * Open the homework details & submission modal
     */
    public function openAssignment(int $id): void
    {
        $student = auth()->user()->student;
        if (!$student) {
            return;
        }

        $enrolledGroupIds = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->with('enrollmentCourses')
            ->get()
            ->flatMap(function ($enrollment) {
                $ids = $enrollment->enrollmentCourses->pluck('group_id');
                if ($enrollment->group_id) {
                    $ids->push($enrollment->group_id);
                }
                return $ids;
            })
            ->unique()
            ->filter();

        $assignment = Assignment::where(function ($q) use ($enrolledGroupIds, $student) {
            if ($enrolledGroupIds->isNotEmpty()) {
                $q->whereIn('group_id', $enrolledGroupIds);
            }
            $q->orWhereHas('submissions', fn($sq) => $sq->where('student_id', $student->id));
        })->find($id);

        if (!$assignment) {
            Notification::make()
                ->danger()
                ->title('غير مصرح')
                ->body('لا يمكنك الوصول إلى هذا الواجب.')
                ->send();
            return;
        }

        $this->activeAssignmentId = $id;
        $this->newImages = [];
        $this->lightboxIndex = null;

        $submission = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', $student->id)
            ->first();

        $this->submissionNotes = $submission?->submission_notes ?? '';
    }

    /**
     * Close the homework modal
     */
    public function closeAssignment(): void
    {
        $this->activeAssignmentId = null;
        $this->newImages = [];
        $this->submissionNotes = '';
        $this->lightboxIndex = null;
        $this->resetErrorBag();
    }

    /**
     * Hook when new images are picked
     */
    public function updatedNewImages(): void
    {
        $this->resetErrorBag('newImages');

        if (count($this->newImages) > 5) {
            $this->newImages = array_slice($this->newImages, 0, 5);
            $this->addError('newImages', 'الحد الأقصى المسموح به هو 5 صور فقط لكل واجب.');
            Notification::make()
                ->warning()
                ->title('تنبيه: الحد الأقصى 5 صور')
                ->body('تم الاحتفاظ بأول 5 صور فقط.')
                ->send();
        }
    }

    /**
     * Remove an image from the selected temporary array before submission
     */
    public function removeSelectedImage(int $index): void
    {
        if (isset($this->newImages[$index])) {
            unset($this->newImages[$index]);
            $this->newImages = array_values($this->newImages);
        }
        $this->resetErrorBag('newImages');
    }

    /**
     * Open lightbox preview
     */
    public function openLightbox(int $index): void
    {
        $this->lightboxIndex = $index;
    }

    /**
     * Close lightbox preview
     */
    public function closeLightbox(): void
    {
        $this->lightboxIndex = null;
    }

    /**
     * Submit homework images to the backend
     */
    public function submitHomework(): void
    {
        $this->resetErrorBag();

        $student = auth()->user()->student;
        if (!$student) {
            Notification::make()
                ->danger()
                ->title('خطأ في الحساب')
                ->body('حسابك غير مرتبط بملف طالب نشط.')
                ->send();
            return;
        }

        $enrolledGroupIds = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->with('enrollmentCourses')
            ->get()
            ->flatMap(function ($enrollment) {
                $ids = $enrollment->enrollmentCourses->pluck('group_id');
                if ($enrollment->group_id) {
                    $ids->push($enrollment->group_id);
                }
                return $ids;
            })
            ->unique()
            ->filter();

        $assignment = Assignment::whereIn('group_id', $enrolledGroupIds)->find($this->activeAssignmentId);
        if (!$assignment) {
            Notification::make()
                ->danger()
                ->title('غير مصرح')
                ->body('لا يمكنك تسليم واجب لا تنتمي لمجموعته.')
                ->send();
            return;
        }

        // Validate images selection
        if (empty($this->newImages)) {
            $this->addError('newImages', 'يرجى اختيار أو التقاط صورة واحدة على الأقل لحل الواجب.');
            return;
        }

        $this->validate([
            'newImages' => 'required|array|min:1|max:5',
            'newImages.*' => 'image|mimes:jpeg,jpg,png,webp,heic,heif|max:5120',
            'submissionNotes' => 'nullable|string|max:1000',
        ], [
            'newImages.required' => 'يرجى إرفاق صور حل الواجب.',
            'newImages.min' => 'يجب إرفاق صورة واحدة على الأقل.',
            'newImages.max' => 'الحد الأقصى هو 5 صور لكل واجب.',
            'newImages.*.image' => 'يجب أن يكون الملف صورة صالحة.',
            'newImages.*.mimes' => 'الصيغ المدعومة هي: JPG, JPEG, PNG, WEBP.',
            'newImages.*.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.',
        ]);

        $this->isSubmitting = true;

        try {
            /** @var HomeworkService $service */
            $service = app(HomeworkService::class);
            $service->submitHomework(
                $assignment,
                $student,
                $this->newImages,
                $this->submissionNotes
            );

            Notification::make()
                ->success()
                ->title('تم تسليم الواجب بنجاح! 🚀')
                ->body('تم حفظ صور إجابتك بنجاح وفي انتظار تصحيح المهندس.')
                ->send();

            $this->newImages = [];
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($field, $msg);
                }
            }
            Notification::make()
                ->danger()
                ->title('خطأ في التسليم')
                ->body(collect($e->errors())->flatten()->first())
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('حدث خطأ غير متوقع')
                ->body($e->getMessage())
                ->send();
        } finally {
            $this->isSubmitting = false;
        }
    }

    protected function getViewData(): array
    {
        $student = auth()->user()->student;

        if (!$student) {
            return [
                'student' => null,
                'assignments' => collect([]),
                'activeAssignment' => null,
                'activeSubmission' => null,
            ];
        }

        // Get group IDs the student is enrolled in
        $groupIds = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->with('enrollmentCourses')
            ->get()
            ->flatMap(function ($enrollment) {
                $ids = $enrollment->enrollmentCourses->pluck('group_id');
                if ($enrollment->group_id) {
                    $ids->push($enrollment->group_id);
                }
                return $ids;
            })
            ->unique()
            ->filter();

        // Get assignments strictly for active groups OR previously submitted assignments from past groups
        $assignments = Assignment::query()
            ->where(function ($q) use ($groupIds, $student) {
                if ($groupIds->isNotEmpty()) {
                    $q->whereIn('group_id', $groupIds);
                }
                $q->orWhereHas('submissions', function ($sq) use ($student) {
                    $sq->where('student_id', $student->id);
                });
            })
            ->with([
                'group.course',
                'instructor.user',
                'submissions' => function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                }
            ])
            ->orderBy('due_date', 'desc')
            ->get()
            ->map(function ($assignment) use ($student) {
                /** @var AssignmentSubmission|null $submission */
                $submission = $assignment->submissions->first();

                $status = 'pending';
                $statusLabel = 'لم يتم التسليم';
                $scoreDisplay = null;

                if ($submission && $submission->submitted_at) {
                    if ($submission->isPublished()) {
                        $status = 'graded';
                        $statusLabel = 'تم التقييم';
                        $scoreDisplay = $submission->score;
                    } elseif ($submission->isDraft()) {
                        $status = 'submitted';
                        $statusLabel = 'قيد المراجعة';
                    } else {
                        $status = 'submitted';
                        $statusLabel = 'تم التسليم';
                    }
                } else {
                    if ($assignment->due_date && $assignment->due_date->isPast()) {
                        $status = 'overdue';
                        $statusLabel = 'متأخر';
                    } else {
                        $status = 'pending';
                        $statusLabel = 'لم يتم التسليم';
                    }
                }

                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title ?? 'واجب منزلي',
                    'course' => $assignment->group?->course?->name ?? $assignment->group?->name ?? 'مقرر دراسي',
                    'group_name' => $assignment->group?->name ?? '',
                    'instructor_name' => $assignment->instructor_name,
                    'due' => $assignment->due_date ? $assignment->due_date->format('Y-m-d') : '-',
                    'total' => $assignment->max_score ?? 10,
                    'score' => $scoreDisplay,
                    'is_graded' => $submission ? $submission->isPublished() : false,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'has_submission' => $submission !== null && $submission->submitted_at !== null,
                    'images_count' => $submission ? $submission->image_count : 0,
                ];
            });

        // Load active assignment and submission for modal
        $activeAssignment = null;
        $activeSubmission = null;

        if ($this->activeAssignmentId) {
            $activeAssignment = Assignment::withoutGlobalScopes()
                ->with(['group.course', 'instructor.user'])
                ->find($this->activeAssignmentId);

            if ($activeAssignment) {
                $activeSubmission = AssignmentSubmission::withoutGlobalScopes()
                    ->where('assignment_id', $this->activeAssignmentId)
                    ->where('student_id', $student->id)
                    ->first();
            }
        }

        return [
            'student' => $student,
            'assignments' => $assignments,
            'activeAssignment' => $activeAssignment,
            'activeSubmission' => $activeSubmission,
        ];
    }
}
