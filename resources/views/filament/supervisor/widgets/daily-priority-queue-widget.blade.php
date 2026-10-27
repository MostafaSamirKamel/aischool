@php
    $queue = $this->getPriorityQueue();
@endphp

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                <x-heroicon-o-queue-list class="w-5 h-5" />
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                    قائمة الأولويات الإشرافية اليومية (Daily Priority Queue)
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    العناصر التي تتطلب تدخلاً عاجلاً أو مراجعة فورية من المشرف التعليمي اليوم
                </p>
            </div>
        </div>

        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $queue->isNotEmpty() ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' }}">
            {{ $queue->count() }} عنصر قيد المتابعة
        </span>
    </div>

    @if($queue->isEmpty())
        <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
            <x-heroicon-o-check-badge class="w-8 h-8 mx-auto text-emerald-500 mb-2" />
            رائع! لا توجد مهام إشرافية عاجلة متأخرة اليوم.
        </div>
    @else
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($queue as $item)
                <div class="py-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold
                                @if($item['priority'] === 'urgent') bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400
                                @else bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 @endif">
                                {{ $item['priority'] === 'urgent' ? 'عاجل' : 'تنبيه' }}
                            </span>
                            <span class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                {{ $item['title'] }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                            <div><span class="font-semibold text-slate-700 dark:text-slate-300">المهندس:</span> {{ $item['instructor_name'] }}</div>
                            <div>•</div>
                            <div><span class="font-semibold text-slate-700 dark:text-slate-300">المجموعة:</span> {{ $item['group_name'] }}</div>
                            <div>•</div>
                            <div class="text-rose-600 dark:text-rose-400 font-medium">{{ $item['reason'] }}</div>
                        </div>

                        @if(!empty($item['existing_observation_code']))
                            <div class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                                يوجد توجيه إشرافي سابق مسجل بالكود: <span class="font-mono font-bold">{{ $item['existing_observation_code'] }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ $item['route'] }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-300 dark:hover:bg-indigo-900 transition">
                            <span>{{ $item['next_action'] }}</span>
                            <x-heroicon-m-arrow-left class="w-3.5 h-3.5" />
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
