@php
    $installments = $this->getInstallments();
    $totalAmount = $installments->sum('amount');
    $totalPaid = $installments->sum(fn($i) => $i->distributed_paid);
    $totalRemaining = $totalAmount - $totalPaid;
    $paidPercentage = $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0;
@endphp

<style>
    .inst-hero {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
        position: relative;
        overflow: hidden;
    }
    
    .inst-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        filter: blur(40px);
    }
    
    .inst-stat {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    
    .inst-stat:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    
    .inst-stat-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: none;
        letter-spacing: 0.3px;
    }
    
    .inst-stat-value {
        font-size: 24px;
        font-weight: 900;
        color: #1e293b;
    }
    
    .inst-list {
        display: grid;
        gap: 14px;
    }
    
    .inst-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .inst-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #6366f1, #8b5cf6);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .inst-card:hover {
        box-shadow: 0 12px 32px rgba(99, 102, 241, 0.12);
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    
    .inst-card:hover::before {
        opacity: 1;
    }
    
    .inst-card.overdue {
        border-color: #fca5a5;
        background: linear-gradient(135deg, #fef2f2 0%, #ffe4e6 100%);
    }
    
    .inst-card.overdue::before {
        background: linear-gradient(180deg, #ef4444, #f87171);
        opacity: 1;
    }
    
    .inst-card.paid {
        background: linear-gradient(135deg, #f0fdf4 0%, #e8f8f0 100%);
        border-color: #bbf7d0;
    }
    
    .inst-card.paid::before {
        background: linear-gradient(180deg, #22c55e, #16a34a);
        opacity: 1;
    }
    
    .inst-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .inst-badge--paid {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
        border: 1.5px solid #86efac;
    }
    
    .inst-badge--paid:hover {
        background: rgba(34, 197, 94, 0.15);
    }
    
    .inst-badge--partial {
        background: rgba(249, 115, 22, 0.1);
        color: #92400e;
        border: 1.5px solid #fed7aa;
    }
    
    .inst-badge--partial:hover {
        background: rgba(249, 115, 22, 0.15);
    }
    
    .inst-badge--pending {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        border: 1.5px solid #93c5fd;
    }
    
    .inst-badge--pending:hover {
        background: rgba(59, 130, 246, 0.15);
    }
    
    .inst-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
    }
    
    .inst-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
    }
    
    .inst-number {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-size: 15px;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    
    .inst-details p {
        margin: 0;
    }
    
    .inst-details-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 15px;
        margin-bottom: 2px;
    }
    
    .inst-details-date {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
    }
    
    .inst-amount-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        padding: 14px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        margin: 14px 0;
        border: 1px solid #e2e8f0;
    }
    
    .inst-amount {
        text-align: center;
    }
    
    .inst-amount-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    
    .inst-amount-value {
        font-weight: 900;
        font-size: 16px;
        color: #1e293b;
    }
    
    .inst-progress-container {
        margin-top: 14px;
    }
    
    .inst-progress-bar {
        width: 100%;
        height: 8px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }
    
    .inst-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #22c55e, #16a34a);
        border-radius: 10px;
        transition: all 0.5s ease;
        position: relative;
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);
    }
    
    .inst-progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .inst-progress-text {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
        text-align: center;
        font-weight: 600;
    }
    
    .inst-empty {
        text-align: center;
        padding: 60px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 2px dashed #cbd5e1;
        border-radius: 20px;
    }
    
    .inst-empty-text {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin-top: 15px;
    }
    
    .inst-empty-sub {
        color: #64748b;
        margin-top: 8px;
        font-size: 13px;
    }
    
    .inst-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }
</style>

