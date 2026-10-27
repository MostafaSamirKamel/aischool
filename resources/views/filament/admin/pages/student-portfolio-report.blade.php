<x-filament-panels::page>
    @include('filament.shared.report-theme')

    <div class="report-shell">
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @if(auth()->user()?->hasRole('super_admin'))
                    <div>
                        <label class="block text-sm font-bold mb-2 text-gray-700 dark:text-gray-200">الفرع</label>
                        <select wire:model.live="tenantFilter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                            <option value="">كل الفروع</option>
                            @foreach($this->getTenantOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-700 dark:text-gray-200">من تاريخ</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-700 dark:text-gray-200">إلى تاريخ</label>
                    <input type="date" wire:model.live="dateTo" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-700 dark:text-gray-200">اختيار الطالب</label>
                    <select wire:model.live="studentId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                        <option value="">اختر الطالب</option>
                        @foreach($this->getStudentsOptions() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if(!empty($portfolio['student_name']))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-gray-100">{{ $portfolio['student_name'] }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">عرض شامل لكل أنشطة الطالب</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="exportPortfolio('csv')" class="px-3 py-2 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-900">تصدير Excel</button>
                        <button type="button" wire:click="exportPortfolio('pdf')" class="px-3 py-2 text-xs font-bold rounded-lg bg-rose-100 text-rose-900">تصدير PDF</button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-center">
                    <p class="text-sm text-blue-700 dark:text-blue-200">المجموعات</p>
                    <p class="text-2xl font-black text-blue-800 dark:text-blue-100">{{ $portfolio['overview']['groups_count'] }}</p>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-center">
                    <p class="text-sm text-emerald-700 dark:text-emerald-200">الحضور</p>
                    <p class="text-2xl font-black text-emerald-800 dark:text-emerald-100">{{ $portfolio['overview']['attendance_rate'] }}%</p>
                </div>
                <div class="p-4 rounded-xl bg-violet-50 dark:bg-violet-900/30 text-center">
                    <p class="text-sm text-violet-700 dark:text-violet-200">الامتحانات</p>
                    <p class="text-2xl font-black text-violet-800 dark:text-violet-100">{{ $portfolio['overview']['exams_count'] }}</p>
                </div>
                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-center">
                    <p class="text-sm text-amber-700 dark:text-amber-200">الواجبات</p>
                    <p class="text-2xl font-black text-amber-800 dark:text-amber-100">{{ $portfolio['overview']['assignments_count'] }}</p>
                </div>
                <div class="p-4 rounded-xl bg-green-50 dark:bg-green-900/30 text-center">
                    <p class="text-sm text-green-700 dark:text-green-200">إجمالي المدفوع</p>
                    <p class="text-2xl font-black text-green-800 dark:text-green-100">{{ number_format((float) $portfolio['overview']['total_paid'], 2) }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <h3 class="text-xl font-black mb-4 text-gray-900 dark:text-gray-100">المجموعات والكورسات</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="py-3 text-right">المجموعة</th>
                                <th class="py-3 text-right">الكورس</th>
                                <th class="py-3 text-right">الحالة</th>
                                <th class="py-3 text-right">التقدم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($portfolio['groups'] as $group)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-3 text-right">{{ $group['group_name'] }}</td>
                                    <td class="py-3 text-right">{{ $group['course_name'] }}</td>
                                    <td class="py-3 text-right">{{ $group['status'] }}</td>
                                    <td class="py-3 text-right">{{ $group['progress'] }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-500">لا توجد بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <h3 class="text-xl font-black mb-4 text-gray-900 dark:text-gray-100">آخر المدفوعات</h3>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @forelse($portfolio['payments'] as $payment)
                            <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900 flex items-center justify-between">
                                <div>
                                    <p class="font-bold">{{ number_format((float) $payment['amount'], 2) }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment['method'] }} - {{ $payment['date'] }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900">{{ $payment['status'] }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">لا توجد مدفوعات</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <h3 class="text-xl font-black mb-4 text-gray-900 dark:text-gray-100">آخر الحضور</h3>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @forelse($portfolio['attendance'] as $attendance)
                            <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900 flex items-center justify-between">
                                <div>
                                    <p class="font-bold">{{ $attendance['group'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $attendance['date'] }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900">{{ $attendance['status'] }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">لا توجد سجلات حضور</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <h3 class="text-xl font-black mb-4 text-gray-900 dark:text-gray-100">نتائج الامتحانات</h3>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @forelse($portfolio['exams'] as $exam)
                            <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <p class="font-bold">{{ $exam['exam'] }}</p>
                                <p class="text-xs text-gray-500">{{ $exam['score'] }} / {{ $exam['total'] }} ({{ $exam['percentage'] }}%)</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">لا توجد نتائج امتحانات</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <h3 class="text-xl font-black mb-4 text-gray-900 dark:text-gray-100">الواجبات</h3>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @forelse($portfolio['assignments'] as $assignment)
                            <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <p class="font-bold">{{ $assignment['assignment'] }}</p>
                                <p class="text-xs text-gray-500">{{ $assignment['submitted_at'] }} - {{ $assignment['status'] }}</p>
                                @if($assignment['score'] !== null)
                                    <p class="text-xs text-gray-500">{{ $assignment['score'] }} / {{ $assignment['max_score'] }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">لا توجد واجبات</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>

    </div>
</x-filament-panels::page>
