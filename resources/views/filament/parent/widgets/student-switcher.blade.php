<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <x-heroicon-o-user-group class="w-5 h-5 text-primary-500" />
                <h3 class="text-lg font-semibold">{{ __('اختر الطالب') }}</h3>
            </div>
            
            <div class="flex-1 max-w-md">
                <select 
                    wire:model.live="selectedStudent"
                    wire:change="switchStudent($event.target.value)"
                    class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-[#1E293B] dark:text-[#F1F5F9] font-medium py-2 px-3 text-sm"
                >
                    <option value="">{{ __('اختر طالب...') }}</option>
                    @foreach($this->getStudents() as $id => $name)
                        <option value="{{ $id }}" {{ $selectedStudent == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        @if($selectedStudent)
            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                <x-heroicon-o-information-circle class="inline w-4 h-4" />
                {{ __('جميع البيانات المعروضة خاصة بالطالب المختار') }}
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
