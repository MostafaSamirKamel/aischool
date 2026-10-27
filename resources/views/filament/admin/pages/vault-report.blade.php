<x-filament-panels::page>
    <style>
        @keyframes slideDown { 
            from { opacity: 0; transform: translateY(-20px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        @keyframes fadeIn { 
            from { opacity: 0; } 
            to { opacity: 1; } 
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .animate-slide-down { animation: slideDown 0.5s ease-out; }
        .animate-fade-in { animation: fadeIn 0.8s ease-out; }
        @media print {
            body * { visibility: hidden; }
            #printable-report, #printable-report * { visibility: visible; }
            #printable-report { position: absolute; left: 0; top: 0; width: 100%; padding: 15mm; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            @page { size: A4; margin: 12mm; }
        }
    </style>

    <div style="display: flex; flex-direction: column; gap: 24px;">
        {{-- Form Section --}}
        <div class="no-print" style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); border: 2px solid #e5e7eb;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                <div style="background: linear-gradient(135deg, rgb(59, 130, 246), rgb(37, 99, 235)); border-radius: 12px; padding: 12px;">
                    ⚙️
                </div>
                <div>
                    <span style="font-size: 24px; font-weight: 900; color: #111827;">{{ __('filament/admin/vault_report.options_heading') }}</span>
                    <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;">اختر الخزينة والفترة الزمنية لإنشاء التقرير</p>
                </div>
            </div>

            <form wire:submit.prevent="generateReport" style="margin-top: 24px;">
                {{ $this->form }}

                <div style="margin-top: 16px;">
                    <x-filament::button type="submit" size="xl" color="primary">
                        <x-filament::icon icon="heroicon-o-document-chart-bar" class="w-6 h-6 me-2" />
                        <span style="font-size: 16px; font-weight: bold;">{{ __('filament/admin/vault_report.generate') }}</span>
                    </x-filament::button>
                </div>
            </form>
        </div>

        {{-- Export & Print Buttons --}}
        @if($reportData)
        <div class="no-print" style="display: flex; gap: 12px;">
            <a href="{{ route('admin.reports.vault.export-excel') }}" 
               style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, rgb(34, 197, 94), rgb(22, 163, 74)); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); transition: all 0.2s; cursor: pointer;"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(34, 197, 94, 0.4)'"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(34, 197, 94, 0.3)'">
                <span style="font-size: 20px;">📊</span> تصدير Excel
            </a>

            <a href="{{ route('admin.reports.vault.export-pdf') }}" 
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
        @endif

        {{-- Report Section --}}
        @if($reportData)
            <div id="printable-report" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); animation: slideDown 0.6s ease-out;">
                {{-- Report Header --}}
                <div style="position: relative; background: linear-gradient(135deg, rgb(79, 70, 229), rgb(109, 40, 217), rgb(147, 51, 234)); color: white; padding: 40px; overflow: hidden;">
                    
                    <div style="position: relative; z-index: 10;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                                    <div style="background: rgba(255, 255, 255, 0.2); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.3);">
                                        📄
                                    </div>
                                    <div>
                                        <h1 style="font-size: 42px; font-weight: 900; margin: 0 0 8px 0; color: white;">
                                            {{ __('filament/admin/vault_report.title') }}
                                        </h1>
                                        <p style="color: #ddd6fe; font-size: 16px; margin: 0;">تقرير شامل لحركة الخزينة</p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 24px; background: rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 16px 24px; width: fit-content; border: 1px solid rgba(255, 255, 255, 0.2);">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="background: rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 8px;">
                                            🏛️
                                        </div>
                                        <div>
                                            <p style="font-size: 11px; color: #ddd6fe; margin: 0 0 4px 0;">الخزينة</p>
                                            <p style="font-weight: bold; font-size: 16px; margin: 0; color: white;">{{ $reportData['vault']->name }}</p>
                                        </div>
                                    </div>
                                    <div style="width: 1px; height: 48px; background: rgba(255, 255, 255, 0.3);"></div>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="background: rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 8px;">
                                            📅
                                        </div>
                                        <div>
                                            <p style="font-size: 11px; color: #ddd6fe; margin: 0 0 4px 0;">الفترة</p>
                                            <p style="font-weight: bold; font-size: 16px; margin: 0; color: white;">{{ $reportData['from_date']->format('d/m/Y') }} - {{ $reportData['to_date']->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="no-print">
                                <x-filament::button
                                    color="success"
                                    icon="heroicon-o-printer"
                                    size="xl"
                                    onclick="window.print()"
                                >
                                    <span style="font-size: 16px; font-weight: bold;">{{ __('filament/admin/vault_report.print') }}</span>
                                </x-filament::button>
                            </div>
                            <div class="print-only" style="display: none; text-align: left;">
                                <div style="font-size: 12px; color: #6b7280;">{{ __('filament/admin/vault_report.print_date') }}</div>
                                <div style="font-weight: 600;">{{ now()->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 40px; display: flex; flex-direction: column; gap: 40px; background: linear-gradient(135deg, #f9fafb, #ffffff);">
                    {{-- Opening Balance Card --}}
                    <div style="position: relative; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); animation: fadeIn 0.8s ease-out;">
                        <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(37, 99, 235), rgb(79, 70, 229), rgb(139, 92, 246));"></div>
                        <div style="position: relative; color: white; padding: 32px;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 20px;">
                                    <div style="background: rgba(255, 255, 255, 0.25); border-radius: 24px; padding: 20px; border: 1px solid rgba(255, 255, 255, 0.3);">
                                        💰
                                    </div>
                                    <div>
                                        <p style="font-size: 14px; margin: 0 0 8px 0; color: #ddd6fe; font-weight: 600;">{{ __('filament/admin/vault_report.opening_balance') }}</p>
                                        <p style="font-size: 56px; font-weight: 900; margin: 0; color: white;">
                                            {{ number_format($reportData['opening_balance'], 2) }}
                                            <span style="font-size: 28px; font-weight: bold; margin-right: 8px;">ج.م</span>
                                        </p>
                                    </div>
                                </div>
                                <div style="text-align: right; background: rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 12px 20px; border: 1px solid rgba(255, 255, 255, 0.2);">
                                    <div style="font-size: 13px; font-weight: 600; color: white; margin: 0 0 8px 0;">رصيد بداية الفترة</div>
                                    <div style="font-size: 12px; color: #ddd6fe; margin: 0;">{{ $reportData['from_date']->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Transactions Table --}}
                    <div style="animation: fadeIn 1s ease-out;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px;">
                            <div style="background: linear-gradient(135deg, rgb(59, 130, 246), rgb(37, 99, 235)); border-radius: 12px; padding: 12px;">
                                📋
                            </div>
                            <div>
                                <h2 style="font-size: 28px; font-weight: 900; color: #111827; margin: 0;">{{ __('filament/admin/vault_report.transactions') }}</h2>
                                <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">جميع المعاملات خلال الفترة المحددة</p>
                            </div>
                            <div style="flex: 1; border-top: 2px dashed #d1d5db; margin-right: 16px;"></div>
                        </div>
                        <div style="overflow: hidden; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: linear-gradient(90deg, #f3f4f6, #f9fafb, #f3f4f6);">
                                        <th style="padding: 20px 32px; text-align: right; font-weight: 900; font-size: 14px; color: #1f2937; border-bottom: 2px solid #d1d5db;">{{ __('filament/admin/vault_report.date') }}</th>
                                        <th style="padding: 20px 32px; text-align: right; font-weight: 900; font-size: 14px; color: #1f2937; border-bottom: 2px solid #d1d5db;">{{ __('filament/admin/vault_report.type') }}</th>
                                        <th style="padding: 20px 32px; text-align: right; font-weight: 900; font-size: 14px; color: #1f2937; border-bottom: 2px solid #d1d5db;">{{ __('filament/admin/vault_report.reference') }}</th>
                                        <th style="padding: 20px 32px; text-align: right; font-weight: 900; font-size: 14px; color: #1f2937; border-bottom: 2px solid #d1d5db;">{{ __('filament/admin/vault_report.description') }}</th>
                                        <th style="padding: 20px 32px; text-align: left; font-weight: 900; font-size: 14px; color: #1f2937; border-bottom: 2px solid #d1d5db;">{{ __('filament/admin/vault_report.amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData['transactions'] as $index => $transaction)
                                        <tr style="background: {{ $index % 2 === 0 ? '#ffffff' : '#f9fafb' }}; transition: all 0.3s ease;">
                                            <td style="padding: 24px 32px; color: #1f2937; font-weight: bold; font-size: 15px; border-bottom: 1px solid #f3f4f6;">
                                                📅 {{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}
                                            </td>
                                            <td style="padding: 24px 32px; border-bottom: 1px solid #f3f4f6;">
                                                <span style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-size: 13px; font-weight: 900; border-radius: 12px; color: white; background: {{ $transaction['is_income'] ? 'linear-gradient(90deg, rgb(16, 185, 129), rgb(5, 150, 105))' : 'linear-gradient(90deg, rgb(239, 68, 68), rgb(220, 38, 38))' }};">
                                                    {{ $transaction['is_income'] ? '📈' : '📉' }}
                                                    {{ $transaction['type'] }}
                                                </span>
                                            </td>
                                            <td style="padding: 24px 32px; font-family: monospace; font-size: 13px; color: #4b5563; font-weight: bold; background: #f9fafb; border-bottom: 1px solid #f3f4f6;">
                                                {{ $transaction['reference'] }}
                                            </td>
                                            <td style="padding: 24px 32px; color: #374151; font-size: 15px; font-weight: 500; border-bottom: 1px solid #f3f4f6;">
                                                {{ $transaction['description'] }}
                                            </td>
                                            <td style="padding: 24px 32px; text-align: left; font-weight: 900; font-size: 22px; color: {{ $transaction['is_income'] ? '#059669' : '#dc2626' }}; border-bottom: 1px solid #f3f4f6;">
                                                {{ $transaction['is_income'] ? '+' : '-' }}
                                                {{ number_format($transaction['amount'], 2) }}
                                                <span style="font-size: 14px; font-weight: bold; margin-right: 4px;">ج.م</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="padding: 80px 32px; text-align: center;">
                                                <div style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                                                    <div style="background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border-radius: 24px; padding: 32px;">
                                                        📭
                                                    </div>
                                                    <p style="font-size: 22px; font-weight: 900; color: #6b7280; margin: 0;">{{ __('filament/admin/vault_report.no_transactions') }}</p>
                                                    <p style="font-size: 13px; color: #9ca3af; margin: 0;">لا توجد عمليات في هذه الفترة</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Summary Cards --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px; animation: fadeIn 1.2s ease-out;">
                        {{-- Total Income --}}
                        <div style="position: relative; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(16, 185, 129), rgb(5, 150, 105), rgb(6, 182, 212));"></div>
                            <div style="position: relative; color: white; padding: 32px;">
                                <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 20px;">
                                    <div style="background: rgba(255, 255, 255, 0.25); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.3);">
                                        📈
                                    </div>
                                    <div style="font-size: 11px; font-weight: 900; background: rgba(255, 255, 255, 0.25); padding: 8px 16px; border-radius: 9999px;">إيرادات</div>
                                </div>
                                <h3 style="font-size: 13px; font-weight: bold; margin: 0 0 12px 0; color: #d1fae5;">{{ __('filament/admin/vault_report.total_income') }}</h3>
                                <p style="font-size: 48px; font-weight: 900; margin: 0; color: white;">
                                    {{ number_format($reportData['total_income'], 2) }}
                                    <span style="font-size: 22px; font-weight: bold; margin-right: 4px;">ج.م</span>
                                </p>
                            </div>
                        </div>

                        {{-- Total Expenses --}}
                        <div style="position: relative; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(239, 68, 68), rgb(220, 38, 38), rgb(236, 72, 153));"></div>
                            <div style="position: relative; color: white; padding: 32px;">
                                <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 20px;">
                                    <div style="background: rgba(255, 255, 255, 0.25); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.3);">
                                        📉
                                    </div>
                                    <div style="font-size: 11px; font-weight: 900; background: rgba(255, 255, 255, 0.25); padding: 8px 16px; border-radius: 9999px;">مصروفات</div>
                                </div>
                                <h3 style="font-size: 13px; font-weight: bold; margin: 0 0 12px 0; color: #fecaca;">{{ __('filament/admin/vault_report.total_expenses') }}</h3>
                                <p style="font-size: 48px; font-weight: 900; margin: 0; color: white;">
                                    {{ number_format($reportData['total_expenses'], 2) }}
                                    <span style="font-size: 22px; font-weight: bold; margin-right: 4px;">ج.م</span>
                                </p>
                            </div>
                        </div>

                        {{-- Closing Balance --}}
                        <div style="position: relative; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(59, 130, 246), rgb(79, 70, 229), rgb(139, 92, 246));"></div>
                            <div style="position: relative; color: white; padding: 32px;">
                                <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 20px;">
                                    <div style="background: rgba(255, 255, 255, 0.25); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.3);">
                                        💰
                                    </div>
                                    <div style="font-size: 11px; font-weight: 900; background: rgba(255, 255, 255, 0.25); padding: 8px 16px; border-radius: 9999px;">رصيد نهائي</div>
                                </div>
                                <h3 style="font-size: 13px; font-weight: bold; margin: 0 0 12px 0; color: #ddd6fe;">{{ __('filament/admin/vault_report.closing_balance') }}</h3>
                                <p style="font-size: 48px; font-weight: 900; margin: 0; color: white;">
                                    {{ number_format($reportData['closing_balance'], 2) }}
                                    <span style="font-size: 22px; font-weight: bold; margin-right: 4px;">ج.م</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Print Footer --}}
                <div class="print-only border-t-2 border-gray-300 pt-8 mt-10 bg-gradient-to-br from-gray-50 to-white" style="display: none;">
                    <div class="bg-white rounded-2xl p-8 shadow-lg">
                        <div class="grid grid-cols-2 gap-8">
                            <div class="flex items-center gap-4">
                                <div class="bg-primary-100 rounded-xl p-3">
                                    <x-filament::icon icon="heroicon-o-user" class="w-6 h-6 text-primary-600" />
                                </div>
                                <div>" style="display: none; border-top: 2px solid #d1d5db; padding-top: 32px; margin-top: 40px; background: linear-gradient(135deg, #f9fafb, #ffffff);">
                    <div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="background: #dbeafe; border-radius: 12px; padding: 12px;">
                                    👤
                                </div>
                                <div>
                                    <p style="font-size: 11px; color: #6b7280; font-weight: bold; margin: 0 0 4px 0;">{{ __('filament/admin/vault_report.created_by') }}</p>
                                    <p style="font-size: 15px; font-weight: 900; color: #111827; margin: 0;">{{ auth()->user()->name }}</p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 16px; text-align: left;">
                                <div style="background: #dbeafe; border-radius: 12px; padding: 12px;">
                                    🏢
                                </div>
                                <div>
                                    <p style="font-size: 11px; color: #6b7280; font-weight: bold; margin: 0 0 4px 0;">{{ __('filament/admin/vault_report.system_name') }}</p>
                                    <p style="font-size: 15px; font-weight: 900; color: #111827; margin: 0;">{{ config('app.name') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
