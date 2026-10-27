<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('Create:Grade');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'integer', Rule::exists('exams', 'id')],
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'enrollment_id' => ['nullable', 'integer', Rule::exists('enrollments', 'id')],
            'marks_obtained' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
