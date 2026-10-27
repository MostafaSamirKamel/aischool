<?php

namespace App\Filament\Student\Pages;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Enrollment;
use App\Filament\Student\Pages\ExamResult;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class TakeExam extends Page
{
    protected string $view = 'filament.student.pages.take-exam';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $slug = 'my-exams/{exam}/take';

    public Exam $examRecord;

    public ExamAttempt $attempt;

    public array $selectedAnswers = [];

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return __('بدء الامتحان');
    }

    public function mount(int|string $exam): void
    {
        $student = auth()->user()?->student;

        if (!$student) {
            abort(403);
        }

        $this->examRecord = Exam::query()
            ->with(['questions.options'])
            ->findOrFail($exam);

        $hasAccess = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->whereHas('enrollmentCourses', function ($query) {
                $query->where('group_id', $this->examRecord->group_id);
            })
            ->exists();

        abort_unless($hasAccess, 403);

        $academicService = app(\App\Services\AcademicCenterService::class);

        $existingAttempt = ExamAttempt::query()
            ->where('exam_id', $this->examRecord->id)
            ->where('student_id', $student->id)
            ->latest('id')
            ->first();

        // If an in-progress attempt exists, resume it (offline recovery / reconnection)
        if ($existingAttempt && !$existingAttempt->completed_at) {
            $duration = (int) ($this->examRecord->duration ?: 60);
            $expiresAt = $existingAttempt->started_at ? $existingAttempt->started_at->copy()->addMinutes($duration) : null;
            
            // Check if authoritative server time has expired
            if ($expiresAt && now()->gt($expiresAt)) {
                $this->attempt = $existingAttempt;
                $this->finalizeAttempt();
                Notification::make()
                    ->warning()
                    ->title(__('انتهى وقت الامتحان المتاح وتم إنهاء المحاولة'))
                    ->send();
                $this->redirect(ExamResult::getUrl(['attempt' => $this->attempt->id]), navigate: true);
                return;
            }

            $this->attempt = $existingAttempt;
            $this->selectedAnswers = ExamAnswer::query()
                ->where('exam_attempt_id', $this->attempt->id)
                ->pluck('exam_question_option_id', 'exam_question_id')
                ->map(fn ($value) => (int) $value)
                ->toArray();
            return;
        }

        // If previous attempt is completed, check if student has a valid retake grant
        if ($existingAttempt && $existingAttempt->completed_at) {
            $check = $academicService->canStudentStartExam($this->examRecord, $student);
            if ($check['allowed'] && $check['action'] === 'retake') {
                $this->attempt = $academicService->startExamAttempt($this->examRecord, $student);
                $this->selectedAnswers = [];
                Notification::make()
                    ->success()
                    ->title(__('بدء محاولة إعادة الامتحان المصرح بها بنجاح'))
                    ->send();
                return;
            }

            $this->redirect(ExamResult::getUrl(['attempt' => $existingAttempt->id]), navigate: true);
            return;
        }

        if (!in_array($this->examRecord->status, ['scheduled', 'in_progress'], true)) {
            Notification::make()
                ->danger()
                ->title(__('الامتحان غير متاح حالياً'))
                ->send();

            $this->redirect(MyExams::getUrl(), navigate: true);

            return;
        }

        if (!$this->examRecord->isAvailableNow()) {
            $message = $this->examRecord->hasEnded()
                ? __('انتهى وقت الامتحان')
                : __('الامتحان لم يبدأ بعد');

            Notification::make()
                ->warning()
                ->title($message)
                ->send();

            $this->redirect(MyExams::getUrl(), navigate: true);

            return;
        }

        // First time attempt
        $this->attempt = $academicService->startExamAttempt($this->examRecord, $student);

        $this->selectedAnswers = ExamAnswer::query()
            ->where('exam_attempt_id', $this->attempt->id)
            ->pluck('exam_question_option_id', 'exam_question_id')
            ->map(fn ($value) => (int) $value)
            ->toArray();
    }

    public function updatedSelectedAnswers($value, $key): void
    {
        $questionId = (int) $key;
        $optionId = (int) $value;

        if (!$questionId || !$optionId || !isset($this->attempt)) {
            return;
        }

        $this->persistAnswerForQuestion($questionId, $optionId);
    }

    public function autoSubmitExam(): void
    {
        if (!isset($this->attempt)) {
            return;
        }

        $this->attempt->refresh();

        if ($this->attempt->completed_at) {
            $this->redirect(ExamResult::getUrl(['attempt' => $this->attempt->id]), navigate: true);

            return;
        }

        $this->finalizeAttempt();

        Notification::make()
            ->warning()
            ->title(__('انتهى وقت الامتحان وتم التسليم تلقائياً'))
            ->send();

        $this->redirect(ExamResult::getUrl(['attempt' => $this->attempt->id]), navigate: true);
    }

    public function submitExam(): void
    {
        if ($this->attempt->completed_at) {
            $this->redirect(ExamResult::getUrl(['attempt' => $this->attempt->id]), navigate: true);

            return;
        }

        $this->finalizeAttempt();

        Notification::make()
            ->success()
            ->title(__('تم تسليم الامتحان بنجاح'))
            ->send();

        $this->redirect(ExamResult::getUrl(['attempt' => $this->attempt->id]), navigate: true);
    }

    private function finalizeAttempt(): void
    {
        DB::transaction(function () {
            $this->examRecord->loadMissing(['questions.options']);

            foreach ($this->selectedAnswers as $questionId => $optionId) {
                $this->persistAnswerForQuestion((int) $questionId, (int) $optionId);
            }

            $this->attempt->refresh();

            if ($this->attempt->completed_at) {
                return;
            }

            $submittedAt = now();
            $endsAt = $this->examRecord->ends_at;

            if ($endsAt && $submittedAt->gt($endsAt)) {
                $submittedAt = $endsAt->copy();
            }

            $startedAt = $this->attempt->started_at ?? $submittedAt;
            $timeTaken = $startedAt ? $startedAt->diffInSeconds($submittedAt) : null;

            $this->attempt->update([
                'completed_at' => $submittedAt,
                'time_taken' => $timeTaken,
            ]);

            $this->attempt->refresh();
            $this->attempt->calculateScore();

            app(\App\Services\AcademicCenterService::class)->recalculateOfficialExamGrade(
                $this->attempt->exam_id,
                $this->attempt->student_id
            );
        });
    }

    private function persistAnswerForQuestion(int $questionId, int $optionId): void
    {
        if ($questionId <= 0 || $optionId <= 0 || !isset($this->attempt)) {
            return;
        }

        $this->examRecord->loadMissing(['questions.options']);
        $question = $this->examRecord->questions->firstWhere('id', $questionId);

        if (!$question) {
            return;
        }

        $selectedOption = $question->options->firstWhere('id', $optionId);

        if (!$selectedOption) {
            return;
        }

        ExamAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $this->attempt->id,
                'exam_question_id' => $question->id,
            ],
            [
                'exam_question_option_id' => $selectedOption->id,
                'is_correct' => (bool) $selectedOption->is_correct,
            ],
        );
    }
}
