<?php

namespace App\Filament\Instructor\Resources\Exams\Pages;

use App\Filament\Instructor\Resources\Exams\ExamResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateExam extends CreateRecord
{
    protected static string $resource = ExamResource::class;

    protected static bool $canCreateAnother = false;

    protected array $initialQuestions = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->initialQuestions = $this->prepareInitialQuestions($data['initial_questions'] ?? []);
        unset($data['initial_questions']);

        $user = Auth::user();
        $data['tenant_id'] = $user->tenant_id;

        // الحصول على instructor_id من علاقة User
        if (!$user->instructor) {
            throw new \Exception('لم يتم العثور على بيانات المدرس. يرجى التواصل مع الإدارة.');
        }

        $data['instructor_id'] = $user->instructor->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        if (empty($this->initialQuestions)) {
            return;
        }

        foreach ($this->initialQuestions as $index => $questionData) {
            $question = $this->record->questions()->create([
                'question_text' => $questionData['question_text'],
                'points' => $questionData['points'],
                'order' => $questionData['order'] ?? ($index + 1),
                'pdf_file' => $questionData['pdf_file'] ?? null,
                'images' => $questionData['images'] ?? null,
            ]);

            foreach ($questionData['options'] as $optionIndex => $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['option_text'],
                    'is_correct' => (bool) ($optionData['is_correct'] ?? false),
                    'order' => $optionIndex + 1,
                ]);
            }
        }
    }

    protected function prepareInitialQuestions(array $questions): array
    {
        $prepared = [];

        foreach ($questions as $question) {
            $questionText = trim((string) ($question['question_text'] ?? ''));
            if ($questionText === '') {
                continue;
            }

            $options = array_values(array_filter($question['options'] ?? [], function ($option) {
                return trim((string) ($option['option_text'] ?? '')) !== '';
            }));

            if (count($options) < 3) {
                throw ValidationException::withMessages([
                    'data.initial_questions' => 'كل سؤال يجب أن يحتوي على 3 اختيارات على الأقل.',
                ]);
            }

            $correctCount = collect($options)->filter(fn ($option) => (bool) ($option['is_correct'] ?? false))->count();
            if ($correctCount !== 1) {
                throw ValidationException::withMessages([
                    'data.initial_questions' => 'كل سؤال يجب أن يحتوي على إجابة صحيحة واحدة فقط.',
                ]);
            }

            $prepared[] = [
                'question_text' => $questionText,
                'points' => (float) ($question['points'] ?? 1),
                'order' => !empty($question['order']) ? (int) $question['order'] : null,
                'pdf_file' => $question['pdf_file'] ?? null,
                'images' => $question['images'] ?? null,
                'options' => array_map(function ($option) {
                    return [
                        'option_text' => trim((string) ($option['option_text'] ?? '')),
                        'is_correct' => (bool) ($option['is_correct'] ?? false),
                    ];
                }, $options),
            ];
        }

        return $prepared;
    }

    protected function getRedirectUrl(): string
    {
        // بعد الإنشاء، يروح على صفحة التعديل عشان يضيف الأسئلة
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return '✅ تم إنشاء الامتحان بنجاح';
    }
}