<div class="space-y-6" dir="rtl">
    <!-- Hero Header -->
    <div class="inst-hero rounded-2xl p-8 shadow-xl text-white relative">
        <div class="relative z-10">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                <span style="font-size: 32px;">📋</span>
                <h2 style="font-size: 28px; font-weight: 900; margin: 0;">جدول الأقساط</h2>
            </div>
            <p style="margin: 0; opacity: 0.9; font-size: 15px; font-weight: 500;">إدارة شاملة لجميع تفاصيل الأقساط والمدفوعات</p>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="inst-stats-grid">
        <div class="inst-stat">
            <div>
                <p class="inst-stat-label">📊 إجمالي الأقساط</p>
                <p class="inst-stat-value">{{ $installments->count() }}</p>
            </div>
        </div>
        
        <div class="inst-stat">
            <div>
                <p class="inst-stat-label">💰 المبلغ الكلي</p>
                <p class="inst-stat-value" style="color: #7c3aed;">{{ number_format($totalAmount, 2) }} ج</p>
            </div>
        </div>
        
        <div class="inst-stat">
            <div>
                <p class="inst-stat-label">✅ المدفوع</p>
                <p class="inst-stat-value" style="color: #16a34a;">{{ number_format($totalPaid, 2) }} ج</p>
            </div>
        </div>
        
        <div class="inst-stat">
            <div>
                <p class="inst-stat-label">⏳ المتبقي</p>
                <p class="inst-stat-value" style="color: #f59e0b;">{{ number_format($totalRemaining, 2) }} ج</p>
            </div>
        </div>
    </div>

    <!-- Overall Progress -->
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <p style="font-weight: 700; color: #1e293b; margin: 0; font-size: 14px;">نسبة السداد الإجمالية</p>
            <p style="font-weight: 900; font-size: 18px; color: #6366f1; margin: 0;">{{ round($paidPercentage) }}%</p>
        </div>
        <div class="inst-progress-bar">
            <div class="inst-progress-fill" style="width: {{ $paidPercentage }}%;"></div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 14px; text-align: center;">
            <div>
                <p style="font-size: 11px; color: #64748b; margin: 0; font-weight: 600;">المبلغ الكلي</p>
                <p style="font-weight: 800; color: #1e293b; margin: 4px 0 0 0;">{{ number_format($totalAmount, 2) }} ج</p>
            </div>
            <div>
                <p style="font-size: 11px; color: #64748b; margin: 0; font-weight: 600;">تم السداد</p>
                <p style="font-weight: 800; color: #22c55e; margin: 4px 0 0 0;">{{ number_format($totalPaid, 2) }} ج</p>
            </div>
            <div>
                <p style="font-size: 11px; color: #64748b; margin: 0; font-weight: 600;">المتبقي</p>
                <p style="font-weight: 800; color: #f59e0b; margin: 4px 0 0 0;">{{ number_format($totalRemaining, 2) }} ج</p>
            </div>
        </div>
    </div>

    <!-- Installments List -->
    @if($installments->count() > 0)
        <div class="inst-list">
            @foreach($installments as $index => $installment)
                @php
                    $status = $installment->distributed_status;
                    $statusBadge = match($status) {
                        'paid' => ['class' => 'inst-badge--paid', 'icon' => '✅', 'label' => 'مكتمل'],
                        'partial' => ['class' => 'inst-badge--partial', 'icon' => '⚠️', 'label' => 'سداد جزئي'],
                        default => ['class' => 'inst-badge--pending', 'icon' => '⏳', 'label' => 'قيد الانتظار'],
                    };
                    $isOverdue = $installment->due_date < now() && $status !== 'paid';
                    $cardClass = $isOverdue ? 'overdue' : ($status === 'paid' ? 'paid' : '');
                    $percentage = $installment->amount > 0 ? ($installment->distributed_paid / $installment->amount) * 100 : 0;
                @endphp
                <div class="inst-card {{ $cardClass }}">
                    <div class="inst-header">
                        <div class="inst-header-left">
                            <div class="inst-number">{{ $index + 1 }}</div>
                            <div class="inst-details">
                                <p class="inst-details-title">القسط #{{ $installment->installment_number }}</p>
                                <p class="inst-details-date">📅 موعد الاستحقاق: {{ $installment->due_date->format('d/m/Y') }}</p>
                                @if($isOverdue)
                                    @php
                                        $daysOverdue = $installment->due_date->diffInDays(now());
                                    @endphp
                                    <p style="font-size: 12px; color: #dc2626; font-weight: 600; margin-top: 4px;">🚨 متأخر {{ $daysOverdue }} يوم</p>
                                @endif
                            </div>
                        </div>
                        <span class="inst-badge {{ $statusBadge['class'] }}">
                            {{ $statusBadge['icon'] }} {{ $statusBadge['label'] }}
                        </span>
                    </div>

                    <!-- Amount Details -->
                    <div class="inst-amount-grid">
                        <div class="inst-amount">
                            <p class="inst-amount-label">قيمة القسط</p>
                            <p class="inst-amount-value" style="color: #6366f1;">{{ number_format($installment->amount, 2) }} ج</p>
                        </div>
                        <div class="inst-amount">
                            <p class="inst-amount-label">المدفوع</p>
                            <p class="inst-amount-value" style="color: #16a34a;">{{ number_format($installment->distributed_paid, 2) }} ج</p>
                        </div>
                        <div class="inst-amount">
                            <p class="inst-amount-label">المتبقي</p>
                            <p class="inst-amount-value" style="color: #f59e0b;">{{ number_format($installment->amount - $installment->distributed_paid, 2) }} ج</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="inst-progress-container">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <p style="font-size: 12px; color: #64748b; font-weight: 600; margin: 0;">نسبة السداد</p>
                            <p style="font-weight: 800; color: #6366f1; margin: 0;">{{ round($percentage) }}%</p>
                        </div>
                        <div class="inst-progress-bar">
                            <div class="inst-progress-fill" style="width: {{ $percentage }}%;"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="inst-empty">
            <span style="font-size: 64px;">📭</span>
            <p class="inst-empty-text">لا توجد أقساط مجدولة</p>
            <p class="inst-empty-sub">هذا التسجيل لا يتضمن نظام تقسيط المدفوعات</p>
        </div>
    @endif
</div>
</div>
