<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Widgets --}}
        @foreach($this->getHeaderWidgets() as $widget)
            @livewire($widget)
        @endforeach

        {{-- Enrollment Details Table --}}
        <x-filament::section>
            <x-slot name="heading">
                قائمة التسجيلات التفصيلية
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3">كود التسجيل</th>
                            <th class="px-6 py-3">الطالب</th>
                            <th class="px-6 py-3">الدورة</th>
                            <th class="px-6 py-3">المجموعة</th>
                            <th class="px-6 py-3">المدفوعات</th>
                            <th class="px-6 py-3">الحالة</th>
                            <th class="px-6 py-3">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $query = \App\Models\Enrollment::query()
                                ->with(['student.user', 'group.course', 'payments']);
                            if (!auth()->user()->is_super_admin) {
                                $query->where('tenant_id', auth()->user()->tenant_id);
                            }
                            $enrollments = $query->latest('enrollment_date')->limit(50)->get();
                        @endphp
                        @foreach($enrollments as $enrollment)
                            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 font-medium">{{ $enrollment->enrollment_code }}</td>
                                <td class="px-6 py-4">{{ $enrollment->student->user->name ?? 'غير محدد' }}</td>
                                <td class="px-6 py-4">{{ $enrollment->groups->pluck('course.name')->unique()->join(' | ') ?: 'غير محدد' }}</td>
                                <td class="px-6 py-4">{{ $enrollment->groups->pluck('name')->join(' | ') ?: 'غير محدد' }}</td>
                                <td class="px-6 py-4 font-bold">
                                    {{ number_format($enrollment->payments->where('status', 'completed')->sum('amount'), 2) }} ج.م
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'active' => 'success',
                                            'completed' => 'info',
                                            'suspended' => 'warning',
                                            'cancelled' => 'danger',
                                        ];
                                        $statusLabels = [
                                            'active' => 'نشط',
                                            'completed' => 'مكتمل',
                                            'suspended' => 'موقوف',
                                            'cancelled' => 'ملغي',
                                        ];
                                    @endphp
                                    <x-filament::badge :color="$statusColors[$enrollment->status] ?? 'gray'">
                                        {{ $statusLabels[$enrollment->status] ?? $enrollment->status }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4">{{ $enrollment->enrollment_date?->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Status Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $query = \App\Models\Enrollment::query();
                if (!auth()->user()->is_super_admin) {
                    $query->where('tenant_id', auth()->user()->tenant_id);
                }
                
                $activeCount = $query->clone()->where('status', 'active')->count();
                $completedCount = $query->clone()->where('status', 'completed')->count();
                $suspendedCount = $query->clone()->where('status', 'suspended')->count();
                $cancelledCount = $query->clone()->where('status', 'cancelled')->count();
            @endphp

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $activeCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">نشط</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $completedCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">مكتمل</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ $suspendedCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">موقوف</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600">{{ $cancelledCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">ملغي</div>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
