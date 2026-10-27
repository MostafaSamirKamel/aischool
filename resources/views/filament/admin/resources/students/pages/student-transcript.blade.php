<x-filament-panels::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}
    </form>

    @if($student)
        <div class="mt-6 space-y-6">
            {{-- Student Info Card --}}
            <x-filament::section>
                <x-slot name="heading">
                    معلومات الطالب
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">الاسم الكامل</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $student->user?->name ?? 'طالب' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">البريد الإلكتروني</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $student->user?->email ?? 'غير متوفر' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">الهاتف</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $student->phone ?? 'غير متوفر' }}</p>
                    </div>
                </div>
            </x-filament::section>

            {{-- Statistics Card --}}
            @if($stats && $stats['total_exams'] > 0)
                <x-filament::section>
                    <x-slot name="heading">
                        إحصائيات الأداء
                    </x-slot>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-4">
                            <p class="text-xs font-medium text-primary-600 dark:text-primary-400">إجمالي الامتحانات</p>
                            <p class="mt-2 text-2xl font-bold text-primary-900 dark:text-primary-100">{{ $stats['total_exams'] }}</p>
                        </div>
                        <div class="bg-success-50 dark:bg-success-900/20 rounded-lg p-4">
                            <p class="text-xs font-medium text-success-600 dark:text-success-400">متوسط النسبة</p>
                            <p class="mt-2 text-2xl font-bold text-success-900 dark:text-success-100">{{ $stats['average_percentage'] }}%</p>
                        </div>
                        <div class="bg-warning-50 dark:bg-warning-900/20 rounded-lg p-4">
                            <p class="text-xs font-medium text-warning-600 dark:text-warning-400">نجح / رسب</p>
                            <p class="mt-2 text-2xl font-bold text-warning-900 dark:text-warning-100">{{ $stats['passing_count'] }} / {{ $stats['failing_count'] }}</p>
                        </div>
                        <div class="bg-info-50 dark:bg-info-900/20 rounded-lg p-4">
                            <p class="text-xs font-medium text-info-600 dark:text-info-400">أعلى / أقل درجة</p>
                            <p class="mt-2 text-lg font-bold text-info-900 dark:text-info-100">{{ $stats['highest_grade'] }}% / {{ $stats['lowest_grade'] }}%</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">توزيع التقديرات</p>
                        <div class="flex gap-2 flex-wrap">
                            @foreach($stats['grade_distribution'] as $grade => $count)
                                @if($count > 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        @if(in_array($grade, ['A+', 'A'])) bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400
                                        @elseif(in_array($grade, ['B+', 'B'])) bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400
                                        @elseif(in_array($grade, ['C+', 'C'])) bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400
                                        @else bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400
                                        @endif">
                                        {{ $grade }}: {{ $count }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </x-filament::section>
            @endif

            {{-- Grades by Course --}}
            @if($grades && count($grades) > 0)
                @foreach($grades as $courseName => $courseGrades)
                    <x-filament::section>
                        <x-slot name="heading">
                            {{ $courseName }}
                        </x-slot>

                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الامتحان</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">النوع</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الدرجة</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">النسبة</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">التقدير</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الحالة</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($courseGrades as $grade)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $grade->exam->title }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                                    @if($grade->exam->type === 'quiz') bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400
                                                    @elseif($grade->exam->type === 'midterm') bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400
                                                    @else bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400
                                                    @endif">
                                                    @if($grade->exam->type === 'quiz') كويز
                                                    @elseif($grade->exam->type === 'midterm') ميدتيرم
                                                    @elseif($grade->exam->type === 'final') فاينال
                                                    @else {{ $grade->exam->type }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $grade->marks_obtained }} / {{ $grade->exam->total_marks }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $grade->percentage }}%</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold
                                                    @if(in_array($grade->grade, ['A+', 'A'])) bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400
                                                    @elseif(in_array($grade->grade, ['B+', 'B'])) bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400
                                                    @elseif(in_array($grade->grade, ['C+', 'C'])) bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400
                                                    @else bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400
                                                    @endif">
                                                    {{ $grade->grade }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($grade->marks_obtained >= $grade->exam->passing_marks)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400">
                                                        ناجح
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400">
                                                        راسب
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $grade->graded_at ? $grade->graded_at->format('d/m/Y') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-filament::section>
                @endforeach

                <div class="flex justify-end">
                    <x-filament::button wire:click="downloadPdf" color="primary">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-2" />
                        تحميل PDF
                    </x-filament::button>
                </div>
            @else
                <x-filament::section>
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        لا توجد درجات مسجلة لهذا الطالب
                    </div>
                </x-filament::section>
            @endif
        </div>
    @endif
</x-filament-panels::page>
