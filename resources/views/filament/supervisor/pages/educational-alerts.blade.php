<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Banner -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-xl border border-indigo-900/50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <x-heroicon-o-bell-alert class="w-6 h-6 text-amber-400" />
                        رادار التنبيهات التعليمية للفرع
                    </h2>
                    <p class="text-sm text-slate-300 mt-1">
                        تجميع استرشادي ذكي فوري للأحداث الأكاديمية التي تتطلب متابعة المشرف (للقراءة والمعاينة دون إنشاء تلقائي غير مرغوب).
                    </p>
                </div>
                <div class="text-left">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                        {{ $alerts->count() }} تنبيه نشط
                    </span>
                </div>
            </div>
        </div>

        @if($alerts->isEmpty())
            <div class="p-12 text-center rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-500">
                    <x-heroicon-o-check-circle class="w-10 h-10" />
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">الفرع في حالة ممتازة ومستقرة!</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    لا توجد تنبيهات متأخرة أو درجات منخفضة غير معالجة في المجموعات المسندة حالياً.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($alerts as $alert)
                    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                        @if($alert['badge'] === 'danger') bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800
                                        @elseif($alert['badge'] === 'warning') bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200 dark:border-amber-800
                                        @else bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400 border border-sky-200 dark:border-sky-800 @endif">
                                        {{ $alert['category_label'] }}
                                    </span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">
                                        {{ $alert['date'] }}
                                    </span>
                                </div>

                                <h4 class="font-bold text-slate-900 dark:text-slate-100 text-base mb-1">
                                    {{ $alert['title'] }}
                                </h4>

                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-3">
                                    {{ $alert['description'] }}
                                </p>

                                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                    <div><span class="font-semibold text-slate-700 dark:text-slate-300">المهندس:</span> {{ $alert['instructor_name'] }}</div>
                                    <div><span class="font-semibold text-slate-700 dark:text-slate-300">المجموعة:</span> {{ $alert['group_name'] }}</div>
                                </div>
                            </div>

                            <div>
                                <a href="{{ $alert['action_url'] }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:text-indigo-400 dark:hover:bg-indigo-900 transition-colors whitespace-nowrap">
                                    <span>{{ $alert['action_label'] }}</span>
                                    <x-heroicon-m-arrow-left class="w-3.5 h-3.5" />
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
