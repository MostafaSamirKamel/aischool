<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Widgets --}}
        @foreach($this->getHeaderWidgets() as $widget)
            @livewire($widget)
        @endforeach

        {{-- Payment Details Table --}}
        <x-filament::section>
            <x-slot name="heading">
                سجل المدفوعات التفصيلي
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3">كود الدفع</th>
                            <th class="px-6 py-3">الطالب</th>
                            <th class="px-6 py-3">المبلغ</th>
                            <th class="px-6 py-3">الطريقة</th>
                            <th class="px-6 py-3">الحالة</th>
                            <th class="px-6 py-3">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $query = \App\Models\Payment::query()->with(['enrollment.student.user']);
                            if (!auth()->user()->is_super_admin) {
                                $query->where('tenant_id', auth()->user()->tenant_id);
                            }
                            $payments = $query->latest('payment_date')->limit(50)->get();
                        @endphp
                        @foreach($payments as $payment)
                            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 font-medium">{{ $payment->payment_code }}</td>
                                <td class="px-6 py-4">{{ $payment->enrollment->student->user->name ?? 'غير محدد' }}</td>
                                <td class="px-6 py-4 font-bold">{{ number_format($payment->amount, 2) }} ج.م</td>
                                <td class="px-6 py-4">
                                    @php
                                        $methodColors = [
                                            'cash' => 'success',
                                            'card' => 'info',
                                            'bank_transfer' => 'warning',
                                            'online' => 'primary',
                                        ];
                                        $methodLabels = [
                                            'cash' => 'نقدي',
                                            'card' => 'بطاقة',
                                            'bank_transfer' => 'تحويل بنكي',
                                            'online' => 'أونلاين',
                                        ];
                                    @endphp
                                    <x-filament::badge :color="$methodColors[$payment->payment_method] ?? 'gray'">
                                        {{ $methodLabels[$payment->payment_method] ?? 'غير محدد' }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            'refunded' => 'info',
                                        ];
                                        $statusLabels = [
                                            'completed' => 'مكتمل',
                                            'pending' => 'معلق',
                                            'failed' => 'فشل',
                                            'refunded' => 'مسترد',
                                        ];
                                    @endphp
                                    <x-filament::badge :color="$statusColors[$payment->status] ?? 'gray'">
                                        {{ $statusLabels[$payment->status] ?? $payment->status }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4">{{ $payment->payment_date?->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Payment Methods Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $query = \App\Models\Payment::query()->where('status', 'completed');
                if (!auth()->user()->is_super_admin) {
                    $query->where('tenant_id', auth()->user()->tenant_id);
                }
                
                $cashTotal = $query->clone()->where('payment_method', 'cash')->sum('amount');
                $cardTotal = $query->clone()->where('payment_method', 'card')->sum('amount');
                $bankTotal = $query->clone()->where('payment_method', 'bank_transfer')->sum('amount');
                $onlineTotal = $query->clone()->where('payment_method', 'online')->sum('amount');
            @endphp

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($cashTotal, 2) }} ج.م</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">نقدي</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($cardTotal, 2) }} ج.م</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">بطاقة</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($bankTotal, 2) }} ج.م</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">تحويل بنكي</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ number_format($onlineTotal, 2) }} ج.م</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">أونلاين</div>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
