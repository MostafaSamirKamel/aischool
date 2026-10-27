<div>
    <x-filament-panels::page>
        <div class="space-y-6">
            {{-- Resource Header --}}
            <div class="flex items-start gap-6 p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="flex-shrink-0">
                    @php
                        $icon = match($record->type) {
                            'pdf' => 'heroicon-o-document-text',
                            'video' => 'heroicon-o-video-camera',
                            'presentation' => 'heroicon-o-presentation-chart-bar',
                            'document' => 'heroicon-o-document',
                            'image' => 'heroicon-o-photo',
                            'audio' => 'heroicon-o-musical-note',
                            'link' => 'heroicon-o-link',
                            default => 'heroicon-o-document',
                        };
                        $colorBg = match($record->type) {
                            'pdf' => 'bg-red-100 dark:bg-red-900/20',
                            'video' => 'bg-blue-100 dark:bg-blue-900/20',
                            'presentation' => 'bg-yellow-100 dark:bg-yellow-900/20',
                            'document' => 'bg-indigo-100 dark:bg-indigo-900/20',
                            'link' => 'bg-green-100 dark:bg-green-900/20',
                            default => 'bg-gray-100 dark:bg-gray-900/20',
                        };
                        $colorText = match($record->type) {
                            'pdf' => 'text-red-600 dark:text-red-400',
                            'video' => 'text-blue-600 dark:text-blue-400',
                            'presentation' => 'text-yellow-600 dark:text-yellow-400',
                            'document' => 'text-indigo-600 dark:text-indigo-400',
                            'link' => 'text-green-600 dark:text-green-400',
                            default => 'text-gray-600 dark:text-gray-400',
                        };
                    @endphp
                    <div class="p-4 {{ $colorBg }} rounded-xl">
                        <x-filament::icon :icon="$icon" class="w-12 h-12 {{ $colorText }}" />
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $record->title }}
                    </h2>
                    @if($record->description)
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ $record->description }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <p class="text-sm text-gray-500 dark:text-gray-400">النوع</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ match($record->type) {
                            'pdf' => '📄 PDF',
                            'video' => '🎥 فيديو',
                            'presentation' => '📊 عرض تقديمي',
                            'document' => '📝 مستند',
                            'image' => '🖼️ صورة',
                            'audio' => '🎵 صوت',
                            'link' => '🔗 رابط',
                            default => '📎 أخرى',
                        } }}
                    </p>
                </div>

                @if($record->file_size)
                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <p class="text-sm text-gray-500 dark:text-gray-400">حجم الملف</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $record->getFileSizeFormatted() }}
                    </p>
                </div>
                @endif

                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <p class="text-sm text-gray-500 dark:text-gray-400">عدد التحميلات</p>
                    <p class="mt-1 text-lg font-semibold text-success-600">
                        {{ $record->download_count }}
                    </p>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <p class="text-sm text-gray-500 dark:text-gray-400">عدد المشاهدات</p>
                    <p class="mt-1 text-lg font-semibold text-info-600">
                        {{ $record->view_count }}
                    </p>
                </div>
            </div>

            {{-- Related Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">معلومات المادة</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-user" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-600 dark:text-gray-300">المدرس: {{ $record->instructor->name }}</span>
                        </div>
                        @if($record->course)
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-academic-cap" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-600 dark:text-gray-300">الكورس: {{ $record->course->name }}</span>
                        </div>
                        @endif
                        @if($record->group)
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-user-group" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-600 dark:text-gray-300">المجموعة: {{ $record->group->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">التواريخ</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                تاريخ الإضافة: {{ $record->created_at->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        @if($record->created_at != $record->updated_at)
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                آخر تحديث: {{ $record->updated_at->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- File/Link Info --}}
            @if($record->file_name || $record->external_link)
            <div class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                @if($record->file_name)
                    <div class="flex items-center gap-3">
                        <x-filament::icon icon="heroicon-o-document" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">اسم الملف:</p>
                            <p class="text-sm text-blue-700 dark:text-blue-300">{{ $record->file_name }}</p>
                        </div>
                    </div>
                @elseif($record->external_link)
                    <div class="flex items-center gap-3">
                        <x-filament::icon icon="heroicon-o-link" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">الرابط الخارجي:</p>
                            <a href="{{ $record->external_link }}" target="_blank" 
                               class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $record->external_link }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
            @endif

            {{-- Download/View Notice --}}
            @if($record->is_downloadable && $record->file_path)
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    <p class="text-sm text-green-800 dark:text-green-200">
                        هذا الملف قابل للتحميل. يمكنك تحميله من الأزرار في الأعلى.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </x-filament-panels::page>
</div>
