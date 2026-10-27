<div class="space-y-6">
    <!-- Enrolled Courses Section -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-gray-800 border-r-4 border-purple-600 pr-3">
            📚 الكورسات المسجلة
        </h3>

        @forelse($enrollment->enrollmentCourses as $enrollmentCourse)
            <div class="bg-white rounded-lg shadow-md border-l-4 border-blue-500 p-4 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h4 class="text-base font-bold text-blue-600">
                            📖 {{ $enrollmentCourse->course->name }}
                        </h4>
                        
                        @if($enrollmentCourse->group)
                            <div class="mt-2 flex flex-col gap-1.5 text-xs text-gray-600">
                                <span>👨‍🏫 <strong>المعلم:</strong> {{ optional(optional($enrollmentCourse->group->instructor)->user)->name ?? 'غير محدد' }}</span>
                                <span>📍 <strong>المجموعة:</strong> 
                                    <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs">
                                        {{ $enrollmentCourse->group->name }}
                                    </span>
                                </span>
                            </div>
                        @endif

                        <!-- Session Counter -->
                        <div class="mt-2 bg-gradient-to-r from-blue-50 to-blue-100 p-2.5 rounded-lg">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-semibold text-gray-700">
                                    ⏱️ السيشن: <span class="text-blue-600">{{ $enrollmentCourse->current_session }}</span> من {{ $enrollmentCourse->total_sessions }}
                                </span>
                                <span class="text-xs font-bold text-blue-600">
                                    {{ $enrollmentCourse->getProgressPercentage() }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-1.5 rounded-full transition-all" 
                                     style="width: {{ $enrollmentCourse->getProgressPercentage() }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Button -->
                    <div class="ml-4 flex flex-col gap-2">
                        @if($enrollmentCourse->isInactive())
                            <button 
                                wire:click="openGroupSelection({{ $enrollmentCourse->id }})"
                                class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-lg transition whitespace-nowrap text-sm">
                                ⭕ غير نشط
                            </button>
                        @elseif($enrollmentCourse->isActive())
                            <button 
                                wire:click="openGroupSelection({{ $enrollmentCourse->id }})"
                                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition whitespace-nowrap text-sm">
                                🟢 نشط
                            </button>
                        @else
                            <button 
                                disabled
                                class="px-4 py-2 bg-gray-400 text-white font-bold rounded-lg cursor-not-allowed whitespace-nowrap text-sm">
                                ✅ مكتمل
                            </button>
                        @endif

                        <!-- Price Info -->
                        <div class="text-right">
                            <span class="text-xs text-gray-600">السعر:</span>
                            <span class="text-sm font-bold text-gray-800 block">
                                {{ number_format($enrollmentCourse->course_price, 2) }} ج.م
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-blue-800">لم يتم اختيار أي كورسات حتى الآن</p>
            </div>
        @endforelse
    </div>

    <!-- Group Selection Modal -->
    @if($showGroupSelection && $selectedCourseId)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-96 overflow-y-auto">
                <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-blue-600 text-white p-6 flex justify-between items-center">
                    <h3 class="text-lg font-bold">اختر المجموعة</h3>
                    <button 
                        wire:click="closeGroupSelection"
                        class="text-white hover:text-gray-200 text-2xl">
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-3">
                    @forelse($availableGroups as $group)
                        <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-800">{{ $group['name'] }}</h4>
                                    <div class="mt-2 text-sm text-gray-600 space-y-1">
                                        <p>👨‍🏫 المعلم: {{ $group['instructor'] }}</p>
                                        <p>📊 السيشن: {{ $group['completed_sessions'] }}/12</p>
                                        <p>👥 السعة: {{ $group['capacity'] }} طالب</p>
                                    </div>
                                </div>

                                @if($group['is_compatible'])
                                    <button 
                                        wire:click="selectGroup({{ $group['id'] }})"
                                        class="ml-4 px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition whitespace-nowrap">
                                        اختر
                                    </button>
                                @else
                                    <div class="ml-4 text-center">
                                        <button disabled class="px-4 py-2 bg-gray-300 text-gray-600 font-bold rounded-lg cursor-not-allowed whitespace-nowrap">
                                            غير متاح
                                        </button>
                                        <p class="text-xs text-red-600 mt-2">{{ $group['incompatible_reason'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-600">
                            لا توجد مجموعات متاحة لهذا الكورس
                        </div>
                    @endforelse
                </div>

                <div class="bg-gray-100 p-4 flex justify-end gap-3">
                    <button 
                        wire:click="closeGroupSelection"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-bold rounded-lg transition">
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
