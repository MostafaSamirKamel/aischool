<div class="space-y-4">
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('تاريخ التسليم') }}</dt>
                <dd class="font-medium">{{ $submission->submitted_at ? $submission->submitted_at->format('d/m/Y H:i') : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">{{ __('الحالة') }}</dt>
                <dd>
                    @if($submission->graded)
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            تم التقييم
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            تم التسليم
                        </span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    @if($submission->notes)
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ __('ملاحظاتك') }}</h4>
            <p class="text-sm whitespace-pre-wrap">{{ $submission->notes }}</p>
        </div>
    @endif

    @if($submission->file_path)
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
            <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ __('الملف المرفوع') }}</h4>
            <a href="{{ Storage::url($submission->file_path) }}" 
               target="_blank" 
               class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                <x-filament::icon icon="heroicon-o-document" class="w-5 h-5" />
                {{ __('عرض الملف') }}
            </a>
        </div>
    @endif

    @if($submission->graded)
        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
            <h4 class="font-bold text-gray-900 dark:text-white mb-3">{{ __('التقييم') }}</h4>
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $submission->grade ?? 0 }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('درجتك') }}</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $assignment->max_grade > 0 ? round(($submission->grade / $assignment->max_grade) * 100) : 0 }}%
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('النسبة المئوية') }}</div>
                </div>
            </div>
        </div>
    @endif

    @if($submission->feedback)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
            <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ __('ملاحظات المدرس') }}</h4>
            <p class="text-sm whitespace-pre-wrap">{{ $submission->feedback }}</p>
        </div>
    @endif
</div>
