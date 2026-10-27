<?php

namespace App\Http\Requests;

use App\Constants\SystemConstants;
use App\Enums\Gender;
use App\Enums\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Update:Student');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $studentId = $this->route('student');

        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'student_code' => [
                'sometimes',
                'string',
                'max:' . SystemConstants::MAX_NAME_LENGTH,
                Rule::unique('students', 'student_code')->ignore($studentId)
            ],
            'national_id' => [
                'nullable',
                'string',
                'size:' . SystemConstants::NATIONAL_ID_LENGTH,
                Rule::unique('students', 'national_id')->ignore($studentId)
            ],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['sometimes', Rule::enum(Gender::class)],
            'phone' => ['sometimes', 'string', 'max:20'],
            'phone_2' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:' . SystemConstants::MAX_DESCRIPTION_LENGTH],
            'city' => ['nullable', 'string', 'max:' . SystemConstants::MAX_NAME_LENGTH],
            'education_level' => ['nullable', 'string', 'max:' . SystemConstants::MAX_NAME_LENGTH],
            'school_name' => ['nullable', 'string', 'max:' . SystemConstants::MAX_NAME_LENGTH],
            'medical_notes' => ['nullable', 'string', 'max:' . SystemConstants::MAX_DESCRIPTION_LENGTH],
            'status' => ['sometimes', Rule::enum(StudentStatus::class)],
            'custom_fields' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'المستخدم',
            'student_code' => 'كود الطالب',
            'national_id' => 'الرقم القومي',
            'birth_date' => 'تاريخ الميلاد',
            'gender' => 'الجنس',
            'phone' => 'رقم الهاتف',
            'phone_2' => 'رقم هاتف إضافي',
            'address' => 'العنوان',
            'city' => 'المدينة',
            'education_level' => 'المستوى التعليمي',
            'school_name' => 'اسم المدرسة',
            'medical_notes' => 'ملاحظات طبية',
            'status' => 'الحالة',
        ];
    }
}
