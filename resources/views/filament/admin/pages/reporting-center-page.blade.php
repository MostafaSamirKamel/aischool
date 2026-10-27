<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">

        {{-- TOP HEADER & ACTIONS BAR --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="p-2 rounded-xl bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400">
                        <x-heroicon-o-presentation-chart-line class="w-6 h-6 inline" />
                    </span>
                    مركز التقارير والاستخبارات التشغيلية والمالية
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    منصة الرقابة المركزية المعتمدة لمدارس الذكاء الاصطناعي — بيانات موثقة ومطابقة محاسبياً وأكاديمياً
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Save Configuration Button --}}
                <button type="button" wire:click="$set('showSaveReportModal', true)" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition">
                    <x-heroicon-o-bookmark class="w-4 h-4" />
                    حفظ الفلترة الحالية
                </button>

                {{-- Export Dropdown / Actions --}}
                <div class="inline-flex rounded-xl shadow-sm">
                    <button type="button" wire:click="exportReport('xlsx')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 rounded-r-xl border border-emerald-200 dark:border-emerald-800 transition">
                        <x-heroicon-o-table-cells class="w-4 h-4" />
                        Excel
                    </button>
                    <button type="button" wire:click="exportReport('pdf')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 border-t border-b border-rose-200 dark:border-rose-800 transition">
                        <x-heroicon-o-document-text class="w-4 h-4" />
                        PDF
                    </button>
                    <button type="button" wire:click="exportReport('csv')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/60 rounded-l-xl border border-sky-200 dark:border-sky-800 transition">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                        CSV
                    </button>
                </div>

                {{-- Refresh Button --}}
                <button type="button" wire:click="loadCurrentReport" class="p-2 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="تحديث البيانات">
                    <x-heroicon-o-arrow-path class="w-5 h-5" />
                </button>
            </div>
        </div>

        {{-- GLOBAL FILTER SYSTEM --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <x-heroicon-o-funnel class="w-4 h-4 text-primary-500" />
                    محددات الفلترة الزمنية والتشغيلية الموحدة
                </span>
                <button type="button" wire:click="resetFilters" class="text-xs text-gray-500 hover:text-rose-500 transition">
                    إعادة ضبط الفلاتر
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Period Preset --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الفترة الزمنية</label>
                    <select wire:model.live="periodPreset" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        <option value="today">اليوم</option>
                        <option value="yesterday">أمس</option>
                        <option value="this_week">هذا الأسبوع</option>
                        <option value="last_week">الأسبوع الماضي</option>
                        <option value="this_month">هذا الشهر</option>
                        <option value="last_month">الشهر الماضي</option>
                        <option value="this_quarter">هذا الربع</option>
                        <option value="this_year">هذا العام</option>
                        <option value="custom">فترة مخصصة</option>
                    </select>
                </div>

                {{-- From Date --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">من تاريخ</label>
                    <input type="date" wire:model="dateFrom" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500" />
                </div>

                {{-- To Date --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">إلى تاريخ</label>
                    <input type="date" wire:model="dateTo" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500" />
                </div>

                {{-- As Of Date --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تاريخ الأرصدة (As-Of-Date)</label>
                    <input type="date" wire:model="asOfDate" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500" />
                </div>

                {{-- Branch Filter --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الفرع</label>
                    <select wire:model="selectedBranchId" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        @if(auth()->user()->is_super_admin || auth()->user()->hasRole('super_admin') || is_null(auth()->user()->tenant_id))
                            <option value="">كافة الفروع المتاحة</option>
                        @endif
                        @foreach($this->availableBranches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Course Filter --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الكورس / المادة</label>
                    <select wire:model="selectedCourseId" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        <option value="">كافة الكورسات</option>
                        @foreach($this->availableCourses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Group Filter --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">المجموعة الدراسية</label>
                    <select wire:model="selectedGroupId" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        <option value="">كافة المجموعات</option>
                        @foreach($this->availableGroups as $grp)
                            <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Vault Filter --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الخزينة</label>
                    <select wire:model="selectedVaultId" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        <option value="">كافة الخزائن</option>
                        @foreach($this->availableVaults as $vault)
                            <option value="{{ $vault->id }}">{{ $vault->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" wire:click="applyFilters" class="inline-flex items-center gap-2 px-5 py-2 text-xs font-black text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow transition">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    تطبيق الفلترة والبحث
                </button>
            </div>
        </div>

        {{-- NAVIGATION TABS BAR --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-1 space-x-reverse overflow-x-auto pb-2 scrollbar-none" aria-label="Tabs">
                @php
                    $tabs = [
                        'overview' => ['name' => 'النظرة التنفيذية', 'icon' => 'heroicon-o-home'],
                        'growth' => ['name' => 'نمو الطلاب', 'icon' => 'heroicon-o-user-plus'],
                        'enrollments' => ['name' => 'الاشتراكات والتجديد', 'icon' => 'heroicon-o-document-check'],
                        'collections' => ['name' => 'التحصيلات النقدية', 'icon' => 'heroicon-o-banknotes'],
                        'installments' => ['name' => 'الأقساط وإعمار الديون', 'icon' => 'heroicon-o-clock'],
                        'vaults' => ['name' => 'الخزائن وحركة النقد', 'icon' => 'heroicon-o-wallet'],
                        'pnl' => ['name' => 'الإيرادات وقائمة الدخل', 'icon' => 'heroicon-o-calculator'],
                        'salaries' => ['name' => 'مرتبات المهندسين', 'icon' => 'heroicon-o-identification'],
                        'cancellations' => ['name' => 'الإلغاءات والتسويات', 'icon' => 'heroicon-o-arrow-uturn-left'],
                        'academic' => ['name' => 'التحليل الأكاديمي', 'icon' => 'heroicon-o-academic-cap'],
                        'supervision' => ['name' => 'الإشراف التربوي', 'icon' => 'heroicon-o-eye'],
                        'employees' => ['name' => 'عمليات الموظفين', 'icon' => 'heroicon-o-briefcase'],
                        'branches' => ['name' => 'مقارنة الفروع', 'icon' => 'heroicon-o-building-office-2'],
                        'alerts' => ['name' => 'التنبيهات الإدارية', 'icon' => 'heroicon-o-bell-alert'],
                        'saved' => ['name' => 'التقارير المحفوظة', 'icon' => 'heroicon-o-bookmark'],
                        'settings' => ['name' => 'إعدادات التقارير', 'icon' => 'heroicon-o-cog-6-tooth'],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <button type="button" wire:click="setSection('{{ $key }}')" class="whitespace-nowrap px-3.5 py-2 text-xs font-bold rounded-xl flex items-center gap-1.5 transition {{ $activeSection === $key ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        <x-dynamic-component :component="$tab['icon']" class="w-4 h-4 inline" />
                        {{ $tab['name'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- TAB CONTENT SECTIONS --}}

        {{-- 1. EXECUTIVE OVERVIEW --}}
        @if($activeSection === 'overview')
            <div class="space-y-6">
                {{-- KPI CARDS GRID --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- New Students Card --}}
                    <div wire:click="openDrillDown('new_students')" class="cursor-pointer bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-primary-500 transition group">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span>الطلاب الجدد المسجلين</span>
                            <x-heroicon-o-user-plus class="w-4 h-4 text-primary-500 group-hover:scale-110 transition" />
                        </div>
                        <div class="text-3xl font-black text-gray-900 dark:text-white">
                            {{ $reportData['new_students_count'] ?? 0 }}
                        </div>
                        <div class="mt-2 text-[10px] text-gray-400 flex items-center gap-1">
                            <span>طالب فريد لأول مرة بالفترة</span>
                            <x-heroicon-o-arrow-left class="w-3 h-3 text-primary-500 inline" />
                        </div>
                    </div>

                    {{-- Enrollments & Renewals Combined Card --}}
                    <div wire:click="openDrillDown('enrollments_renewals')" class="cursor-pointer bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-emerald-500 transition group">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span>الاشتراكات والتجديدات (إجمالي موحد)</span>
                            <x-heroicon-o-document-check class="w-4 h-4 text-emerald-500 group-hover:scale-110 transition" />
                        </div>
                        <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400">
                            {{ $reportData['total_enrollments_count'] ?? 0 }}
                        </div>
                        <div class="mt-2 text-[10px] text-emerald-600/80 font-medium">
                            قيمة تعاقدية: {{ number_format($reportData['contracted_enrollment_value'] ?? 0, 2) }} ج.م
                        </div>
                    </div>

                    {{-- Actual Collections Card --}}
                    <div wire:click="openDrillDown('actual_collections')" class="cursor-pointer bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-sky-500 transition group">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span>التحصيلات النقدية الفعلية</span>
                            <x-heroicon-o-banknotes class="w-4 h-4 text-sky-500 group-hover:scale-110 transition" />
                        </div>
                        <div class="text-3xl font-black text-sky-600 dark:text-sky-400">
                            {{ number_format($reportData['actual_collections'] ?? 0, 2) }} <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <div class="mt-2 text-[10px] text-sky-600/80 font-medium">
                            نقدية محصلة ومودعة بالخزائن
                        </div>
                    </div>

                    {{-- Total Outstanding Debt Card --}}
                    <div wire:click="openDrillDown('overdue_debt')" class="cursor-pointer bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-rose-500 transition group">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span>إجمالي المديونيات المستحقة</span>
                            <x-heroicon-o-clock class="w-4 h-4 text-rose-500 group-hover:scale-110 transition" />
                        </div>
                        <div class="text-3xl font-black text-rose-600 dark:text-rose-400">
                            {{ number_format($reportData['total_outstanding_debt'] ?? 0, 2) }} <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <div class="mt-2 text-[10px] text-rose-600/80 font-medium flex items-center justify-between">
                            <span>متأخرات: {{ number_format($reportData['overdue_debt'] ?? 0, 2) }} ج.م</span>
                            <span class="text-amber-500">مستحق اليوم: {{ number_format($reportData['currently_due_debt'] ?? 0, 2) }} ج.م</span>
                        </div>
                    </div>
                </div>

                {{-- SECOND ROW: CASH FLOW & ACADEMIC INTEGRITY --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Cash Flow Overview --}}
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                        <h2 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                            <x-heroicon-o-wallet class="w-4 h-4 text-primary-500" />
                            حركة السيولة والتدفق النقدي الفعلي بالخزائن
                        </h2>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-800">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-1">المقبوضات النقدية</span>
                                <span class="text-base font-black text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($reportData['actual_cash_inflows'] ?? 0, 2) }}
                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-800">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-1">المدفوعات والمصروفات</span>
                                <span class="text-base font-black text-rose-600 dark:text-rose-400">
                                    {{ number_format($reportData['actual_cash_disbursements'] ?? 0, 2) }}
                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-1">صافي الحركة النقدية</span>
                                <span class="text-base font-black {{ ($reportData['net_cash_movement'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ number_format($reportData['net_cash_movement'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-200">
                            <strong>قاعدة محاسبية معتمدة:</strong> التحويلات الداخلية بين الخزائن لا تولد إيراداً أو مصروفاً للمدرسة وتُعامل كحركة مقاصة نقدية فقط.
                        </div>
                    </div>

                    {{-- Academic & Attendance Compliance --}}
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                        <h2 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                            <x-heroicon-o-academic-cap class="w-4 h-4 text-primary-500" />
                            مؤشرات الانضباط والامتثال الأكاديمي
                        </h2>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-1">الجلسات المكتملة</span>
                                <span class="text-base font-black text-gray-800 dark:text-gray-200">
                                    {{ $reportData['completed_sessions_count'] ?? 0 }}
                                </span>
                            </div>
                            <div wire:click="openDrillDown('unrecorded_sessions')" class="cursor-pointer p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 hover:border-amber-500 transition">
                                <span class="text-[11px] text-amber-700 dark:text-amber-300 block mb-1">جلسات بدون رصد حضور</span>
                                <span class="text-base font-black text-amber-600 dark:text-amber-400">
                                    {{ $reportData['unrecorded_attendance_sessions_count'] ?? 0 }}
                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-950/30 border border-primary-200 dark:border-primary-800">
                                <span class="text-[11px] text-primary-700 dark:text-primary-300 block mb-1">نسبة انضباط التسجيل</span>
                                <span class="text-base font-black text-primary-600 dark:text-primary-400">
                                    {{ $reportData['attendance_compliance_rate'] ?? 0 }}%
                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl text-xs text-blue-800 dark:text-blue-200">
                            <strong>قاعدة أكاديمية صارمة:</strong> عدم رصد الحضور يُعد تأخيراً إدارياً ولا يُحسب إطلاقاً كغياب للطلاب.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 2. STUDENT GROWTH --}}
        @if($activeSection === 'growth')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير تسجيل الطلاب الجدد</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">يُحسب الطالب الجديد ككيان فريد مرة واحدة في المدرسة مهما تعددت كورساته</p>
                    </div>
                    <div class="text-sm font-bold bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400 px-3 py-1.5 rounded-xl">
                        إجمالي الطلاب الجدد: {{ $reportData['total_unique_students'] ?? 0 }}
                    </div>
                </div>

                {{-- Table of Students --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">#</th>
                                <th class="p-3">كود الطالب</th>
                                <th class="p-3">اسم الطالب</th>
                                <th class="p-3">رقم الهاتف</th>
                                <th class="p-3">الفرع</th>
                                <th class="p-3">تاريخ ووقت التسجيل</th>
                                <th class="p-3">عدد الاشتراكات</th>
                                <th class="p-3">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($reportData['records'] ?? [] as $st)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono text-gray-400">{{ $st['id'] }}</td>
                                    <td class="p-3 font-mono font-bold">{{ $st['student_code'] }}</td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $st['name'] }}</td>
                                    <td class="p-3 font-mono text-gray-500">{{ $st['phone'] }}</td>
                                    <td class="p-3">{{ $st['branch_name'] }}</td>
                                    <td class="p-3 text-gray-500">{{ $st['registration_date'] }}</td>
                                    <td class="p-3 font-bold">{{ $st['enrollments_count'] }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $st['status'] === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $st['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-400">لا توجد تسجيلات جديدة مطابقة لمعايير الفلترة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 3. ENROLLMENTS & RENEWALS --}}
        @if($activeSection === 'enrollments')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-100 dark:border-gray-700 pb-3 gap-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير الاشتراكات والتجديدات الموحد</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">الاشتراكات الجديدة والتجديدات مجتمعة في إجمالي رئيسي واحد مع تفصيل العمليات</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 px-3 py-1.5 rounded-xl">
                            إجمالي الحركات: {{ $reportData['total_enrollment_and_renewal_events'] ?? 0 }}
                        </span>
                        <span class="text-xs font-bold bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300 px-3 py-1.5 rounded-xl">
                            التعاقد: {{ number_format($reportData['total_contracted_value'] ?? 0, 2) }} ج.م
                        </span>
                        <span class="text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300 px-3 py-1.5 rounded-xl">
                            المسدد: {{ number_format($reportData['total_paid_amount'] ?? 0, 2) }} ج.م
                        </span>
                    </div>
                </div>

                {{-- Table of Enrollments --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">كود الاشتراك</th>
                                <th class="p-3">الطالب</th>
                                <th class="p-3">الكورسات والمجموعات</th>
                                <th class="p-3">نوع العملية</th>
                                <th class="p-3">التاريخ</th>
                                <th class="p-3">الصافي التعاقدي</th>
                                <th class="p-3">المدفوع</th>
                                <th class="p-3">المتبقي</th>
                                <th class="p-3">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($reportData['records'] ?? [] as $enr)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono font-bold">{{ $enr['enrollment_code'] }}</td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $enr['student_name'] }}</td>
                                    <td class="p-3 text-gray-600 dark:text-gray-300">{{ $enr['courses'] }} ({{ $enr['groups'] }})</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $enr['operation_type'] === 'تجديد اشتراك' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' }}">
                                            {{ $enr['operation_type'] }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500">{{ $enr['date'] }}</td>
                                    <td class="p-3 font-bold">{{ number_format($enr['net_amount'], 2) }}</td>
                                    <td class="p-3 text-emerald-600 font-bold">{{ number_format($enr['paid_amount'], 2) }}</td>
                                    <td class="p-3 text-rose-600 font-bold">{{ number_format($enr['remaining_amount'], 2) }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $enr['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : ($enr['status'] === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-700') }}">
                                            {{ $enr['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400">لا توجد اشتراكات أو تجديدات مسجلة خلال الفترة المحددة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 4. ACTUAL COLLECTIONS --}}
        @if($activeSection === 'collections')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير التحصيلات النقدية الفعلية</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">سندات القبض النقدية الفعلية المودعة بالخزائن — لا يتم تكرار السند بتوزيعه على الأقساط</p>
                    </div>
                    <div class="text-sm font-bold bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 px-3 py-1.5 rounded-xl">
                        إجمالي التحصيلات: {{ number_format($reportData['total_collections'] ?? 0, 2) }} ج.م ({{ $reportData['collections_count'] ?? 0 }} سند)
                    </div>
                </div>

                {{-- Payment Methods Breakdown --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($reportData['by_method'] ?? [] as $m)
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                            <span class="text-[11px] text-gray-500 block mb-1">{{ $m['method_label'] }}</span>
                            <span class="text-base font-black text-gray-900 dark:text-white">{{ number_format($m['total'], 2) }} ج.م</span>
                            <span class="text-[10px] text-gray-400 block mt-0.5">({{ $m['count'] }} حركة)</span>
                        </div>
                    @endforeach
                </div>

                {{-- Table of Payments --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">رقم السند</th>
                                <th class="p-3">تاريخ الدفع</th>
                                <th class="p-3">اسم الطالب</th>
                                <th class="p-3">كود الاشتراك</th>
                                <th class="p-3">المبلغ</th>
                                <th class="p-3">طريقة الدفع</th>
                                <th class="p-3">الخزينة المودع بها</th>
                                <th class="p-3">الموظف المستلم</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($reportData['records'] ?? [] as $pay)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono font-bold text-sky-600">{{ $pay['payment_code'] }}</td>
                                    <td class="p-3 text-gray-500">{{ $pay['payment_date'] }}</td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $pay['student_name'] }}</td>
                                    <td class="p-3 font-mono">{{ $pay['enrollment_code'] }}</td>
                                    <td class="p-3 font-black text-emerald-600">{{ number_format($pay['amount'], 2) }}</td>
                                    <td class="p-3">{{ $pay['payment_method'] }}</td>
                                    <td class="p-3">{{ $pay['vault_name'] }}</td>
                                    <td class="p-3 text-gray-500">{{ $pay['received_by'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-400">لا توجد سندات تحصيل مسجلة خلال هذه الفترة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 5. INSTALLMENTS & DEBT AGING --}}
        @if($activeSection === 'installments')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير الأقساط وإعمار الديون والمديونيات</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">فصل تام بين المتأخرات، والمستحق في تاريخ المرجع، والأقساط المستقبلية القادمة</p>
                </div>

                {{-- 3 Core Debt Blocks --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800">
                        <span class="text-xs font-bold text-rose-700 dark:text-rose-300 block mb-1">المديونيات المتأخرة (Overdue)</span>
                        <div class="text-2xl font-black text-rose-600 dark:text-rose-400">
                            {{ number_format($reportData['overdue_amount'] ?? 0, 2) }} <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <span class="text-[10px] text-rose-500 mt-1 block">عدد الأقساط: {{ $reportData['overdue_count'] ?? 0 }}</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-300 block mb-1">المستحق في تاريخ اليوم (Currently Due)</span>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400">
                            {{ number_format($reportData['currently_due_amount'] ?? 0, 2) }} <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <span class="text-[10px] text-amber-500 mt-1 block">عدد الأقساط: {{ $reportData['currently_due_count'] ?? 0 }}</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                        <span class="text-xs font-bold text-blue-700 dark:text-blue-300 block mb-1">الأقساط المستقبلية (Future Installments)</span>
                        <div class="text-2xl font-black text-blue-600 dark:text-blue-400">
                            {{ number_format($reportData['future_amount'] ?? 0, 2) }} <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <span class="text-[10px] text-blue-500 mt-1 block">عدد الأقساط: {{ $reportData['future_count'] ?? 0 }}</span>
                    </div>
                </div>

                {{-- Aging Buckets --}}
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 space-y-3">
                    <h3 class="text-xs font-black text-gray-700 dark:text-gray-300">أوعية إعمار الديون المتأخرة (Aging Buckets)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-500 block">1 إلى 7 أيام</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($reportData['aging_buckets']['1_7_days'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-500 block">8 إلى 30 يوماً</span>
                            <span class="text-sm font-bold text-amber-600">{{ number_format($reportData['aging_buckets']['8_30_days'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-500 block">31 إلى 60 يوماً</span>
                            <span class="text-sm font-bold text-orange-600">{{ number_format($reportData['aging_buckets']['31_60_days'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-500 block">أكثر من 60 يوماً</span>
                            <span class="text-sm font-bold text-rose-600">{{ number_format($reportData['aging_buckets']['over_60_days'] ?? 0, 2) }} ج.م</span>
                        </div>
                    </div>
                </div>

                {{-- Table of Installments --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">القسط</th>
                                <th class="p-3">الطالب</th>
                                <th class="p-3">كود الاشتراك</th>
                                <th class="p-3">تاريخ الاستحقاق</th>
                                <th class="p-3">أصل المبلغ</th>
                                <th class="p-3">المسدد</th>
                                <th class="p-3">المتبقي</th>
                                <th class="p-3">حالة الاستحقاق</th>
                                <th class="p-3">حالة السداد</th>
                                <th class="p-3">أيام التأخير</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($reportData['records'] ?? [] as $ins)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono font-bold">قسط #{{ $ins['installment_number'] }}</td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $ins['student_name'] }}</td>
                                    <td class="p-3 font-mono">{{ $ins['enrollment_code'] }}</td>
                                    <td class="p-3 text-gray-500">{{ $ins['due_date'] }}</td>
                                    <td class="p-3 font-bold">{{ number_format($ins['original_amount'], 2) }}</td>
                                    <td class="p-3 text-emerald-600 font-bold">{{ number_format($ins['paid_amount'], 2) }}</td>
                                    <td class="p-3 text-rose-600 font-bold">{{ number_format($ins['remaining_amount'], 2) }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ins['due_status'] === 'متأخر' ? 'bg-rose-100 text-rose-700' : ($ins['due_status'] === 'مستحق اليوم' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                            {{ $ins['due_status'] }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ins['payment_status'] === 'مسدد جزئياً' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $ins['payment_status'] }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-mono {{ $ins['days_overdue'] > 0 ? 'text-rose-600 font-bold' : 'text-gray-400' }}">
                                        {{ $ins['days_overdue'] > 0 ? $ins['days_overdue'] . ' يوم' : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-gray-400">لا توجد أقساط مطابقة للمعايير المحددة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 6. VAULTS AND CASH MOVEMENT --}}
        @if($activeSection === 'vaults')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير ومطابقة حركة الخزائن</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">المعادلة المعتمدة: الرصيد الافتتاحي + التدفقات الداخلة - التدفقات الخارجة = الرصيد الختامي</p>
                    </div>
                </div>

                {{-- Table of Vaults --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">الخزينة</th>
                                <th class="p-3">الرصيد الافتتاحي</th>
                                <th class="p-3">التحصيلات النقدية</th>
                                <th class="p-3">تحويلات واردة</th>
                                <th class="p-3">المصروفات</th>
                                <th class="p-3">المرتبات المنصرفة</th>
                                <th class="p-3">استردادات مسددة</th>
                                <th class="p-3">تحويلات صادرة</th>
                                <th class="p-3">الرصيد الختامي</th>
                                <th class="p-3">الرصيد الحالي بالنظام</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($reportData['vaults'] ?? [] as $v)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $v['vault_name'] }}</td>
                                    <td class="p-3 font-bold">{{ number_format($v['opening_balance'], 2) }}</td>
                                    <td class="p-3 text-emerald-600 font-bold">+{{ number_format($v['cash_receipts'], 2) }}</td>
                                    <td class="p-3 text-emerald-600">+{{ number_format($v['transfers_in'], 2) }}</td>
                                    <td class="p-3 text-rose-600 font-bold">-{{ number_format($v['expenses'], 2) }}</td>
                                    <td class="p-3 text-rose-600 font-bold">-{{ number_format($v['salaries'], 2) }}</td>
                                    <td class="p-3 text-rose-600 font-bold">-{{ number_format($v['refunds'], 2) }}</td>
                                    <td class="p-3 text-rose-600">-{{ number_format($v['transfers_out'], 2) }}</td>
                                    <td class="p-3 font-black text-sky-600 text-sm">{{ number_format($v['closing_balance'], 2) }}</td>
                                    <td class="p-3 font-mono font-bold">{{ number_format($v['current_system_balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-gray-400">لا توجد خزائن نشطة مسجلة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 7. REVENUE, EXPENSE & P&L --}}
        @if($activeSection === 'pnl')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">قائمة الدخل التشغيلي والتحليل المقارن</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">الفصل الصارم بين التدفق النقدي الفعلي والالتزامات التعاقدية التشغيلية</p>
                </div>

                {{-- Notice Banner --}}
                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-200 leading-relaxed">
                    <strong>إشعار سياسة الإفصاح المالي:</strong> {{ $reportData['operating_statement_view']['accounting_policy_notice'] ?? '' }}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Cash Flow Column --}}
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 space-y-3">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white border-b pb-2">الأساس النقدي التشغيلي (Cash Flow)</h3>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>التحصيلات النقدية المودعة</span>
                            <span class="font-bold text-emerald-600">+{{ number_format($reportData['cash_flow_view']['cash_collections'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>المصروفات التشغيلية المدفوعة</span>
                            <span class="font-bold text-rose-600">-{{ number_format($reportData['cash_flow_view']['cash_expenses_paid'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>المرتبات المنصرفة نقداً</span>
                            <span class="font-bold text-rose-600">-{{ number_format($reportData['cash_flow_view']['salaries_paid'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs pt-2 font-black">
                            <span>صافي التدفق النقدي التشغيلي</span>
                            <span class="text-sm {{ ($reportData['cash_flow_view']['net_cash_flow'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ number_format($reportData['cash_flow_view']['net_cash_flow'] ?? 0, 2) }} ج.م
                            </span>
                        </div>
                    </div>

                    {{-- Accrual / Contractual Column --}}
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 space-y-3">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white border-b pb-2">المركز التحليلي التعاقدي (Contractual / Operating)</h3>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>صافي القيمة التعاقدية للاشتراكات</span>
                            <span class="font-bold text-emerald-600">+{{ number_format($reportData['operating_statement_view']['contracted_enrollments_value'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>إيرادات إضافية مسجلة</span>
                            <span class="font-bold text-emerald-600">+{{ number_format($reportData['operating_statement_view']['direct_revenues_recorded'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>استحقاقات أتعاب المهندسين المكتسبة</span>
                            <span class="font-bold text-rose-600">-{{ number_format($reportData['operating_statement_view']['instructor_compensation_earned'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>المصروفات التشغيلية المسجلة</span>
                            <span class="font-bold text-rose-600">-{{ number_format($reportData['operating_statement_view']['operating_expenses_recorded'] ?? 0, 2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs pt-2 font-black">
                            <span>النتيجة التشغيلية التحليلية</span>
                            <span class="text-sm {{ ($reportData['operating_statement_view']['operating_result_analytic'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ number_format($reportData['operating_statement_view']['operating_result_analytic'] ?? 0, 2) }} ج.م
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 8. INSTRUCTOR SALARIES --}}
        @if($activeSection === 'salaries')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير مستحقات ومرتبات المهندسين</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">تقييمات الإشراف التربوي (1-5) مستقلة تماماً ولا تؤثر تلقائياً على حسابات المرتبات</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-xs font-bold bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-xl">
                            المستحق المكتسب: {{ number_format($reportData['total_earned'] ?? 0, 2) }} ج.م
                        </span>
                        <span class="text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 px-3 py-1.5 rounded-xl">
                            المنصرف فعلياً: {{ number_format($reportData['total_disbursed'] ?? 0, 2) }} ج.م
                        </span>
                        <span class="text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300 px-3 py-1.5 rounded-xl">
                            المتبقي المستحق: {{ number_format($reportData['outstanding_salary'] ?? 0, 2) }} ج.م
                        </span>
                    </div>
                </div>

                {{-- Table of Salaries --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">المهندس / المحاضر</th>
                                <th class="p-3">الفترة المحسوبة</th>
                                <th class="p-3">الجلسات المنجزة</th>
                                <th class="p-3">سعر الجلسة</th>
                                <th class="p-3">المكافآت</th>
                                <th class="p-3">الخصومات</th>
                                <th class="p-3">إجمالي المستحق</th>
                                <th class="p-3">الحالة</th>
                                <th class="p-3">تاريخ الصرف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($reportData['records'] ?? [] as $sal)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $sal['instructor_name'] }}</td>
                                    <td class="p-3 text-gray-500">{{ $sal['period_label'] }}</td>
                                    <td class="p-3 font-bold">{{ $sal['completed_sessions'] }}</td>
                                    <td class="p-3">{{ number_format($sal['rate_per_session'], 2) }}</td>
                                    <td class="p-3 text-emerald-600">+{{ number_format($sal['bonus_amount'], 2) }}</td>
                                    <td class="p-3 text-rose-600">-{{ number_format($sal['deduction_amount'], 2) }}</td>
                                    <td class="p-3 font-black">{{ number_format($sal['total_amount'], 2) }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sal['status'] === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $sal['status'] }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500">{{ $sal['payment_date'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400">لا توجد سجلات مرتبات معتمدة خلال الفترة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 9. CANCELLATIONS & REFUNDS --}}
        @if($activeSection === 'cancellations')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير الإلغاءات والتسويات المالية والاسترداد</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">الفصل الصارم: 1. إلغاء الاشتراك 2. قرار التسوية المالية 3. صرف الاسترداد الفعلي من الخزينة</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                        <span class="text-[11px] text-gray-500 block mb-1">عدد الإلغاءات</span>
                        <span class="text-xl font-black text-gray-800 dark:text-gray-200">{{ $reportData['cancellations_count'] ?? 0 }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                        <span class="text-[11px] text-amber-700 dark:text-amber-300 block mb-1">الديون المسقطة بالتسوية</span>
                        <span class="text-xl font-black text-amber-600 dark:text-amber-400">{{ number_format($reportData['waived_debt_total'] ?? 0, 2) }} ج.م</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                        <span class="text-[11px] text-blue-700 dark:text-blue-300 block mb-1">التزام الاسترداد المعتمد</span>
                        <span class="text-xl font-black text-blue-600 dark:text-blue-400">{{ number_format($reportData['total_refund_liability'] ?? 0, 2) }} ج.م</span>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                        <span class="text-[11px] text-emerald-700 dark:text-emerald-300 block mb-1">الاسترداد المنصرف فعلياً</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($reportData['actual_refunds_disbursed'] ?? 0, 2) }} ج.م</span>
                    </div>
                </div>

                {{-- Table of Cancellations --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">الطالب</th>
                                <th class="p-3">تاريخ الإلغاء</th>
                                <th class="p-3">الموظف المنفذ</th>
                                <th class="p-3">سبب الإلغاء</th>
                                <th class="p-3">نوع التسوية</th>
                                <th class="p-3">المبلغ المسقط</th>
                                <th class="p-3">المحتجز بالمدرسة</th>
                                <th class="p-3">المستحق استرداده</th>
                                <th class="p-3">المنصرف من الخزنة</th>
                                <th class="p-3">حالة الاسترداد</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($reportData['records'] ?? [] as $can)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $can['student_name'] }}</td>
                                    <td class="p-3 text-gray-500">{{ $can['cancelled_at'] }}</td>
                                    <td class="p-3 text-gray-500">{{ $can['cancelled_by'] }}</td>
                                    <td class="p-3 text-gray-700 dark:text-gray-300">{{ $can['cancellation_reason'] }}</td>
                                    <td class="p-3 font-bold text-primary-600">{{ $can['settlement_type'] }}</td>
                                    <td class="p-3 text-amber-600">{{ number_format($can['waived_amount'], 2) }}</td>
                                    <td class="p-3 text-gray-700 font-bold">{{ number_format($can['retained_amount'], 2) }}</td>
                                    <td class="p-3 text-blue-600 font-bold">{{ number_format($can['refundable_amount'], 2) }}</td>
                                    <td class="p-3 text-emerald-600 font-black">{{ number_format($can['refunded_amount'], 2) }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $can['refund_status'] === 'refunded' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $can['refund_status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-gray-400">لا توجد اشتراكات ملغاة خلال هذه الفترة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 10. ACADEMIC & EXAMS --}}
        @if($activeSection === 'academic')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">التحليل الأكاديمي والامتحانات</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">لكل مادة مؤشراتها المستقلة دون دمج عشوائي — مسودات الدرجات مستثناة من التقييمات الرسمية</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                        <span class="text-xs text-gray-500 block mb-1">جلسات الحضور المكتملة</span>
                        <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $reportData['sessions']['completed'] ?? 0 }}</span>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                        <span class="text-xs text-amber-700 dark:text-amber-300 block mb-1">جلسات لم يُرصد حضورها</span>
                        <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $reportData['attendance']['unrecorded_sessions_count'] ?? 0 }}</span>
                        <span class="text-[10px] text-amber-500 block mt-1">تأخر رصد إداري وليس غياباً</span>
                    </div>

                    <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                        <span class="text-xs text-blue-700 dark:text-blue-300 block mb-1">تسليمات الواجبات المنشورة</span>
                        <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $reportData['homework']['graded_published'] ?? 0 }}</span>
                        <span class="text-[10px] text-blue-500 block mt-1">ينتظر التصحيح: {{ $reportData['homework']['pending_grading'] ?? 0 }}</span>
                    </div>

                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800">
                        <span class="text-xs text-rose-700 dark:text-rose-300 block mb-1">نتائج الامتحانات المنخفضة (&lt; 50%)</span>
                        <span class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $reportData['exams']['low_scores_count'] ?? 0 }}</span>
                        <span class="text-[10px] text-rose-500 block mt-1">نسبة النجاح العامة: {{ $reportData['exams']['pass_rate'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- 11. SUPERVISION --}}
        @if($activeSection === 'supervision')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">تقارير الإشراف التربوي المعتمدة</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">حالات الملاحظات الإشرافية المعتمدة: (جديدة، بانتظار مراجعة المشرف، مكتملة) — التقارير الرسمية لا تُنشر إلا بتوقيع المشرف</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                        <span class="text-[11px] text-gray-500 block mb-1">الملاحظات الجديدة</span>
                        <span class="text-xl font-black text-gray-800 dark:text-gray-200">{{ $reportData['observations']['new'] ?? 0 }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                        <span class="text-[11px] text-amber-700 dark:text-amber-300 block mb-1">بانتظار مراجعة المشرف</span>
                        <span class="text-xl font-black text-amber-600 dark:text-amber-400">{{ $reportData['observations']['waiting_review'] ?? 0 }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                        <span class="text-[11px] text-emerald-700 dark:text-emerald-300 block mb-1">ملاحظات مكتملة ومعتمدة</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ $reportData['observations']['completed'] ?? 0 }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800">
                        <span class="text-[11px] text-purple-700 dark:text-purple-300 block mb-1">التقارير الإشرافية المنشورة</span>
                        <span class="text-xl font-black text-purple-600 dark:text-purple-400">{{ $reportData['supervision_reports']['published'] ?? 0 }}</span>
                    </div>
                </div>

                {{-- Table of Observations --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">كود الملاحظة</th>
                                <th class="p-3">عنوان التوجيه</th>
                                <th class="p-3">المهندس المعني</th>
                                <th class="p-3">المشرف التربوي</th>
                                <th class="p-3">المجموعة</th>
                                <th class="p-3">الأولوية</th>
                                <th class="p-3">تاريخ الإنشاء</th>
                                <th class="p-3">الحالة الرسمية</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($reportData['recent_observations'] ?? [] as $obs)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono font-bold text-primary-600">{{ $obs['observation_code'] }}</td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $obs['title'] }}</td>
                                    <td class="p-3">{{ $obs['instructor_name'] }}</td>
                                    <td class="p-3 text-gray-500">{{ $obs['supervisor_name'] }}</td>
                                    <td class="p-3">{{ $obs['group_name'] }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $obs['priority'] === 'urgent' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $obs['priority'] }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500">{{ $obs['created_at'] }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $obs['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($obs['status'] === 'waiting_supervisor_review' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                            {{ $obs['status_label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-400">لا توجد ملاحظات إشرافية مسجلة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 12. EMPLOYEES --}}
        @if($activeSection === 'employees')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">العمليات التشغيلية لموظفي المدرسة</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">مؤشرات تشغيلية متخصصة لكل طبيعة عمل دون فرض تقييم عشوائي موحد</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Registration Staff --}}
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 space-y-3">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white">مسؤولو التسجيل والقبول</h3>
                        <div class="space-y-2">
                            @forelse($reportData['registration_staff'] ?? [] as $rs)
                                <div class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-xs">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $rs['name'] }}</span>
                                    <div class="flex gap-3 text-gray-500">
                                        <span>تسجيل طلاب: <strong>{{ $rs['students_registered'] }}</strong></span>
                                        <span>اشتراكات: <strong>{{ $rs['enrollments_recorded'] }}</strong></span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400">لا يوجد موظفو تسجيل مسجلون.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Accountants --}}
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 space-y-3">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white">المحاسبون وأمناء الخزائن</h3>
                        <div class="space-y-2">
                            @forelse($reportData['accountants'] ?? [] as $acc)
                                <div class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-xs">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $acc['name'] }}</span>
                                    <div class="flex gap-3">
                                        <span class="text-gray-500">سندات قبض: <strong>{{ $acc['payments_recorded_count'] }}</strong></span>
                                        <span class="text-emerald-600 font-bold">{{ number_format($acc['collections_amount'], 2) }} ج.م</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400">لا يوجد محاسبون مسجلون.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 13. BRANCH COMPARISON --}}
        @if($activeSection === 'branches')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">المقارنة المعيارية بين فروع المدرسة</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">مقارنة المؤشرات الرئيسية بالفروع تحت نفس التعريف الزمني والمعايير الموحدة</p>
                </div>

                {{-- Table of Branches --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">الفرع</th>
                                <th class="p-3">طلاب جدد</th>
                                <th class="p-3">الاشتراكات والتجديدات</th>
                                <th class="p-3">القيمة التعاقدية</th>
                                <th class="p-3">التحصيلات الفعلية</th>
                                <th class="p-3">إجمالي المديونيات</th>
                                <th class="p-3">الطلاب النشطون</th>
                                <th class="p-3">المجموعات النشطة</th>
                                <th class="p-3">جلسات غير مسجلة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($reportData['branches'] ?? [] as $br)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white text-sm">{{ $br['branch_name'] }}</td>
                                    <td class="p-3 font-bold">{{ $br['new_students'] }}</td>
                                    <td class="p-3 font-bold text-emerald-600">{{ $br['enrollments_count'] }}</td>
                                    <td class="p-3 font-bold">{{ number_format($br['contracted_value'], 2) }}</td>
                                    <td class="p-3 font-black text-sky-600">{{ number_format($br['actual_collections'], 2) }}</td>
                                    <td class="p-3 text-rose-600 font-bold">{{ number_format($br['total_debt'], 2) }}</td>
                                    <td class="p-3">{{ $br['active_students'] }}</td>
                                    <td class="p-3">{{ $br['active_groups'] }}</td>
                                    <td class="p-3 font-mono text-amber-600 font-bold">{{ $br['unrecorded_attendance_sessions'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400">لا توجد بيانات مقارنة متاحة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 14. MANAGEMENT ALERTS --}}
        @if($activeSection === 'alerts')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">لوحة التنبيهات الإدارية والاستثناءات</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">تنبيهات مشتقة من بيانات حقيقية — للاطلاع والرقابة فقط ولا تنفذ إجراءات تلقائية على الحسابات</p>
                </div>

                <div class="space-y-3">
                    @forelse($reportData['alerts'] ?? [] as $al)
                        <div class="p-4 rounded-xl border flex items-start justify-between gap-4 {{ $al['severity'] === 'danger' ? 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-800' : ($al['severity'] === 'warning' ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800' : 'bg-blue-50/50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-800') }}">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $al['severity'] === 'danger' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' : ($al['severity'] === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300') }}">
                                        {{ $al['type'] }}
                                    </span>
                                    <h3 class="text-xs font-bold text-gray-900 dark:text-white">{{ $al['title'] }}</h3>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300">{{ $al['description'] }}</p>
                            </div>
                            <div class="text-left text-[11px] text-gray-400 whitespace-nowrap">
                                <div>{{ $al['branch_name'] }}</div>
                                <div>{{ $al['date'] }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-emerald-600 font-bold bg-emerald-50 dark:bg-emerald-950/20 rounded-xl">
                            لا توجد استثناءات أو تنبيهات إدارية نشطة حالياً. جميع المؤشرات ضمن الحدود المعتمدة.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- 15. SAVED & SCHEDULED REPORTS --}}
        @if($activeSection === 'saved')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">التقارير المحفوظة والمجدولة</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">إدارة التكوينات المفضلة للمستخدم وجدولة الإعداد المسبق للمسودات</p>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-black text-gray-700 dark:text-gray-300">التقارير المحفوظة</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse($reportData['saved'] ?? [] as $sr)
                            <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $sr['name'] }}</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">القسم: {{ $sr['category'] }}</div>
                                </div>
                                <button type="button" wire:click="setSection('{{ $sr['category'] }}')" class="px-3 py-1 text-xs font-bold bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-100 transition">
                                    فتح التقرير
                                </button>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 col-span-2">لا توجد تقارير محفوظة حالياً. يمكنك حفظ أي تقرير بالضغط على زر "حفظ الفلترة الحالية".</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        {{-- 16. REPORTING SETTINGS --}}
        @if($activeSection === 'settings')
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">إعدادات مركز التقارير الموحد</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">إعداد الحدود المعيارية للتنبيهات وتخصيص ترويسة ملفات الـ PDF</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">الحد الأدنى لتنبيه المديونية المتأخرة (ج.م)</label>
                        <input type="number" readonly value="{{ $reportData['settings']['alert_threshold_debt'] ?? 1000 }}" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200" />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">نسبة التنبيه للدرجات المنخفضة (%)</label>
                        <input type="number" readonly value="{{ $reportData['settings']['alert_threshold_low_score_percentage'] ?? 50 }}" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200" />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">ساعات مهلة تصحيح الواجبات</label>
                        <input type="number" readonly value="{{ $reportData['settings']['alert_homework_overdue_hours'] ?? 48 }}" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200" />
                    </div>
                </div>

                <div class="p-3 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl text-xs text-blue-800 dark:text-blue-200">
                    <strong>ملاحظة الصلاحيات:</strong> تعديل الحدود المعيارية لسياسات التنبيهات مقتصر على الإدارة العامة ومفوضي الفروع المعتمدين.
                </div>
            </div>
        @endif

        {{-- DRILL DOWN MODAL --}}
        @if($showDrillDownModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-5xl w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 max-h-[90vh] flex flex-col">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-magnifying-glass-plus class="w-5 h-5 text-primary-500 inline" />
                            {{ $drillDownTitle }}
                        </h3>
                        <button type="button" wire:click="closeDrillDownModal" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="overflow-y-auto flex-1">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold sticky top-0">
                                <tr>
                                    @foreach($drillDownHeaders as $head)
                                        <th class="p-3 border-b">{{ $head }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($drillDownRows as $row)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                        @foreach($row as $cell)
                                            <td class="p-3">{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($drillDownHeaders) }}" class="p-6 text-center text-gray-400">لا توجد سجلات تفصيلية مسجلة لهذا المؤشر.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 dark:border-gray-700 pt-3">
                        <button type="button" wire:click="closeDrillDownModal" class="px-4 py-2 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                            إغلاق النافذة
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- SAVE REPORT CONFIGURATION MODAL --}}
        @if($showSaveReportModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">حفظ تكوين التقرير الحالي</h3>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">اسم التقرير المحفوظ</label>
                        <input type="text" wire:model="savedReportName" placeholder="مثال: تقرير اشتراكات فرع القاهرة لشهر أكتوبر" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500" />
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showSaveReportModal', false)" class="px-3.5 py-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                            إلغاء
                        </button>
                        <button type="button" wire:click="saveReportConfiguration" class="px-4 py-2 text-xs font-black text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition">
                            تأكيد الحفظ
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
