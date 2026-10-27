<div class="p-6 space-y-4">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
            @php
                $icon = match($resource->type) {
                    'pdf' => 'heroicon-o-document-text',
                    'video' => 'heroicon-o-video-camera',
                    'presentation' => 'heroicon-o-presentation-chart-bar',
                    'document' => 'heroicon-o-document',
                    'image' => 'heroicon-o-photo',
                    'audio' => 'heroicon-o-musical-note',
                    'link' => 'heroicon-o-link',
                    default => 'heroicon-o-document',
                };
                $color = match($resource->type) {
                    'pdf' => 'danger',
                    'video' => 'info',
                    'presentation' => 'warning',
                    'document' => 'primary',
                    'link' => 'success',
                    default => 'gray',
                };
            @endphp
            <div class="p-3 rounded-lg bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20">
                <x-filament::icon :icon="$icon" class="w-8 h-8 text-{{ $color }}-600 dark:text-{{ $color }}-400" />
            </div>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $resource->title }}</h3>
            @if($resource->description)
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $resource->description }}</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400">النوع</p>
            <p class="mt-1 font-medium text-gray-900 dark:text-white">
                {{ match($resource->type) {
                    'pdf' => 'PDF',
                    'video' => 'فيديو',
                    'presentation' => 'عرض تقديمي',
                    'document' => 'مستند',
                    'image' => 'صورة',
                    'audio' => 'صوت',
                    'link' => 'رابط',
                    default => 'أخرى',
                } }}
            </p>
        </div>

        @if($resource->file_size)
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400">حجم الملف</p>
            <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $resource->getFileSizeFormatted() }}</p>
        </div>
        @endif

        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400">التحميلات</p>
            <p class="mt-1 font-medium text-success-600">{{ $resource->download_count }}</p>
        </div>

        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400">المشاهدات</p>
            <p class="mt-1 font-medium text-info-600">{{ $resource->view_count }}</p>
        </div>
    </div>

    @if($resource->course || $resource->group)
    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <p class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">مرتبط بـ:</p>
        <div class="flex gap-2">
            @if($resource->course)
                <span class="px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                    📚 {{ $resource->course->name }}
                </span>
            @endif
            @if($resource->group)
                <span class="px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                    👥 {{ $resource->group->name }}
                </span>
            @endif
        </div>
    </div>
    @endif

    <div class="flex gap-2 pt-4">
        @if($resource->file_path)
            <a href="{{ Storage::disk('public')->url($resource->file_path) }}" 
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                <x-filament::icon icon="heroicon-o-eye" class="w-4 h-4" />
                عرض الملف
            </a>
            @if($resource->is_downloadable)
                <a href="{{ Storage::disk('public')->url($resource->file_path) }}" 
                   download="{{ $resource->file_name }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-success-600 text-white rounded-lg hover:bg-success-700">
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-4 h-4" />
                    تحميل
                </a>
            @endif
        @elseif($resource->external_link)
            <a href="{{ $resource->external_link }}" 
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                <x-filament::icon icon="heroicon-o-link" class="w-4 h-4" />
                فتح الرابط
            </a>
        @endif
    </div>

    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
            <span>تم الإضافة: {{ $resource->created_at->format('Y-m-d H:i') }}</span>
            @if($resource->created_at != $resource->updated_at)
                <span>آخر تحديث: {{ $resource->updated_at->format('Y-m-d H:i') }}</span>
            @endif
        </div>
    </div>
</div>
