<div class="space-y-6">
    {{-- معلومات التسليم --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">الطالب</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $submission->student->user->name }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $submission->student->student_code }}</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">الواجب</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $submission->assignment->title }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $submission->assignment->group->name }}</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">تاريخ التسليم</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $submission->submitted_at->format('d/m/Y H:i') }}</div>
            @if($submission->submitted_at > $submission->assignment->due_date)
                <div class="text-xs text-red-600 dark:text-red-400 mt-1">متأخر</div>
            @else
                <div class="text-xs text-green-600 dark:text-green-400 mt-1">في الموعد</div>
            @endif
        </div>
    </div>

    {{-- محتوى التسليم --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">محتوى التسليم</h3>
        
        @if($submission->content)
            <div class="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="prose dark:prose-invert max-w-none">
                    {!! $submission->content !!}
                </div>
            </div>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-400">لا يوجد محتوى نصي</p>
        @endif
    </div>

    {{-- الملفات المرفقة --}}
    @if($submission->file_path)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">الملفات المرفقة</h3>
            
            @php
                $files = is_array($submission->file_path) ? $submission->file_path : [$submission->file_path];
            @endphp
            
            <div class="space-y-2">
                @foreach($files as $file)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ basename($file) }}
                                </div>
                            </div>
                        </div>
                        <a href="{{ \Storage::url($file) }}" 
                           target="_blank"
                           class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none">
                            تحميل
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- التصحيح --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">التصحيح</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">الدرجة</div>
                <div class="font-semibold text-2xl text-gray-900 dark:text-gray-100">
                    @if($submission->grade !== null)
                        <span class="{{ $submission->grade >= ($submission->assignment->max_grade * 0.6) ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $submission->grade }} / {{ $submission->assignment->max_grade }}
                        </span>
                    @else
                        <span class="text-gray-400">لم يصحح بعد</span>
                    @endif
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">الحالة</div>
                <div class="mt-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $submission->status === 'graded' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                           ($submission->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                           'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200') }}">
                        {{ $submission->status === 'graded' ? 'مصحح' : 
                           ($submission->status === 'pending' ? 'قيد المراجعة' : 'معاد') }}
                    </span>
                </div>
            </div>
        </div>

        @if($submission->feedback)
            <div class="mt-4 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <div class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">ملاحظات المدرس:</div>
                <div class="text-sm text-blue-700 dark:text-blue-300 prose dark:prose-invert max-w-none">
                    {!! $submission->feedback !!}
                </div>
            </div>
        @endif
    </div>
</div>
