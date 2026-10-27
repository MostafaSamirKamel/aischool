<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Export & Print Buttons -->
        <div class="no-print" style="display: flex; gap: 12px; animation: slideDown 0.5s ease-out;">
            <a href="{{ route('admin.reports.course-financial.export-excel') }}" 
                style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, rgb(34, 197, 94), rgb(22, 163, 74)); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); transition: all 0.2s; cursor: pointer;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(34, 197, 94, 0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(34, 197, 94, 0.3)'">
                <span style="font-size: 20px;">📊</span> تصدير Excel
            </a>

            <a href="{{ route('admin.reports.course-financial.export-pdf') }}" 
                style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, rgb(239, 68, 68), rgb(220, 38, 38)); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transition: all 0.2s; cursor: pointer;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(239, 68, 68, 0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(239, 68, 68, 0.3)'">
                <span style="font-size: 20px;">📄</span> تصدير PDF
            </a>

            <a href="javascript:window.print();" 
                style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, rgb(59, 130, 246), rgb(37, 99, 235)); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transition: all 0.2s; cursor: pointer;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(59, 130, 246, 0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.3)'">
                <span style="font-size: 20px;">🖨️</span> طباعة
            </a>
        </div>

        <form wire:submit.prevent="applyFilters" class="p-4 space-y-4 bg-white shadow rounded-xl dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">المسار التدريبي</label>
                    <select wire:model="track_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">كل المسارات</option>
                        @foreach($this->getTracks() as $track)
                            <option value="{{ $track->id }}">{{ $track->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">حالة الكورس</label>
                    <select wire:model="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">الكل</option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                        <option value="completed">مكتمل</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">من تاريخ (تسجيل)</label>
                    <input type="date" wire:model="date_from" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">إلى تاريخ (تسجيل)</label>
                    <input type="date" wire:model="date_to" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            
            <div class="flex justify-end mt-4">
                <x-filament::button type="submit" color="primary">
                    تطبيق الفلاتر
                </x-filament::button>
            </div>
        </form>

        @php
            $data = $reportData;
            $courses = $data['courses'];
            $totalAllCourses = $data['totalAllCourses'];
        @endphp

        <div class="grid grid-cols-1 gap-4">
            <x-filament::section class="col-span-1">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">إجمالي إيرادات الكورسات للفترة المحددة</h2>
                <p class="text-3xl font-black text-primary-600 dark:text-primary-400">
                    {{ number_format($totalAllCourses, 2) }}
                </p>
            </x-filament::section>
        </div>

        <div class="p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">المجموعة</th>
                            <th scope="col" class="px-6 py-3">المدرس</th>
                            <th scope="col" class="px-6 py-3 text-center">عدد الطلاب</th>
                            <th scope="col" class="px-6 py-3">المبلغ المحصل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['courses'] as $courseData)
                            <tr class="bg-gray-100 border-b dark:bg-gray-700 dark:border-gray-600 font-bold">
                                <td colspan="3" class="px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $courseData['course']->name }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-lg text-primary-600 dark:text-primary-400">
                                    {{ number_format($courseData['total_course_revenue'], 2) }}
                                </td>
                            </tr>
                            
                            @forelse($courseData['groups'] as $groupData)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white" style="padding-right: 2rem;">
                                        ↳ {{ $groupData['group']->name }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $groupData['group']->instructor?->user?->name ?? 'غير محدد' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                                            {{ $groupData['group']->enrollmentCourses->count() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-green-600 dark:text-green-400 font-bold">
                                        {{ number_format($groupData['revenue'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        لا يوجد مجموعات لهذا الكورس
                                    </td>
                                </tr>
                            @endforelse
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    لا يوجد بيانات لعرضها
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
