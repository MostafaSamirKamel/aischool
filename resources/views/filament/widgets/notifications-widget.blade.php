<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <span>🔔 الإشعارات</span>
                @if($this->getUnreadCount() > 0)
                    <x-filament::badge color="danger" size="sm">
                        {{ $this->getUnreadCount() }} جديد
                    </x-filament::badge>
                @endif
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            @if($this->getUnreadCount() > 0)
                <x-filament::button
                    wire:click="markAllAsRead"
                    size="sm"
                    color="gray"
                >
                    تعليم الكل كمقروء
                </x-filament::button>
            @endif
        </x-slot>

        <div class="space-y-3">
            @forelse($this->getNotifications() as $notification)
                <div 
                    class="flex items-start gap-3 p-3 rounded-lg transition hover:bg-gray-50 dark:hover:bg-gray-800 {{ $notification['read_at'] ? 'opacity-60' : 'bg-primary-50 dark:bg-primary-900/10' }}"
                    wire:key="notification-{{ $notification['id'] }}"
                >
                    <div class="flex-shrink-0">
                        <x-filament::icon
                            :icon="$notification['icon']"
                            class="w-6 h-6 text-{{ $notification['color'] }}-500"
                        />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $notification['title'] }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $notification['message'] }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            {{ $notification['created_at']->diffForHumans() }}
                        </p>
                    </div>

                    @if(!$notification['read_at'])
                        <div class="flex-shrink-0">
                            <x-filament::button
                                wire:click="markAsRead('{{ $notification['id'] }}')"
                                size="sm"
                                color="gray"
                                icon="heroicon-o-check"
                            >
                                قراءة
                            </x-filament::button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-8">
                    <x-filament::icon
                        icon="heroicon-o-bell-slash"
                        class="w-12 h-12 text-gray-400 mx-auto mb-3"
                    />
                    <p class="text-sm text-gray-500">
                        لا توجد إشعارات
                    </p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
