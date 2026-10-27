<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Group Selector Bar -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <label for="group-select" class="text-sm font-bold text-slate-700 dark:text-slate-300">
                    اختر المجموعة:
                </label>
                <select id="group-select" 
                        wire:change="selectGroup($event.target.value)" 
                        class="rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($groupsList as $grp)
                        <option value="{{ $grp->id }}" @selected($selectedGroupId === $grp->id)>
                            {{ $grp->name }} ({{ $grp->course?->name ?? 'مادة غير محددة' }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if($selectedGroupId)
                <div class="flex items-center gap-2">
                    <a href="/supervisor/supervisory-observations/create?group_id={{ $selectedGroupId }}&instructor_id={{ $groupProfileData['instructor']?->id }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition">
                        <x-heroicon-o-plus-circle class="w-4 h-4" />
                        <span>إنشاء ملاحظة لهذه المجموعة</span>
                    </a>
                </div>
            @endif
        </div>

        @if($groupProfileData)
            <!-- Group Header Card -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-xl border border-indigo-900/50">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 mb-2">
                            {{ $groupProfileData['course']?->name ?? 'كورس غير محدد' }}
                        </span>
                        <h2 class="text-2xl font-bold">{{ $groupProfileData['group']->name }}</h2>
                        <div class="text-sm text-slate-300 mt-1">
                            👨‍🏫 المهندس المحاضر: <span class="font-semibold text-white">{{ $groupProfileData['instructor']?->user?->name ?? 'غير محدد' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="px-4 py-2 rounded-xl bg-indigo-500/10 border border-indigo-400/20 text-center">
                            <span class="block text-xl font-bold text-indigo-300">{{ $groupProfileData['students_count'] }}</span>
                            <span class="text-xs text-slate-400">طالب مقيد</span>
                        </div>
                        <div class="px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-400/20 text-center">
                            <span class="block text-xl font-bold text-emerald-400">{{ $groupProfileData['completed_sessions_count'] }}</span>
                            <span class="text-xs text-slate-400">جلسات منفذة</span>
                        </div>
                        <div class="px-4 py-2 rounded-xl bg-rose-500/10 border border-rose-400/20 text-center">
                            <span class="block text-xl font-bold text-rose-400">{{ $groupProfileData['missing_attendance_count'] }}</span>
                            <span class="text-xs text-slate-400">جلسات بلا حضور</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Group Educational Workload & Observations -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Academic Progress Card -->
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <x-heroicon-o-academic-cap class="w-5 h-5 text-indigo-600" />
                        الأنشطة الأكاديمية والواجبات للمجموعة
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-center">
                            <span class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $groupProfileData['assignments_count'] }}</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-1">واجبات وتكليفات مطروحة</span>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-center">
                            <span class="text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $groupProfileData['exams_count'] }}</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-1">امتحانات وتقييمات</span>
                        </div>
                    </div>
                </div>

                <!-- Group Observations Card -->
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-indigo-600" />
                        الملاحظات الإشرافية المرتبطة بالمجموعة ({{ $groupProfileData['observations']->count() }})
                    </h3>

                    @if($groupProfileData['observations']->isEmpty())
                        <div class="p-6 text-center text-sm text-slate-500">
                            لا توجد ملاحظات إشرافية مسجلة على هذه المجموعة.
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($groupProfileData['observations'] as $obs)
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="font-bold font-mono text-indigo-600">{{ $obs->observation_code }}</span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200 mr-2">{{ $obs->title }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded font-semibold
                                        @if($obs->status === 'completed') bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400
                                        @else bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 @endif">
                                        {{ $obs->status_label }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
