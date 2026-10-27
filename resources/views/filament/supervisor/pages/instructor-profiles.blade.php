<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Instructor Selector Bar -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <label for="instructor-select" class="text-sm font-bold text-slate-700 dark:text-slate-300">
                    اختر المهندس:
                </label>
                <select id="instructor-select" 
                        wire:change="selectInstructor($event.target.value)" 
                        class="rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($instructorsList as $inst)
                        <option value="{{ $inst->id }}" @selected($selectedInstructorId === $inst->id)>
                            {{ $inst->user?->name ?? 'غير محدد' }} ({{ $inst->specialization ?? '—' }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if($selectedInstructorId)
                <div class="flex items-center gap-2">
                    <a href="/supervisor/supervisory-observations/create?instructor_id={{ $selectedInstructorId }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition">
                        <x-heroicon-o-plus-circle class="w-4 h-4" />
                        <span>توجيه ملاحظة إشرافية</span>
                    </a>

                    <a href="/supervisor/instructor-evaluations/create?instructor_id={{ $selectedInstructorId }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition">
                        <x-heroicon-o-academic-cap class="w-4 h-4" />
                        <span>إجراء تقييم شهري</span>
                    </a>
                </div>
            @endif
        </div>

        @if($profileData)
            <!-- Instructor Profile Hero Card -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-xl border border-indigo-900/50">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-600/30 border border-indigo-400/40 flex items-center justify-center text-white text-2xl font-bold">
                            {{ mb_substr($profileData['name'], 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">{{ $profileData['name'] }}</h2>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-300 mt-1">
                                <span>📚 التخصص: {{ $profileData['specialization'] }}</span>
                                <span>•</span>
                                <span>🏢 القسم: {{ $profileData['department'] }}</span>
                                <span>•</span>
                                <span>✉️ {{ $profileData['email'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="px-4 py-2 rounded-xl bg-indigo-500/10 border border-indigo-400/20 text-center">
                            <span class="block text-xl font-bold text-indigo-300">{{ $profileData['monitored_groups_count'] }}</span>
                            <span class="text-xs text-slate-400">مجموعات مشرف عليها</span>
                        </div>
                        <div class="px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-400/20 text-center">
                            <span class="block text-xl font-bold text-emerald-400">{{ $profileData['completed_sessions'] }}</span>
                            <span class="text-xs text-slate-400">جلسات منفذة</span>
                        </div>
                        <div class="px-4 py-2 rounded-xl bg-rose-500/10 border border-rose-400/20 text-center">
                            <span class="block text-xl font-bold text-rose-400">{{ $profileData['sessions_missing_attendance'] }}</span>
                            <span class="text-xs text-slate-400">تأخر تسجيل الحضور</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monitored Groups -->
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                    <x-heroicon-o-user-group class="w-5 h-5 text-indigo-600" />
                    المجموعات المسندة للمهندس داخل نطاق الفرع ({{ $profileData['monitored_groups_count'] }})
                </h3>

                @if($profileData['monitored_groups']->isEmpty())
                    <p class="text-sm text-slate-500">لا توجد مجموعات مسندة لهذا المهندس حالياً.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($profileData['monitored_groups'] as $grp)
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm mb-1">{{ $grp->name }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-2">{{ $grp->course?->name ?? 'مادة غير محددة' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    الجلسات المنفذة: {{ $grp->completed_sessions ?? 0 }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Supervisory Observations Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Open Observations -->
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500" />
                            الملاحظات القائمة وغير المغلقة ({{ $profileData['open_observations']->count() }})
                        </h3>
                    </div>

                    @if($profileData['open_observations']->isEmpty())
                        <div class="p-6 text-center text-sm text-emerald-600 dark:text-emerald-400">
                            لا توجد ملاحظات إشرافية مفتوحة حالياً لهذا المهندس.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($profileData['open_observations'] as $obs)
                                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-850 flex items-center justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $obs->observation_code }}</span>
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold
                                                @if($obs->status === 'waiting_supervisor_review') bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400
                                                @else bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-400 @endif">
                                                {{ $obs->status_label }}
                                            </span>
                                        </div>
                                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $obs->title }}</div>
                                    </div>
                                    <a href="/supervisor/supervisory-observations/{{ $obs->id }}" class="text-xs font-semibold text-indigo-600 hover:underline whitespace-nowrap">
                                        معاينة
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Latest Published Monthly Evaluation -->
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                        <x-heroicon-o-academic-cap class="w-5 h-5 text-indigo-600" />
                        آخر تقييم شهري رسمي منشور
                    </h3>

                    @if(!$profileData['latest_monthly_evaluation'])
                        <div class="p-6 text-center text-sm text-slate-500">
                            لم يتم نشر تقييم شهري لهذا المهندس بعد.
                        </div>
                    @else
                        @php $eval = $profileData['latest_monthly_evaluation']; @endphp
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900">
                                <div>
                                    <span class="text-xs text-slate-500">فترة التقييم:</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm mr-1">{{ $eval->evaluation_period }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500">المتوسط:</span>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500 text-white mr-1">
                                        {{ $eval->calculated_average ?? '—' }} / 5.00
                                    </span>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 text-xs space-y-1.5">
                                <div><strong class="text-emerald-600 dark:text-emerald-400">نقاط القوة:</strong> {{ Str::limit($eval->strengths, 120) }}</div>
                                <div><strong class="text-amber-600 dark:text-amber-400">جوانب التحسين:</strong> {{ Str::limit($eval->areas_for_improvement, 120) }}</div>
                                <div><strong class="text-indigo-600 dark:text-indigo-400">التوصيات:</strong> {{ Str::limit($eval->recommendations, 120) }}</div>
                            </div>

                            <div class="text-left pt-2">
                                <a href="/supervisor/instructor-evaluations/{{ $eval->id }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                                    معاينة تفاصيل التقييم الكاملة والاعتراضات ←
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
