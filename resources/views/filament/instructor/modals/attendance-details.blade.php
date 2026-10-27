<div class="space-y-6">
    {{-- معلومات الجلسة --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">التاريخ</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $session->attendance_date->format('d/m/Y') }}</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">المجموعة</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $session->group->name }}</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">الكورس</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $session->group->course->name }}</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">القاعة</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $session->classroom ?? 'غير محدد' }}</div>
        </div>
    </div>

    {{-- إحصائيات الحضور --}}
    <div class="grid grid-cols-5 gap-3">
        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $session->total_students }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">إجمالي الطلاب</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $session->present_count }}</div>
            <div class="text-xs text-green-600 dark:text-green-400 mt-1">حاضر</div>
        </div>
        
        <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $session->absent_count }}</div>
            <div class="text-xs text-red-600 dark:text-red-400 mt-1">غائب</div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $session->late_count }}</div>
            <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">متأخر</div>
        </div>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($session->attendance_rate, 1) }}%</div>
            <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">نسبة الحضور</div>
        </div>
    </div>

    {{-- ملاحظات الجلسة --}}
    @if($session->session_notes)
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <div class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">ملاحظات الجلسة:</div>
            <div class="text-sm text-blue-700 dark:text-blue-300">{{ $session->session_notes }}</div>
        </div>
    @endif

    {{-- قائمة الطلاب --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">سجل الحضور</h3>
        
        @if($session->records->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">اسم الطالب</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الحالة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">وقت الحضور</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">وقت الانصراف</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($session->records->sortBy('student.user.name') as $index => $record)
                            <tr class="{{ $record->status === 'absent' ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $record->student->user->name ?? $record->student_name }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $record->status === 'present' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                           ($record->status === 'absent' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                           ($record->status === 'late' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                           'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200')) }}">
                                        {{ $record->status === 'present' ? 'حاضر' : 
                                           ($record->status === 'absent' ? 'غائب' : 
                                           ($record->status === 'late' ? 'متأخر' : 'غياب بعذر')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $record->notes ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">لا يوجد سجل حضور</p>
            </div>
        @endif
    </div>

    {{-- معلومات المسجل --}}
    @if($session->recordedBy)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                سجل بواسطة: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $session->recordedBy->name }}</span>
                في {{ $session->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
    @endif
</div>
