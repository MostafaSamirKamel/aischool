<div class="space-y-6">
    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">الطالب</div>
            <div class="text-lg font-bold text-gray-800">{{ $attempt->student->user->name }}</div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">الدرجة</div>
            <div class="text-2xl font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                {{ $attempt->score }} / {{ $attempt->total_score }}
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">النسبة المئوية</div>
            <div class="text-2xl font-bold text-primary-600">
                {{ number_format($attempt->percentage, 1) }}%
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">الوقت المستغرق</div>
            <div class="text-2xl font-bold text-gray-700">
                {{ floor($attempt->time_taken / 60) }} دقيقة
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600 mb-1">الحالة</div>
            <div class="text-2xl font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                {{ $attempt->passed ? 'ناجح ✓' : 'راسب ✗' }}
            </div>
        </div>
    </div>

    {{-- Exam Info --}}
    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
        <h4 class="font-bold text-md mb-3 text-gray-800">معلومات الامتحان</h4>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            <div>
                <span class="text-gray-600">الامتحان:</span>
                <span class="font-semibold ml-2">{{ $attempt->exam->title }}</span>
            </div>
            <div>
                <span class="text-gray-600">الكورس:</span>
                <span class="font-semibold ml-2">{{ $attempt->exam->course->name }}</span>
            </div>
            <div>
                <span class="text-gray-600">المدرس:</span>
                <span class="font-semibold ml-2">{{ $attempt->exam->instructor->full_name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-600">تاريخ الإكمال:</span>
                <span class="font-semibold ml-2">{{ $attempt->completed_at->format('d/m/Y h:i A') }}</span>
            </div>
            <div>
                <span class="text-gray-600">عدد الأسئلة:</span>
                <span class="font-semibold ml-2">{{ count($attempt->answers) }} سؤال</span>
            </div>
            <div>
                <span class="text-gray-600">الحد الأدنى للنجاح:</span>
                <span class="font-semibold ml-2">{{ number_format(($attempt->exam->passing_marks / $attempt->exam->total_marks) * 100, 0) }}%</span>
            </div>
        </div>
    </div>

    {{-- Answers Review --}}
    <div class="space-y-3">
        <h4 class="font-bold text-md text-gray-800">إجابات الطالب</h4>
        
        @foreach($attempt->answers as $index => $answer)
            <div class="border rounded-lg p-3 {{ $answer->is_correct ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                <div class="flex items-start gap-2 mb-2">
                    <span class="bg-primary-500 text-white rounded-full w-7 h-7 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        {{ $index + 1 }}
                    </span>
                    
                    @if($answer->is_correct)
                        <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                    @else
                        <x-heroicon-o-x-circle class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" />
                    @endif
                    
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 text-sm">{{ $answer->question->question_text }}</p>
                        <span class="text-xs text-gray-600">{{ $answer->question->points }} درجة</span>
                    </div>
                </div>
                
                <div class="space-y-1.5 mr-9">
                    @foreach($answer->question->options as $option)
                        <div class="flex items-center gap-2 p-2 rounded text-sm
                            @if($option->is_correct) bg-green-100 border border-green-300
                            @elseif($answer->selectedOption && $answer->selectedOption->id === $option->id) bg-red-100 border border-red-300
                            @else bg-gray-50 border border-gray-200
                            @endif
                        ">
                            @if($option->is_correct)
                                <x-heroicon-s-check-circle class="w-4 h-4 text-green-600 flex-shrink-0" />
                            @elseif($answer->selectedOption && $answer->selectedOption->id === $option->id)
                                <x-heroicon-s-x-circle class="w-4 h-4 text-red-600 flex-shrink-0" />
                            @else
                                <div class="w-4 h-4"></div>
                            @endif
                            
                            <span class="{{ $option->is_correct ? 'font-semibold text-green-800' : 'text-gray-700' }}">
                                {{ $option->option_text }}
                            </span>
                            
                            @if($option->is_correct)
                                <span class="mr-auto text-xs bg-green-200 text-green-800 px-2 py-0.5 rounded">صح</span>
                            @elseif($answer->selectedOption && $answer->selectedOption->id === $option->id)
                                <span class="mr-auto text-xs bg-red-200 text-red-800 px-2 py-0.5 rounded">اختار</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
