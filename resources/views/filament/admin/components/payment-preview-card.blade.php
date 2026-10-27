<div class="space-y-4" dir="rtl">
    @php
        $preview = $getState() ?? [];
        $paymentAmount = floatval($preview['payment_amount'] ?? 0);
        $currentBalance = floatval($preview['current_balance'] ?? 0);
        $expectedBalance = floatval($preview['expected_balance'] ?? 0);
        $affected = $preview['affected_installments'] ?? [];
        $surplus = floatval($preview['unallocated_surplus'] ?? 0);
        $vaultName = $preview['vault_name'] ?? 'لم يتم تحديد الخزنة بعد';
    @endphp

    @if($paymentAmount > 0)
        <div class="rounded-xl border-2 border-indigo-200 dark:border-indigo-800 bg-gradient-to-br from-indigo-50/50 via-white to-sky-50/50 dark:from-slate-900 dark:via-slate-900 dark:to-indigo-950/40 p-5 shadow-md">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-indigo-100 dark:border-indigo-900/60">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">⚡</span>
                    <div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-slate-100 m-0">معاينة التوزيع المالي الفوري (قبل التأكيد)</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 m-0">محاكاة مطابقة لمحرك التوزيع الرسمي بالأقدمية المالية (Oldest-First Allocation)</p>
                    </div>
                </div>
                <div class="text-xs text-indigo-700 dark:text-indigo-300 font-semibold bg-indigo-100 dark:bg-indigo-900/60 px-3 py-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800">
                    🏛️ الخزنة المستلمة: <span class="font-bold">{{ $vaultName }}</span>
                </div>
            </div>

            <!-- Financial Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 my-4">
                <div class="p-3.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center shadow-sm">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold">المبلغ المراد سداده الآن</div>
                    <div class="text-xl font-extrabold text-blue-600 dark:text-sky-400 mt-1">
                        {{ number_format($paymentAmount, 2) }} <span class="text-xs font-normal">ج.م</span>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center shadow-sm">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold">إجمالي المتبقي الحالي</div>
                    <div class="text-xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">
                        {{ number_format($currentBalance, 2) }} <span class="text-xs font-normal">ج.م</span>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center shadow-sm">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold">المتبقي المتوقع بعد الدفع</div>
                    <div class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ number_format($expectedBalance, 2) }} <span class="text-xs font-normal">ج.م</span>
                    </div>
                </div>
            </div>

            @if($surplus > 0.009)
                <div class="p-3 rounded-lg bg-red-50 dark:bg-red-950/50 border border-red-300 dark:border-red-800 text-red-800 dark:text-red-300 text-xs flex items-center gap-2 mb-4 font-semibold">
                    <span>⚠️</span>
                    <span>تنبيه: المبلغ المدخل يتجاوز إجمالي الأقساط المفتوحة بفائض قدره {{ number_format($surplus, 2) }} ج.م. النظام يمنع التحصيل الزائد.</span>
                </div>
            @endif

            <!-- Affected Installments Breakdown -->
            @if(!empty($affected))
                <div class="mt-4">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 flex items-center gap-2">
                        <span>🎯</span>
                        <span>الأقساط التي سيتم توزيع المبلغ عليها تلقائياً:</span>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-100 dark:bg-slate-700/60 border-b border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold">
                                <tr>
                                    <th class="p-2.5 text-center">القسط</th>
                                    <th class="p-2.5">تاريخ الاستحقاق</th>
                                    <th class="p-2.5">قيمة القسط</th>
                                    <th class="p-2.5">المدفوع سابقاً</th>
                                    <th class="p-2.5">المبلغ المخصص له</th>
                                    <th class="p-2.5">المتبقي بعد الدفعة</th>
                                    <th class="p-2.5 text-center">الحالة الجديدة المتوقعة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @foreach($affected as $item)
                                    <tr>
                                        <td class="p-2.5 text-center font-bold text-slate-800 dark:text-slate-100">#{{ $item['installment_number'] }}</td>
                                        <td class="p-2.5 text-slate-600 dark:text-slate-400 font-mono">{{ $item['due_date'] }}</td>
                                        <td class="p-2.5 font-semibold text-slate-800 dark:text-slate-100">{{ number_format($item['amount'], 2) }} ج.م</td>
                                        <td class="p-2.5 text-slate-500 dark:text-slate-400">{{ number_format($item['current_paid'], 2) }} ج.م</td>
                                        <td class="p-2.5 font-bold text-blue-600 dark:text-sky-400">+ {{ number_format($item['allocated_amount'], 2) }} ج.م</td>
                                        <td class="p-2.5 font-bold {{ $item['new_remaining'] <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                            {{ number_format($item['new_remaining'], 2) }} ج.م
                                        </td>
                                        <td class="p-2.5 text-center">
                                            @if($item['new_status'] === 'paid')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                                                    ✅ مدفوع بالكامل
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300">
                                                    ⚠️ مدفوع جزئياً
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
