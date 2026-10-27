<x-filament-panels::page>
    <div class="space-y-6">
        {{-- فلاتر البحث --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">فلاتر البحث</h3>
                <form wire:submit.prevent="applyFilters">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">من تاريخ</label>
                            <input 
                                type="date" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                wire:model="dateFrom"
                                style="direction: ltr; text-align: right;"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">إلى تاريخ</label>
                            <input 
                                type="date" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                wire:model="dateTo"
                                style="direction: ltr; text-align: right;"
                            >
                        </div>

                        @if(auth()->user()->hasRole('super_admin'))
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-2">الفرع</label>
                                <select class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg" wire:model="tenantId">
                                    <option value="">جميع الفروع</option>
                                    @foreach($this->getTenants() as $tenant)
                                        <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        
                        <div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition-all flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                تطبيق الفلاتر
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- البطاقات الإحصائية --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- إجمالي الإيرادات --}}
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-white bg-opacity-25">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-4xl font-bold mb-2">{{ number_format($this->getTotalRevenue(), 2) }}</h3>
                    <p class="opacity-90 mb-0">ريال سعودي</p>
                    <p class="text-sm mt-2 opacity-75">إجمالي الإيرادات</p>
                </div>
            </div>

            {{-- إجمالي المصروفات --}}
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-white bg-opacity-25">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-4xl font-bold mb-2">{{ number_format($this->getTotalExpenses(), 2) }}</h3>
                    <p class="opacity-90 mb-0">ريال سعودي</p>
                    <p class="text-sm mt-2 opacity-75">إجمالي المصروفات</p>
                </div>
            </div>

            {{-- صافي الربح --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-white bg-opacity-25">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-4xl font-bold mb-2">{{ number_format($this->getNetProfit(), 2) }}</h3>
                    <p class="opacity-90 mb-0">ريال سعودي</p>
                    <p class="text-sm mt-2 opacity-75">صافي الربح</p>
                </div>
            </div>

            {{-- هامش الربح --}}
            <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-white bg-opacity-25">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-4xl font-bold mb-2">{{ number_format($this->getProfitMargin(), 2) }}%</h3>
                    <p class="opacity-90 mb-0">نسبة مئوية</p>
                    <p class="text-sm mt-2 opacity-75">هامش الربح</p>
                </div>
            </div>
        </div>

        {{-- الرسوم البيانية --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h5 class="text-lg font-semibold mb-4 text-gray-900">الإيرادات حسب الفئة</h5>
                    <canvas id="revenueByCategory" height="250"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h5 class="text-lg font-semibold mb-4 text-gray-900">المصروفات حسب الفئة</h5>
                    <canvas id="expensesByCategory" height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- الاتجاه الشهري --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6">
                <h5 class="text-lg font-semibold mb-4 text-gray-900">الاتجاه الشهري (آخر 6 أشهر)</h5>
                <canvas id="monthlyTrend" height="100"></canvas>
            </div>
        </div>

        {{-- آخر العمليات --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6">
                <h5 class="text-lg font-semibold mb-4 text-gray-900">آخر العمليات المالية</h5>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="table-light">
                            <tr>
                                <th class="text-end">النوع</th>
                                <th class="text-end">التاريخ</th>
                                <th class="text-end">الفئة</th>
                                <th class="text-end">المبلغ</th>
                                <th class="text-end">الحالة</th>
                                <th class="text-end">الوصف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->getRecentTransactions() as $transaction)
                                <tr>
                                    <td class="text-end">
                                        @if($transaction['type'] == 'revenue')
                                            <span class="badge bg-success">إيراد</span>
                                        @else
                                            <span class="badge bg-danger">مصروف</span>
                                        @endif
                                    </td>
                                    <td class="text-end small">{{ \Carbon\Carbon::parse($transaction['date'])->format('Y-m-d') }}</td>
                                    <td class="text-end">{{ $transaction['category'] }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($transaction['amount'], 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $transaction['status'] == 'paid' || $transaction['status'] == 'received' ? 'success' : 'warning' }}">
                                            {{ $transaction['status'] == 'paid' || $transaction['status'] == 'received' ? 'مدفوع' : 'معلق' }}
                                        </span>
                                    </td>
                                    <td class="text-end small text-muted">{{ $transaction['description'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">لا توجد عمليات مالية</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue by Category
            const revenueByCategoryCtx = document.getElementById('revenueByCategory');
            if (revenueByCategoryCtx) {
                @php
                    $revenueByCategory = $this->getRevenueByCategory();
                @endphp
                new Chart(revenueByCategoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($revenueByCategory->pluck('category.name')),
                        datasets: [{
                            data: @json($revenueByCategory->pluck('total')),
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(139, 92, 246, 0.8)',
                            ],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            // Expenses by Category
            const expensesByCategoryCtx = document.getElementById('expensesByCategory');
            if (expensesByCategoryCtx) {
                @php
                    $expensesByCategory = $this->getExpensesByCategory();
                @endphp
                new Chart(expensesByCategoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($expensesByCategory->pluck('category.name')),
                        datasets: [{
                            data: @json($expensesByCategory->pluck('total')),
                            backgroundColor: [
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(139, 92, 246, 0.8)',
                            ],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            // Monthly Trend
            const monthlyTrendCtx = document.getElementById('monthlyTrend');
            if (monthlyTrendCtx) {
                @php
                    $monthlyTrend = $this->getMonthlyTrend();
                @endphp
                new Chart(monthlyTrendCtx, {
                    type: 'line',
                    data: {
                        labels: @json($monthlyTrend['months']),
                        datasets: [
                            {
                                label: 'الإيرادات',
                                data: @json($monthlyTrend['revenues']),
                                borderColor: 'rgb(16, 185, 129)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'المصروفات',
                                data: @json($monthlyTrend['expenses']),
                                borderColor: 'rgb(239, 68, 68)',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-filament-panels::page>
