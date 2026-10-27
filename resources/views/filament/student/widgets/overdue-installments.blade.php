<x-filament-widgets::widget>
    @if($this->hasOverdueInstallments())
    <style>
        .overdue-alert {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #ef4444;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(239, 68, 68, 0.15);
            padding: 1.25rem;
            font-family: inherit;
        }
        
        .overdue-header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .installment-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin-bottom: 1.25rem;
        }

        .installment-card {
            background: white;
            border-radius: 12px;
            border: 2px solid #fecaca;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            overflow: hidden;
            padding: 1.15rem;
        }
        
        .installment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.18);
            border-color: #f87171;
        }

        .installment-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            align-items: center;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .installment-grid {
                grid-template-columns: 1fr;
                gap: 0.85rem;
                text-align: center;
            }
        }
        
        .installment-badge {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }
        
        .amount-badge {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 0.5rem 1.15rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 800;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
            display: inline-block;
        }
        
        .info-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 0.85rem 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
    </style>
    
    <div class="overdue-alert" dir="rtl">
        <div class="overdue-header">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <h3 style="margin: 0 0 0.2rem 0; font-size: 1.1rem; font-weight: 800; color: #ffffff;">⚠️ تنبيه: لديك أقساط متأخرة</h3>
                <p style="margin: 0; opacity: 0.92; font-size: 0.85rem; color: #ffffff;">يرجى سداد الأقساط المتأخرة في أقرب وقت ممكن</p>
            </div>
        </div>

        <div class="installment-list">
            @foreach($this->getOverdueInstallments() as $installment)
            <div class="installment-card">
                <div class="installment-grid">
                    <!-- معلومات الكورس -->
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <span class="installment-badge" style="margin-top: 2px;">{{ $installment->installment_number }}</span>
                        <div>
                            <h5 style="margin: 0 0 0.25rem 0; font-weight: 800; color: #18243d; font-size: 0.95rem;">
                                {{ $installment->enrollment->groups->pluck('course.name')->unique()->join(' | ') ?: 'غير محدد' }}
                            </h5>
                            <p style="margin: 0; color: #64748b; font-size: 0.825rem;">القسط رقم {{ $installment->installment_number }}</p>
                        </div>
                    </div>
                    
                    <!-- تاريخ الاستحقاق -->
                    <div style="text-align: center;">
                        <p style="margin: 0 0 0.2rem 0; color: #64748b; font-size: 0.8rem;">تاريخ الاستحقاق</p>
                        <h6 style="margin: 0; font-weight: 800; color: #dc2626; font-size: 0.95rem;">
                            {{ $installment->due_date->format('d/m/Y') }}
                        </h6>
                        <p style="margin: 0.2rem 0 0 0; color: #dc2626; font-size: 0.775rem; font-weight: 700;">
                            متأخر {{ now()->diffInDays($installment->due_date) }} يوم
                        </p>
                    </div>
                    
                    <!-- المبلغ -->
                    <div style="text-align: center;">
                        <p style="margin: 0 0 0.35rem 0; color: #64748b; font-size: 0.8rem;">المبلغ المستحق</p>
                        <div class="amount-badge">
                            {{ number_format($installment->remaining_amount, 2) }} ج.م
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="info-box">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; color: #b45309;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p style="margin: 0; color: #92400e; font-weight: 700; font-size: 0.825rem;">
                💡 للتواصل مع الإدارة لترتيب السداد، يرجى الاتصال على الأرقام المتاحة في قسم الاتصال.
            </p>
        </div>
    </div>
    @endif
</x-filament-widgets::widget>
