<x-filament-widgets::widget>
    @if($this->hasOverdueInstallments())
    <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-500 rounded-lg p-6">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-red-700 dark:text-red-400 mb-2">
                    ⚠️ تنبيه: أقساط متأخرة لأبنائك
                </h3>
                <p class="text-red-600 dark:text-red-300 mb-4">
                    يوجد أقساط متأخرة لأحد أبنائك أو أكثر. يرجى سداد الأقساط في أقرب وقت ممكن.
                </p>

                <div class="space-y-3">
                    @foreach($this->getOverdueInstallments() as $installment)
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-red-300 dark:border-red-700">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $installment->enrollment->student->user->name }}
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $installment->enrollment->groups->pluck('course.name')->unique()->join(' | ') ?: 'غير محدد' }} - القسط رقم {{ $installment->installment_number }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-red-600 dark:text-red-400">
                                    {{ number_format($installment->remaining_amount, 2) }} ج.م
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    استحقاق: {{ $installment->due_date->format('d/m/Y') }}
                                </p>
                                <p class="text-xs text-red-600 dark:text-red-400 font-semibold mt-1">
                                    متأخر {{ now()->diffInDays($installment->due_date) }} يوم
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-300 dark:border-yellow-700">
                    <p class="text-sm text-yellow-800 dark:text-yellow-300">
                        💡 للتواصل مع الإدارة لترتيب السداد، يرجى الاتصال على الأرقام المتاحة.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-filament-widgets::widget>
