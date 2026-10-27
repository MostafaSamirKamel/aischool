<div class="inline-block">
    <!-- Status Button -->
    @if($this->enrollmentCourse)
        <button 
            wire:click="openGroupSelection"
            @class([
                'px-4 py-2 text-white font-bold rounded-lg transition whitespace-nowrap text-sm',
                'bg-yellow-500 hover:bg-yellow-600' => $this->enrollmentCourse->isInactive(),
                'bg-green-500 hover:bg-green-600' => $this->enrollmentCourse->isActive(),
                'bg-gray-400 cursor-not-allowed' => $this->enrollmentCourse->isCompleted(),
            ])
            @disabled($this->enrollmentCourse->isCompleted())
        >
            @if($this->enrollmentCourse->isInactive())
                ⭕ غير نشط
            @elseif($this->enrollmentCourse->isActive())
                🟢 نشط
            @else
                ✅ مكتمل
            @endif
        </button>
    @endif

    <!-- Group Selection Modal -->
    @if($showGroupModal && $this->enrollmentCourse)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-blue-600 text-white p-6 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-lg font-bold">
                            @if($this->enrollmentCourse->isInactive())
                                اختر مجموعة لتفعيل الكورس
                            @else
                                غيّر المجموعة
                            @endif
                        </h3>
                        <p class="text-sm text-purple-100 mt-1">
                            📖 {{ $this->enrollmentCourse->course->name }}
                        </p>
                    </div>
                    <button 
                        wire:click="closeModal"
                        class="text-white hover:text-gray-200 text-2xl">
                        ✕
                    </button>
                </div>

                <!-- Content -->
                <div class="overflow-y-auto flex-1 p-6">
                    @if(count($this->availableGroups) > 0)
                        <div class="space-y-4">
                            @foreach($this->availableGroups as $group)
                                <div @class([
                                    'border rounded-lg p-4 transition',
                                    'hover:bg-gray-50 cursor-pointer' => $group['is_compatible'],
                                    'bg-red-50 border-red-200 opacity-60' => !$group['is_compatible'],
                                ])>
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="font-bold text-gray-800 text-base">
                                                👥 {{ $group['name'] }}
                                            </h4>
                                            
                                            <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                                <div class="bg-blue-50 p-2 rounded">
                                                    <span class="text-blue-700 block text-xs">المعلم</span>
                                                    <span class="font-semibold text-blue-900">{{ $group['instructor_name'] }}</span>
                                                </div>
                                                <div class="bg-green-50 p-2 rounded">
                                                    <span class="text-green-700 block text-xs">السيشن</span>
                                                    <span class="font-semibold text-green-900">
                                                        {{ $group['completed_sessions'] }}/{{ $this->enrollmentCourse->total_sessions }}
                                                    </span>
                                                </div>
                                                <div class="bg-purple-50 p-2 rounded">
                                                    <span class="text-purple-700 block text-xs">السعة</span>
                                                    <span class="font-semibold text-purple-900">
                                                        {{ $group['current_students'] }}/{{ $group['capacity'] }}
                                                    </span>
                                                </div>
                                                <div class="bg-orange-50 p-2 rounded">
                                                    <span class="text-orange-700 block text-xs">المواعيد</span>
                                                    @if(is_array($group['schedule']) && count($group['schedule']) > 0)
                                                        <span class="font-semibold text-orange-900 text-xs">
                                                            {{ count($group['schedule']) }} أوقات
                                                        </span>
                                                    @else
                                                        <span class="text-orange-600 text-xs">-</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if($group['is_compatible'])
                                            <button 
                                                wire:click="selectGroup({{ $group['id'] }})"
                                                class="ml-4 px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition whitespace-nowrap shrink-0">
                                                اختر ✓
                                            </button>
                                        @else
                                            <div class="ml-4 text-center shrink-0">
                                                <button disabled class="px-4 py-2 bg-gray-300 text-gray-600 font-bold rounded-lg cursor-not-allowed whitespace-nowrap">
                                                    غير متاح
                                                </button>
                                                <p class="text-xs text-red-600 mt-2 w-40">
                                                    {{ $group['incompatible_reason'] }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-600">
                            <p class="text-lg">🔍 لا توجد مجموعات متاحة</p>
                            <p class="text-sm mt-2">يرجى التواصل مع الإدارة</p>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="bg-gray-100 p-4 flex justify-end gap-3 shrink-0">
                    <button 
                        wire:click="closeModal"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-bold rounded-lg transition">
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
