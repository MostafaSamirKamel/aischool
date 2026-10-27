<div class="space-y-6" dir="rtl">
    @php
        $allInstallments = $getState() ?? [];
        $today = now()->format('Y-m-d');
        
        $overdueList = [];
        $todayList = [];
        $futureList = [];
        $paidList = [];
        
        foreach ($allInstallments as $inst) {
            $rem = floatval($inst['remaining'] ?? ($inst['amount'] - $inst['paid_amount']));
            $dueDate = $inst['due_date'] ?? '';
            $statusStr = $inst['status'] ?? '';
            $isPaid = ($rem <= 0.001) || ($statusStr === 'مدفوع') || ($statusStr === 'paid');
            
            if ($isPaid) {
                $paidList[] = $inst;
            } elseif ($dueDate < $today) {
                $overdueList[] = $inst;
            } elseif ($dueDate === $today) {
                $todayList[] = $inst;
            } else {
                $futureList[] = $inst;
            }
        }

        $totalAmount = collect($allInstallments)->sum('amount');
        $totalPaid = collect($allInstallments)->sum('paid_amount');
        $totalRemaining = collect($allInstallments)->sum(fn($i) => max(0, $i['amount'] - $i['paid_amount']));
        $paidPercentage = $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0;
    @endphp

    @if(!empty($allInstallments))
        {{-- Header Section --}}
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%); border-radius: 16px; padding: 24px; color: white; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <span style="font-size: 32px;">📋</span>
                    <div>
                        <h2 style="font-size: 22px; font-weight: 900; margin: 0; color: white;">جدول وخطة الأقساط</h2>
                        <p style="color: rgba(255, 255, 255, 0.9); font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
                            تحديث لحظي لحالة الأقساط وفقاً للمدفوعات الموثقة
                        </p>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <div style="text-align: center; background: rgba(255, 255, 255, 0.18); border-radius: 12px; padding: 8px 16px;">
                        <div style="font-size: 11px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">الأقساط المفتوحة</div>
                        <div style="font-size: 20px; font-weight: 900;">{{ count($overdueList) + count($todayList) + count($futureList) }}</div>
                    </div>
                    <div style="text-align: center; background: rgba(255, 255, 255, 0.18); border-radius: 12px; padding: 8px 16px;">
                        <div style="font-size: 11px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">المسدد بالكامل</div>
                        <div style="font-size: 20px; font-weight: 900; color: #86efac;">{{ count($paidList) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <div style="background: white; border-radius: 14px; padding: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600;">إجمالي الأقساط</span>
                        <div style="font-size: 20px; font-weight: 900; color: #1e293b; margin-top: 4px;">{{ number_format($totalAmount, 2) }} ج.م</div>
                    </div>
                    <span style="font-size: 26px;">💰</span>
                </div>
            </div>

            <div style="background: white; border-radius: 14px; padding: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600;">المدفوع من الأقساط</span>
                        <div style="font-size: 20px; font-weight: 900; color: #16a34a; margin-top: 4px;">{{ number_format($totalPaid, 2) }} ج.م</div>
                    </div>
                    <span style="font-size: 26px;">✅</span>
                </div>
            </div>

            <div style="background: white; border-radius: 14px; padding: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 600;">المتبقي غير المسدد</span>
                        <div style="font-size: 20px; font-weight: 900; color: #d97706; margin-top: 4px;">{{ number_format($totalRemaining, 2) }} ج.م</div>
                    </div>
                    <span style="font-size: 26px;">⏳</span>
                </div>
            </div>
        </div>

        {{-- Group 1: 🚨 Overdue Installments --}}
        @if(!empty($overdueList))
            <div class="rounded-xl border border-red-200 dark:border-red-900 bg-red-50/40 dark:bg-red-950/20 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🚨</span>
                        <h3 class="font-extrabold text-sm text-red-800 dark:text-red-300 m-0">الأقساط المتأخرة المستحقة (تجاوزت موعد الاستحقاق)</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        {{ count($overdueList) }} قسط متأخر
                    </span>
                </div>
                <div class="overflow-x-auto rounded-lg border border-red-200 dark:border-red-800 bg-white dark:bg-slate-900">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-red-100/60 dark:bg-red-950/60 text-red-900 dark:text-red-200 font-bold">
                            <tr>
                                <th class="p-2.5 text-center">#</th>
                                <th class="p-2.5">تاريخ الاستحقاق</th>
                                <th class="p-2.5">أيام التأخير</th>
                                <th class="p-2.5">قيمة القسط</th>
                                <th class="p-2.5">المدفوع منه</th>
                                <th class="p-2.5">المتبقي المطلوب</th>
                                <th class="p-2.5 text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-100 dark:divide-red-900/40">
                            @foreach($overdueList as $inst)
                                @php
                                    $diff = now()->diffInDays(\Carbon\Carbon::parse($inst['due_date']));
                                @endphp
                                <tr>
                                    <td class="p-2.5 text-center font-bold text-slate-800 dark:text-slate-100">#{{ $inst['installment_number'] }}</td>
                                    <td class="p-2.5 text-slate-700 dark:text-slate-300 font-mono">{{ $inst['due_date'] }}</td>
                                    <td class="p-2.5 font-bold text-red-600 dark:text-red-400">متأخر {{ $diff }} يوم</td>
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">{{ number_format($inst['amount'], 2) }} ج.م</td>
                                    <td class="p-2.5 text-emerald-600 dark:text-emerald-400">{{ number_format($inst['paid_amount'], 2) }} ج.م</td>
                                    <td class="p-2.5 font-extrabold text-red-700 dark:text-red-300">{{ number_format($inst['remaining'], 2) }} ج.م</td>
                                    <td class="p-2.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300">
                                            🚨 متأخر
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Group 2: ⏳ Currently Due Today --}}
        @if(!empty($todayList))
            <div class="rounded-xl border border-amber-200 dark:border-amber-900 bg-amber-50/40 dark:bg-amber-950/20 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">⏳</span>
                        <h3 class="font-extrabold text-sm text-amber-800 dark:text-amber-300 m-0">أقساط تستحق اليوم</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                        {{ count($todayList) }} قسط مستحق اليوم
                    </span>
                </div>
                <div class="overflow-x-auto rounded-lg border border-amber-200 dark:border-amber-800 bg-white dark:bg-slate-900">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-amber-100/60 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 font-bold">
                            <tr>
                                <th class="p-2.5 text-center">#</th>
                                <th class="p-2.5">تاريخ الاستحقاق</th>
                                <th class="p-2.5">قيمة القسط</th>
                                <th class="p-2.5">المدفوع منه</th>
                                <th class="p-2.5">المتبقي المطلوب</th>
                                <th class="p-2.5 text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-100 dark:divide-amber-900/40">
                            @foreach($todayList as $inst)
                                <tr>
                                    <td class="p-2.5 text-center font-bold text-slate-800 dark:text-slate-100">#{{ $inst['installment_number'] }}</td>
                                    <td class="p-2.5 text-slate-700 dark:text-slate-300 font-mono">{{ $inst['due_date'] }}</td>
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">{{ number_format($inst['amount'], 2) }} ج.م</td>
                                    <td class="p-2.5 text-emerald-600 dark:text-emerald-400">{{ number_format($inst['paid_amount'], 2) }} ج.م</td>
                                    <td class="p-2.5 font-extrabold text-amber-700 dark:text-amber-300">{{ number_format($inst['remaining'], 2) }} ج.م</td>
                                    <td class="p-2.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                            ⏳ يستحق اليوم
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Group 3: 📅 Future Installments --}}
        @if(!empty($futureList))
            <div class="rounded-xl border border-sky-200 dark:border-sky-900 bg-sky-50/40 dark:bg-sky-950/20 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📅</span>
                        <h3 class="font-extrabold text-sm text-sky-800 dark:text-sky-300 m-0">الأقساط القادمة المستحقة مستقبلاً</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                        {{ count($futureList) }} قسط قادم
                    </span>
                </div>
                <div class="overflow-x-auto rounded-lg border border-sky-200 dark:border-sky-800 bg-white dark:bg-slate-900">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-sky-100/60 dark:bg-sky-950/60 text-sky-900 dark:text-sky-200 font-bold">
                            <tr>
                                <th class="p-2.5 text-center">#</th>
                                <th class="p-2.5">تاريخ الاستحقاق</th>
                                <th class="p-2.5">قيمة القسط</th>
                                <th class="p-2.5">المدفوع منه</th>
                                <th class="p-2.5">المتبقي المطلوب</th>
                                <th class="p-2.5 text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sky-100 dark:divide-sky-900/40">
                            @foreach($futureList as $inst)
                                <tr>
                                    <td class="p-2.5 text-center font-bold text-slate-800 dark:text-slate-100">#{{ $inst['installment_number'] }}</td>
                                    <td class="p-2.5 text-slate-700 dark:text-slate-300 font-mono">{{ $inst['due_date'] }}</td>
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">{{ number_format($inst['amount'], 2) }} ج.م</td>
                                    <td class="p-2.5 text-emerald-600 dark:text-emerald-400">{{ number_format($inst['paid_amount'], 2) }} ج.م</td>
                                    <td class="p-2.5 font-bold text-sky-700 dark:text-sky-300">{{ number_format($inst['remaining'], 2) }} ج.م</td>
                                    <td class="p-2.5 text-center">
                                        @if(floatval($inst['paid_amount']) > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                                ⚠️ مدفوع جزئياً
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-900/60 dark:text-sky-300">
                                                ⏳ قادم
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

        {{-- Group 4: ✅ Fully Paid Installments History --}}
        @if(!empty($paidList))
            <div class="rounded-xl border border-emerald-200 dark:border-emerald-900 bg-emerald-50/40 dark:bg-emerald-950/20 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✅</span>
                        <h3 class="font-extrabold text-sm text-emerald-800 dark:text-emerald-300 m-0">سجل الأقساط المسددة بالكامل (أرشيف تاريخي دائم)</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                        {{ count($paidList) }} قسط مسدد
                    </span>
                </div>
                <div class="overflow-x-auto rounded-lg border border-emerald-200 dark:border-emerald-800 bg-white dark:bg-slate-900">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-emerald-100/60 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-200 font-bold">
                            <tr>
                                <th class="p-2.5 text-center">#</th>
                                <th class="p-2.5">تاريخ الاستحقاق</th>
                                <th class="p-2.5">قيمة القسط الأصلية</th>
                                <th class="p-2.5">المبلغ المسدد</th>
                                <th class="p-2.5">تاريخ السداد</th>
                                <th class="p-2.5 text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 dark:divide-emerald-900/40">
                            @foreach($paidList as $inst)
                                <tr>
                                    <td class="p-2.5 text-center font-bold text-slate-800 dark:text-slate-100">#{{ $inst['installment_number'] }}</td>
                                    <td class="p-2.5 text-slate-600 dark:text-slate-400 font-mono">{{ $inst['due_date'] }}</td>
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">{{ number_format($inst['amount'], 2) }} ج.م</td>
                                    <td class="p-2.5 font-extrabold text-emerald-600 dark:text-emerald-400">{{ number_format($inst['paid_amount'], 2) }} ج.م</td>
                                    <td class="p-2.5 text-slate-600 dark:text-slate-400 font-mono">{{ $inst['paid_date'] ?? '-' }}</td>
                                    <td class="p-2.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                                            ✅ مسدد بالكامل
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @else
        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 p-8 text-center bg-slate-50 dark:bg-slate-900">
            <span class="text-4xl block mb-2">📭</span>
            <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200 m-0">لا توجد أقساط مجدولة لهذا الطالب</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">خطة الدفع المعتمدة لا تتضمن أقساطاً أو لم يتم اختيار طالب بعد.</p>
        </div>
    @endif
</div>
