<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HomeworkService
{
    /**
     * Submit homework images and optional notes for a student.
     *
     * @param Student $student
     * @param Assignment $assignment
     * @param array $uploadedFiles Array of UploadedFile or Livewire TemporaryUploadedFile
     * @param string|null $notes
     * @return AssignmentSubmission
     * @throws ValidationException
     */
    public function submitHomework($first, $second, array $uploadedFiles, ?string $notes = null): AssignmentSubmission
    {
        if ($first instanceof Assignment && $second instanceof Student) {
            $assignment = $first;
            $student = $second;
        } elseif ($first instanceof Student && $second instanceof Assignment) {
            $student = $first;
            $assignment = $second;
        } else {
            throw new \InvalidArgumentException('submitHomework requires a Student and an Assignment.');
        }

        // 1. Verify Tenant Isolation
        if ($student->tenant_id && $assignment->tenant_id && (int) $student->tenant_id !== (int) $assignment->tenant_id) {
            throw ValidationException::withMessages([
                'assignment' => 'لا يمكنك تسليم واجب لا ينتمي إلى فرعك الأكاديمي.',
            ]);
        }

        // 2. Validate images count (max 5 images)
        if (empty($uploadedFiles)) {
            throw ValidationException::withMessages([
                'images' => 'يرجى إرفاق صورة واحدة على الأقل لحل الواجب.',
            ]);
        }

        if (count($uploadedFiles) > 5) {
            throw ValidationException::withMessages([
                'images' => 'الحد الأقصى للصور هو 5 صور لكل واجب.',
            ]);
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
        $maxSizeBytes = 5 * 1024 * 1024; // 5 MB per image

        $storedPaths = [];

        DB::beginTransaction();
        try {
            foreach ($uploadedFiles as $index => $file) {
                if (!$file instanceof UploadedFile && !is_a($file, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::class)) {
                    continue;
                }

                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, $allowedExtensions)) {
                    throw ValidationException::withMessages([
                        'images' => "الملف رقم " . ($index + 1) . " غير مدعوم. الصيغ المدعومة هي: JPG, JPEG, PNG, WEBP.",
                    ]);
                }

                if ($file->getSize() > $maxSizeBytes) {
                    throw ValidationException::withMessages([
                        'images' => "حجم الصورة رقم " . ($index + 1) . " يتجاوز الحد الأقصى المسموح به (5 ميجابايت).",
                    ]);
                }

                // Generate safe, randomized unique filename
                $safeName = sprintf(
                    'asg_%d_stu_%d_%s_%d.%s',
                    $assignment->id,
                    $student->id,
                    Str::random(12),
                    $index + 1,
                    $ext
                );

                // Store in private storage directory
                $subDir = 'assignments/submissions/' . date('Y/m');
                $storedPath = $file->storeAs($subDir, $safeName, 'local');
                $storedPaths[] = $storedPath;
            }

            if (empty($storedPaths)) {
                throw ValidationException::withMessages([
                    'images' => 'لم يتم حفظ أي صورة صالحة. يرجى إعادة المحاولة.',
                ]);
            }

            // Find existing submission or create new
            $submission = AssignmentSubmission::withoutGlobalScopes()
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->first();

            $data = [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'tenant_id' => $assignment->tenant_id ?? $student->tenant_id,
                'submission_notes' => $notes ? trim(strip_tags($notes)) : null,
                'images' => $storedPaths,
                'file_path' => $storedPaths[0],
                'submitted_at' => now(),
                'status' => 'submitted',
                // If student resubmits, reset draft/published scores so it can be re-evaluated
                'published_at' => null,
                'score' => null,
                'instructor_feedback' => null,
                'graded_by' => null,
                'graded' => false,
            ];

            if ($submission) {
                // If there were old images, optionally retain or overwrite
                $submission->update($data);
            } else {
                $submission = AssignmentSubmission::create($data);
            }

            DB::commit();
            return $submission;
        } catch (\Exception $e) {
            DB::rollBack();
            // Clean up any files saved during this transaction
            foreach ($storedPaths as $p) {
                Storage::disk('local')->delete($p);
            }
            if ($e instanceof ValidationException) {
                throw $e;
            }
            throw ValidationException::withMessages([
                'images' => 'حدث خطأ أثناء حفظ صور الواجب: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Save an evaluation as a private draft without publishing it to the student.
     */
    public function saveDraftGrade(AssignmentSubmission $submission, mixed $arg2, mixed $arg3 = null, mixed $arg4 = null): AssignmentSubmission
    {
        if ($arg2 instanceof User) {
            $grader = $arg2;
            $score = $arg3 !== null ? (float) $arg3 : null;
            $feedback = $arg4;
        } else {
            $score = $arg2 !== null ? (float) $arg2 : null;
            $feedback = $arg3;
            $grader = $arg4 instanceof User ? $arg4 : auth()->user();
        }

        if ($grader && $grader->hasRole('secretary')) {
            throw new AuthorizationException('غير مصرح للسكرتارية بتصحيح أو رصد درجات الواجبات.');
        }

        $maxScore = (float) ($submission->assignment?->max_score ?? 10);
        if ($score !== null) {
            $this->validateGradeRange($score, $maxScore);
        }

        $submission->update([
            'score' => $score !== null ? round($score, 1) : null,
            'instructor_feedback' => $feedback ? trim($feedback) : null,
            'graded_by' => $grader?->id,
            'graded_at' => now(),
            'published_at' => null, // Stays null so student CANNOT see it
            'status' => 'submitted',
            'graded' => false,
        ]);

        return $submission;
    }

    /**
     * Officially publish the final grade and feedback to the student.
     */
    public function publishGrade(AssignmentSubmission $submission, mixed $arg2, mixed $arg3 = null, mixed $arg4 = null): AssignmentSubmission
    {
        if ($arg2 instanceof User) {
            $grader = $arg2;
            $score = (float) $arg3;
            $feedback = $arg4;
        } else {
            $score = (float) $arg2;
            $feedback = $arg3;
            $grader = $arg4 instanceof User ? $arg4 : auth()->user();
        }

        if ($grader && $grader->hasRole('secretary')) {
            throw new AuthorizationException('غير مصرح للسكرتارية باعتماد أو نشر درجات الواجبات.');
        }

        $maxScore = (float) ($submission->assignment?->max_score ?? 10);
        $this->validateGradeRange($score, $maxScore);

        $submission->update([
            'score' => round($score, 1),
            'instructor_feedback' => $feedback ? trim($feedback) : null,
            'graded_by' => $grader?->id,
            'graded_at' => now(),
            'published_at' => now(), // Officially published!
            'status' => 'graded',
            'graded' => true,
        ]);

        return $submission;
    }

    /**
     * Retrieve all eligible students in the assigned group with their submission state.
     *
     * @param Assignment $assignment
     * @return Collection
     */
    public function getGroupStudentsWithSubmissions(Assignment $assignment): Collection
    {
        $groupId = $assignment->group_id;
        $tenantId = $assignment->tenant_id;
        $maxScore = (float) $assignment->max_score > 0 ? (float) $assignment->max_score : 10;
        $now = now();
        $dueDate = $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->endOfDay() : null;
        $isPastDeadline = $dueDate ? $now->gt($dueDate) : false;

        // Retrieve all active enrolled students in this group
        $studentsQuery = Student::withoutGlobalScopes()
            ->where(function ($query) use ($groupId) {
                $query->whereHas('enrollments', function ($q) use ($groupId) {
                    $q->where('enrollments.status', 'active')
                      ->where(function ($sub) use ($groupId) {
                          $sub->where('enrollments.group_id', $groupId)
                              ->orWhereHas('enrollmentCourses', fn($ec) => $ec->where('enrollment_courses.group_id', $groupId));
                      });
                });
            });

        if ($tenantId) {
            $studentsQuery->where('students.tenant_id', $tenantId);
        }

        $students = $studentsQuery->with(['user'])->get();

        // Transfer awareness: Filter students who transferred in after due date unless they already submitted
        $filteredStudents = $students->filter(function (Student $student) use ($assignment, $dueDate) {
            if (!$dueDate) {
                return true;
            }

            $transfer = \App\Models\StudentGroupTransfer::where('student_id', $student->id)
                ->where('to_group_id', $assignment->group_id)
                ->latest('transferred_at')
                ->first();

            if ($transfer && $transfer->transferred_at) {
                $transferredAt = \Carbon\Carbon::parse($transfer->transferred_at);
                if ($transferredAt->gt($dueDate)) {
                    $hasSubmission = AssignmentSubmission::withoutGlobalScopes()
                        ->where('assignment_id', $assignment->id)
                        ->where('student_id', $student->id)
                        ->exists();

                    return $hasSubmission;
                }
            }

            return true;
        });

        // Also include students who transferred OUT of this group but have submissions for this assignment
        $transferredOutStudentIds = AssignmentSubmission::withoutGlobalScopes()
            ->where('assignment_id', $assignment->id)
            ->whereNotIn('student_id', $filteredStudents->pluck('id'))
            ->pluck('student_id')
            ->unique();

        if ($transferredOutStudentIds->isNotEmpty()) {
            $transferredStudents = Student::withoutGlobalScopes()
                ->whereIn('id', $transferredOutStudentIds)
                ->with(['user'])
                ->get();

            $filteredStudents = $filteredStudents->concat($transferredStudents);
        }

        // Preload submissions for this assignment indexed by student_id
        $submissions = AssignmentSubmission::withoutGlobalScopes()
            ->where('assignment_id', $assignment->id)
            ->with('grader')
            ->get()
            ->keyBy('student_id');

        return $filteredStudents->map(function ($student) use ($assignment, $submissions, $dueDate, $isPastDeadline, $maxScore) {
            /** @var AssignmentSubmission|null $sub */
            $sub = $submissions->get($student->id);

            $imageCount = 0;
            $submittedAt = null;
            $isLate = false;
            $punctualityStatus = 'pending_submission';
            $punctualityLabel = 'بانتظار التسليم';
            $punctualityBadge = 'gray';

            $reviewStatus = 'none';
            $reviewStatusLabel = '—';
            $reviewStatusBadge = 'gray';
            $scoreDisplay = '—';

            if ($sub && $sub->submitted_at) {
                $imageCount = $sub->image_count;
                $submittedAt = \Carbon\Carbon::parse($sub->submitted_at);
                $isLate = $dueDate ? $submittedAt->gt($dueDate) : false;

                if ($isLate) {
                    $punctualityStatus = 'submitted_late';
                    $punctualityLabel = 'سلّم متأخرًا';
                    $punctualityBadge = 'warning';
                } else {
                    $punctualityStatus = 'submitted_on_time';
                    $punctualityLabel = 'تم التسليم في الموعد';
                    $punctualityBadge = 'success';
                }

                if ($sub->isPublished()) {
                    $reviewStatus = 'graded';
                    $reviewStatusLabel = 'تم التصحيح والاعتماد';
                    $reviewStatusBadge = 'success';
                    $scoreVal = (float) $sub->score;
                    $scoreDisplay = round($scoreVal, 1) . ' / ' . round($maxScore, 1);
                } elseif ($sub->isDraft()) {
                    $reviewStatus = 'draft';
                    $reviewStatusLabel = 'مسودة تقييم';
                    $reviewStatusBadge = 'warning';
                    $scoreVal = (float) $sub->score;
                    $scoreDisplay = round($scoreVal, 1) . ' / ' . round($maxScore, 1) . ' (مسودة)';
                } else {
                    $reviewStatus = 'awaiting_grading';
                    $reviewStatusLabel = 'بانتظار التصحيح';
                    $reviewStatusBadge = 'info';
                    $scoreDisplay = '—';
                }
            } else {
                // Not submitted
                if ($isPastDeadline) {
                    $punctualityStatus = 'overdue';
                    $punctualityLabel = 'متأخر ولم يسلّم';
                    $punctualityBadge = 'danger';
                } else {
                    $punctualityStatus = 'pending_submission';
                    $punctualityLabel = 'بانتظار التسليم';
                    $punctualityBadge = 'gray';
                }
            }

            return [
                'student' => $student,
                'student_id' => $student->id,
                'student_name' => $student->user?->name ?? 'طالب غير محدد',
                'student_code' => $student->student_code ?? "STD-{$student->id}",
                'images_count' => $imageCount,
                'submission' => $sub,
                'has_submission' => (bool) ($sub && $sub->submitted_at),
                'submitted_at' => $submittedAt,
                'submitted_at_formatted' => $submittedAt ? $submittedAt->format('Y-m-d H:i') : '—',
                'is_late' => $isLate,
                'punctuality_status' => $punctualityStatus,
                'punctuality_label' => $punctualityLabel,
                'punctuality_badge' => $punctualityBadge,
                'review_status' => $reviewStatus,
                'review_status_label' => $reviewStatusLabel,
                'review_status_badge' => $reviewStatusBadge,
                'score' => $sub?->score,
                'score_display' => $scoreDisplay,
                'is_published' => $sub ? $sub->isPublished() : false,
                'is_draft' => $sub ? $sub->isDraft() : false,
                'is_overdue' => !$sub && $isPastDeadline,
                // Backward-compatibility keys for existing views/tests:
                'status' => $sub ? ($sub->isPublished() ? 'graded' : ($sub->isDraft() ? 'draft' : 'submitted')) : ($isPastDeadline ? 'overdue' : 'not_submitted'),
                'status_label' => $reviewStatus !== 'none' ? $reviewStatusLabel : $punctualityLabel,
                'grade_display' => $scoreDisplay,
            ];
        });
    }

    /**
     * Validate that grade is strictly non-negative and does not exceed maximum.
     *
     * @param float $score
     * @param float|null $maxScore
     * @throws ValidationException
     */
    public function validateGradeRange(float $score, ?float $maxScore = null): void
    {
        $max = $maxScore !== null && $maxScore > 0 ? $maxScore : 10.0;
        if ($score < 0 || $score > $max) {
            $msg = $max == 10.0 ? 'يجب أن تكون الدرجة بين 0 و 10 فقط.' : "يجب أن تكون الدرجة بين 0 و {$max} فقط.";
            throw ValidationException::withMessages([
                'grade' => $msg,
            ]);
        }
    }
}
