<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form Section --}}
        <x-filament::section class="no-print">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-adjustments-horizontal" class="w-6 h-6 text-primary-600" />
                    <span class="text-xl font-bold">{{ __('filament/admin/vault_resource.report.options_heading') }}</span>
                </div>
            </x-slot>

            <form wire:submit.prevent="generateReport">
                {{ $this->form }}

                <div class="mt-6">
                    <x-filament::button type="submit" size="lg" color="primary">
                        <x-filament::icon icon="heroicon-o-document-chart-bar" class="w-5 h-5 me-2" />
                        {{ __('filament/admin/vault_resource.report.generate') }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Report Section --}}
        @if($reportData)
            <div id="printable-report" class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-8">
                {{-- Report Header --}}
                <div class="border-b-4 border-primary-600 pb-6 mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ __('filament/admin/vault_resource.report.title') }}
                            </h1>
                            <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <x-filament::icon icon="heroicon-o-building-library" class="w-4 h-4" />
                                    <span class="font-semibold">{{ $reportData['vault']->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4" />
                                    <span>من {{ $reportData['from_date']->format('d/m/Y') }} إلى {{ $reportData['to_date']->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="no-print">
                            <x-filament::button
                                color="success"
                                icon="heroicon-o-printer"
                                size="lg"
                                onclick="window.print()"
                            >
                                {{ __('filament/admin/vault_resource.report.print') }}
                            </x-filament::button>
                        </div>
                        <div class="print-only text-left" style="display: none;">
                            <div class="text-sm text-gray-500">{{ __('filament/admin/vault_resource.report.print_date') }}</div>
                            <div class="font-semibold">{{ now()->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Opening Balance Card --}}
                <div class="mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="bg-white/20 rounded-full p-3">
                                    <x-filament::icon icon="heroicon-o-wallet" class="w-8 h-8" />
                                </div>
                                <div>
                                    <p class="text-sm opacity-90 mb-1">{{ __('filament/admin/vault_resource.report.opening_balance') }}</p>
                                    <p class="text-3xl font-bold">{{ number_format($reportData['opening_balance'], 2) }} <span class="text-xl">ج.م</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Transactions Table --}}
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-list-bullet" class="w-5 h-5 text-primary-600" />
                        <span>{{ __('filament/admin/vault_resource.report.transactions') }}</span>
                    </h2>
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-6 py-4 text-right font-bold text-gray-700 dark:text-gray-300">{{ __('filament/admin/vault_resource.report.date') }}</th>
                                    <th class="px-6 py-4 text-right font-bold text-gray-700 dark:text-gray-300">{{ __('filament/admin/vault_resource.report.type') }}</th>
                                    <th class="px-6 py-4 text-right font-bold text-gray-700 dark:text-gray-300">{{ __('filament/admin/vault_resource.report.reference') }}</th>
                                    <th class="px-6 py-4 text-right font-bold text-gray-700 dark:text-gray-300">{{ __('filament/admin/vault_resource.report.description') }}</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-700 dark:text-gray-300">{{ __('filament/admin/vault_resource.report.amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($reportData['transactions'] as $index => $transaction)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ $index % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/50 dark:bg-gray-800/30' }}">
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full {{ $transaction['is_income'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                                {{ $transaction['type'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs text-gray-600 dark:text-gray-400">
                                            {{ $transaction['reference'] }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            {{ $transaction['description'] }}
                                        </td>
                                        <td class="px-6 py-4 text-left font-bold text-lg {{ $transaction['is_income'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            <span class="font-normal text-sm">{{ $transaction['is_income'] ? '+' : '-' }}</span>
                                            {{ number_format($transaction['amount'], 2) }}
                                            <span class="text-sm font-normal">ج.م</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                                <x-filament::icon icon="heroicon-o-inbox" class="w-16 h-16 opacity-50" />
                                                <p class="text-lg font-semibold">{{ __('filament/admin/vault_resource.report.no_transactions') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Summary Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    {{-- Total Income --}}
                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-6 border-2 border-green-200 dark:border-green-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-green-500 text-white rounded-lg p-2">
                                <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-6 h-6" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('filament/admin/vault_resource.report.total_income') }}</h3>
                        </div>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                            {{ number_format($reportData['total_income'], 2) }}
                            <span class="text-xl">ج.م</span>
                        </p>
                    </div>

                    {{-- Total Expenses --}}
                    <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-6 border-2 border-red-200 dark:border-red-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-red-500 text-white rounded-lg p-2">
                                <x-filament::icon icon="heroicon-o-arrow-trending-down" class="w-6 h-6" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('filament/admin/vault_resource.report.total_expenses') }}</h3>
                        </div>
                        <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                            {{ number_format($reportData['total_expenses'], 2) }}
                            <span class="text-xl">ج.م</span>
                        </p>
                    </div>

                    {{-- Closing Balance --}}
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border-2 border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-blue-500 text-white rounded-lg p-2">
                                <x-filament::icon icon="heroicon-o-calculator" class="w-6 h-6" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('filament/admin/vault_resource.report.closing_balance') }}</h3>
                        </div>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($reportData['closing_balance'], 2) }}
                            <span class="text-xl">ج.م</span>
                        </p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="border-t-2 border-gray-200 dark:border-gray-700 pt-6 mt-8 print-only" style="display: none;">
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <div>
                            <p>{{ __('filament/admin/vault_resource.report.created_by') }}: {{ auth()->user()->name }}</p>
                        </div>
                        <div class="text-left">
                            <p>{{ __('filament/admin/vault_resource.report.system_name') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }

            body * {
                visibility: hidden;
            }

            #printable-report,
            #printable-report * {
                visibility: visible;
            }

            #printable-report {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            /* تحسين الطباعة */
            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            /* إزالة الظلال والتدرجات في الطباعة */
            .shadow-lg,
            .shadow {
                box-shadow: none !important;
            }

            /* تحسين الألوان للطباعة */
            .bg-gradient-to-r,
            .bg-gradient-to-br {
                background: #3b82f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .border {
                border: 1px solid #000 !important;
            }
        }

        /* تحسينات عامة */
        table {
            border-collapse: separate;
            border-spacing: 0;
        }

        table th {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
    @endpush
</x-filament-panels::page>
