@php
    $stats = $this->getStats();
    $attendanceByMonth = $this->getAttendanceByMonth();
    $revenueByMonth = $this->getRevenueByMonth();
    $topInstructors = $this->getTopInstructors();
    $groupsDistribution = $this->getGroupsDistribution();
    $branchesComparison = $this->getBranchesComparison();
    $profitByMonth = $this->getProfitByMonth();
    $profitByCourse = $this->getProfitByCourse();
    $profitByGroup = $this->getProfitByGroup();
    $subscribersByCourse = $this->getSubscribersByCourse();
    $classroomsReport = $this->getClassroomsReport();
    $financialOverview = $this->getFinancialOverview();
@endphp

<x-filament-panels::page>
    @include('filament.shared.report-theme')

    <div class="report-shell">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">من تاريخ</label>
                <input type="date" wire:model.live="dateFrom" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" wire:model.live="dateTo" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="button" wire:click="resetDateFilters" class="px-4 py-2 text-sm font-bold rounded-lg bg-gray-100 text-gray-800">إعادة الفترة الافتراضية</button>
            </div>
        </div>
        <p class="mt-3 text-xs text-gray-500">الفترة المطبقة: {{ $this->dateFrom }} إلى {{ $this->dateTo }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="exportReport('profit-month', 'csv')" class="px-3 py-2 text-xs font-bold rounded-lg bg-cyan-100 text-cyan-900">تصدير أرباح الشهر Excel</button>
            <button type="button" wire:click="exportReport('profit-month', 'pdf')" class="px-3 py-2 text-xs font-bold rounded-lg bg-cyan-700 text-white">تصدير أرباح الشهر PDF</button>

            <button type="button" wire:click="exportReport('profit-course', 'csv')" class="px-3 py-2 text-xs font-bold rounded-lg bg-fuchsia-100 text-fuchsia-900">تصدير أرباح الكورس Excel</button>
            <button type="button" wire:click="exportReport('profit-course', 'pdf')" class="px-3 py-2 text-xs font-bold rounded-lg bg-fuchsia-700 text-white">تصدير أرباح الكورس PDF</button>

            <button type="button" wire:click="exportReport('profit-group', 'csv')" class="px-3 py-2 text-xs font-bold rounded-lg bg-amber-100 text-amber-900">تصدير أرباح المجموعة Excel</button>
            <button type="button" wire:click="exportReport('profit-group', 'pdf')" class="px-3 py-2 text-xs font-bold rounded-lg bg-amber-700 text-white">تصدير أرباح المجموعة PDF</button>

            <button type="button" wire:click="exportReport('subscribers-course', 'csv')" class="px-3 py-2 text-xs font-bold rounded-lg bg-indigo-100 text-indigo-900">تصدير مشتركين الكورس Excel</button>
            <button type="button" wire:click="exportReport('subscribers-course', 'pdf')" class="px-3 py-2 text-xs font-bold rounded-lg bg-indigo-700 text-white">تصدير مشتركين الكورس PDF</button>

            <button type="button" wire:click="exportReport('classrooms', 'csv')" class="px-3 py-2 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-900">تصدير تقرير القاعات Excel</button>
            <button type="button" wire:click="exportReport('classrooms', 'pdf')" class="px-3 py-2 text-xs font-bold rounded-lg bg-emerald-700 text-white">تصدير تقرير القاعات PDF</button>
        </div>
    </div>

    {{-- إحصائيات سريعة --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- إجمالي الطلاب --}}
        <div class="modern-stat-card bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-2xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all duration-300">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold shadow-md">الطلاب</span>
                </div>
                <h3 class="text-5xl font-black mb-2">{{ $stats['totalStudents'] }}</h3>
                <p class="text-sm opacity-90 font-medium">إجمالي الطلاب المسجلين</p>
            </div>
        </div>

        {{-- إجمالي المدرسين --}}
        <div class="modern-stat-card bg-gradient-to-br from-emerald-500 to-green-600 text-white rounded-2xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all duration-300">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold shadow-md">المدرسين</span>
                </div>
                <h3 class="text-5xl font-black mb-2">{{ $stats['totalInstructors'] }}</h3>
                <p class="text-sm opacity-90 font-medium">إجمالي المدرسين النشطين</p>
            </div>
        </div>

        {{-- المجموعات --}}
        <div class="modern-stat-card bg-gradient-to-br from-purple-500 to-pink-600 text-white rounded-2xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all duration-300">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold shadow-md">المجموعات</span>
                </div>
                <h3 class="text-5xl font-black mb-2">{{ $stats['activeGroups'] }}<span class="text-2xl opacity-80">/{{ $stats['totalGroups'] }}</span></h3>
                <p class="text-sm opacity-90 font-medium">المجموعات النشطة / الإجمالي</p>
            </div>
        </div>

        {{-- نسبة الحضور --}}
        <div class="modern-stat-card bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all duration-300">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold shadow-md">الحضور</span>
                </div>
                <h3 class="text-5xl font-black mb-2">{{ $stats['attendancePercentage'] }}%</h3>
                <p class="text-sm opacity-90 font-medium">نسبة الحضور آخر 30 يوم</p>
            </div>
        </div>
    </div>

    {{-- الإيرادات والامتحانات --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl p-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">إجمالي الإيرادات (آخر 30 يوم)</p>
                    <h3 class="text-4xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['totalRevenue'] }} <span class="text-xl">جنيه</span></h3>
                </div>
            </div>
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-green-600 rounded-full" style="width: 75%"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-xl p-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">عدد الامتحانات (آخر 30 يوم)</p>
                    <h3 class="text-4xl font-black text-rose-600 dark:text-rose-400">{{ $stats['totalExams'] }} <span class="text-xl">امتحان</span></h3>
                </div>
            </div>
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-rose-500 to-red-600 rounded-full" style="width: 60%"></div>
            </div>
        </div>
    </div>

    {{-- رسم بياني: الحضور حسب الشهر --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-3 shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">إحصائيات الحضور</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">آخر 6 أشهر</p>
            </div>
        </div>
        <div wire:ignore>
            <canvas id="attendanceChart" height="100"></canvas>
        </div>
    </div>

    {{-- رسم بياني: الإيرادات حسب الشهر --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl p-3 shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">الإيرادات</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">آخر 6 أشهر</p>
            </div>
        </div>
        <div wire:ignore>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    {{-- ملخص مالي --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200">
            <p class="text-sm font-bold text-emerald-700">إجمالي المحصل</p>
            <p class="text-2xl font-black text-emerald-900">{{ number_format((float) $financialOverview['total_collected'], 2) }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200">
            <p class="text-sm font-bold text-amber-700">المصروفات التشغيلية</p>
            <p class="text-2xl font-black text-amber-900">{{ number_format((float) $financialOverview['total_operating_cost'], 2) }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-cyan-50 border border-cyan-200">
            <p class="text-sm font-bold text-cyan-700">رواتب مدفوعة</p>
            <p class="text-2xl font-black text-cyan-900">{{ number_format((float) $financialOverview['total_salaries'], 2) }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-indigo-50 border border-indigo-200">
            <p class="text-sm font-bold text-indigo-700">صافي الربح</p>
            <p class="text-2xl font-black text-indigo-900">{{ number_format((float) $financialOverview['total_net'], 2) }}</p>
        </div>
    </div>

    {{-- تقارير الأرباح --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl p-3 shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10M12 4v16"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">الأرباح حسب الشهر</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">آخر 12 شهر</p>
                </div>
            </div>
            <div wire:ignore>
                <canvas id="profitMonthChart" height="120"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl p-3 shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h13M9 5h13M3 5h.01M3 12h.01M3 19h.01"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">عدد المشتركين بكل كورس</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">ترتيب تنازلي</p>
                </div>
            </div>
            <div wire:ignore>
                <canvas id="subscribersCourseChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-gradient-to-br from-fuchsia-500 to-pink-600 rounded-xl p-3 shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.657-1.79 3-4 3S1 20.657 1 19s1.79-3 4-3 4 1.343 4 3zm12-10c0 1.657-1.79 3-4 3s-4-1.343-4-3 1.79-3 4-3 4 1.343 4 3z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">الأرباح حسب الكورس</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">أعلى 10 كورسات</p>
                </div>
            </div>
            <div wire:ignore>
                <canvas id="profitCourseChart" height="120"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-3 shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">الأرباح حسب المجموعة</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">أعلى 10 مجموعات</p>
                </div>
            </div>
            <div wire:ignore>
                <canvas id="profitGroupChart" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- تقارير القاعات --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl p-3 shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">تقارير القاعات والقاعات المتاحة</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">الحالة الحالية للقاعات والاستخدام</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-center">
                <p class="text-sm text-blue-700 dark:text-blue-200">إجمالي القاعات</p>
                <p class="text-2xl font-black text-blue-800 dark:text-blue-100">{{ $classroomsReport['summary']['total'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-green-50 dark:bg-green-900/30 text-center">
                <p class="text-sm text-green-700 dark:text-green-200">قاعات نشطة</p>
                <p class="text-2xl font-black text-green-800 dark:text-green-100">{{ $classroomsReport['summary']['active'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/30 text-center">
                <p class="text-sm text-red-700 dark:text-red-200">قاعات غير نشطة</p>
                <p class="text-2xl font-black text-red-800 dark:text-red-100">{{ $classroomsReport['summary']['inactive'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-center">
                <p class="text-sm text-emerald-700 dark:text-emerald-200">متاحة الآن</p>
                <p class="text-2xl font-black text-emerald-800 dark:text-emerald-100">{{ $classroomsReport['summary']['available_now'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-center">
                <p class="text-sm text-amber-700 dark:text-amber-200">مشغولة الآن</p>
                <p class="text-2xl font-black text-amber-800 dark:text-amber-100">{{ $classroomsReport['summary']['occupied_now'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-center">
                <p class="text-sm text-purple-700 dark:text-purple-200">بها جداول</p>
                <p class="text-2xl font-black text-purple-800 dark:text-purple-100">{{ $classroomsReport['summary']['with_schedule'] }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800">
                    <tr>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">القاعة</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">حالة القاعة</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">حالة الآن</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">مجموعات نشطة</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">إجمالي المجموعات</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">الجروبات ومواعيدها</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">الأوقات المتاحة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($classroomsReport['items'] as $classroom)
                        <tr>
                            <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-gray-100">{{ $classroom['name'] }}</td>
                            <td class="px-6 py-4 text-right">
                                @if($classroom['status'] === 'active')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 font-bold text-xs">نشطة</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100 font-bold text-xs">غير نشطة</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($classroom['is_occupied_now'])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-100 font-bold text-xs">مشغولة</span>
                                @elseif($classroom['is_available_now'])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-100 font-bold text-xs">متاحة</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 font-bold text-xs">غير متاحة</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">{{ $classroom['active_groups'] }}</td>
                            <td class="px-6 py-4 text-right">{{ $classroom['total_groups'] }}</td>
                            <td class="px-6 py-4 text-right align-top">
                                @if(!empty($classroom['groups_schedule']))
                                    <div class="space-y-1">
                                        @foreach($classroom['groups_schedule'] as $item)
                                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $item }}</div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500">لا توجد جروبات مجدولة</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right align-top">
                                @if(!empty($classroom['free_slots']))
                                    <div class="space-y-1">
                                        @foreach($classroom['free_slots'] as $item)
                                            <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ $item }}</div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500">لا توجد أوقات متاحة</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- توزيع المجموعات --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-3 shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">توزيع المجموعات حسب الحالة</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">نظرة عامة على حالات المجموعات</p>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($groupsDistribution as $item)
                <div class="text-center p-6 rounded-2xl transform hover:scale-105 transition-all duration-300 shadow-lg" style="background: linear-gradient(135deg, {{ $item['color'] }}15 0%, {{ $item['color'] }}30 100%);">
                    <div class="mb-3">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full shadow-lg" style="background-color: {{ $item['color'] }};">
                            <span class="text-3xl font-black text-white">{{ $item['count'] }}</span>
                        </div>
                    </div>
                    <p class="text-sm font-bold" style="color: {{ $item['color'] }};">{{ $item['status'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- أفضل المدرسين --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-3 shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">أفضل 10 مدرسين</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">حسب عدد المجموعات</p>
            </div>
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800">
                    <tr>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">#</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">اسم المدرس</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">عدد المجموعات</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">عدد الطلاب</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($topInstructors as $index => $instructor)
                        <tr class="hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200 {{ $index < 3 ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}">
                            <td class="px-6 py-5 text-right">
                                @if($index < 3)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white font-black text-sm shadow-lg">
                                        {{ $index + 1 }}
                                    </span>
                                @else
                                    <span class="text-gray-500 font-bold">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-right font-bold text-gray-900 dark:text-gray-100">{{ $instructor['name'] }}</td>
                            <td class="px-6 py-5 text-right">
                                <span class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100 font-bold text-sm">
                                    {{ $instructor['groups_count'] }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <span class="inline-flex items-center px-4 py-2 rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 font-bold text-sm">
                                    {{ $instructor['students_count'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- مقارنة الفروع (للـ super_admin فقط) --}}
    @if(count($branchesComparison) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl p-3 shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">مقارنة الفروع</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">أداء الفروع المختلفة</p>
                </div>
            </div>
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800">
                        <tr>
                            <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">اسم الفرع</th>
                            <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">الطلاب</th>
                            <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">المدرسين</th>
                            <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">المجموعات</th>
                            <th class="px-6 py-4 text-right text-sm font-black text-gray-700 dark:text-gray-200">الإيرادات (30 يوم)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($branchesComparison as $index => $branch)
                            <tr class="hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200 {{ $index % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800/50' }}">
                                <td class="px-6 py-5 text-right font-black text-gray-900 dark:text-gray-100">{{ $branch['name'] }}</td>
                                <td class="px-6 py-5 text-right">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100 font-bold text-sm">
                                        {{ $branch['students'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-100 font-bold text-sm">
                                        {{ $branch['instructors'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-100 font-bold text-sm">
                                        {{ $branch['groups'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-100 font-black text-sm">
                                        {{ number_format($branch['revenue'], 2) }} جنيه
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modern Chart Defaults
            Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
            Chart.defaults.font.size = 13;
            Chart.defaults.color = '#64748b';
            
            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart');
            if (attendanceCtx) {
                new Chart(attendanceCtx, {
                    type: 'bar',
                    data: {
                        labels: @js(array_column($attendanceByMonth, 'month')),
                        datasets: [
                            {
                                label: 'حاضر',
                                data: @js(array_column($attendanceByMonth, 'present')),
                                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                                borderColor: 'rgb(16, 185, 129)',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                            },
                            {
                                label: 'غائب',
                                data: @js(array_column($attendanceByMonth, 'absent')),
                                backgroundColor: 'rgba(239, 68, 68, 0.85)',
                                borderColor: 'rgb(239, 68, 68)',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                            },
                            {
                                label: 'متأخر',
                                data: @js(array_column($attendanceByMonth, 'late')),
                                backgroundColor: 'rgba(245, 158, 11, 0.85)',
                                borderColor: 'rgb(245, 158, 11)',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.85)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        weight: '600'
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        weight: '600'
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: @js(array_column($revenueByMonth, 'month')),
                        datasets: [{
                            label: 'الإيرادات (جنيه)',
                            data: @js(array_column($revenueByMonth, 'revenue')),
                            backgroundColor: 'rgba(99, 102, 241, 0.15)',
                            borderColor: 'rgb(99, 102, 241)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 6,
                            pointBackgroundColor: 'rgb(99, 102, 241)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 3,
                            pointHoverRadius: 8,
                            pointHoverBackgroundColor: 'rgb(99, 102, 241)',
                            pointHoverBorderWidth: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.85)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' ج';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        weight: '600'
                                    },
                                    callback: function(value) {
                                        return value.toLocaleString() + ' ج';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        weight: '600'
                                    }
                                }
                            }
                        }
                    }
                });
            }

            const profitMonthCtx = document.getElementById('profitMonthChart');
            if (profitMonthCtx) {
                new Chart(profitMonthCtx, {
                    type: 'bar',
                    data: {
                        labels: @js(array_column($profitByMonth, 'month')),
                        datasets: [{
                            label: 'الأرباح',
                            data: @js(array_column($profitByMonth, 'profit')),
                            backgroundColor: 'rgba(6, 182, 212, 0.75)',
                            borderColor: 'rgb(6, 182, 212)',
                            borderWidth: 2,
                            borderRadius: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                    }
                });
            }

            const profitCourseCtx = document.getElementById('profitCourseChart');
            if (profitCourseCtx) {
                new Chart(profitCourseCtx, {
                    type: 'bar',
                    data: {
                        labels: @js(array_column($profitByCourse, 'name')),
                        datasets: [{
                            label: 'الأرباح',
                            data: @js(array_column($profitByCourse, 'profit')),
                            backgroundColor: 'rgba(217, 70, 239, 0.75)',
                            borderColor: 'rgb(217, 70, 239)',
                            borderWidth: 2,
                            borderRadius: 8,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: true,
                    }
                });
            }

            const profitGroupCtx = document.getElementById('profitGroupChart');
            if (profitGroupCtx) {
                new Chart(profitGroupCtx, {
                    type: 'bar',
                    data: {
                        labels: @js(array_column($profitByGroup, 'name')),
                        datasets: [{
                            label: 'الأرباح',
                            data: @js(array_column($profitByGroup, 'profit')),
                            backgroundColor: 'rgba(245, 158, 11, 0.8)',
                            borderColor: 'rgb(245, 158, 11)',
                            borderWidth: 2,
                            borderRadius: 8,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: true,
                    }
                });
            }

            const subscribersCourseCtx = document.getElementById('subscribersCourseChart');
            if (subscribersCourseCtx) {
                new Chart(subscribersCourseCtx, {
                    type: 'bar',
                    data: {
                        labels: @js(array_column($subscribersByCourse, 'course')),
                        datasets: [{
                            label: 'عدد المشتركين',
                            data: @js(array_column($subscribersByCourse, 'count')),
                            backgroundColor: 'rgba(79, 70, 229, 0.75)',
                            borderColor: 'rgb(79, 70, 229)',
                            borderWidth: 2,
                            borderRadius: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                    }
                });
            }
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .modern-stat-card {
            position: relative;
            overflow: hidden;
        }
        
        .modern-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: translate(30%, -30%);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modern-stat-card {
            animation: fadeInUp 0.5s ease-out;
        }
        
        .modern-stat-card:nth-child(1) {
            animation-delay: 0.1s;
        }
        
        .modern-stat-card:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .modern-stat-card:nth-child(3) {
            animation-delay: 0.3s;
        }
        
        .modern-stat-card:nth-child(4) {
            animation-delay: 0.4s;
        }
    </style>
    @endpush

    </div>
</x-filament-panels::page>
