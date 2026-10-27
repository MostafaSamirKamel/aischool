<div class="space-y-4" dir="rtl">
    @php
        $payments = $getState() ?? [];
    @endphp

    @if(!empty($payments))
        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="p-4 bg-gradient-to-r from-blue-600 to-indigo-700 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🧾</span>
                    <div>
                        <h3 class="font-bold text-base text-white m-0">سجل إيصالات ومدفوعات الطالب السابقة</h3>
                        <p class="text-xs text-blue-100 m-0">عرض كافة سندات القبض الموثقة في النظام والمربوطة بالخزينة</p>
                    </div>
                </div>
                <div class="px-3 py-1 bg-white/20 rounded-lg text-xs font-bold">
                    {{ count($payments) }} إيصال
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-xs">
                            <th class="p-3 text-center">#</th>
                            <th class="p-3">رقم الإيصال</th>
                            <th class="p-3">التاريخ</th>
                            <th class="p-3">المبلغ</th>
                            <th class="p-3">طريقة الدفع</th>
                            <th class="p-3">الخزنة المستلمة</th>
                            <th class="p-3">الموظف المستلم</th>
                            <th class="p-3">كود التسجيل</th>
                            <th class="p-3 text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($payments as $index => $pay)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="p-3 text-center font-bold text-slate-500">{{ $index + 1 }}</td>
                                <td class="p-3">
                                    <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $pay['payment_code'] }}</span>
                                    @if(!empty($pay['transaction_reference']))
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $pay['transaction_reference'] }}</div>
                                    @endif
                                </td>
                                <td class="p-3 text-slate-700 dark:text-slate-300 font-medium">{{ $pay['payment_date'] }}</td>
                                <td class="p-3">
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($pay['amount'], 2) }} ج.م</span>
                                </td>
                                <td class="p-3 text-slate-600 dark:text-slate-400">
                                    @php
                                        $methodLabel = match($pay['payment_method'] ?? 'cash') {
                                            'cash' => '💵 نقدي',
                                            'card' => '💳 بطاقة',
                                            'bank_transfer' => '🏛️ تحويل بنكي',
                                            'online', 'mobile_wallet' => '📱 محفظة إلكترونية',
                                            default => $pay['payment_method'] ?? 'نقدي',
                                        };
                                    @endphp
                                    {{ $methodLabel }}
                                </td>
                                <td class="p-3 text-slate-700 dark:text-slate-300">{{ $pay['vault_name'] }}</td>
                                <td class="p-3 text-slate-600 dark:text-slate-400 text-xs">{{ $pay['received_by'] }}</td>
                                <td class="p-3 text-slate-600 dark:text-slate-400 text-xs font-mono">{{ $pay['enrollment_code'] }}</td>
                                <td class="p-3 text-center">
                                    @if(($pay['status'] ?? '') === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            ✅ مكتمل
                                        </span>
                                    @elseif(($pay['status'] ?? '') === 'refunded')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                            ↩️ مسترد
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            {{ $pay['status'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700">
                        <tr>
                            <td colspan="3" class="p-3 text-left font-bold text-slate-700 dark:text-slate-300">إجمالي المدفوعات المسجلة:</td>
                            <td class="p-3 font-extrabold text-emerald-600 dark:text-emerald-400 text-base">
                                {{ number_format(collect($payments)->where('status', 'completed')->sum('amount'), 2) }} ج.م
                            </td>
                            <td colspan="5"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-center text-slate-500 dark:text-slate-400 text-xs">
            لا توجد إيصالات مدفوعات سابقة لهذا الطالب.
        </div>
    @endif
</div>
