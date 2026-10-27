<x-filament::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">سجل النشاط</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        الاسم: {{ $this->record->name }}
                    </p>
                </div>
                <a href="{{ route('filament.admin.resources.users.index') }}" 
                   class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-200 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700">
                    رجوع
                </a>
            </div>
        </div>

        <!-- Activity Details -->
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Left Column -->
            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-lg font-semibold">الحدث</h3>
                <p class="inline-block rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-900 dark:bg-blue-900/30 dark:text-blue-200">
                    {{ $activity->event ?? 'N/A' }}
                </p>

                <h3 class="mb-2 mt-6 text-lg font-semibold">الوصف</h3>
                <p class="text-gray-700 dark:text-gray-300">{{ $activity->description ?? 'N/A' }}</p>

                <h3 class="mb-2 mt-6 text-lg font-semibold">بواسطة</h3>
                <p class="text-gray-700 dark:text-gray-300">
                    {{ optional($activity->causer)->name ?? 'System' }}
                </p>

                <h3 class="mb-2 mt-6 text-lg font-semibold">التاريخ</h3>
                <p class="text-gray-700 dark:text-gray-300">
                    {{ $activity->created_at->format('Y-m-d H:i:s') }}
                </p>
            </div>

            <!-- Right Column - Changes -->
            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-lg font-semibold">التغييرات</h3>
                
                @if($activity->properties && isset($activity->properties['attributes']))
                    <div class="space-y-4">
                        @foreach($activity->properties['attributes'] as $key => $newValue)
                            <div class="border-l-4 border-green-500 bg-green-50 p-3 dark:border-green-400 dark:bg-green-900/20">
                                <p class="font-semibold text-green-900 dark:text-green-200">{{ $key }}:</p>
                                <div class="mt-2 space-y-1">
                                    @if(isset($activity->properties['old']) && isset($activity->properties['old'][$key]))
                                        <p class="text-sm text-red-700 dark:text-red-300">
                                            <span class="font-semibold">القيم السابقة:</span>
                                            {{ $activity->properties['old'][$key] }}
                                        </p>
                                    @endif
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        <span class="font-semibold">القيم الجديدة:</span>
                                        {{ $newValue }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">لا توجد تغييرات</p>
                @endif
            </div>
        </div>

        <!-- Raw Properties -->
        @if($activity->properties)
            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-lg font-semibold">التفاصيل</h3>
                <pre class="overflow-auto rounded bg-gray-100 p-4 text-sm dark:bg-gray-800">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
    </div>
</x-filament::page>
