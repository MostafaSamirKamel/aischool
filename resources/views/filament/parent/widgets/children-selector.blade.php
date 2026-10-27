<div class="fi-wi-stats-overview-card relative rounded-2xl bg-white p-4 shadow-sm border border-slate-200 dark:border-slate-700 dark:bg-[#172033] transition-all">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base">
                👨‍🎓
            </span>
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                {{ __('أبنائي الطلاب (اختر لمتابعة بياناته)') }}
            </h3>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
            {{ count($children) }} {{ count($children) == 1 ? 'طالب' : 'طلاب' }}
        </span>
    </div>

    @if(count($children) > 0)
        <div class="flex flex-wrap gap-2.5">
            @foreach($children as $child)
                <button
                    type="button"
                    wire:click="selectChild({{ $child['id'] }})"
                    class="group flex items-center gap-2.5 rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200 border {{ $selectedChildId == $child['id'] ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/25 ring-2 ring-blue-500/20' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 dark:bg-slate-800/60 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800' }}"
                >
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-extrabold {{ $selectedChildId == $child['id'] ? 'bg-white/20 text-white' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' }}">
                        {{ mb_substr($child['name'], 0, 1, 'utf-8') }}
                    </span>
                    <div class="text-start leading-tight">
                        <div class="font-bold text-sm">{{ $child['name'] }}</div>
                        <div class="text-[11px] opacity-75 mt-0.5">{{ $child['class'] }}</div>
                    </div>
                    @if($selectedChildId == $child['id'])
                        <span class="text-sm font-bold ms-1">✓</span>
                    @endif
                </button>
            @endforeach
        </div>
    @else
        <div class="mt-2 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 p-3 text-amber-700 dark:text-amber-400">
            <p class="text-xs font-semibold">لا يوجد طلاب مرتبطين بهذا الحساب حالياً.</p>
        </div>
    @endif
</div>
