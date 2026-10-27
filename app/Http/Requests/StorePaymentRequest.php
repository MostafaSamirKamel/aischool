<?php

namespace App\Http\Requests;

use App\Constants\SystemConstants;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Create:StudentPayment');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'amount' => [
                'required',
                'numeric',
                'min:' . SystemConstants::MIN_PAYMENT_AMOUNT,
                'max:' . SystemConstants::MAX_PAYMENT_AMOUNT
            ],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'vault_id' => ['required', 'exists:vaults,id'],
            'reference_number' => ['nullable', 'string', 'max:' . SystemConstants::MAX_NAME_LENGTH],
            'notes' => ['nullable', 'string', 'max:' . SystemConstants::MAX_DESCRIPTION_LENGTH],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:' . SystemConstants::MAX_NAME_LENGTH],
            'items.*.amount' => [
                'required',
                'numeric',
                'min:' . SystemConstants::MIN_PAYMENT_AMOUNT
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'الطالب',
            'amount' => 'المبلغ',
            'payment_date' => 'تاريخ الدفع',
            'payment_method' => 'طريقة الدفع',
            'vault_id' => 'الخزينة',
            'reference_number' => 'رقم المرجع',
            'notes' => 'ملاحظات',
            'items' => 'بنود الدفع',
            'items.*.description' => 'وصف البند',
            'items.*.amount' => 'مبلغ البند',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Calculate total amount from items
        if ($this->has('items') && is_array($this->items)) {
            $total = collect($this->items)->sum('amount');
            $this->merge(['amount' => $total]);
        }
    }
}
