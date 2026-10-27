<div>
    <x-filament-panels::page>
        <x-filament::section>
            <x-slot name="heading">
                تسجيل حضور الطلاب
            </x-slot>

            {{-- Selection Form --}}
            <form wire:submit="loadStudents" class="mb-6">
                {{ $this->form }}
            </form>

            @if (!empty($students))
                {{-- Session Info --}}
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">عدد الطلاب:</span>
                            <span class="text-lg font-bold text-primary-600">{{ count($students) }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">ترتيب السيشن:</span>
                            <span class="text-lg font-bold text-info-600">
                                {{ $currentSessionNumber ?? '-' }} / {{ $totalSessionsCount ?? '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">المتبقي:</span>
                            <span class="text-lg font-bold text-warning-600">{{ $remainingSessionsCount ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">الحاضرون:</span>
                            <span class="text-lg font-bold text-success-600">
                                {{ collect($attendanceData)->where('status', 'present')->count() }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">الغائبون:</span>
                            <span class="text-lg font-bold text-danger-600">
                                {{ collect($attendanceData)->where('status', 'absent')->count() }}
                            </span>
                        </div>
                    </div>
                    
                    @if ($sessionId)
                        <div class="mt-3 text-sm text-info-600 dark:text-info-400">
                            <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 inline" />
                            هذه الجلسة تم تسجيلها مسبقاً. يمكنك تعديل البيانات.
                        </div>
                    @endif
                </div>

                {{-- Attendance Table --}}
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 w-12">#</th>
                                <th class="px-4 py-3">كود الطالب</th>
                                <th class="px-4 py-3">اسم الطالب</th>
                                <th class="px-4 py-3 w-72">حالة الحضور</th>
                                <th class="px-4 py-3 w-32">وقت الحضور</th>
                                <th class="px-4 py-3">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($students as $index => $student)
                                <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $student['code'] }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $student['name'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <button
                                                type="button"
                                                wire:click="updateAttendanceStatus({{ $student['id'] }}, 'present')"
                                                class="flex-1 px-3 py-2 rounded-lg border-2 transition-all text-xs font-medium
                                                    {{ $attendanceData[$student['id']]['status'] === 'present' 
                                                        ? 'border-success-500 bg-success-50 text-success-700 dark:bg-success-900 dark:text-success-300' 
                                                        : 'border-gray-200 hover:border-success-300 dark:border-gray-700' }}">
                                                <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4 inline mr-1" />
                                                حاضر
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="updateAttendanceStatus({{ $student['id'] }}, 'late')"
                                                class="flex-1 px-3 py-2 rounded-lg border-2 transition-all text-xs font-medium
                                                    {{ $attendanceData[$student['id']]['status'] === 'late' 
                                                        ? 'border-warning-500 bg-warning-50 text-warning-700 dark:bg-warning-900 dark:text-warning-300' 
                                                        : 'border-gray-200 hover:border-warning-300 dark:border-gray-700' }}">
                                                <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 inline mr-1" />
                                                متأخر
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="updateAttendanceStatus({{ $student['id'] }}, 'absent')"
                                                class="flex-1 px-3 py-2 rounded-lg border-2 transition-all text-xs font-medium
                                                    {{ $attendanceData[$student['id']]['status'] === 'absent' 
                                                        ? 'border-danger-500 bg-danger-50 text-danger-700 dark:bg-danger-900 dark:text-danger-300' 
                                                        : 'border-gray-200 hover:border-danger-300 dark:border-gray-700' }}">
                                                <x-filament::icon icon="heroicon-o-x-circle" class="w-4 h-4 inline mr-1" />
                                                غائب
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="updateAttendanceStatus({{ $student['id'] }}, 'excused')"
                                                class="flex-1 px-3 py-2 rounded-lg border-2 transition-all text-xs font-medium
                                                    {{ $attendanceData[$student['id']]['status'] === 'excused' 
                                                        ? 'border-info-500 bg-info-50 text-info-700 dark:bg-info-900 dark:text-info-300' 
                                                        : 'border-gray-200 hover:border-info-300 dark:border-gray-700' }}">
                                                <x-filament::icon icon="heroicon-o-document-text" class="w-4 h-4 inline mr-1" />
                                                بعذر
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if (in_array($attendanceData[$student['id']]['status'], ['present', 'late']))
                                            <input
                                                type="time"
                                                wire:model.blur="attendanceData.{{ $student['id'] }}.check_in_time"
                                                wire:change="updateCheckInTime({{ $student['id'] }}, $event.target.value)"
                                                class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-800"
                                            />
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="text"
                                            wire:model.blur="attendanceData.{{ $student['id'] }}.notes"
                                            wire:change="updateNotes({{ $student['id'] }}, $event.target.value)"
                                            placeholder="ملاحظات..."
                                            class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-800"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Save Button --}}
                <div class="mt-6 flex justify-end">
                    <x-filament::button
                        wire:click="saveAttendance"
                        size="lg"
                        color="primary"
                    >
                        <x-filament::icon icon="heroicon-o-check" class="w-5 h-5 mr-2" />
                        حفظ الحضور
                    </x-filament::button>
                </div>
            @else
                <div class="text-center py-12">
                    <x-filament::icon icon="heroicon-o-user-group" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">
                        اختر المجموعة والتاريخ لبدء تسجيل الحضور
                    </p>
                </div>
            @endif
        </x-filament::section>
    </x-filament-panels::page>
</div>
