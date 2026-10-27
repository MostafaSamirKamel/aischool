<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListExamsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('ViewAny:Exam');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])],
            'type' => ['nullable', Rule::in(['quiz', 'midterm', 'final', 'assignment', 'project'])],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
