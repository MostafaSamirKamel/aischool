<div class="space-y-2">
    @foreach($attachments as $file)
        @php
            $url = asset('storage/' . $file);
            $filename = basename($file);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            
            $icon = match(strtolower($extension)) {
                'pdf' => '📄',
                'doc', 'docx' => '📝',
                'jpg', 'jpeg', 'png', 'gif' => '🖼️',
                default => '📎'
            };
        @endphp
        
        <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <span class="text-2xl">{{ $icon }}</span>
            <a href="{{ $url }}" target="_blank" class="text-primary-600 hover:underline flex-1">
                {{ $filename }}
            </a>
            <a href="{{ $url }}" download class="text-gray-500 hover:text-gray-700">
                ⬇️
            </a>
        </div>
    @endforeach
</div>
