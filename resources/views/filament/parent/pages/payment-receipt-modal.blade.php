<div class="space-y-4">
    <div class="text-center border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('إيصال دفع') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('رقم الإيصال') }}: {{ $payment->payment_code }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('اسم الطالب') }}</dt>
            <dd class="font-medium">{{ $payment->student->user->name }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('رقم الطالب') }}</dt>
            <dd class="font-medium">{{ $payment->student->student_code }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('الكورس') }}</dt>
            <dd class="font-medium">{{ $payment->group->course->name }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('المجموعة') }}</dt>
            <dd class="font-medium">{{ $payment->group->name }}</dd>
        </div>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg text-center">
        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('المبلغ المدفوع') }}</div>
        <div class="text-4xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($payment->amount, 2) }} {{ __('جنيه') }}</div>
    </div>

    <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('تاريخ الدفع') }}</dt>
            <dd class="font-medium">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('طريقة الدفع') }}</dt>
            <dd class="font-medium">
                {{ match($payment->payment_method) {
                    'cash' => 'نقدي',
                    'card' => 'بطاقة',
                    'bank_transfer' => 'تحويل بنكي',
                    'online' => 'دفع إلكتروني',
                    default => $payment->payment_method,
                } }}
            </dd>
        </div>
        <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('الحالة') }}</dt>
            <dd>
                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    {{ __('مكتمل') }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('تاريخ الإصدار') }}</dt>
            <dd class="font-medium">{{ now()->format('d/m/Y H:i') }}</dd>
        </div>
    </div>

    @if($payment->notes)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
            <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ __('ملاحظات') }}</h4>
            <p class="text-sm whitespace-pre-wrap">{{ $payment->notes }}</p>
        </div>
    @endif

    <div class="text-center text-xs text-gray-500 dark:text-gray-400 mt-4">
        {{ __('هذا إيصال إلكتروني صادر من نظام إدارة المدرسة') }}
    </div>
</div>
