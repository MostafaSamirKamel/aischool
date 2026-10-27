<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Cron Job Setup Instructions --}}
        <div class="bg-warning-50 dark:bg-warning-900/20 border-2 border-warning-500 rounded-lg p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-warning-700 dark:text-warning-400 mb-2">
                        ⚙️ إعداد Cron Job على السيرفر
                    </h3>
                    <p class="text-warning-600 dark:text-warning-300 mb-4">
                        لتشغيل المهام المجدولة تلقائياً، يجب إضافة Cron Job على السيرفر (cPanel أو Terminal)
                    </p>

                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-warning-300 dark:border-warning-700">
                        <p class="text-sm font-semibold mb-2">Command للإضافة في cPanel Cron Jobs:</p>
                        <div class="bg-gray-900 rounded p-3 relative">
                            <code class="text-green-400 text-sm font-mono break-all">{{ $this->getCronJobCommand() }}</code>
                            <button 
                                onclick="navigator.clipboard.writeText('{{ $this->getCronJobCommand() }}')"
                                class="absolute top-2 right-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs"
                            >
                                نسخ
                            </button>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                            * * * * * = يعمل كل دقيقة (Laravel سيحدد متى يشغل كل مهمة)
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">📋 خطوات cPanel:</h4>
                            <ol class="text-sm text-blue-800 dark:text-blue-300 space-y-1 list-decimal list-inside">
                                <li>افتح cPanel</li>
                                <li>ابحث عن "Cron Jobs"</li>
                                <li>اختر "Once Per Minute"</li>
                                <li>انسخ الـ Command من الأعلى</li>
                                <li>احفظ</li>
                            </ol>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                            <h4 class="font-semibold text-green-900 dark:text-green-300 mb-2">🖥️ خطوات Terminal:</h4>
                            <ol class="text-sm text-green-800 dark:text-green-300 space-y-1 list-decimal list-inside">
                                <li>افتح Terminal/SSH</li>
                                <li>اكتب: crontab -e</li>
                                <li>الصق الـ Command</li>
                                <li>احفظ (Ctrl+X ثم Y)</li>
                                <li>اكتب: crontab -l للتحقق</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <x-filament::section>
            <x-slot name="heading">
                تشغيل يدوي للمهام
            </x-slot>
            <x-slot name="description">
                يمكنك تشغيل المهام يدوياً من هنا للاختبار أو في حالة الطوارئ
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-filament::button
                    wire:click="runInstallmentsUpdate"
                    color="success"
                    icon="heroicon-o-currency-dollar"
                >
                    تحديث حالة الأقساط
                </x-filament::button>

                <x-filament::button
                    wire:click="runExamReminders"
                    color="info"
                    icon="heroicon-o-bell"
                >
                    إرسال تذكيرات الامتحانات
                </x-filament::button>

                <x-filament::button
                    wire:click="runAllScheduled"
                    color="warning"
                    icon="heroicon-o-play"
                >
                    تشغيل جميع المهام
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Scheduled Tasks List --}}
        <x-filament::section>
            <x-slot name="heading">
                المهام المجدولة
            </x-slot>
            <x-slot name="description">
                قائمة بجميع المهام المجدولة وأوقات تشغيلها
            </x-slot>

            <div class="space-y-4">
                @foreach($this->getScheduledTasks() as $task)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 dark:text-white text-lg">
                                {{ $task['name'] }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $task['description'] }}
                            </p>
                            <div class="flex gap-4 mt-3 text-sm">
                                <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-3 py-1 rounded">
                                    📅 {{ $task['schedule'] }}
                                </span>
                                <span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded font-mono">
                                    {{ $task['command'] }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <x-filament::button
                                wire:click="{{ $task['action'] }}"
                                size="sm"
                                color="success"
                            >
                                تشغيل الآن
                            </x-filament::button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- System Information --}}
        <x-filament::section>
            <x-slot name="heading">
                معلومات النظام
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">مسار المشروع</div>
                    <div class="font-mono text-xs text-gray-900 dark:text-white mt-1 break-all">
                        {{ base_path() }}
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">PHP Version</div>
                    <div class="font-semibold text-gray-900 dark:text-white mt-1">
                        {{ PHP_VERSION }}
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Laravel Version</div>
                    <div class="font-semibold text-gray-900 dark:text-white mt-1">
                        {{ app()->version() }}
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Help & Resources --}}
        <x-filament::section>
            <x-slot name="heading">
                المساعدة والموارد
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">📚 ملفات التوثيق</h4>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="/storage/INSTALLMENTS_SCHEDULING_GUIDE.md" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                دليل جدولة تحديث الأقساط
                            </a>
                        </li>
                        <li>
                            <a href="/storage/CPANEL_CRON_SETUP_GUIDE.md" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                دليل إعداد Cron من cPanel
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">🔧 أوامر مفيدة</h4>
                    <div class="space-y-2 text-sm font-mono">
                        <div class="bg-gray-900 rounded p-2 text-green-400">
                            php artisan schedule:list
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">عرض جميع المهام المجدولة</div>
                        
                        <div class="bg-gray-900 rounded p-2 text-green-400">
                            php artisan schedule:work
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">تشغيل Scheduler محلياً للتطوير</div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
