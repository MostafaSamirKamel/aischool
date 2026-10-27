<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Card --}}
        <div class="bg-gradient-to-r from-green-500 to-green-600 overflow-hidden shadow-lg sm:rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold">
                        {{ number_format($this->record->total_amount, 2) }} جنيه
                    </h2>
                    <p class="text-green-100 mt-1">
                        {{ $this->record->period_label }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold
                        @if($this->record->status === 'paid') bg-white text-green-600
                        @elseif($this->record->status === 'approved') bg-blue-100 text-blue-800
                        @elseif($this->record->status === 'pending') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ $this->record->status_label }}
                    </span>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-4">
                <div class="bg-white/20 rounded-lg p-3">
                    <div class="text-sm text-green-100">السيشنات المكتملة</div>
                    <div class="text-2xl font-bold">{{ $this->record->completed_sessions }}</div>
                </div>
                <div class="bg-white/20 rounded-lg p-3">
                    <div class="text-sm text-green-100">سعر السيشن</div>
                    <div class="text-2xl font-bold">{{ number_format($this->record->rate_per_session, 0) }} ج</div>
                </div>
                <div class="bg-white/20 rounded-lg p-3">
                    <div class="text-sm text-green-100">قيمة السيشنات</div>
                    <div class="text-2xl font-bold">{{ number_format($this->record->sessions_amount, 0) }} ج</div>
                </div>
            </div>
        </div>

        {{-- Payment Breakdown --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Amount Details --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    تفاصيل المبلغ
                </h3>
                <dl class="space-y-3">
                    @if($this->record->base_amount > 0)
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الراتب الأساسي</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 font-semibold">
                            {{ number_format($this->record->base_amount, 2) }} جنيه
                        </dd>
                    </div>
                    @endif

                    <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            قيمة السيشنات ({{ $this->record->completed_sessions }} × {{ number_format($this->record->rate_per_session, 0) }})
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 font-semibold">
                            {{ number_format($this->record->sessions_amount, 2) }} جنيه
                        </dd>
                    </div>

                    @if($this->record->bonus_amount > 0)
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                        <dt class="text-sm font-medium text-green-600">المكافآت</dt>
                        <dd class="text-sm text-green-600 font-semibold">
                            + {{ number_format($this->record->bonus_amount, 2) }} جنيه
                        </dd>
                    </div>
                    @endif

                    @if($this->record->deduction_amount > 0)
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                        <dt class="text-sm font-medium text-red-600">الخصومات</dt>
                        <dd class="text-sm text-red-600 font-semibold">
                            - {{ number_format($this->record->deduction_amount, 2) }} جنيه
                        </dd>
                    </div>
                    @endif

                    <div class="flex justify-between items-center pt-3 bg-gray-50 dark:bg-gray-900 -mx-6 px-6 py-4">
                        <dt class="text-base font-bold text-gray-900 dark:text-white">الإجمالي</dt>
                        <dd class="text-xl font-bold text-green-600">
                            {{ number_format($this->record->total_amount, 2) }} جنيه
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Period & Payment Info --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    معلومات الفترة والدفع
                </h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">فترة الدفع</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            من {{ $this->record->period_start->format('Y-m-d') }} 
                            إلى {{ $this->record->period_end->format('Y-m-d') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي السيشنات</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->total_sessions }} سيشن
                            @if($this->record->cancelled_sessions > 0)
                                <span class="text-red-600">({{ $this->record->cancelled_sessions }} ملغي)</span>
                            @endif
                        </dd>
                    </div>

                    @if($this->record->status === 'paid')
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تاريخ الدفع</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->payment_date->format('Y-m-d') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">طريقة الدفع</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->payment_method_label }}
                        </dd>
                    </div>

                    @if($this->record->payment_reference)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">رقم المرجع</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->payment_reference }}
                        </dd>
                    </div>
                    @endif
                    @endif

                    @if($this->record->approved_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تاريخ الاعتماد</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->approved_at->format('Y-m-d H:i') }}
                        </dd>
                    </div>
                    @endif

                    @if($this->record->notes)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ملاحظات</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->notes }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Sessions Details --}}
        @if($this->record->sessions_data && count($this->record->sessions_data) > 0)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                تفاصيل السيشنات المكتملة ({{ count($this->record->sessions_data) }})
            </h3>
            <div class="overflow-x-auto -mx-6 px-6">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gradient-to-r from-green-50 to-green-100 dark:from-gray-900 dark:to-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">التاريخ</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">المجموعة</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">السعر</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach($this->record->sessions_data as $index => $session)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $session['date'] }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">{{ $session['group'] ?? 'غير محدد' }}</td>
                            <td class="px-4 py-3 text-sm font-bold text-green-600">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ number_format($session['rate'] ?? 0, 0) }} ج.م
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gradient-to-r from-green-50 to-green-100 dark:from-gray-900 dark:to-gray-800">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white text-right">الإجمالي:</td>
                            <td class="px-4 py-3 text-sm font-bold text-green-600">
                                {{ number_format($this->record->sessions_amount, 0) }} ج.م
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
