<div class="space-y-4">
    @forelse($this->getActivities() as $activity)
        <div class="flex items-start space-x-4 rounded-lg border border-gray-200 bg-white p-4 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
            <div class="flex-shrink-0">
                <div @class([
                    'flex h-10 w-10 items-center justify-center rounded-full',
                    'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400' => $activity['color'] === 'bg-blue-100 text-blue-600',
                    'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400' => $activity['color'] === 'bg-green-100 text-green-600',
                    'bg-Blue-100 text-Blue-600 dark:bg-Blue-900 dark:text-Blue-400' => $activity['color'] === 'bg-Blue-100 text-Blue-600',
                ])>
                    @switch($activity['type'])
                        @case('patient')
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            @break
                        @case('reservation')
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            @break
                        @case('doctor')
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            @break
                    @endswitch
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity['title'] }}</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $activity['description'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">{{ $activity['date'] }}</p>
            </div>
        </div>
    @empty
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-center dark:border-gray-700 dark:bg-gray-800">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No activities yet</p>
        </div>
    @endforelse
</div>
