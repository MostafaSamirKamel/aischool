<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Widgets --}}
        @foreach($this->getHeaderWidgets() as $widget)
            @livewire($widget)
        @endforeach

        {{-- Student Details Table --}}
        <x-filament::section>
            <x-slot name="heading">
                قائمة الطلاب التفصيلية
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3">كود الطالب</th>
                            <th class="px-6 py-3">الاسم</th>
                            <th class="px-6 py-3">النوع</th>
                            <th class="px-6 py-3">الهاتف</th>
                            <th class="px-6 py-3">التسجيلات</th>
                            <th class="px-6 py-3">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $query = \App\Models\Student::query()->with('user')->withCount('enrollments');
                            if (!auth()->user()->is_super_admin) {
                                $query->where('tenant_id', auth()->user()->tenant_id);
                            }
                            $students = $query->latest()->limit(50)->get();
                        @endphp
                        @foreach($students as $student)
                            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 font-medium">{{ $student->student_code }}</td>
                                <td class="px-6 py-4">{{ $student->user->name }}</td>
                                <td class="px-6 py-4">
                                    <x-filament::badge :color="$student->gender === 'male' ? 'info' : 'pink'">
                                        {{ $student->gender === 'male' ? 'ذكر' : 'أنثى' }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4">{{ $student->phone }}</td>
                                <td class="px-6 py-4">
                                    <x-filament::badge color="success">
                                        {{ $student->enrollments_count }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'active' => 'success',
                                            'inactive' => 'gray',
                                            'suspended' => 'danger',
                                            'graduated' => 'info',
                                        ];
                                        $statusLabels = [
                                            'active' => 'نشط',
                                            'inactive' => 'غير نشط',
                                            'suspended' => 'موقوف',
                                            'graduated' => 'متخرج',
                                        ];
                                    @endphp
                                    <x-filament::badge :color="$statusColors[$student->status] ?? 'gray'">
                                        {{ $statusLabels[$student->status] ?? $student->status }}
                                    </x-filament::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
