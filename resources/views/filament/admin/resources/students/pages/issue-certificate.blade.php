<x-filament-panels::page>
    <form wire:submit="generate">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-3">
            <x-filament::button
                type="button"
                color="gray"
                tag="a"
                :href="App\Filament\Admin\Resources\Students\StudentResource::getUrl('index')"
            >
                إلغاء
            </x-filament::button>

            <x-filament::button type="submit" color="success">
                <x-heroicon-o-document-text class="w-5 h-5 mr-2" />
                إصدار الشهادة
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            نموذج الشهادة
        </x-slot>

        <div class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 p-8 rounded-lg border-4 border-yellow-400">
            <div class="text-center">
                <div class="text-6xl mb-4">🎓</div>
                <h2 class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2">شهادة إتمام</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-6">Certificate of Completion</p>
                
                <div class="my-8">
                    <p class="text-gray-600 dark:text-gray-400 mb-3">هذه الشهادة تمنح لـ</p>
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100 border-b-2 border-yellow-400 inline-block px-12 pb-2">
                        [اسم الطالب]
                    </div>
                </div>

                <p class="text-gray-700 dark:text-gray-300 my-6">تقديراً لإتمام الكورس بنجاح</p>
                
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 my-6">
                    [اسم الكورس]
                </div>

                <div class="inline-block bg-blue-50 dark:bg-blue-900/20 px-12 py-4 rounded-lg border-2 border-blue-500 my-6">
                    <p class="text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wider">التقدير النهائي</p>
                    <p class="text-4xl font-bold text-blue-600 dark:text-blue-400 my-2">[A+]</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wider">[95%]</p>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mt-6">
                    تاريخ الإتمام: [التاريخ]
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
