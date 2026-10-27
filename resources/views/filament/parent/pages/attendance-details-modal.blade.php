<div class="space-y-4">
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('التاريخ') }}</dt>
                <dd class="font-medium">{{ $attendance->date ? $attendance->date->format('d/m/Y') : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('الحالة') }}</dt>
                <dd>
                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg
                        {{ match($attendance->status) {
                            'present' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'absent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            'late' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'excused' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
                        } }}">
                        {{ match($attendance->status) {
                            'present' => 'حاضر',
                            'absent' => 'غائب',
                            'late' => 'متأخر',
                            'excused' => 'غياب بعذر',
                            default => $attendance->status,
                        } }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('الكورس') }}</dt>
                <dd class="font-medium">{{ $attendance->session->group->course->name }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('المجموعة') }}</dt>
                <dd class="font-medium">{{ $attendance->session->group->name }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('موعد الحصة') }}</dt>
                <dd class="font-medium">{{ $attendance->session->attendance_date ? $attendance->session->attendance_date->format('d/m/Y H:i') : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('مدة الحصة') }}</dt>
                <dd class="font-medium">{{ $attendance->session->duration ?? '-' }} {{ __('دقيقة') }}</dd>
            </div>
        </dl>
    </div>

    @if($attendance->check_in_time || $attendance->check_out_time)
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <h4 class="font-bold text-gray-900 dark:text-white mb-3">{{ __('أوقات الحضور والانصراف') }}</h4>
            <dl class="grid grid-cols-2 gap-4">
                <div class="text-center">
                    <x-filament::icon icon="heroicon-o-arrow-right-on-rectangle" class="w-8 h-8 mx-auto mb-2 text-green-600" />
                    <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('وقت الحضور') }}</dt>
                    <dd class="text-xl font-bold">{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</dd>
                </div>
                <div class="text-center">
                    <x-filament::icon icon="heroicon-o-arrow-left-on-rectangle" class="w-8 h-8 mx-auto mb-2 text-red-600" />
                    <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('وقت الانصراف') }}</dt>
                    <dd class="text-xl font-bold">{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</dd>
                </div>
            </dl>
        </div>
    @endif

    @if($attendance->notes)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
            <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ __('ملاحظات') }}</h4>
            <p class="text-sm whitespace-pre-wrap">{{ $attendance->notes }}</p>
        </div>
    @endif

    @if($attendance->session->notes)
        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
            <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ __('ملاحظات الحصة') }}</h4>
            <p class="text-sm whitespace-pre-wrap">{{ $attendance->session->notes }}</p>
        </div>
    @endif
</div>
