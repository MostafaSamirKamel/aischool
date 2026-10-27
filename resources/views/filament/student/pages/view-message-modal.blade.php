<div class="p-6">
    <div class="space-y-4">
        {{-- Message Header --}}
        <div class="flex items-start justify-between pb-4 border-b">
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $record->subject }}
                </h3>
                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-1">
                        <x-filament::icon
                            icon="heroicon-o-user-group"
                            class="w-4 h-4"
                        />
                        <span>{{ $record->group->name }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <x-filament::icon
                            icon="heroicon-o-calendar"
                            class="w-4 h-4"
                        />
                        <span>{{ $record->sent_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </div>
            
            <div>
                @php
                    $priorityColor = match($record->priority) {
                        'high' => 'danger',
                        'normal' => 'warning',
                        'low' => 'info',
                    };
                    $priorityLabel = match($record->priority) {
                        'high' => 'عالية',
                        'normal' => 'عادية',
                        'low' => 'منخفضة',
                    };
                @endphp
                <x-filament::badge :color="$priorityColor">
                    {{ $priorityLabel }}
                </x-filament::badge>
            </div>
        </div>

        {{-- Message Body --}}
        <div class="prose dark:prose-invert max-w-none">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                {!! nl2br(e($record->message)) !!}
            </div>
        </div>

        {{-- Attachments --}}
        @if($record->attachments && count($record->attachments) > 0)
            <div class="pt-4 border-t">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <x-filament::icon
                        icon="heroicon-o-paper-clip"
                        class="w-4 h-4"
                    />
                    المرفقات ({{ count($record->attachments) }})
                </h4>
                <div class="grid grid-cols-1 gap-2">
                    @foreach($record->attachments as $attachment)
                        <a 
                            href="{{ Storage::url($attachment) }}" 
                            target="_blank"
                            class="flex items-center gap-2 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                        >
                            <x-filament::icon
                                icon="heroicon-o-document"
                                class="w-5 h-5 text-gray-600 dark:text-gray-400"
                            />
                            <span class="flex-1 text-sm text-gray-900 dark:text-white">
                                {{ basename($attachment) }}
                            </span>
                            <x-filament::icon
                                icon="heroicon-o-arrow-down-tray"
                                class="w-4 h-4 text-gray-600 dark:text-gray-400"
                            />
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Metadata --}}
        <div class="pt-4 border-t text-xs text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-4">
                <span>تم الإرسال: {{ $record->sent_at->diffForHumans() }}</span>
                @if($record->scheduled_at)
                    <span>• تم الجدولة: {{ $record->scheduled_at->format('Y-m-d H:i') }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
