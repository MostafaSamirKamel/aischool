<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Card --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    تفاصيل التقييم
                </h2>
                <div class="text-3xl">
                    {{ str_repeat('⭐', $this->record->rating) }}
                </div>
            </div>
        </div>

        {{-- Review Details --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Reviewer Info --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    معلومات المقيّم
                </h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">النوع</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            @if($this->record->reviewer_type === 'student')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    طالب
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-Blue-100 text-Blue-800">
                                    ولي أمر
                                </span>
                            @endif
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الاسم</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->reviewer_name }}
                        </dd>
                    </div>
                    
                    @if($this->record->is_anonymous)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الخصوصية</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                تقييم مجهول
                            </span>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Course & Group Info --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    معلومات الكورس والمجموعة
                </h3>
                <dl class="space-y-3">
                    @if($this->record->course)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الكورس</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->course->name }}
                        </dd>
                    </div>
                    @endif
                    
                    @if($this->record->group)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">المجموعة</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->group->name }}
                        </dd>
                    </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تاريخ التقييم</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->record->created_at->format('Y-m-d H:i') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Comment --}}
        @if($this->record->comment)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                التعليق
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    {{ $this->record->comment }}
                </p>
            </div>
        </div>
        @endif

        {{-- Aspects Ratings --}}
        @if($this->record->aspects && count($this->record->aspects) > 0)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                تقييم الجوانب المختلفة
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->record->aspects as $aspect => $rating)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        @switch($aspect)
                            @case('teaching_quality')
                                جودة التدريس
                                @break
                            @case('communication')
                                التواصل
                                @break
                            @case('punctuality')
                                الالتزام بالمواعيد
                                @break
                            @case('knowledge')
                                المعرفة العلمية
                                @break
                            @case('interaction')
                                التفاعل مع الطلاب
                                @break
                            @default
                                {{ $aspect }}
                        @endswitch
                    </div>
                    <div class="text-xl">
                        {{ str_repeat('⭐', $rating) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
