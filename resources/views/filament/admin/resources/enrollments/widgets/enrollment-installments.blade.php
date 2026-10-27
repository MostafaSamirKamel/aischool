@php
    $installments = $this->getInstallments();
    $stats = $this->getStats();
    $paidPercentage = $stats['total_amount'] > 0 ? ($stats['total_paid'] / $stats['total_amount']) * 100 : 0;
@endphp

<style scoped>
    .inst-hero {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
        border-radius: 1.25rem;
        padding: 2rem;
        box-shadow: 0 12px 32px rgba(99, 102, 241, 0.15);
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
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
    
    .inst-hero-content {
        position: relative;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .inst-hero h2 {
        font-size: 1.75rem;
        font-weight: 900;
        color: white;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .inst-hero p {
        margin: 0.5rem 0 0 0;
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .inst-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .inst-stat {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }
    
    .inst-stat:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    
    .inst-stat-label {
        font-size: 0.8125rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: none;
        letter-spacing: 0.3px;
    }
    
    .inst-stat-value {
        font-size: 1.5rem;
        font-weight: 900;
        color: #1e293b;
    }
    
    .inst-progress-section {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    
    .inst-progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .inst-progress-header p {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
    }
    
    .inst-progress-bar {
        width: 100%;
        height: 0.5rem;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        position: relative;
    }
    
    .inst-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #22c55e, #16a34a);
        border-radius: 999px;
        transition: all 0.5s ease;
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);
        position: relative;
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
    
    .inst-list {
        display: grid;
        gap: 1rem;
    }
    
    .inst-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
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
    
    .inst-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .inst-card-title {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }
    
    .inst-card-number {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-size: 0.9375rem;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        flex-shrink: 0;
    }
    
    .inst-card-info p {
        margin: 0;
    }
    
    .inst-card-title-text {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.9375rem;
    }
    
    .inst-card-date {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.25rem;
        font-weight: 500;
    }
    
    .inst-card-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.875rem;
        border-radius: 1.25rem;
        font-size: 0.75rem;
        font-weight: 700;
        backdrop-filter: blur(10px);
        position: relative;
        z-index: 1;
    }
    
    .inst-badge--paid {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
        border: 1.5px solid #86efac;
    }
    
    .inst-badge--partial {
        background: rgba(249, 115, 22, 0.1);
        color: #92400e;
        border: 1.5px solid #fed7aa;
    }
    
    .inst-badge--pending {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        border: 1.5px solid #93c5fd;
    }
    
    .inst-amounts {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 0.875rem;
        margin-top: 1rem;
        border: 1px solid #e2e8f0;
        position: relative;
        z-index: 1;
    }
    
    .inst-amount {
        text-align: center;
    }
    
    .inst-amount-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.375rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .inst-amount-value {
        font-weight: 900;
        font-size: 1rem;
        color: #1e293b;
    }
    
    .inst-progress {
        margin-top: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .inst-progress-header-small {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .inst-progress-header-small p {
        margin: 0;
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
    }
    
    .inst-progress-value {
        font-weight: 800;
        color: #6366f1;
        font-size: 0.875rem;
    }
    
    .inst-progress-bar-container {
        width: 100%;
        height: 0.5rem;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    
    .inst-progress-fill {
        background: linear-gradient(90deg, #22c55e, #16a34a);
        height: 100%;
        border-radius: 999px;
        transition: all 0.3s ease;
    }
    
    .inst-progress-text {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0.375rem 0 0 0;
        text-align: center;
        font-weight: 600;
    }
    
    .inst-empty {
        text-align: center;
        padding: 3.75rem 1.25rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 2px dashed #cbd5e1;
        border-radius: 1.25rem;
    }
    
    .inst-empty-icon {
        font-size: 4rem;
    }
    
    .inst-empty-title {
        font-size: 1.125rem;
        font-weight: 800;
        color: #0f172a;
        margin: 1rem 0 0 0;
    }
    
    .inst-empty-text {
        color: #64748b;
        margin: 0.5rem 0 0 0;
        font-size: 0.8125rem;
    }
    
    @media (max-width: 1024px) {
        .inst-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        
        .inst-amounts {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 640px) {
        .inst-stat-grid {
            grid-template-columns: 1fr;
        }
        
        .inst-amounts {
            grid-template-columns: 1fr;
        }
        
        .inst-hero h2 {
            font-size: 1.25rem;
        }
    }
</style>

<div class="inst-hero">
    <div class="inst-hero-content">
        <h2>📋 جدول الأقساط</h2>
        <p>إدارة شاملة لجميع الأقساط والمدفوعات</p>
    </div>
</div>

@if($stats['total_count'] > 0)
    <!-- Statistics Grid -->
    <div class="inst-stat-grid">
        <div class="inst-stat">
            <div class="inst-stat-label">📊 إجمالي الأقساط</div>
            <div class="inst-stat-value">{{ $stats['total_count'] }}</div>
        </div>
        <div class="inst-stat">
            <div class="inst-stat-label">💰 المبلغ الكلي</div>
            <div class="inst-stat-value" style="color: #7c3aed;">{{ number_format($stats['total_amount'], 2) }} ج</div>
        </div>
        <div class="inst-stat">
            <div class="inst-stat-label">✅ المدفوع</div>
            <div class="inst-stat-value" style="color: #16a34a;">{{ number_format($stats['total_paid'], 2) }} ج</div>
        </div>
        <div class="inst-stat">
            <div class="inst-stat-label">⏳ المتبقي</div>
            <div class="inst-stat-value" style="color: #f59e0b;">{{ number_format($stats['total_remaining'], 2) }} ج</div>
        </div>
    </div>

    <!-- Overall Progress -->
    <div class="inst-progress-section">
        <div class="inst-progress-header">
            <p>نسبة السداد الإجمالية</p>
            <p style="font-size: 1.25rem; color: #6366f1; margin: 0; font-weight: 900;">{{ round($paidPercentage) }}%</p>
        </div>
        <div class="inst-progress-bar">
            <div class="inst-progress-fill" style="width: {{ $paidPercentage }}%;"></div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1rem; text-align: center;">
            <div>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0; font-weight: 600;">المبلغ الكلي</p>
                <p style="font-weight: 800; color: #1e293b; margin: 0.25rem 0 0 0;">{{ number_format($stats['total_amount'], 2) }} ج</p>
            </div>
            <div>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0; font-weight: 600;">تم السداد</p>
                <p style="font-weight: 800; color: #22c55e; margin: 0.25rem 0 0 0;">{{ number_format($stats['total_paid'], 2) }} ج</p>
            </div>
            <div>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0; font-weight: 600;">المتبقي</p>
                <p style="font-weight: 800; color: #f59e0b; margin: 0.25rem 0 0 0;">{{ number_format($stats['total_remaining'], 2) }} ج</p>
            </div>
        </div>
    </div>

    <!-- Installments List -->
    <div class="inst-list">
        @foreach($installments as $index => $installment)
            @php
                $status = $installment->distributed_status;
                $statusBadge = match($status) {
                    'paid' => ['class' => 'inst-badge--paid', 'icon' => '✅', 'label' => 'مكتمل'],
                    'partial' => ['class' => 'inst-badge--partial', 'icon' => '⚠️', 'label' => 'جزئي'],
                    default => ['class' => 'inst-badge--pending', 'icon' => '⏳', 'label' => 'قريب'],
                };
                $percentage = $installment->amount > 0 ? min(100, ($installment->distributed_paid / $installment->amount) * 100) : 0;
            @endphp
            <div class="inst-card">
                <div class="inst-card-header">
                    <div class="inst-card-title">
                        <div class="inst-card-number">{{ $index + 1 }}</div>
                        <div class="inst-card-info">
                            <p class="inst-card-title-text">القسط #{{ $installment->installment_number }}</p>
                            <p class="inst-card-date">📅 الاستحقاق: {{ $installment->due_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <span class="inst-card-badge {{ $statusBadge['class'] }}">
                        <span>{{ $statusBadge['icon'] }}</span>
                        {{ $statusBadge['label'] }}
                    </span>
                </div>

                <!-- Amount Details -->
                <div class="inst-amounts">
                    <div class="inst-amount">
                        <div class="inst-amount-label">قيمة القسط</div>
                        <div class="inst-amount-value" style="color: #6366f1;">{{ number_format($installment->amount, 2) }} ج</div>
                    </div>
                    <div class="inst-amount">
                        <div class="inst-amount-label">المدفوع</div>
                        <div class="inst-amount-value" style="color: #16a34a;">{{ number_format($installment->distributed_paid, 2) }} ج</div>
                    </div>
                    <div class="inst-amount">
                        <div class="inst-amount-label">المتبقي</div>
                        <div class="inst-amount-value" style="color: #f59e0b;">{{ number_format($installment->amount - $installment->distributed_paid, 2) }} ج</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="inst-progress">
                    <div class="inst-progress-header-small">
                        <p>نسبة السداد</p>
                        <p class="inst-progress-value">{{ round($percentage) }}%</p>
                    </div>
                    <div class="inst-progress-bar-container">
                        <div class="inst-progress-fill" style="width: {{ $percentage }}%;"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="inst-empty">
        <div class="inst-empty-icon">📭</div>
        <h3 class="inst-empty-title">لا توجد أقساط مجدولة</h3>
        <p class="inst-empty-text">هذا التسجيل لا يتضمن نظام تقسيط المدفوعات</p>
    </div>
@endif
