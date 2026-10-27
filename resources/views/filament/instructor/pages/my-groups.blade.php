<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-primary-50 to-blue-50 dark:from-primary-900/20 dark:to-blue-900/20 rounded-xl p-6 border border-primary-200 dark:border-primary-800">
            <div class="flex items-center gap-4">
                <div class="bg-primary-100 dark:bg-primary-900 p-4 rounded-full">
                    <i class="bi bi-people-fill text-primary-600" style="font-size: 2rem;"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">مجموعاتي</h2>
                    <p class="text-gray-600 dark:text-gray-400">إدارة ومتابعة المجموعات المسندة إليك</p>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
