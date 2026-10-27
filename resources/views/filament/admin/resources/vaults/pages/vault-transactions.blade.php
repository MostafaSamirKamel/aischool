<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Overview --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @php
                $user = auth()->user();
                $tenantId = $user->tenant_id;
                
                $vaultsQuery = \App\Models\Vault::query()->where('is_active', true);
                if (!($user->is_super_admin && is_null($tenantId))) {
                    $vaultsQuery->where('tenant_id', $tenantId);
                }
                $vaults = $vaultsQuery->get();
                
                $totalBalance = 0;
                foreach ($vaults as $vault) {
                    $revenues = \App\Models\Revenue::where('vault_id', $vault->id)->sum('amount');
                    $studentPayments = \App\Models\StudentPayment::where('vault_id', $vault->id)
                        ->where('status', 'completed')
                        ->sum('net_amount');
                    $expenses = \App\Models\Expense::where('vault_id', $vault->id)->sum('amount');
                    $salaryPayments = \App\Models\SalaryPayment::where('vault_id', $vault->id)
                        ->where('status', 'paid')
                        ->sum('amount');
                    $totalBalance += $vault->opening_balance + $revenues + $studentPayments - $expenses - $salaryPayments;
                }
            @endphp
            
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">💰</span>
                        <span>إجمالي الرصيد</span>
                    </div>
                </x-slot>
                
                <div class="text-center">
                    <div class="text-3xl font-bold {{ $totalBalance >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ number_format($totalBalance, 2) }} جنيه
                    </div>
                    <div class="mt-1 text-sm text-gray-500">
                        لجميع الخزائن ({{ $vaults->count() }})
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📊</span>
                        <span>عدد المعاملات</span>
                    </div>
                </x-slot>
                
                <div class="text-center">
                    @php
                        $totalTransactions = 
                            \App\Models\Revenue::whereNotNull('vault_id')->count() +
                            \App\Models\StudentPayment::whereNotNull('vault_id')->where('status', 'completed')->count() +
                            \App\Models\SalaryPayment::whereNotNull('vault_id')->where('status', 'paid')->count() +
                            \App\Models\Expense::whereNotNull('vault_id')->count();
                    @endphp
                    <div class="text-3xl font-bold text-primary-600">
                        {{ number_format($totalTransactions) }}
                    </div>
                    <div class="mt-1 text-sm text-gray-500">
                        معاملة مسجلة
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📅</span>
                        <span>التاريخ</span>
                    </div>
                </x-slot>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-700">
                        {{ now()->format('d/m/Y') }}
                    </div>
                    <div class="mt-1 text-sm text-gray-500">
                        {{ now()->locale('ar')->translatedFormat('l') }}
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Transactions Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
