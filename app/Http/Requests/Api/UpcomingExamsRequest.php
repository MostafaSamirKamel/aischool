<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpcomingExamsRequest extends FormRequest
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
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
