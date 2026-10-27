<?php

namespace App\Filament\Student\Pages;

use App\Models\ExamAttempt;
use Filament\Pages\Page;

class ExamResult extends Page
{
    protected string $view = 'filament.student.pages.exam-result';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $slug = 'my-exams/results/{attempt}';

    public ExamAttempt $attemptRecord;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return __('نتيجة الامتحان');
    }

    public function mount(int|string $attempt): void
    {
        $student = auth()->user()?->student;

        if (!$student) {
            abort(403);
        }

        $this->attemptRecord = ExamAttempt::query()
            ->with([
                'exam.questions.options',
                'answers.question',
                'answers.selectedOption',
            ])
            ->findOrFail($attempt);

        abort_unless((int) $this->attemptRecord->student_id === (int) $student->id, 403);

        if (!$this->attemptRecord->completed_at) {
            $this->redirect(TakeExam::getUrl(['exam' => $this->attemptRecord->exam_id]), navigate: true);
        }
    }
}
