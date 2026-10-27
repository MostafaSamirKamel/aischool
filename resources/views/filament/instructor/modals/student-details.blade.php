<div class="space-y-6">
    @php
        $instructorId = \App\Models\Instructor::where('user_id', auth()->id())->value('id');
    @endphp
    
    {{-- معلومات أساسية --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">الاسم الكامل</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $student->user->name }}</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">كود الطالب</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $student->student_code }}</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">النوع</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $student->gender === 'male' ? 'ذكر' : 'أنثى' }}</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">العمر</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $student->age ?? 'غير محدد' }} سنة</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">الهاتف</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $student->phone ?? 'غير محدد' }}</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">البريد الإلكتروني</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ $student->email ?? 'غير محدد' }}</div>
        </div>
    </div>

    {{-- المجموعات المسجل فيها --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">المجموعات</h3>
        
        @php
            $enrollments = $student->enrollments()
                ->whereHas('groups', function($query) use ($instructorId) {
                    $query->where('instructor_id', $instructorId);
                })
                ->with(['groups.course'])
                ->get();
        @endphp
        
        @if($enrollments->count() > 0)
            <div class="space-y-2">
                @foreach($enrollments as $enrollment)
                    @foreach($enrollment->groups as $group)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $group->name }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $group->course->name }}</div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $enrollment->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                   ($enrollment->status === 'completed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                   'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200') }}">
                                {{ $enrollment->status === 'active' ? 'نشط' : 
                                   ($enrollment->status === 'completed' ? 'مكتمل' : 
                                   ($enrollment->status === 'withdrawn' ? 'منسحب' : $enrollment->status)) }}
                            </span>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-400">لا يوجد تسجيلات في مجموعاتك</p>
        @endif
    </div>

    {{-- الدرجات --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">الدرجات</h3>
        
        @php
            $grades = \App\Models\Grade::query()
                ->whereHas('enrollment', function($query) use ($student, $instructorId) {
                    $query->where('student_id', $student->id)
                        ->whereHas('groups', function($q) use ($instructorId) {
                            $q->where('instructor_id', $instructorId);
                        });
                })
                ->with(['exam', 'enrollment.groups'])
                ->latest()
                ->take(5)
                ->get();
        @endphp
        
        @if($grades->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">الامتحان</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">الدرجة</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">النسبة</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($grades as $grade)
                            @php
                                $percentage = $grade->exam->total_marks > 0 
                                    ? ($grade->score / $grade->exam->total_marks) * 100 
                                    : 0;
                            @endphp
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $grade->exam->title }}</td>
                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $grade->score }} / {{ $grade->exam->total_marks }}
                                </td>
                                <td class="px-3 py-2 text-sm">
                                    <span class="font-semibold {{ $percentage >= 50 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($percentage, 1) }}%
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $grade->status === 'passed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                           ($grade->status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                           'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                        {{ $grade->status === 'passed' ? 'ناجح' : ($grade->status === 'failed' ? 'راسب' : 'قيد المراجعة') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-400">لا توجد درجات بعد</p>
        @endif
    </div>
</div>
