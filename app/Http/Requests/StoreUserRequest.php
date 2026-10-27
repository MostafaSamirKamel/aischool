<?php

namespace App\Http\Requests;

use App\Constants\SystemConstants;
use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Create:User');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:' . SystemConstants::MAX_NAME_LENGTH
            ],
            'username' => [
                'nullable',
                'string',
                'max:' . SystemConstants::MAX_NAME_LENGTH,
                'alpha_dash',
                'unique:users,username'
            ],
            'email' => [
                'required',
                'email',
                'max:' . SystemConstants::MAX_NAME_LENGTH,
                'unique:users,email'
            ],
            'password' => [
                'required',
                Password::min(SystemConstants::MIN_PASSWORD_LENGTH)
                    ->letters()
                    ->numbers()
                    ->mixedCase()
                    ->symbols()
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:' . SystemConstants::MAX_DESCRIPTION_LENGTH],
            'qualification' => ['nullable', 'string', 'max:' . SystemConstants::MAX_NAME_LENGTH],
            'job_title' => ['nullable', 'string', 'max:' . SystemConstants::MAX_NAME_LENGTH],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'is_super_admin' => ['boolean'],
            'is_tenant_owner' => ['boolean'],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,name'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'username' => 'اسم المستخدم',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'phone' => 'رقم الهاتف',
            'date_of_birth' => 'تاريخ الميلاد',
            'address' => 'العنوان',
            'qualification' => 'المؤهل',
            'job_title' => 'المسمى الوظيفي',
            'tenant_id' => 'الفرع',
            'status' => 'الحالة',
            'roles' => 'الأدوار',
        ];
    }
}
