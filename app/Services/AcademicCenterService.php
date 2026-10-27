<?php

namespace App\Services;

use App\Models\AcademicGradeCorrection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamRetakeGrant;
use App\Models\Group;
use App\Models\HomeworkDeadlineExtension;
use App\Models\HomeworkResubmissionGrant;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcademicCenterService
{
    /**
     * Submit homework images and notes for a student, handling replace vs new version with resubmission grant.
     */
    public function submitHomework(Assignment $assignment, Student $student, array $uploadedFiles, ?string $notes = null): AssignmentSubmission
    {
        // 1. Tenant Isolation
        if ($student->tenant_id && $assignment->tenant_id && (int) $student->tenant_id !== (int) $assignment->tenant_id) {
            throw ValidationException::withMessages([
                'assignment' => 'لا يمكنك تسليم واجب لا ينتمي إلى فرعك الأكاديمي.',
            ]);
        }

        // 2. Validate images
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

        $now = now();
        $effectiveDueDate = $assignment->getEffectiveDueDateForStudent($student);
        $isPastDeadline = $effectiveDueDate ? $now->gt($effectiveDueDate) : false;

        // 3. Check existing submissions
        $existingSubmissions = AssignmentSubmission::withoutGlobalScopes()
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->orderByDesc('attempt_number')
            ->get();

        $latestSubmission = $existingSubmissions->first();
        $hasGraded = $existingSubmissions->where('graded', true)->whereNotNull('score')->isNotEmpty();

        // Check for active resubmission grant
        $activeGrant = HomeworkResubmissionGrant::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->whereNull('consumed_at')
            ->where('expires_at', '>', $now)
            ->latest('id')
            ->first();

        // Enforce Deadline & Graded Submission rules
        if ($hasGraded) {
            // Once graded, cannot submit without active resubmission grant
            if (!$activeGrant) {
                throw ValidationException::withMessages([
                    'assignment' => 'تم تصحيح الواجب بالفعل. لا يمكنك إعادة التسليم إلا بعد منح إذن إعادة تسليم من الإدارة.',
                ]);
            }
        } elseif ($isPastDeadline && !$activeGrant) {
            // Overdue and no grant
            throw ValidationException::withMessages([
                'assignment' => 'انتهى الموعد النهائي لتسليم الواجب. يرجى مراجعة إدارة الفرع لتمديد الموعد أو فتح استثناء.',
            ]);
        }

        // Store files securely
        $storedPaths = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
        $maxSizeBytes = 5 * 1024 * 1024; // 5MB

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
                        'images' => "حجم الصورة رقم " . ($index + 1) . " يتجاوز 5 ميجابايت.",
                    ]);
                }

                $safeName = sprintf(
                    'asg_%d_stu_%d_%s_%d.%s',
                    $assignment->id,
                    $student->id,
                    Str::random(12),
                    $index + 1,
                    $ext
                );

                $subDir = 'assignments/submissions/' . date('Y/m');
                $storedPath = $file->storeAs($subDir, $safeName, 'local');
                $storedPaths[] = $storedPath;
            }

            if (empty($storedPaths)) {
                throw ValidationException::withMessages([
                    'images' => 'لم يتم حفظ أي صورة صالحة. يرجى إعادة المحاولة.',
                ]);
            }

            // Decide whether to replace ungraded submission OR create new version with grant
            if ($latestSubmission && !$latestSubmission->graded && !$activeGrant) {
                // Replacing ungraded submission within open window
                $latestSubmission->update([
                    'submission_notes' => $notes ? trim(strip_tags($notes)) : null,
                    'images' => $storedPaths,
                    'file_path' => $storedPaths[0],
                    'submitted_at' => $now,
                    'status' => 'submitted',
                ]);
                $submission = $latestSubmission;
            } else {
                // New attempt / version
                $nextAttemptNumber = ($latestSubmission ? (int) $latestSubmission->attempt_number : 0) + 1;

                $submission = AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'tenant_id' => $assignment->tenant_id ?? $student->tenant_id,
                    'attempt_number' => $nextAttemptNumber,
                    'submission_notes' => $notes ? trim(strip_tags($notes)) : null,
                    'file_path' => $storedPaths[0],
                    'images' => $storedPaths,
                    'submitted_at' => $now,
                    'status' => 'submitted',
                    'graded' => false,
                    'score' => null,
                    'instructor_feedback' => null,
                    'graded_by' => null,
                    'graded_at' => null,
                    'published_at' => null,
                    'is_official' => false, // Will become official if/when graded with highest score
                ]);

                // Consume the resubmission grant if one was used
                if ($activeGrant) {
                    $activeGrant->update([
                        'status' => 'consumed',
                        'consumed_at' => $now,
                        'consumed_submission_id' => $submission->id,
                    ]);
                }
            }

            DB::commit();
            return $submission;
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($storedPaths as $p) {
                Storage::disk('local')->delete($p);
            }
            if ($e instanceof ValidationException) {
                throw $e;
            }
            throw ValidationException::withMessages([
                'images' => 'حدث خطأ أثناء حفظ تسليم الواجب: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Instantly grade and publish a homework submission.
     * Marks submission corrected, recalculates official highest grade, and publishes immediately.
     */
    public function gradeHomework(AssignmentSubmission $submission, float $score, ?string $feedback = null, ?User $grader = null): AssignmentSubmission
    {
        $grader = $grader ?: auth()->user();

        if ($grader && $grader->hasRole('secretary')) {
            throw new AuthorizationException('غير مصرح للسكرتارية باعتماد أو تصحيح درجات الواجبات.');
        }

        $assignment = $submission->assignment;
        $maxScore = (float) ($assignment?->max_score ?? 10);

        if ($score < 0 || $score > $maxScore) {
            throw ValidationException::withMessages([
                'score' => "يجب أن تكون الدرجة بين 0 و {$maxScore} درجة.",
            ]);
        }

        DB::transaction(function () use ($submission, $score, $feedback, $grader, $assignment) {
            $now = now();

            // 1. Update this submission
            $submission->update([
                'score' => round($score, 1),
                'instructor_feedback' => $feedback ? trim($feedback) : null,
                'graded_by' => $grader?->id,
                'graded_at' => $now,
                'published_at' => $now, // Instant publication!
                'status' => 'graded',
                'graded' => true,
            ]);

            // 2. Recalculate highest eligible corrected grade for this student & assignment
            $this->recalculateOfficialHomeworkGrade($submission->assignment_id, $submission->student_id);
        });

        return $submission->fresh();
    }

    /**
     * Recalculates and sets the official highest grade among all graded submissions.
     */
    public function recalculateOfficialHomeworkGrade(int $assignmentId, int $studentId): ?AssignmentSubmission
    {
        $submissions = AssignmentSubmission::withoutGlobalScopes()
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->where('graded', true)
            ->whereNotNull('score')
            ->orderByDesc('score')
            ->orderByDesc('attempt_number')
            ->get();

        if ($submissions->isEmpty()) {
            return null;
        }

        $highest = $submissions->first();

        // Mark highest as official in DB, all others as unofficial
        AssignmentSubmission::withoutGlobalScopes()
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->where('id', '!=', $highest->id)
            ->update(['is_official' => false]);

        AssignmentSubmission::withoutGlobalScopes()
            ->where('id', $highest->id)
            ->update(['is_official' => true]);

        $highest->refresh();

        return $highest;
    }

    /**
     * Correct a published homework grade with mandatory reason and full audit log.
     */
    public function correctPublishedGrade(AssignmentSubmission $submission, float $newScore, string $reason, ?User $corrector = null): AcademicGradeCorrection
    {
        $corrector = $corrector ?: auth()->user();

        if (!$reason || trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'سبب تعديل الدرجة إلزامي لتوثيق العملية في سجل التدقيق.',
            ]);
        }

        $maxScore = (float) ($submission->assignment?->max_score ?? 10);
        if ($newScore < 0 || $newScore > $maxScore) {
            throw ValidationException::withMessages([
                'score' => "يجب أن تكون الدرجة المعدلة بين 0 و {$maxScore}.",
            ]);
        }

        return DB::transaction(function () use ($submission, $newScore, $reason, $corrector) {
            $oldScore = $submission->score;

            // Log correction in audit table
            $log = AcademicGradeCorrection::create([
                'tenant_id' => $submission->tenant_id,
                'target_type' => 'homework',
                'target_id' => $submission->id,
                'student_id' => $submission->student_id,
                'assignment_id' => $submission->assignment_id,
                'corrected_by' => $corrector->id,
                'previous_score' => $oldScore,
                'new_score' => round($newScore, 1),
                'reason' => trim($reason),
            ]);

            // Update submission score
            $submission->update([
                'score' => round($newScore, 1),
                'graded_at' => now(),
            ]);

            // Re-evaluate highest official grade
            $this->recalculateOfficialHomeworkGrade($submission->assignment_id, $submission->student_id);

            return $log;
        });
    }

    /**
     * Grant a one-time homework resubmission to a student.
     */
    public function grantHomeworkResubmission(Assignment $assignment, Student $student, Carbon $expiresAt, string $reason, ?User $granter = null): HomeworkResubmissionGrant
    {
        $granter = $granter ?: auth()->user();

        if (!$reason || trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'سبب منح إذن إعادة التسليم إلزامي.',
            ]);
        }

        if ($expiresAt->isPast()) {
            throw ValidationException::withMessages([
                'expires_at' => 'تاريخ صلاحية الإذن يجب أن يكون في المستقبل.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $student, $expiresAt, $reason, $granter) {
            // Cancel any existing active grants for this student and assignment
            HomeworkResubmissionGrant::where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->whereNull('consumed_at')
                ->update(['status' => 'revoked']);

            return HomeworkResubmissionGrant::create([
                'tenant_id' => $assignment->tenant_id ?? $student->tenant_id,
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'granted_by' => $granter->id,
                'expires_at' => $expiresAt,
                'reason' => trim($reason),
                'status' => 'active',
            ]);
        });
    }

    /**
     * Extend homework deadline group-wide.
     */
    public function extendGroupDeadline(Assignment $assignment, Carbon $newDueDate, string $reason, ?User $granter = null): HomeworkDeadlineExtension
    {
        $granter = $granter ?: auth()->user();

        if (!$reason || trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'سبب تمديد الموعد إلزامي.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $newDueDate, $reason, $granter) {
            $prevDue = $assignment->due_date;

            $extension = HomeworkDeadlineExtension::create([
                'tenant_id' => $assignment->tenant_id,
                'assignment_id' => $assignment->id,
                'scope' => 'group',
                'previous_due_date' => $prevDue,
                'new_due_date' => $newDueDate,
                'reason' => trim($reason),
                'granted_by' => $granter->id,
            ]);

            $assignment->update(['due_date' => $newDueDate]);

            return $extension;
        });
    }

    /**
     * Reopen homework submission for an individual student.
     */
    public function reopenStudentSubmission(Assignment $assignment, Student $student, Carbon $newDueDate, string $reason, ?User $granter = null): HomeworkDeadlineExtension
    {
        $granter = $granter ?: auth()->user();

        if (!$reason || trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'سبب فتح استثناء التسليم للطالب إلزامي.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $student, $newDueDate, $reason, $granter) {
            $prevDue = $assignment->getEffectiveDueDateForStudent($student);

            return HomeworkDeadlineExtension::create([
                'tenant_id' => $assignment->tenant_id ?? $student->tenant_id,
                'assignment_id' => $assignment->id,
                'scope' => 'student',
                'student_id' => $student->id,
                'previous_due_date' => $prevDue,
                'new_due_date' => $newDueDate,
                'reason' => trim($reason),
                'granted_by' => $granter->id,
            ]);
        });
    }

    // =========================================================================
    // EXAM MANAGEMENT, ATTEMPTS & RETAKES
    // =========================================================================

    /**
     * Check if student can start or resume an exam attempt.
     */
    public function canStudentStartExam(Exam $exam, Student $student): array
    {
        $now = now();

        // 1. In-progress attempt exists? (Recovery / Resume)
        $inProgress = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereNull('completed_at')
            ->latest('id')
            ->first();

        if ($inProgress) {
            // Check if timer expired
            $duration = (int) ($exam->duration ?: 60);
            $expiresAt = $inProgress->started_at ? $inProgress->started_at->copy()->addMinutes($duration) : null;
            if ($expiresAt && $now->gt($expiresAt)) {
                // Auto-finalize expired attempt
                $this->finalizeExamAttempt($inProgress);
            } else {
                return ['allowed' => true, 'action' => 'resume', 'attempt' => $inProgress];
            }
        }

        // 2. Check if student has completed attempts
        $completedAttempts = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->count();

        if ($completedAttempts > 0) {
            // Must have an active retake grant
            $grant = ExamRetakeGrant::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->whereNull('consumed_at')
                ->where('expires_at', '>', $now)
                ->latest('id')
                ->first();

            if (!$grant) {
                return [
                    'allowed' => false,
                    'reason' => 'أتممت جميع محاولات الامتحان المتاحة. لإعادة الامتحان يرجى طلب إذن إعادة اختبار من الإدارة.',
                ];
            }

            return ['allowed' => true, 'action' => 'retake', 'grant' => $grant];
        }

        // 3. First time attempt - check exam availability
        if (!in_array($exam->status, ['scheduled', 'in_progress'], true)) {
            return ['allowed' => false, 'reason' => 'الامتحان غير متاح حالياً.'];
        }

        if ($exam->exam_date && $exam->exam_date->isFuture()) {
            return ['allowed' => false, 'reason' => 'الامتحان لم يبدأ بعد، موعد البدء: ' . $exam->exam_date->format('Y-m-d H:i')];
        }

        if ($exam->hasEnded()) {
            return ['allowed' => false, 'reason' => 'انتهى الوقت المحدد لإجراء الامتحان.'];
        }

        return ['allowed' => true, 'action' => 'start'];
    }

    /**
     * Start a new exam attempt or resume active one. Atomically consumes retake grant if applicable.
     */
    public function startExamAttempt(Exam $exam, Student $student): ExamAttempt
    {
        $check = $this->canStudentStartExam($exam, $student);
        if (!$check['allowed']) {
            throw ValidationException::withMessages([
                'exam' => $check['reason'] ?? 'غير مصرح ببدء الامتحان.',
            ]);
        }

        if ($check['action'] === 'resume' && isset($check['attempt'])) {
            return $check['attempt'];
        }

        return DB::transaction(function () use ($exam, $student, $check) {
            $now = now();
            $latestAttempt = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->orderByDesc('attempt_number')
                ->first();

            $attemptNumber = ($latestAttempt ? (int) $latestAttempt->attempt_number : 0) + 1;

            $attempt = ExamAttempt::create([
                'tenant_id' => $exam->tenant_id ?? $student->tenant_id,
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'attempt_number' => $attemptNumber,
                'total_score' => (float) $exam->total_marks,
                'score' => null,
                'percentage' => null,
                'passed' => null,
                'is_official' => false,
                'started_at' => $now,
            ]);

            // If a retake grant was required, consume it atomically
            if ($check['action'] === 'retake' && isset($check['grant'])) {
                $check['grant']->update([
                    'status' => 'consumed',
                    'consumed_at' => $now,
                    'consumed_attempt_id' => $attempt->id,
                ]);
            }

            return $attempt;
        });
    }

    /**
     * Grant a one-time exam retake permission to a student.
     */
    public function grantExamRetake(Exam $exam, Student $student, Carbon $expiresAt, string $reason, ?User $granter = null): ExamRetakeGrant
    {
        $granter = $granter ?: auth()->user();

        if (!$reason || trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'سبب منح إذن إعادة الامتحان إلزامي.',
            ]);
        }

        if ($expiresAt->isPast()) {
            throw ValidationException::withMessages([
                'expires_at' => 'تاريخ انتهاء صلاحية إذن إعادة الامتحان يجب أن يكون في المستقبل.',
            ]);
        }

        return DB::transaction(function () use ($exam, $student, $expiresAt, $reason, $granter) {
            // Cancel overlapping active grants
            ExamRetakeGrant::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->whereNull('consumed_at')
                ->update(['status' => 'revoked']);

            return ExamRetakeGrant::create([
                'tenant_id' => $exam->tenant_id ?? $student->tenant_id,
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'granted_by' => $granter->id,
                'expires_at' => $expiresAt,
                'reason' => trim($reason),
                'status' => 'active',
            ]);
        });
    }

    /**
     * Finalize an exam attempt, compute automatic scores, and recalculate highest eligible official score.
     */
    public function finalizeExamAttempt(ExamAttempt $attempt): ExamAttempt
    {
        if ($attempt->completed_at) {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt) {
            $now = now();
            $attempt->loadMissing(['exam.questions.options', 'answers']);

            $totalScore = (float) ($attempt->exam?->total_marks ?: 100);
            $earnedScore = 0.0;

            foreach ($attempt->answers as $answer) {
                $question = $answer->question;
                if (!$question) continue;

                // Auto-grade multiple choice / true-false
                $selectedOption = $answer->option;
                $isCorrect = $selectedOption && $selectedOption->is_correct;
                $points = $isCorrect ? (float) $question->points : 0.0;

                $answer->update([
                    'is_correct' => $isCorrect,
                ]);

                $earnedScore += $points;
            }

            $percentage = $totalScore > 0 ? round(($earnedScore / $totalScore) * 100, 2) : 0;
            $passingMarks = (float) ($attempt->exam?->passing_marks ?: ($totalScore * 0.5));
            $passed = $earnedScore >= $passingMarks;
            $timeTaken = $attempt->started_at ? $now->diffInSeconds($attempt->started_at) : null;

            $attempt->update([
                'score' => round($earnedScore, 2),
                'total_score' => $totalScore,
                'percentage' => $percentage,
                'passed' => $passed,
                'completed_at' => $now,
                'time_taken' => $timeTaken,
            ]);

            // Recalculate highest eligible score for this exam and student
            $this->recalculateOfficialExamScore($attempt->exam_id, $attempt->student_id);

            return $attempt->fresh();
        });
    }

    /**
     * Recalculates and sets the official highest score among completed eligible exam attempts.
     */
    public function recalculateOfficialExamScore(int $examId, int $studentId): ?ExamAttempt
    {
        $attempts = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->whereNotNull('score')
            ->orderByDesc('score')
            ->orderByDesc('attempt_number')
            ->get();

        if ($attempts->isEmpty()) {
            return null;
        }

        $highest = $attempts->first();

        // Mark highest as official, others false
        ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->where('id', '!=', $highest->id)
            ->update(['is_official' => false]);

        ExamAttempt::where('id', $highest->id)
            ->update(['is_official' => true]);

        $highest->refresh();

        return $highest;
    }

    /**
     * Alias for recalculateOfficialExamScore.
     */
    public function recalculateOfficialExamGrade(int $examId, int $studentId): ?ExamAttempt
    {
        return $this->recalculateOfficialExamScore($examId, $studentId);
    }

    /**
     * Correct a published exam score with mandatory audit reason.
     */
    public function correctPublishedExamScore(ExamAttempt $attempt, float $newScore, string $reason, ?User $corrector = null): AcademicGradeCorrection
    {
        $corrector = $corrector ?: auth()->user();

        if (!$reason || trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'سبب تعديل درجة الامتحان إلزامي.',
            ]);
        }

        $totalMarks = (float) ($attempt->exam?->total_marks ?? 100);
        if ($newScore < 0 || $newScore > $totalMarks) {
            throw ValidationException::withMessages([
                'score' => "يجب أن تكون الدرجة بين 0 و {$totalMarks}.",
            ]);
        }

        return DB::transaction(function () use ($attempt, $newScore, $reason, $corrector, $totalMarks) {
            $oldScore = $attempt->score;
            $percentage = $totalMarks > 0 ? round(($newScore / $totalMarks) * 100, 2) : 0;
            $passingMarks = (float) ($attempt->exam?->passing_marks ?: ($totalMarks * 0.5));

            $log = AcademicGradeCorrection::create([
                'tenant_id' => $attempt->tenant_id,
                'target_type' => 'exam',
                'target_id' => $attempt->id,
                'student_id' => $attempt->student_id,
                'exam_id' => $attempt->exam_id,
                'corrected_by' => $corrector->id,
                'previous_score' => $oldScore,
                'new_score' => round($newScore, 2),
                'reason' => trim($reason),
            ]);

            $attempt->update([
                'score' => round($newScore, 2),
                'percentage' => $percentage,
                'passed' => $newScore >= $passingMarks,
            ]);

            $this->recalculateOfficialExamScore($attempt->exam_id, $attempt->student_id);

            return $log;
        });
    }

    /**
     * Check if exam questions can be modified.
     * Throws exception if attempts have already begun.
     */
    public function validateExamCanBeEdited(Exam $exam): void
    {
        if ($exam->hasStartedAttempts()) {
            throw ValidationException::withMessages([
                'exam' => 'لا يمكن تعديل بنية أو أسئلة الامتحان بعد بدء الطلاب في إجراء محاولات عليه. لحماية نزاهة التقييم وسجلات الطلاب التاريخية.',
            ]);
        }
    }

    // =========================================================================
    // GROUP ACADEMIC CENTER PROVIDERS (FOR THE 4 MAIN TABS)
    // =========================================================================

    /**
     * Get aggregate statistics for the Group Academic Center header.
     */
    public function getGroupAcademicHeaderStats(Group $group): array
    {
        $sessionCount = $group->attendanceSessions()->count();
        if ($sessionCount === 0) {
            $sessionCount = $group->sessionSchedules()->count();
        }

        $homeworkCount = Assignment::where('group_id', $group->id)->count();

        $pendingHomeworkGrading = AssignmentSubmission::whereHas('assignment', fn($q) => $q->where('group_id', $group->id))
            ->where(function ($q) {
                $q->whereNull('score')->orWhere('graded', false);
            })
            ->whereNotNull('submitted_at')
            ->count();

        $examsCount = Exam::where('group_id', $group->id)->count();

        $examsNeedingReview = ExamAttempt::whereHas('exam', fn($q) => $q->where('group_id', $group->id))
            ->whereNotNull('completed_at')
            ->whereNull('score')
            ->count();

        return [
            'sessions_count' => $sessionCount,
            'homework_count' => $homeworkCount,
            'pending_grading_count' => $pendingHomeworkGrading,
            'exams_count' => $examsCount,
            'exams_needing_review' => $examsNeedingReview,
        ];
    }
}
