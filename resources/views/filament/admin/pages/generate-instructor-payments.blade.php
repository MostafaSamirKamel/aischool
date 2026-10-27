<x-filament-panels::page>
    @php
        $data = $this->getViewData();
        $stats = $data['stats'];
        $recentPayments = $data['recentPayments'];
    @endphp

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-yellow-500 to-amber-600 rounded-xl p-6 shadow-lg text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold opacity-90">قيد المراجعة</span>
                <svg class="w-8 h-8 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-black">{{ $stats['total_pending'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl p-6 shadow-lg text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold opacity-90">معتمد</span>
                <svg class="w-8 h-8 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-black">{{ $stats['total_approved'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-6 shadow-lg text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold opacity-90">مدفوع</span>
                <svg class="w-8 h-8 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-3xl font-black">{{ $stats['total_paid'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-6 shadow-lg text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold opacity-90">إجمالي هذا الشهر</span>
                <svg class="w-8 h-8 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-black">{{ number_format($stats['this_month_amount'], 0) }}</p>
            <p class="text-xs opacity-75 mt-1">جنيه</p>
        </div>
    </div>

    {{-- Instructions --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-xl p-6 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-2">كيف تعمل الصفحة؟</h3>
                <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                    <li class="flex items-start gap-2">
                        <span class="font-bold">1.</span>
                        <span>اضغط على <strong>"توليد لجميع المدرسين"</strong> لإنشاء أجور شهرية لجميع المدرسين النشطين</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">2.</span>
                        <span>أو اضغط على <strong>"توليد لمدرس واحد"</strong> لإنشاء أجر لمدرس محدد مع تحديد الفترة</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">3.</span>
                        <span>بعد التوليد، ستظهر الأجور في صفحة "أجور المدرسين" تحت قائمة "إدارة الحاجوزي"</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">4.</span>
                        <span>المدرس سيرى أجوره في صفحة "أجوري" في لوحة تحكم المدرس</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Recent Payments --}}
    @if($recentPayments->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">آخر 10 أجور تم توليدها</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">#</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">المدرس</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">الفترة</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">الجلسات</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">المبلغ</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($recentPayments as $index => $payment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                        {{ $payment->instructor->user->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $payment->period_start->format('Y-m-d') }} - {{ $payment->period_end->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $payment->completed_sessions }} / {{ $payment->total_sessions }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                        {{ number_format($payment->total_amount, 2) }} جنيه
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300',
                                            'approved' => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900/30 dark:text-blue-300',
                                            'paid' => 'bg-green-100 text-green-800 border-green-300 dark:bg-green-900/30 dark:text-green-300',
                                            'cancelled' => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/30 dark:text-red-300',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $statusColors[$payment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $payment->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-gray-100">لا توجد أجور بعد</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">ابدأ بتوليد أجور للمدرسين باستخدام الأزرار في الأعلى</p>
        </div>
    @endif
</x-filament-panels::page>
