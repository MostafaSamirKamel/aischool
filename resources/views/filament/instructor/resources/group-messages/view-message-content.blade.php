<div class="space-y-4">
    {{-- Message Content --}}
    <div class="prose max-w-none dark:prose-invert">
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            {!! $record->message !!}
        </div>
    </div>

    {{-- Attachments --}}
    @if(!empty($record->attachments))
        <div class="border-t pt-4">
            <h4 class="font-semibold mb-2 text-gray-900 dark:text-white">المرفقات:</h4>
            <div class="space-y-2">
                @foreach($record->attachments as $attachment)
                    @php $fileName = basename($attachment); @endphp
                    <div class="flex items-center gap-2 p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        <a href="{{ asset('storage/' . $attachment) }}" 
                           target="_blank" 
                           class="text-blue-600 hover:underline flex-1">
                            {{ $fileName }}
                        </a>
                        <a href="{{ asset('storage/' . $attachment) }}" 
                           download 
                           class="text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Sender Info --}}
    <div class="border-t pt-4">
        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                المرسل: <strong>{{ $record->sender->name }}</strong>
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $record->created_at->format('d/m/Y h:i A') }}
            </span>
        </div>
    </div>
</div>
