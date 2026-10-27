<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Widgets --}}
        @foreach($this->getHeaderWidgets() as $widget)
            @livewire($widget)
        @endforeach

        {{-- Group Performance Table --}}
        <x-filament::section>
            <x-slot name="heading">
                تقرير أداء المجموعات
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3">الدورة</th>
                            <th class="px-6 py-3">المجموعة</th>
                            <th class="px-6 py-3">المدرس</th>
                            <th class="px-6 py-3">الطلاب</th>
                            <th class="px-6 py-3">السعة</th>
                            <th class="px-6 py-3">نسبة الإشغال</th>
                            <th class="px-6 py-3">الإيرادات</th>
                            <th class="px-6 py-3">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $query = \App\Models\Group::query()
                                ->with(['course', 'instructor.user', 'enrollments'])
                                ->withCount('enrollments');
                            if (!auth()->user()->is_super_admin) {
                                $query->where('tenant_id', auth()->user()->tenant_id);
                            }
                            $groups = $query->latest()->limit(30)->get();
                        @endphp
                        @foreach($groups as $group)
                            @php
                                $revenue = $group->enrollments()
                                    ->join('payments', 'enrollments.id', '=', 'payments.enrollment_id')
                                    ->where('payments.status', 'completed')
                                    ->sum('payments.amount');
                                $occupancyRate = ($group->enrollments_count / max($group->capacity, 1)) * 100;
                            @endphp
                            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 font-medium">{{ $group->course->name ?? 'غير محدد' }}</td>
                                <td class="px-6 py-4">{{ $group->name }}</td>
                                <td class="px-6 py-4">{{ $group->instructor->user->name ?? 'غير محدد' }}</td>
                                <td class="px-6 py-4">
                                    <x-filament::badge color="primary">
                                        {{ $group->enrollments_count }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4">
                                    <x-filament::badge color="gray">
                                        {{ $group->capacity }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $color = match(true) {
                                            $occupancyRate >= 90 => 'success',
                                            $occupancyRate >= 70 => 'info',
                                            $occupancyRate >= 50 => 'warning',
                                            default => 'danger',
                                        };
                                    @endphp
                                    <x-filament::badge :color="$color">
                                        {{ number_format($occupancyRate, 1) }}%
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ number_format($revenue, 2) }} ج.م
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'upcoming' => 'warning',
                                            'active' => 'success',
                                            'completed' => 'info',
                                            'cancelled' => 'danger',
                                        ];
                                        $statusLabels = [
                                            'upcoming' => 'قادمة',
                                            'active' => 'نشطة',
                                            'completed' => 'مكتملة',
                                            'cancelled' => 'ملغية',
                                        ];
                                    @endphp
                                    <x-filament::badge :color="$statusColors[$group->status] ?? 'gray'">
                                        {{ $statusLabels[$group->status] ?? $group->status }}
                                    </x-filament::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Top Courses --}}
        <x-filament::section>
            <x-slot name="heading">
                أعلى الدورات أداءً
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $query = \App\Models\Course::query()
                        ->withCount(['groups', 'groups as active_groups_count' => function($q) {
                            $q->where('status', 'active');
                        }]);
                    if (!auth()->user()->is_super_admin) {
                        $query->where('tenant_id', auth()->user()->tenant_id);
                    }
                    $topCourses = $query->orderByDesc('groups_count')->limit(6)->get();
                @endphp

                @foreach($topCourses as $course)
                    <x-filament::section>
                        <div class="p-4">
                            <h3 class="font-semibold text-lg mb-2">{{ $course->name }}</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">المجموعات:</span>
                                    <span class="font-bold">{{ $course->groups_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">النشطة:</span>
                                    <span class="font-bold text-green-600">{{ $course->active_groups_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">المستوى:</span>
                                    <span class="font-bold">{{ $course->level ?? 'غير محدد' }}</span>
                                </div>
                            </div>
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
