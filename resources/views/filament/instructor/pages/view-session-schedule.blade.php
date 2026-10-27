<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Card --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->record->title ?: 'سيشن ' . $this->record->group->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $this->record->date->format('l, d F Y') }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-primary-600">
                        {{ $this->record->time_range }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ $this->record->duration_formatted }}
                    </div>
                </div>
            </div>

            {{-- Status Badge --}}
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                    @if($this->record->status === 'scheduled') bg-blue-100 text-blue-800
                    @elseif($this->record->status === 'completed') bg-green-100 text-green-800
                    @elseif($this->record->status === 'cancelled') bg-red-100 text-red-800
                    @else bg-yellow-100 text-yellow-800
                    @endif">
                    @switch($this->record->status)
                        @case('scheduled') مجدول @break
                        @case('completed') مكتمل @break
                        @case('cancelled') ملغي @break
                        @case('rescheduled') تم إعادة الجدولة @break
                    @endswitch
                </span>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($this->record->type === 'regular') bg-blue-100 text-blue-800
                    @elseif($this->record->type === 'makeup') bg-Blue-100 text-Blue-800
                    @elseif($this->record->type === 'extra') bg-green-100 text-green-800
                    @else bg-red-100 text-red-800
                    @endif">
                    @switch($this->record->type)
                        @case('regular') سيشن عادي @break
                        @case('makeup') سيشن تعويضي @break
                        @case('extra') سيشن إضافي @break
                        @case('exam') امتحان @break
                    @endswitch
                </span>

                @if($this->record->attendance_taken)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        ✓ تم تسجيل الحضور
                    </span>
                @endif
            </div>
        </div>

        {{-- Conflict Warnings --}}
        @if($this->record->has_conflicts && $this->record->conflicts_data)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="mr-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            تحذير: توجد تضاربات في الجدول
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300 space-y-2">
                            @if(isset($this->record->conflicts_data['instructor']))
                                <div>
                                    <strong>تضارب مع جدولك:</strong>
                                    <ul class="list-disc list-inside mt-1">
                                        @foreach($this->record->conflicts_data['instructor'] as $conflict)
                                            <li>لديك سيشن آخر مع {{ $conflict['group'] }} ({{ $conflict['time'] }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(isset($this->record->conflicts_data['classroom']))
                                <div>
                                    <strong>تضارب في القاعة:</strong>
                                    <ul class="list-disc list-inside mt-1">
                                        @foreach($this->record->conflicts_data['classroom'] as $conflict)
                                            <li>محجوزة لـ {{ $conflict['group'] }} مع {{ $conflict['instructor'] }} ({{ $conflict['time'] }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(isset($this->record->conflicts_data['students']))
                                <div>
                                    <strong>تضارب مع طلاب:</strong>
                                    <ul class="list-disc list-inside mt-1">
                                        @foreach($this->record->conflicts_data['students'] as $conflict)
                                            <li>{{ $conflict['student_name'] }} - لديه {{ $conflict['conflicting_group'] }} ({{ $conflict['time'] }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Info Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Session Info --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    معلومات السيشن
                </h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">المجموعة</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->group->name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الكورس</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->course->name }}
                        </dd>
                    </div>

                    @if($this->record->classroom)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">القاعة</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->classroom->name }}
                            @if($this->record->classroom->capacity)
                                <span class="text-gray-500">(سعة {{ $this->record->classroom->capacity }} طالب)</span>
                            @endif
                        </dd>
                    </div>
                    @endif

                    @if($this->record->description)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الوصف</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->description }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Additional Info --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    معلومات إضافية
                </h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تاريخ الإنشاء</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->created_at->format('Y-m-d H:i') }}
                        </dd>
                    </div>

                    @if($this->record->attendance_taken)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تسجيل الحضور</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            تم التسجيل في {{ $this->record->attendance_taken_at->format('Y-m-d H:i') }}
                        </dd>
                    </div>
                    @endif

                    @if($this->record->status === 'cancelled')
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">سبب الإلغاء</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->cancellation_reason }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تاريخ الإلغاء</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->cancelled_at->format('Y-m-d H:i') }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-filament-panels::page>
