<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @if($students && $students->count() > 0)
            <x-filament::section class="mt-6">
                <x-slot name="heading">
                    قائمة الطلاب ({{ $students->count() }} طالب)
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    #
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    اسم الطالب
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    البريد الإلكتروني
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    الدرجة المستحقة
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    ملاحظات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($students as $index => $student)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                        <input type="hidden" name="students[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $student->email }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <input 
                                            type="number" 
                                            name="students[{{ $index }}][marks_obtained]"
                                            step="0.01"
                                            min="0"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 text-sm"
                                            placeholder="0.00"
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <input 
                                            type="text" 
                                            name="students[{{ $index }}][remarks]"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 text-sm"
                                            placeholder="ملاحظات اختيارية"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button
                        type="button"
                        color="gray"
                        tag="a"
                        :href="App\Filament\Admin\Resources\Grades\GradeResource::getUrl('index')"
                    >
                        إلغاء
                    </x-filament::button>

                    <x-filament::button type="submit">
                        حفظ الدرجات
                    </x-filament::button>
                </div>
            </x-filament::section>
        @elseif($examId)
            <x-filament::section class="mt-6">
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    لا يوجد طلاب مسجلين في هذه المجموعة
                </div>
            </x-filament::section>
        @endif
    </form>
</x-filament-panels::page>
