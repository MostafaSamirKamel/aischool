<div class="space-y-6">
    @php
        $days = [
            'saturday' => 'السبت',
            'sunday' => 'الأحد',
            'monday' => 'الاثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            'friday' => 'الجمعة',
        ];
        $student = $studentId ? \App\Models\Student::find($studentId) : null;
    @endphp

    @if($group)
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4">
            {{-- Course and Group Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ __('معلومات الكورس') }}</h4>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('اسم الكورس') }}</dt>
                            <dd class="font-medium">{{ $group->course->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('الوصف') }}</dt>
                            <dd class="text-sm">{{ $group->course->description ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('عدد الساعات') }}</dt>
                            <dd class="font-medium">{{ $group->course->duration_hours ?? '-' }} {{ __('ساعة') }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ __('معلومات المجموعة') }}</h4>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('المجموعة') }}</dt>
                            <dd class="font-medium">{{ $group->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('المدرس') }}</dt>
                            <dd class="font-medium">{{ $group->instructor->user->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('تاريخ البداية') }}</dt>
                            <dd class="font-medium">{{ $group->start_date ? $group->start_date->format('d/m/Y') : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('تاريخ النهاية') }}</dt>
                            <dd class="font-medium">{{ $group->end_date ? $group->end_date->format('d/m/Y') : '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Schedule --}}
            @if($group->schedule)
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-3">{{ __('جدول المواعيد') }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                            $schedules = is_string($group->schedule) 
                                ? json_decode($group->schedule, true) 
                                : $group->schedule;
                        @endphp
                        @foreach($schedules ?? [] as $schedule)
                            <div class="flex items-center gap-2 bg-white dark:bg-gray-800 p-3 rounded">
                                <x-filament::icon icon="heroicon-o-calendar" class="w-5 h-5 text-primary-600" />
                                <div>
                                    <div class="font-bold">{{ $days[$schedule['day']] ?? $schedule['day'] }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $schedule['start_time'] ?? $schedule['time'] ?? '-' }} 
                                        @if(isset($schedule['end_time']))
                                            - {{ $schedule['end_time'] }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Stats for this group --}}
            @php
                $groupId = $group->id;
                $totalSessions = $group->attendanceSessions()->count();
                $presentCount = $student
                    ? \App\Models\AttendanceRecord::where('student_id', $student->id)
                        ->whereHas('session', function ($q) use ($groupId) {
                            $q->where('group_id', $groupId);
                        })
                        ->where('status', 'present')
                        ->count()
                    : 0;
                $absentCount = $student
                    ? \App\Models\AttendanceRecord::where('student_id', $student->id)
                        ->whereHas('session', function ($q) use ($groupId) {
                            $q->where('group_id', $groupId);
                        })
                        ->where('status', 'absent')
                        ->count()
                    : 0;
                $attendanceRate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100) : 0;
            @endphp

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-lg text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $totalSessions }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('إجمالي الحصص') }}</div>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $presentCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('الحضور') }}</div>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-4 rounded-lg text-center">
                    <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $absentCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('الغياب') }}</div>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg text-center">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $attendanceRate }}%</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('نسبة الحضور') }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg text-center">
            <p class="text-gray-600 dark:text-gray-300">لا توجد مجموعة مرتبطة بهذه المادة.</p>
        </div>
    @endif
</div>
