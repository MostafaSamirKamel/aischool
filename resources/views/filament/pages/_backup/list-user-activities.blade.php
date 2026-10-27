<x-filament::page>
    <div class="mb-6">
        <h2 class="text-3xl font-bold">سجل النشاط</h2>
        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $this->record->name }}</p>
    </div>

    <!-- Activity Log Table -->
    <div class="space-y-4">
        @forelse($this->table->getRecords() as $activity)
            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(optional($activity->causer)->name ?? 'System') }}&background=random" 
                             alt="" class="h-10 w-10 rounded-full">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ optional($activity->causer)->name ?? 'System' }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="inline-block rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-900 dark:bg-blue-900/30 dark:text-blue-200">
                                    {{ ucfirst($activity->event) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($activity->description) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activity->created_at->format('Y-m-d, H:i:s') }}</p>
                    </div>
                </div>

                <!-- Changes Table -->
                @if($activity->properties && isset($activity->properties['attributes']) && count($activity->properties['attributes']) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">الحقل</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">القيمة السابقة</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">القيمة الجديدة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($activity->properties['attributes'] as $key => $newValue)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $key }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            @if(isset($activity->properties['old'][$key]))
                                                <span class="rounded bg-red-100 px-2 py-1 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                                                    {{ $activity->properties['old'][$key] }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            <span class="rounded bg-green-100 px-2 py-1 text-green-800 dark:bg-green-900/30 dark:text-green-200">
                                                {{ $newValue }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">لا توجد تغييرات</p>
                @endif
            </div>
        @empty
            <div class="rounded-lg bg-white p-8 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-gray-500 dark:text-gray-400">لا توجد سجلات نشاط</p>
            </div>
        @endforelse
    </div>
</x-filament::page>
