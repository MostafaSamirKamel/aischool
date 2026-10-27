<x-filament-panels::page>
    <div dir="rtl" class="classroom-availability-container space-y-6">
        <style>
            .ca-glass-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
                transition: all 0.2s ease-in-out;
            }
            .ca-glass-card:hover {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
            }
            .ca-pill-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.5rem 0.95rem;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 700;
                transition: all 0.15s ease;
                cursor: pointer;
                border: 1px solid transparent;
            }
            .ca-pill-btn.active {
                background: #364FAC;
                color: #ffffff;
                border-color: #2b3e8a;
                box-shadow: 0 2px 4px rgba(54, 79, 172, 0.3);
            }
            .ca-pill-btn:not(.active) {
                background: #f1f5f9;
                color: #475569;
                border-color: #e2e8f0;
            }
            .ca-pill-btn:not(.active):hover {
                background: #e2e8f0;
                color: #1e293b;
            }
            .ca-timeline-bar {
                height: 14px;
                border-radius: 9999px;
                overflow: hidden;
                background: #f1f5f9;
                display: flex;
                width: 100%;
                border: 1px solid #cbd5e1;
            }
            .ca-slot-avail {
                background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
                transition: opacity 0.2s;
            }
            .ca-slot-avail:hover {
                opacity: 0.85;
            }
            .ca-slot-occ {
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                transition: opacity 0.2s;
            }
            .ca-slot-occ:hover {
                opacity: 0.85;
            }
            .ca-slot-unavail {
                background: #94a3b8;
            }
            .ca-badge-avail {
                background: #dcfce7;
                color: #15803d;
                border: 1px solid #bbf7d0;
            }
            .ca-badge-occ {
                background: #ffedd5;
                color: #c2410c;
                border: 1px solid #fed7aa;
            }
            .ca-badge-unavail {
                background: #f1f5f9;
                color: #64748b;
                border: 1px solid #cbd5e1;
            }
            .ca-badge-conflict {
                background: #fee2e2;
                color: #b91c1c;
                border: 1px solid #fca5a5;
            }
            .ca-legend-dot {
                width: 12px;
                height: 12px;
                border-radius: 9999px;
                display: inline-block;
            }
            .ca-table-cell-day {
                min-width: 130px;
                vertical-align: top;
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }
        </style>

        {{-- Header Controls & Filter Panel --}}
        <div class="ca-glass-card p-5 space-y-4">
            {{-- Top Row: Date Navigator & View Switcher --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                {{-- Date Navigation --}}
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="changeDate(1)" 
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition"
                        title="اليوم التالي"
                    >
                        <x-heroicon-m-chevron-right class="w-5 h-5" />
                    </button>

                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 shadow-sm">
                        <x-heroicon-m-calendar-days class="w-5 h-5 text-indigo-600" />
                        <input 
                            type="date" 
                            wire:model.live="selectedDate" 
                            class="bg-transparent border-none p-0 text-sm font-bold text-gray-800 focus:ring-0 cursor-pointer"
                        />
                    </div>

                    <button 
                        type="button" 
                        wire:click="changeDate(-1)" 
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition"
                        title="اليوم السابق"
                    >
                        <x-heroicon-m-chevron-left class="w-5 h-5" />
                    </button>

                    <button 
                        type="button" 
                        wire:click="changeDate(0)" 
                        wire:click="$set('selectedDate', '{{ now()->format('Y-m-d') }}')"
                        class="text-xs font-bold px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition"
                    >
                        اليوم الحالي
                    </button>
                </div>

                {{-- View Mode Switcher (Daily vs Weekly) --}}
                <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200 shadow-inner">
                    <button 
                        type="button" 
                        wire:click="setViewMode('daily')" 
                        class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-bold transition {{ $viewMode === 'daily' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                    >
                        <x-heroicon-m-clock class="w-4 h-4" />
                        <span>العرض اليومي المفصل</span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="setViewMode('weekly')" 
                        class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-bold transition {{ $viewMode === 'weekly' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                    >
                        <x-heroicon-m-table-cells class="w-4 h-4" />
                        <span>الجدول الأسبوعي الشامل</span>
                    </button>
                </div>
            </div>

            {{-- Quick Day-of-Week Pills --}}
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100">
                <span class="text-xs font-bold text-gray-500 ml-2">أيام الأسبوع:</span>
                @php
                    $currentDayOfWeek = strtolower(\Carbon\Carbon::parse($selectedDate)->format('l'));
                    $days = [
                        'saturday' => 'السبت',
                        'sunday' => 'الأحد',
                        'monday' => 'الاثنين',
                        'tuesday' => 'الثلاثاء',
                        'wednesday' => 'الأربعاء',
                        'thursday' => 'الخميس',
                        'friday' => 'الجمعة',
                    ];
                @endphp
                @foreach($days as $key => $name)
                    <button 
                        type="button" 
                        wire:click="setDayOfWeek('{{ $key }}')" 
                        class="ca-pill-btn {{ $currentDayOfWeek === $key ? 'active' : '' }}"
                    >
                        {{ $name }}
                    </button>
                @endforeach
            </div>

            {{-- Search & Filter Controls --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-3 border-t border-gray-100">
                {{-- Search Classroom --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">بحث عن قاعة</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="searchQuery" 
                            placeholder="ابحث باسم القاعة أو موقعها..." 
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 pr-8 pl-3"
                        />
                        <x-heroicon-m-magnifying-glass class="w-4 h-4 text-gray-400 absolute right-2.5 top-2.5 pointer-events-none" />
                    </div>
                </div>

                {{-- Filter by Specific Classroom --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">تحديد قاعة معينة</label>
                    <select 
                        wire:model.live="selectedClassroomId" 
                        class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5"
                    >
                        <option value="">جميع القاعات المتاحة</option>
                        @foreach($this->classroomsList as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter by Occupancy Status --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">تصفية حسب حالة التوفر</label>
                    <select 
                        wire:model.live="filterStatus" 
                        class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5"
                    >
                        <option value="all">كل الحالات</option>
                        <option value="with_available">قاعات بها أوقات متاحة</option>
                        <option value="fully_available">قاعات شاغرة بالكامل (100% متاحة)</option>
                        <option value="has_bookings">قاعات بها مجموعات اليوم</option>
                    </select>
                </div>

                {{-- Tenant / Branch Filter for Super Admin --}}
                @if(auth()->user()->is_super_admin && count($this->tenantsList) > 0)
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">الفرع / المدرسة</label>
                        <select 
                            wire:model.live="selectedTenantId" 
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5"
                        >
                            <option value="">جميع الفروع</option>
                            @foreach($this->tenantsList as $tId => $tName)
                                <option value="{{ $tId }}">{{ $tName }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="flex items-end">
                        @if($searchQuery || $selectedClassroomId || $filterStatus !== 'all')
                            <button 
                                type="button" 
                                wire:click="resetFilters" 
                                class="text-xs font-bold text-red-600 hover:text-red-700 p-2 bg-red-50 hover:bg-red-100 rounded-lg transition border border-red-200 w-full text-center"
                            >
                                ✕ إعادة ضبط التصفية
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Legend & Operating Window Notice --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-2 text-xs font-medium text-gray-600">
            <div class="flex flex-wrap items-center gap-4">
                <span class="font-bold text-gray-700">دليل الألوان:</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="ca-legend-dot bg-green-500"></span>
                    <strong class="text-green-700 font-bold">متاح للحجز (شاغر)</strong>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="ca-legend-dot bg-orange-500"></span>
                    <strong class="text-orange-700 font-bold">مشغول بمجموعة دراسية</strong>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="ca-legend-dot bg-gray-400"></span>
                    <strong class="text-gray-600 font-bold">صيانة / غير متاح</strong>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="ca-legend-dot bg-red-500"></span>
                    <strong class="text-red-700 font-bold">تنبيه تعارض مواعيد</strong>
                </span>
            </div>

            <div class="bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1 rounded-full text-xs font-bold">
                ⏰ ساعات العمل التشغيلية للقاعات: 09:00 ص – 09:00 م (12 ساعة يومياً)
            </div>
        </div>

        {{-- VIEW MODE 1: DAILY VIEW --}}
        @if($viewMode === 'daily')
            @php
                $classroomsData = $this->dailyAvailability;
                $totalClassrooms = count($classroomsData);
                $fullyAvailableCount = count(array_filter($classroomsData, fn($c) => $c['is_active'] && empty($c['occupied_slots'])));
                $occupiedCount = count(array_filter($classroomsData, fn($c) => !empty($c['occupied_slots'])));
                $totalGroupsToday = array_sum(array_map(fn($c) => count($c['occupied_slots']), $classroomsData));
            @endphp

            {{-- Summary Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="ca-glass-card p-4 border-l-4 border-l-indigo-600 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-bold">إجمالي القاعات المعروضة</div>
                        <div class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalClassrooms }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        🏛️
                    </div>
                </div>

                <div class="ca-glass-card p-4 border-l-4 border-l-green-500 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-bold">قاعات شاغرة طوال اليوم</div>
                        <div class="text-2xl font-black text-green-600 mt-0.5">{{ $fullyAvailableCount }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center font-bold">
                        🟢
                    </div>
                </div>

                <div class="ca-glass-card p-4 border-l-4 border-l-orange-500 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-bold">قاعات بها مجموعات اليوم</div>
                        <div class="text-2xl font-black text-orange-600 mt-0.5">{{ $occupiedCount }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold">
                        🟠
                    </div>
                </div>

                <div class="ca-glass-card p-4 border-l-4 border-l-blue-500 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-bold">إجمالي الحصص المجدولة اليوم</div>
                        <div class="text-2xl font-black text-blue-600 mt-0.5">{{ $totalGroupsToday }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        📚
                    </div>
                </div>
            </div>

            {{-- Classroom Cards Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @forelse($classroomsData as $item)
                    <div class="ca-glass-card p-5 flex flex-col justify-between space-y-4">
                        {{-- Card Header --}}
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-black text-gray-900">{{ $item['classroom_name'] }}</h3>
                                        @if($item['is_active'])
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                                نشطة
                                            </span>
                                        @elseif($item['status'] === 'maintenance')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                تحت الصيانة
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                                غير نشطة
                                            </span>
                                        @endif

                                        @if($item['stats']['has_conflicts'] ?? false)
                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-300 animate-pulse">
                                                ⚠️ تعارض مواعيد
                                            </span>
                                        @endif
                                    </div>

                                    @if(!empty($item['location']))
                                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                            <x-heroicon-m-map-pin class="w-3.5 h-3.5 text-gray-400" />
                                            <span>{{ $item['location'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="text-left bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-700">
                                    <span>👥 السعة القصوى:</span>
                                    <span class="text-indigo-700 font-black text-sm mr-1">{{ $item['capacity'] }} طالب</span>
                                </div>
                            </div>

                            {{-- Visual Proportional Day Timeline Bar --}}
                            @if($item['is_active'])
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-[11px] font-bold text-gray-500 mb-1">
                                        <span>09:00 ص</span>
                                        <span class="text-gray-400">مخطط إشغال اليوم الزمني</span>
                                        <span>09:00 م</span>
                                    </div>
                                    <div class="ca-timeline-bar" title="مخطط توفر القاعة على مدار اليوم">
                                        @php
                                            $totalWorkMins = 12 * 60; // 720 minutes
                                        @endphp
                                        @foreach($item['timeline'] as $block)
                                            @php
                                                $blockMins = \Carbon\Carbon::parse($block['end_time'])->diffInMinutes(\Carbon\Carbon::parse($block['start_time']));
                                                $pct = round(($blockMins / $totalWorkMins) * 100, 1);
                                                $cls = match($block['status']) {
                                                    'available' => 'ca-slot-avail',
                                                    'occupied' => 'ca-slot-occ',
                                                    default => 'ca-slot-unavail',
                                                };
                                            @endphp
                                            <div 
                                                class="{{ $cls }}" 
                                                style="width: {{ $pct }}%;" 
                                                title="{{ $block['start_time'] }} - {{ $block['end_time'] }} ({{ $block['label'] }})"
                                            ></div>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mt-1 font-medium">
                                        <span>مشغول: <strong class="text-orange-600 font-bold">{{ $item['stats']['occupied_hours'] }} ساعة</strong> ({{ $item['stats']['occupied_percentage'] }}%)</span>
                                        <span>متاح: <strong class="text-green-600 font-bold">{{ $item['stats']['available_hours'] }} ساعة</strong></span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Chronological Periods Breakdown --}}
                        <div class="space-y-2.5 pt-3 border-t border-gray-100 flex-1">
                            <h4 class="text-xs font-black text-gray-700 uppercase tracking-wider mb-2">الفترات الزمنية المتاحة والمشغولة:</h4>

                            @if(!$item['is_active'])
                                <div class="ca-badge-unavail p-3 rounded-xl text-center text-xs font-bold">
                                    {{ $item['status_label'] }} — لا يمكن حجز أو جدولة مجموعات في هذه القاعة حالياً.
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($item['timeline'] as $slot)
                                        @if($slot['status'] === 'available')
                                            {{-- Available Period (GREEN) --}}
                                            <div class="ca-badge-avail p-3 rounded-xl flex items-center justify-between text-xs transition hover:bg-green-100">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span>
                                                    <strong class="font-black text-green-900 text-sm">متاح للحجز: {{ $slot['start_time'] }} – {{ $slot['end_time'] }}</strong>
                                                    <span class="text-green-700">({{ $slot['duration_formatted'] }})</span>
                                                </div>
                                                <div class="text-[11px] text-green-800 font-bold bg-green-200/60 px-2 py-0.5 rounded-md">
                                                    جاهزة لاستقبال مجموعة جديدة
                                                </div>
                                            </div>
                                        @else
                                            {{-- Occupied Period (ORANGE) --}}
                                            <div class="{{ ($slot['has_conflict'] ?? false) ? 'ca-badge-conflict' : 'ca-badge-occ' }} p-3 rounded-xl space-y-1.5 transition">
                                                <div class="flex items-center justify-between text-xs">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block"></span>
                                                        <strong class="font-black text-orange-950 text-sm">{{ $slot['start_time'] }} – {{ $slot['end_time'] }}</strong>
                                                        <span class="text-orange-800">({{ $slot['duration_formatted'] }})</span>
                                                    </div>

                                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-white/70 text-orange-900 border border-orange-200 shadow-2xs">
                                                        {{ $slot['source'] === 'session_schedule' ? 'حصّة مؤكدة' : 'جدول أسبوعي' }}
                                                    </span>
                                                </div>

                                                <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-xs border-t border-orange-200/50">
                                                    <div>
                                                        <span class="font-black text-gray-900 text-sm">{{ $slot['label'] }}</span>
                                                        <span class="text-gray-500 mr-1">({{ $slot['course'] }})</span>
                                                    </div>

                                                    <div class="flex items-center gap-3 text-xs font-medium text-gray-600">
                                                        <span>👨‍🏫 {{ $slot['instructor'] }}</span>
                                                        <span class="font-bold text-gray-800 bg-white/90 px-2 py-0.5 rounded border border-gray-200">
                                                            الطلاب: {{ $slot['students_count'] }} / {{ $slot['capacity'] }}
                                                            @if($slot['available_seats'] > 0)
                                                                <small class="text-green-600 font-bold">({{ $slot['available_seats'] }} مقعد شاغر)</small>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>

                                                @if($slot['has_conflict'] ?? false)
                                                    <div class="text-[11px] font-bold text-red-700 bg-red-100 p-1.5 rounded mt-1">
                                                        ⚠️ تنبيه: يوجد تداخل زمني مع مجموعة أخرى في نفس القاعة بنفس التوقيت!
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Card Footer Guidance --}}
                        @if($item['is_active'])
                            <div class="pt-3 border-t border-gray-100 text-xs text-gray-500 flex items-center justify-between">
                                <span>💡 سعة القاعة تتيح إضافة مجموعة جديدة حتى <strong>{{ $item['capacity'] }} طالب</strong> في الأوقات الخضراء.</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-2 ca-glass-card p-12 text-center text-gray-500 space-y-3">
                        <div class="text-4xl">🏛️</div>
                        <h3 class="text-base font-bold text-gray-800">لا توجد قاعات تطابق معايير التصفية</h3>
                        <p class="text-xs text-gray-500">يرجى تعديل معايير البحث أو اختيار فرع/قاعة مختلفة من الشريط العلوي.</p>
                    </div>
                @endforelse
            </div>

        {{-- VIEW MODE 2: WEEKLY TIMETABLE MATRIX --}}
        @else
            @php
                $weeklyData = $this->weeklyAvailability;
            @endphp

            <div class="ca-glass-card overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-m-calendar-days class="w-5 h-5 text-indigo-600" />
                        <h3 class="text-sm font-black text-gray-900">
                            الجدول الأسبوعي الشامل للقاعات: من {{ \Carbon\Carbon::parse($weeklyData['week_start'])->format('Y/m/d') }} إلى {{ \Carbon\Carbon::parse($weeklyData['week_end'])->format('Y/m/d') }}
                        </h3>
                    </div>
                    <div class="text-xs text-gray-500 font-bold">
                        اضغط على أي يوم للانتقال مباشرة لعرض تفاصيل اليوم الكاملة
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 text-xs font-black border-b border-gray-200">
                                <th class="p-3 w-44 sticky right-0 bg-gray-100 z-10 border-l border-gray-200">القاعة</th>
                                @foreach($weeklyData['days_header'] as $hDay)
                                    <th class="p-3 text-center border-l border-gray-200 min-w-[140px] cursor-pointer hover:bg-gray-200 transition" wire:click="selectDate('{{ $hDay['date'] }}')">
                                        <div class="text-indigo-800">{{ $hDay['day_name_ar'] }}</div>
                                        <div class="text-[11px] font-normal text-gray-500">{{ $hDay['formatted'] }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-xs">
                            @forelse($weeklyData['classrooms'] as $wClassroom)
                                <tr class="hover:bg-gray-50/70 transition">
                                    {{-- Classroom Name & Capacity --}}
                                    <td class="p-3 font-bold text-gray-900 sticky right-0 bg-white border-l border-gray-200 shadow-2xs">
                                        <div class="text-sm font-black">{{ $wClassroom['classroom_name'] }}</div>
                                        <div class="text-[11px] text-gray-500 font-normal">سعة: {{ $wClassroom['capacity'] }} طالب</div>
                                        @if(!$wClassroom['is_active'])
                                            <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] bg-gray-200 text-gray-700">غير نشطة</span>
                                        @endif
                                    </td>

                                    {{-- Day Cells --}}
                                    @foreach($weeklyData['days_header'] as $hDay)
                                        @php
                                            $dayData = $wClassroom['days'][$hDay['day_of_week']] ?? null;
                                        @endphp
                                        <td class="ca-table-cell-day border-l border-gray-100">
                                            @if(!$wClassroom['is_active'])
                                                <div class="p-1.5 rounded bg-gray-100 text-gray-400 text-center text-[10px] font-bold">
                                                    مغلقة
                                                </div>
                                            @elseif(empty($dayData['occupied_slots']))
                                                <div 
                                                    wire:click="selectDate('{{ $hDay['date'] }}')" 
                                                    class="p-2 rounded-lg bg-green-50 border border-green-200 text-green-700 text-center font-bold cursor-pointer hover:bg-green-100 transition"
                                                    title="القاعة شاغرة طوال هذا اليوم - اضغط للتفاصيل"
                                                >
                                                    <span class="text-green-600 block text-xs">🟢 شاغرة بالكامل</span>
                                                    <span class="text-[10px] text-green-800 font-normal">09:00 ص – 09:00 م</span>
                                                </div>
                                            @else
                                                <div 
                                                    wire:click="selectDate('{{ $hDay['date'] }}')"
                                                    class="space-y-1 cursor-pointer"
                                                    title="اضغط لعرض تفاصيل هذا اليوم"
                                                >
                                                    @foreach($dayData['occupied_slots'] as $occ)
                                                        <div class="p-1.5 rounded bg-orange-100 border border-orange-200 text-orange-950 font-bold hover:bg-orange-200 transition">
                                                            <div class="text-[11px] font-black text-orange-900">{{ $occ['start_time'] }} – {{ $occ['end_time'] }}</div>
                                                            <div class="truncate text-[10px] font-medium text-gray-800">{{ $occ['group_name'] }}</div>
                                                        </div>
                                                    @endforeach

                                                    <div class="text-[10px] text-green-700 font-bold text-center pt-0.5">
                                                        ✓ متاح {{ $dayData['stats']['available_hours'] }} ساعة
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-500 font-bold">
                                        لا توجد بيانات قاعات متاحة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
