@php
    $files = array_values(array_filter((array) $getState()));
    $storage = \Illuminate\Support\Facades\Storage::disk($disk ?? 'public');
@endphp

<div class="space-y-2">
    @if (empty($files))
        <div class="text-sm text-gray-500">
            {{ $emptyLabel ?? 'لا توجد مرفقات' }}
        </div>
    @else
        @foreach ($files as $file)
            @php
                $url = $storage->url($file);
                $name = basename($file);
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
            @endphp

            <div class="flex items-center gap-2 p-2 border rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                @if ($isImage)
                    <img src="{{ $url }}" alt="{{ $name }}" class="h-10 w-10 rounded object-cover" />
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded bg-gray-200 dark:bg-gray-700">
                        <x-heroicon-o-paper-clip class="h-5 w-5 text-gray-500" />
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">{{ $name }}</p>
                </div>

                <a href="{{ $url }}" target="_blank" download
                    class="text-primary-600 hover:text-primary-500 text-sm font-medium">
                    تحميل
                </a>
            </div>
        @endforeach
    @endif
</div>
