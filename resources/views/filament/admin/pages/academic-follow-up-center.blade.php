<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">

        {{-- 1. Main Navigation Header & Section Switcher --}}
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-academic-cap class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                        مركز متابعة الطلاب اليومي الموحد (مركز المتابعة التعليمية للطلاب)
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        منصة موحدة لرصد غياب الطلاب، الواجبات المتأخرة، الامتحانات، الدرجات الضعيفة، والأقساط المستحقة، والتواصل المباشر مع أولياء الأمور
                    </p>
                </div>

                {{-- Core Sections Navigation Tabs --}}
                <div class="flex flex-wrap items-center gap-1.5 bg-gray-100 dark:bg-gray-900 p-1.5 rounded-lg border border-gray-200 dark:border-gray-700">
                    <button type="button"
                        wire:click="setSection('daily')"
                        class="px-3 py-1.5 text-xs font-bold rounded-md transition-all flex items-center gap-1.5 {{ $activeSection === 'daily' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-bell-alert class="w-4 h-4" />
                        متابعة اليوم
                    </button>
                    <button type="button"
                        wire:click="$set('activeSection', 'exams'); setTab('exams')"
                        class="px-3 py-1.5 text-xs font-bold rounded-md transition-all flex items-center gap-1.5 {{ ($activeSection === 'exams' || ($activeSection === 'daily' && $activeTab === 'exams')) ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                        متابعة الامتحانات
                    </button>
                    <button type="button"
                        wire:click="$set('activeSection', 'homework'); setTab('homework')"
                        class="px-3 py-1.5 text-xs font-bold rounded-md transition-all flex items-center gap-1.5 {{ ($activeSection === 'homework' || ($activeSection === 'daily' && $activeTab === 'homework')) ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-document-text class="w-4 h-4" />
                        متابعة الواجبات
                    </button>
                    <button type="button"
                        wire:click="setSection('students')"
                        class="px-3 py-1.5 text-xs font-bold rounded-md transition-all flex items-center gap-1.5 {{ $activeSection === 'students' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-user-group class="w-4 h-4" />
                        ملفات الطلاب
                    </button>
                    <button type="button"
                        wire:click="setSection('cases')"
                        class="px-3 py-1.5 text-xs font-bold rounded-md transition-all flex items-center gap-1.5 {{ $activeSection === 'cases' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                        حالات المتابعة
                    </button>
                    <button type="button"
                        wire:click="setSection('contacts')"
                        class="px-3 py-1.5 text-xs font-bold rounded-md transition-all flex items-center gap-1.5 {{ $activeSection === 'contacts' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-phone-arrow-up-right class="w-4 h-4" />
                        سجل التواصل
                    </button>
                    <button type="button"
                        wire:click="setSection('reports')"
                        class="px-3 py-1.5 text-xs font-bold rounded-md transition-all flex items-center gap-1.5 {{ $activeSection === 'reports' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <x-heroicon-o-chart-bar class="w-4 h-4" />
                        التقارير اليومية
                    </button>
                </div>
            </div>

            {{-- Cascading Filter Bar --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4">
                {{-- Branch --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">الفرع الأكاديمي</label>
                    <select wire:model.live="selectedBranchId" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        @foreach($this->branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Course --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">المقرر الدراسي</label>
                    <select wire:model.live="selectedCourseId" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        <option value="">جميع المقررات</option>
                        @foreach($this->courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Group --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">المجموعة الدراسية المصرح بها</label>
                    <select wire:model.live="selectedGroupId" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        <option value="">جميع المجموعات المصرح بها</option>
                        @foreach($this->groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Specific Activity (Exam / Assignment) or Search --}}
                <div>
                    @if($activeSection === 'exams' || ($activeSection === 'daily' && $activeTab === 'exams'))
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">الامتحان المحدد</label>
                        <select wire:model.live="selectedExamId" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">-- اختر الامتحان --</option>
                            @foreach($this->exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }} ({{ $exam->exam_date ? $exam->exam_date->format('Y-m-d') : 'بدون موعد' }})</option>
                            @endforeach
                        </select>
                    @elseif($activeSection === 'homework' || ($activeSection === 'daily' && $activeTab === 'homework'))
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">الواجب المحدد</label>
                        <select wire:model.live="selectedAssignmentId" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">-- اختر الواجب --</option>
                            @foreach($this->assignments as $assignment)
                                <option value="{{ $assignment->id }}">{{ $assignment->title }} (استحقاق: {{ $assignment->due_date ? $assignment->due_date->format('Y-m-d') : 'بدون موعد' }})</option>
                            @endforeach
                        </select>
                    @else
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">بحث بالاسم أو الهاتف</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="ابحث باسم الطالب أو رقم الهاتف..." class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 pl-8 pr-3" />
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" />
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- SECTION 1: متابعة اليوم (DAILY ALERTS QUEUE & KPIS) --}}
        {{-- ========================================================================= --}}
        @if($activeSection === 'daily')
            {{-- 8 KPI Metric Cards --}}
            @php $kpi = $this->kpiMetrics; @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                <div class="p-3 bg-red-50 dark:bg-red-950/40 rounded-xl border border-red-100 dark:border-red-900/50 shadow-sm text-center">
                    <span class="text-[11px] font-semibold text-red-700 dark:text-red-400">طلاب محتاجون متابعة</span>
                    <p class="text-2xl font-black text-red-900 dark:text-red-300 mt-1">{{ $kpi['students_needing_followup'] }}</p>
                    <span class="text-[9px] text-red-500">لديهم مشكلات أكاديمية</span>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-100 dark:border-amber-900/50 shadow-sm text-center">
                    <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">حالات جديدة</span>
                    <p class="text-2xl font-black text-amber-900 dark:text-amber-300 mt-1">{{ $kpi['new_cases'] }}</p>
                    <span class="text-[9px] text-amber-500">في انتظار التواصل</span>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-100 dark:border-emerald-900/50 shadow-sm text-center">
                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">تم التواصل</span>
                    <p class="text-2xl font-black text-emerald-900 dark:text-emerald-300 mt-1">{{ $kpi['contacted_cases'] }}</p>
                    <span class="text-[9px] text-emerald-500">تم التواصل بنجاح</span>
                </div>
                <div class="p-3 bg-purple-50 dark:bg-purple-950/40 rounded-xl border border-purple-100 dark:border-purple-900/50 shadow-sm text-center">
                    <span class="text-[11px] font-semibold text-purple-700 dark:text-purple-400">واجبات لم تُسلّم</span>
                    <p class="text-2xl font-black text-purple-900 dark:text-purple-300 mt-1">{{ $kpi['missing_homework'] }}</p>
                    <span class="text-[9px] text-purple-500">تجاوزت الموعد</span>
                </div>
                <div class="p-3 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-100 dark:border-rose-900/50 shadow-sm text-center">
                    <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400">درجات ضعيفة</span>
                    <p class="text-2xl font-black text-rose-900 dark:text-rose-300 mt-1">{{ $kpi['low_grades'] }}</p>
                    <span class="text-[9px] text-rose-500">أقل من 50%</span>
                </div>
                <div class="p-3 bg-orange-50 dark:bg-orange-950/40 rounded-xl border border-orange-100 dark:border-orange-900/50 shadow-sm text-center">
                    <span class="text-[11px] font-semibold text-orange-700 dark:text-orange-400">غياب الطلاب</span>
                    <p class="text-2xl font-black text-orange-900 dark:text-orange-300 mt-1">{{ $kpi['student_absences'] }}</p>
                    <span class="text-[9px] text-orange-500">غياب مؤكد</span>
                </div>
                <div class="p-3 bg-indigo-50 dark:bg-indigo-950/40 rounded-xl border border-indigo-100 dark:border-indigo-900/50 shadow-sm text-center">
                    <span class="text-[11px] font-semibold text-indigo-700 dark:text-indigo-400">امتحانات لم تُحل</span>
                    <p class="text-2xl font-black text-indigo-900 dark:text-indigo-300 mt-1">{{ $kpi['missed_exams'] }}</p>
                    <span class="text-[9px] text-indigo-500">تخلف عن الامتحان</span>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-100 dark:border-blue-900/50 shadow-sm text-center">
                    <span class="text-[11px] font-semibold text-blue-700 dark:text-blue-400">تواصل اليوم</span>
                    <p class="text-2xl font-black text-blue-900 dark:text-blue-300 mt-1">{{ $kpi['contacts_today'] }}</p>
                    <span class="text-[9px] text-blue-500">محاولات مسجلة</span>
                </div>
            </div>

            {{-- Quick Filter Pills --}}
            <div class="flex flex-wrap items-center gap-2 pt-1 pb-1">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">تصفية التنبيهات:</span>
                <button type="button" wire:click="$set('issueFilter', 'all')"
                    class="px-3 py-1 text-xs rounded-full font-semibold transition-all {{ $issueFilter === 'all' ? 'bg-gray-800 text-white dark:bg-white dark:text-gray-900 shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400' }}">
                    الكل
                </button>
                <button type="button" wire:click="$set('issueFilter', 'confirmed_absence')"
                    class="px-3 py-1 text-xs rounded-full font-semibold transition-all {{ $issueFilter === 'confirmed_absence' ? 'bg-orange-600 text-white' : 'bg-orange-50 text-orange-700 hover:bg-orange-100' }}">
                    غياب مؤكد
                </button>
                <button type="button" wire:click="$set('issueFilter', 'repeated_absence')"
                    class="px-3 py-1 text-xs rounded-full font-semibold transition-all {{ $issueFilter === 'repeated_absence' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                    غياب متكرر
                </button>
                <button type="button" wire:click="$set('issueFilter', 'missing_homework')"
                    class="px-3 py-1 text-xs rounded-full font-semibold transition-all {{ $issueFilter === 'missing_homework' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                    واجب لم يسلّم
                </button>
                <button type="button" wire:click="$set('issueFilter', 'low_homework_score')"
                    class="px-3 py-1 text-xs rounded-full font-semibold transition-all {{ $issueFilter === 'low_homework_score' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    واجب أقل من 50%
                </button>
                <button type="button" wire:click="$set('issueFilter', 'missed_exam')"
                    class="px-3 py-1 text-xs rounded-full font-semibold transition-all {{ $issueFilter === 'missed_exam' ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' }}">
                    امتحان لم يحل
                </button>
                <button type="button" wire:click="$set('issueFilter', 'low_exam_score')"
                    class="px-3 py-1 text-xs rounded-full font-semibold transition-all {{ $issueFilter === 'low_exam_score' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    امتحان أقل من 50%
                </button>
            </div>

            {{-- Main Daily Student List Table --}}
            @php $alerts = $this->alertsList; @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                            قائمة الطلاب المحتاجين لمتابعة اليوم ({{ $alerts->count() }} تنبيه)
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">يتم رصد الحالات تلقائياً ولا يتم إنشاء مهمة متابعة إلا بقرار الموظف المسؤول</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="py-3 px-4">اسم الطالب</th>
                                <th class="py-3 px-4">الكورس</th>
                                <th class="py-3 px-4">الجروب</th>
                                <th class="py-3 px-4">سبب المتابعة</th>
                                <th class="py-3 px-4">الحالة التعليمية</th>
                                <th class="py-3 px-4">موبايل ولي الأمر</th>
                                <th class="py-3 px-4">موبايل الطالب</th>
                                <th class="py-3 px-4">مسؤول المتابعة</th>
                                <th class="py-3 px-4">حالة المتابعة</th>
                                <th class="py-3 px-4 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($alerts as $item)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $item['student_name'] }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $item['student_code'] }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-gray-700 dark:text-gray-300">{{ $item['course_name'] }}</td>
                                    <td class="py-3.5 px-4 text-gray-600 dark:text-gray-400">{{ $item['group_name'] }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold
                                            {{ in_array($item['issue_type'], ['confirmed_absence', 'repeated_absence']) ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ in_array($item['issue_type'], ['missing_homework', 'missed_exam']) ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ in_array($item['issue_type'], ['low_homework_score', 'low_exam_score']) ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ $item['issue_type'] === 'overdue_installment' ? 'bg-emerald-100 text-emerald-800' : '' }}">
                                            {{ $item['issue_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300 max-w-xs truncate" title="{{ $item['academic_status'] }}">
                                        {{ $item['academic_status'] }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs">
                                        @if($item['parent_phone'] !== '— (محجوب)' && $item['parent_phone'] !== 'غير مسجل')
                                            <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-semibold" dir="ltr">
                                                <x-heroicon-m-phone class="w-3.5 h-3.5 text-emerald-600" />
                                                <span>{{ $item['parent_phone'] }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">{{ $item['parent_phone'] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs">
                                        @if($item['student_phone'] !== '— (محجوب)' && $item['student_phone'] !== 'غير مسجل')
                                            <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 font-semibold" dir="ltr">
                                                <x-heroicon-m-device-phone-mobile class="w-3.5 h-3.5 text-gray-400" />
                                                <span>{{ $item['student_phone'] }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">{{ $item['student_phone'] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300 font-medium">{{ $item['assigned_employee'] }}</td>
                                    <td class="py-3.5 px-4">
                                        @if($item['has_active_case'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $item['existing_task']->status_badge_color === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $item['case_status'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-[11px]">بدون متابعة</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" wire:click="openStudentProfile({{ $item['student_id'] }})"
                                                class="px-2 py-1 text-[11px] font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded">
                                                ملف الطالب
                                            </button>
                                            @if(!$item['has_active_case'])
                                                <button type="button"
                                                    wire:click="openFollowUpModal({{ $item['student_id'] }}, '{{ $item['source_type'] }}', {{ $item['source_id'] }}, '{{ addslashes($item['issue_label']) }}', '{{ addslashes($item['academic_status']) }}', {{ $item['group_id'] }})"
                                                    class="px-2.5 py-1 text-[11px] font-bold bg-primary-600 hover:bg-primary-700 text-white rounded shadow-sm">
                                                    إنشاء متابعة
                                                </button>
                                            @else
                                                <button type="button"
                                                    wire:click="openContactLogModal({{ $item['existing_task']->id }})"
                                                    class="px-2.5 py-1 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded shadow-sm">
                                                    تسجيل تواصل
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-12 text-center text-gray-400">
                                        <x-heroicon-o-check-circle class="w-12 h-12 mx-auto text-emerald-400 mb-2 opacity-60" />
                                        <p class="font-bold text-gray-600 dark:text-gray-300">لا توجد تنبيهات متابعة مطلوبة ضمن المجموعات المحددة حالياً</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Detailed Activity Roster Section (Exams & Homework Roster) --}}
            @php $roster = $this->roster; @endphp
            @if($roster->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mt-6">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h4 class="font-bold text-gray-900 dark:text-white text-xs flex items-center gap-2">
                            <span>كشف تفصيلي لمجموعة: {{ $this->groups->firstWhere('id', $this->selectedGroupId)?->name }}</span>
                            <span class="text-gray-400 font-normal">({{ $this->activeTab === 'exams' ? 'امتحان محدد' : 'واجب محدد' }})</span>
                        </h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 font-semibold border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="py-3 px-4">اسم الطالب</th>
                                    <th class="py-3 px-4">الحالة</th>
                                    <th class="py-3 px-4 text-center">الدرجة</th>
                                    <th class="py-3 px-4">موبايل ولي الأمر</th>
                                    <th class="py-3 px-4">موبايل الطالب</th>
                                    <th class="py-3 px-4 text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($roster as $row)
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30">
                                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $row['student_name'] }}</td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold {{ $row['status_badge'] === 'danger' ? 'bg-red-100 text-red-800' : ($row['status_badge'] === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700') }}">
                                                {{ $row['status_label'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono font-bold">{{ $row['score_display'] }}</td>
                                        <td class="py-3 px-4 font-mono font-semibold text-emerald-700" dir="ltr">{{ $row['parent_phone'] }}</td>
                                        <td class="py-3 px-4 font-mono text-gray-600" dir="ltr">{{ $row['student_phone'] }}</td>
                                        <td class="py-3 px-4 text-center">
                                            @if(!$row['has_follow_up'])
                                                <button type="button" wire:click="openFollowUpModal({{ $row['student_id'] }}, '{{ $this->activeTab === 'exams' ? 'exam' : 'homework' }}', {{ $this->activeTab === 'exams' ? $this->selectedExamId : $this->selectedAssignmentId }}, '{{ $row['status_label'] }}', '{{ $row['status_label'] }}', {{ $this->selectedGroupId }})" class="px-2 py-1 text-[11px] font-bold bg-red-600 text-white rounded">إنشاء متابعة</button>
                                            @else
                                                <button type="button" wire:click="openContactLogModal({{ $row['task']->id }})" class="px-2 py-1 text-[11px] font-bold bg-emerald-600 text-white rounded">تسجيل تواصل</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

        {{-- ========================================================================= --}}
        {{-- SECTION 2: EXAMS & HOMEWORK ROSTER VIEW (Dedicated Tab) --}}
        {{-- ========================================================================= --}}
        @if($activeSection === 'exams' || $activeSection === 'homework')
            @php $roster = $this->roster; @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 dark:text-white">
                        {{ $activeSection === 'exams' ? 'كشف متابعة الامتحانات' : 'كشف متابعة الواجبات' }} ({{ $roster->count() }} طالب مؤهل)
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 font-semibold border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="py-3 px-4">اسم الطالب</th>
                                <th class="py-3 px-4">الحالة</th>
                                <th class="py-3 px-4 text-center">الدرجة</th>
                                <th class="py-3 px-4">موبايل ولي الأمر</th>
                                <th class="py-3 px-4">موبايل الطالب</th>
                                <th class="py-3 px-4 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($roster as $row)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30">
                                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $row['student_name'] }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold {{ $row['status_badge'] === 'danger' ? 'bg-red-100 text-red-800' : ($row['status_badge'] === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700') }}">
                                            {{ $row['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono font-bold">{{ $row['score_display'] }}</td>
                                    <td class="py-3 px-4 font-mono font-semibold text-emerald-700" dir="ltr">{{ $row['parent_phone'] }}</td>
                                    <td class="py-3 px-4 font-mono text-gray-600" dir="ltr">{{ $row['student_phone'] }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @if(!$row['has_follow_up'])
                                            <button type="button" wire:click="openFollowUpModal({{ $row['student_id'] }}, '{{ $this->activeTab === 'exams' ? 'exam' : 'homework' }}', {{ $this->activeTab === 'exams' ? $this->selectedExamId : $this->selectedAssignmentId }}, '{{ $row['status_label'] }}', '{{ $row['status_label'] }}', {{ $this->selectedGroupId }})" class="px-2.5 py-1 text-[11px] font-bold bg-red-600 text-white rounded">إنشاء متابعة</button>
                                        @else
                                            <button type="button" wire:click="openContactLogModal({{ $row['task']->id }})" class="px-2.5 py-1 text-[11px] font-bold bg-emerald-600 text-white rounded">تسجيل تواصل</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">لا يوجد طلاب مؤهلين في النشاط المحدد</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- SECTION 3: ملفات الطلاب --}}
        {{-- ========================================================================= --}}
        @if($activeSection === 'students')
            @php $students = $this->studentsList; @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">دليل ملفات الطلاب المصرح بمتابعتهم ({{ $students->count() }} طالب)</h3>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 font-semibold border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="py-3 px-4">كود الطالب</th>
                                <th class="py-3 px-4">اسم الطالب</th>
                                <th class="py-3 px-4">المجموعات المقيد بها</th>
                                <th class="py-3 px-4">موبايل ولي الأمر</th>
                                <th class="py-3 px-4">موبايل الطالب</th>
                                <th class="py-3 px-4 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($students as $st)
                                @php
                                    $service = app(\App\Services\AcademicFollowUpService::class);
                                    $canViewPhones = $service->canViewPhone(auth()->user());
                                    $parentP = $canViewPhones ? $service->resolveParentPhone($st) : '— (محجوب)';
                                    $studentP = $canViewPhones ? $service->resolveStudentPhone($st) : '— (محجوب)';
                                @endphp
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30">
                                    <td class="py-3.5 px-4 font-mono font-bold text-gray-500">{{ $st->student_code ?? "STD-{$st->id}" }}</td>
                                    <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ $st->user?->name ?? 'طالب' }}</td>
                                    <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">
                                        @foreach($st->enrollments->where('status', 'active') as $enr)
                                            <span class="inline-block bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-[10px] mr-1 mb-1">
                                                {{ $enr->group?->course?->name }} ({{ $enr->group?->name }})
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-semibold text-emerald-700" dir="ltr">{{ $parentP }}</td>
                                    <td class="py-3.5 px-4 font-mono text-gray-600" dir="ltr">{{ $studentP }}</td>
                                    <td class="py-3.5 px-4 text-center">
                                        <button type="button" wire:click="openStudentProfile({{ $st->id }})" class="px-3 py-1.5 text-xs font-bold bg-primary-600 hover:bg-primary-700 text-white rounded">عرض الملف الشامل</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">لا يوجد طلاب مسجلين</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- SECTION 4: حالات المتابعة --}}
        {{-- ========================================================================= --}}
        @if($activeSection === 'cases')
            @php $cases = $this->followUpCases; @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">سجل حالات المتابعة ({{ $cases->count() }} حالة)</h3>
                        <p class="text-xs text-gray-500 mt-0.5">الحالات تعتمد على حالتين فقط: "جديدة" في انتظار التواصل، و "تم التواصل" بمجرد إتمام الاتصال</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500">الحالة:</span>
                        <select wire:model.live="caseStatusFilter" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="all">الكل</option>
                            <option value="new">جديدة (في انتظار التواصل)</option>
                            <option value="contacted">تم التواصل</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 font-semibold border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="py-3 px-4">رقم الحالة</th>
                                <th class="py-3 px-4">اسم الطالب</th>
                                <th class="py-3 px-4">عنوان المتابعة</th>
                                <th class="py-3 px-4">الموظف المسؤول</th>
                                <th class="py-3 px-4">حالة المتابعة</th>
                                <th class="py-3 px-4">تاريخ الإنشاء</th>
                                <th class="py-3 px-4 text-center">محاولات التواصل</th>
                                <th class="py-3 px-4 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($cases as $task)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30">
                                    <td class="py-3.5 px-4 font-mono font-bold text-gray-400">#{{ $task->id }}</td>
                                    <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ $task->student?->user?->name ?? 'طالب' }}</td>
                                    <td class="py-3.5 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $task->title }}</td>
                                    <td class="py-3.5 px-4 font-bold text-primary-700 dark:text-primary-300">{{ $task->assignee?->name ?? 'غير محدد' }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $task->status_badge_color === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $task->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-400 font-mono">{{ $task->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="py-3.5 px-4 text-center font-bold">{{ $task->contacts->count() }}</td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" wire:click="openContactLogModal({{ $task->id }})" class="px-2.5 py-1 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded">تسجيل تواصل</button>
                                            <button type="button" wire:click="openReassignModal({{ $task->id }})" class="px-2 py-1 text-[11px] font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded">إعادة تكليف</button>
                                            <button type="button" wire:click="openHistoryModal({{ $task->id }})" class="p-1 text-gray-500 hover:text-gray-900 rounded"><x-heroicon-m-clock class="w-4 h-4" /></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-gray-400">لا توجد حالات متابعة مسجلة حالياً</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- SECTION 5: سجل التواصل --}}
        {{-- ========================================================================= --}}
        @if($activeSection === 'contacts')
            @php $contacts = $this->contactLogs; @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-white">سجل محاولات التواصل المستقل ({{ $contacts->count() }} محاولة)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 font-semibold border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="py-3 px-4">وقت التواصل</th>
                                <th class="py-3 px-4">اسم الطالب</th>
                                <th class="py-3 px-4">الشخص المتواصل معه</th>
                                <th class="py-3 px-4">رقم الهاتف</th>
                                <th class="py-3 px-4">وسيلة الاتصال</th>
                                <th class="py-3 px-4">النتيجة</th>
                                <th class="py-3 px-4">الملاحظات</th>
                                <th class="py-3 px-4">الموظف المسجل</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($contacts as $c)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30">
                                    <td class="py-3.5 px-4 font-mono text-gray-500">{{ $c->contacted_at?->format('Y-m-d H:i') }}</td>
                                    <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ $c->student?->user?->name ?? 'طالب' }}</td>
                                    <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300">{{ $c->guardian_name ?? 'ولي الأمر' }}</td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-emerald-700" dir="ltr">{{ $c->guardian_phone }}</td>
                                    <td class="py-3.5 px-4 text-gray-600">{{ $c->method_label }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $c->contact_result === 'contacted' || $c->contact_result === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $c->result_label }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 max-w-sm truncate" title="{{ $c->notes }}">{{ $c->notes ?: '—' }}</td>
                                    <td class="py-3.5 px-4 font-medium">{{ $c->recordedByUser?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-gray-400">لا توجد محاولات تواصل مسجلة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- SECTION 6: التقارير اليومية --}}
        {{-- ========================================================================= --}}
        @if($activeSection === 'reports')
            @php $rep = $this->dailyReport; @endphp
            <div class="space-y-6">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300">تاريخ التقرير:</label>
                        <input type="date" wire:model.live="reportDate" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center shadow-sm">
                        <span class="text-xs font-medium text-gray-500">حالات جديدة</span>
                        <p class="text-2xl font-black text-amber-600 mt-1">{{ $rep['new_cases_count'] }}</p>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center shadow-sm">
                        <span class="text-xs font-medium text-gray-500">تم التواصل</span>
                        <p class="text-2xl font-black text-emerald-600 mt-1">{{ $rep['contacted_cases_count'] }}</p>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center shadow-sm">
                        <span class="text-xs font-medium text-gray-500">إجمالي الاتصالات</span>
                        <p class="text-2xl font-black text-blue-600 mt-1">{{ $rep['total_contact_attempts'] }}</p>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center shadow-sm">
                        <span class="text-xs font-medium text-gray-500">تم الرد</span>
                        <p class="text-2xl font-black text-emerald-700 mt-1">{{ $rep['successful_contacts'] }}</p>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center shadow-sm">
                        <span class="text-xs font-medium text-gray-500">لم يرد / غير متاح</span>
                        <p class="text-2xl font-black text-red-600 mt-1">{{ $rep['unsuccessful_contacts'] }}</p>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center shadow-sm">
                        <span class="text-xs font-medium text-gray-500">إعادة التكليف</span>
                        <p class="text-2xl font-black text-purple-600 mt-1">{{ $rep['reassignments_count'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- MODALS --}}
        {{-- ========================================================================= --}}

        {{-- Modal: Create Follow-up Case --}}
        @if($showFollowUpModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full border border-gray-100 dark:border-gray-700 overflow-hidden" @click.away="$wire.set('showFollowUpModal', false)">
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">إنشاء حالة متابعة جديدة</h3>
                        <button type="button" wire:click="$set('showFollowUpModal', false)" class="text-gray-400 hover:text-gray-600"><x-heroicon-m-x-mark class="w-5 h-5" /></button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg text-xs space-y-1">
                            <div class="flex justify-between"><span class="text-gray-500">الطالب:</span><span class="font-bold">{{ $activeStudentName }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">هاتف ولي الأمر:</span><span class="font-mono font-bold text-emerald-600" dir="ltr">{{ $activeParentPhone }}</span></div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الموظف المسؤول عن المتابعة <span class="text-red-500">*</span></label>
                            <select wire:model="taskAssignedTo" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($this->employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">عنوان المتابعة <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="activeItemTitle" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">التفاصيل والملاحظات <span class="text-red-500">*</span></label>
                            <textarea wire:model="taskReason" rows="3" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                        <div class="text-[11px] text-gray-500 bg-blue-50 dark:bg-blue-950/40 p-2.5 rounded-lg">
                            ℹ️ الحالة تبدأ تلقائياً كـ <strong>"جديدة"</strong>، وتتحول إلى <strong>"تم التواصل"</strong> فقط عند توثيق اتصال ناجح فعلي مع ولي الأمر.
                        </div>
                    </div>
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showFollowUpModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600">إلغاء</button>
                        <button type="button" wire:click="saveFollowUpTask" class="px-4 py-2 text-xs font-bold bg-primary-600 text-white rounded-lg">حفظ الحالة</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal: Record Contact Attempt --}}
        @if($showContactLogModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full border border-gray-100 dark:border-gray-700 overflow-hidden" @click.away="$wire.set('showContactLogModal', false)">
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">توثيق محاولة تواصل مع ولي الأمر</h3>
                        <button type="button" wire:click="$set('showContactLogModal', false)" class="text-gray-400 hover:text-gray-600"><x-heroicon-m-x-mark class="w-5 h-5" /></button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg text-xs space-y-1">
                            <div class="flex justify-between"><span class="text-gray-500">الطالب:</span><span class="font-bold">{{ $activeStudentName }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">رقم ولي الأمر:</span><span class="font-mono font-bold text-emerald-600" dir="ltr">{{ $activeParentPhone }}</span></div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">وسيلة التواصل</label>
                            <select wire:model="contactMethod" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="phone_call">اتصال هاتفي</option>
                                <option value="whatsapp">محادثة واتساب</option>
                                <option value="in_person">مقابلة شخصية في الفرع</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">نتيجة التواصل <span class="text-red-500">*</span></label>
                            <select wire:model="contactResult" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="contacted">تم التواصل (تم الرد من ولي الأمر وإبلاغه)</option>
                                <option value="no_answer">لم يرد (حاول الموظف ولم يجيب الطرف الآخر)</option>
                                <option value="unreachable">الرقم غير متاح / مغلق</option>
                            </select>
                            <p class="text-[11px] text-gray-500 mt-1">⚠️ ملاحظة: اختيار <strong>"لم يرد"</strong> أو <strong>"الرقم غير متاح"</strong> يسجل المحاولة ويحافظ على حالة المهمة <strong>"جديدة"</strong> دون إغلاقها.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">ملاحظات ونتيجة المكالمة</label>
                            <textarea wire:model="contactNotes" rows="3" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showContactLogModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600">إلغاء</button>
                        <button type="button" wire:click="saveContactLog" class="px-4 py-2 text-xs font-bold bg-emerald-600 text-white rounded-lg">تسجيل المحاولة</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal: Reassign Case --}}
        @if($showReassignModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full border border-gray-100 dark:border-gray-700 overflow-hidden" @click.away="$wire.set('showReassignModal', false)">
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">إعادة تكليف مسؤول الحالة</h3>
                        <button type="button" wire:click="$set('showReassignModal', false)" class="text-gray-400 hover:text-gray-600"><x-heroicon-m-x-mark class="w-5 h-5" /></button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الموظف البديل المكلف <span class="text-red-500">*</span></label>
                            <select wire:model="reassignToUserId" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">-- اختر الموظف --</option>
                                @foreach($this->employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">سبب إعادة التكليف</label>
                            <textarea wire:model="reassignReason" rows="2" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showReassignModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600">إلغاء</button>
                        <button type="button" wire:click="saveReassignment" class="px-4 py-2 text-xs font-bold bg-purple-600 text-white rounded-lg">تأكيد التكليف</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal: Contact History --}}
        @if($showHistoryModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full border border-gray-100 dark:border-gray-700 overflow-hidden" @click.away="$wire.set('showHistoryModal', false)">
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">سجل محاولات التواصل للحالة #{{ $activeTaskId }}</h3>
                        <button type="button" wire:click="$set('showHistoryModal', false)" class="text-gray-400 hover:text-gray-600"><x-heroicon-m-x-mark class="w-5 h-5" /></button>
                    </div>
                    <div class="p-6 max-h-96 overflow-y-auto space-y-3">
                        @forelse($timelineContacts as $tc)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $tc['guardian_name'] ?? 'ولي الأمر' }}</span>
                                    <span class="font-mono text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($tc['contacted_at'])->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-emerald-600" dir="ltr">{{ $tc['guardian_phone'] }}</span>
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-200 dark:bg-gray-600">
                                        {{ $tc['contact_result'] === 'contacted' ? 'تم التواصل' : ($tc['contact_result'] === 'no_answer' ? 'لم يرد' : $tc['contact_result']) }}
                                    </span>
                                </div>
                                @if(!empty($tc['notes']))
                                    <p class="text-gray-600 dark:text-gray-300 text-[11px] pt-1 border-t border-gray-200 mt-1">{{ $tc['notes'] }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-center text-gray-400 py-6 text-xs">لا توجد محاولات تواصل مسجلة بعد</p>
                        @endforelse
                    </div>
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="button" wire:click="$set('showHistoryModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600">إغلاق</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal: Student Follow-up Profile --}}
        @if($showStudentProfileModal && $activeStudentProfile)
            @php $p = $activeStudentProfile; @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-3xl w-full border border-gray-100 dark:border-gray-700 overflow-hidden max-h-[90vh] flex flex-col" @click.away="$wire.set('showStudentProfileModal', false)">
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">الملف الشامل لمتابعة الطالب: {{ $p['name'] }}</h3>
                            <div class="flex items-center gap-3 text-xs text-gray-500 mt-1 font-mono">
                                <span>كود: {{ $p['code'] }}</span>
                                <span>موبايل ولي الأمر: <strong class="text-emerald-600" dir="ltr">{{ $p['parent_phone'] }}</strong></span>
                                <span>موبايل الطالب: <strong class="text-gray-700" dir="ltr">{{ $p['student_phone'] }}</strong></span>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('showStudentProfileModal', false)" class="text-gray-400 hover:text-gray-600"><x-heroicon-m-x-mark class="w-6 h-6" /></button>
                    </div>
                    <div class="p-6 overflow-y-auto space-y-6 text-xs flex-1">
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white mb-2">المقررات والمجموعات المقيد بها</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($p['courses'] as $c)
                                    <div class="p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $c['course_name'] }}</div>
                                        <div class="text-gray-500 text-[11px]">مجموعة: {{ $c['group_name'] }}</div>
                                        <div class="text-primary-600 text-[10px]">المدرب: {{ $c['instructors'] ?: 'غير محدد' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white mb-2">ملخص الحضور والغياب</h4>
                            <div class="grid grid-cols-4 gap-2 text-center">
                                <div class="p-2 bg-gray-50 dark:bg-gray-700/30 rounded-lg"><span class="text-gray-400 text-[10px]">إجمالي الجلسات</span><p class="font-bold text-sm">{{ $p['attendance']['total'] }}</p></div>
                                <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 rounded-lg"><span class="text-emerald-700 text-[10px]">حضور</span><p class="font-bold text-sm text-emerald-700">{{ $p['attendance']['present'] }}</p></div>
                                <div class="p-2 bg-red-50 dark:bg-red-950/40 rounded-lg"><span class="text-red-700 text-[10px]">غياب مؤكد</span><p class="font-bold text-sm text-red-700">{{ $p['attendance']['absent'] }}</p></div>
                                <div class="p-2 bg-blue-50 dark:bg-blue-950/40 rounded-lg"><span class="text-blue-700 text-[10px]">نسبة الحضور</span><p class="font-bold text-sm text-blue-700">{{ $p['attendance']['rate'] }}%</p></div>
                            </div>
                        </div>

                        @if($p['can_view_financial'])
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white mb-2 text-emerald-700">المعلومات المالية والأقساط المستحقة (للاطلاع والمتابعة فقط)</h4>
                                <div class="border border-emerald-100 rounded-lg overflow-hidden bg-emerald-50/20">
                                    <table class="w-full text-right text-[11px]">
                                        <thead class="bg-emerald-50 text-emerald-800">
                                            <tr>
                                                <th class="p-2">رقم القسط</th>
                                                <th class="p-2">المبلغ المستحق</th>
                                                <th class="p-2">المسدد</th>
                                                <th class="p-2">المتبقي</th>
                                                <th class="p-2">تاريخ الاستحقاق</th>
                                                <th class="p-2 text-center">الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-emerald-100">
                                            @forelse($p['installments'] as $inst)
                                                <tr>
                                                    <td class="p-2 font-bold font-mono">قسط {{ $inst['installment_number'] }}</td>
                                                    <td class="p-2 font-mono">{{ $inst['amount'] }} ج.م</td>
                                                    <td class="p-2 font-mono text-emerald-600">{{ $inst['paid_amount'] }} ج.م</td>
                                                    <td class="p-2 font-mono font-bold {{ $inst['remaining'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $inst['remaining'] }} ج.م</td>
                                                    <td class="p-2 font-mono text-gray-500">{{ $inst['due_date'] }}</td>
                                                    <td class="p-2 text-center">
                                                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold {{ $inst['status'] === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($inst['status'] === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100') }}">{{ $inst['status'] }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="p-3 text-center text-gray-400">لا توجد أقساط مسجلة</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="button" wire:click="$set('showStudentProfileModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600">إغلاق</button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
