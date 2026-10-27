<x-filament-panels::page>
    <div class="student-theme-wrapper" dir="rtl" x-data="{ tab: 'transactions' }">
        <link rel="stylesheet" href="{{ asset('css/student-theme-scoped.css') }}">
        
        <div>
            <!-- Header Text -->
            <div class="page-header">
                <h1>مدفوعاتي واشتراكاتي</h1>
                <p>مراجعة كل مستحقاتك ومدفوعاتك المسجلة</p>
            </div>

            <div class="payment-hero">
                <div>
                    <span>إجمالي المتبقي</span>
                    <h2>{{ number_format($totals['remaining'], 2) }} ج.م</h2>
                    <p>يتم احتساب المبالغ بناءً على التسجيلات والخصومات والمدفوعات الفعلية.</p>
                </div>
                <div class="wallet-visual">
                    <x-student-icon name="wallet" size="43" />
                </div>
            </div>

            <div class="stats-grid payments-stats" style="margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--text-color);">
                        <x-student-icon name="receipt" size="24" />
                    </div>
                    <div><strong>{{ number_format($totals['gross'], 0) }}</strong><span>الإجمالي</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon mint">
                        <x-student-icon name="check-circle" size="24" />
                    </div>
                    <div><strong>{{ number_format($totals['paid'], 0) }}</strong><span>المدفوع</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <x-student-icon name="star" size="24" />
                    </div>
                    <div><strong>{{ number_format($totals['discount'], 0) }}</strong><span>الخصومات</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon peach">
                        <x-student-icon name="file-text" size="24" />
                    </div>
                    <div><strong>{{ $totals['count'] }}</strong><span>سجلات الدفع</span></div>
                </div>
            </div>

            @php
                $overdueInstallments = array_filter($installments->toArray(), fn($i) => $i['status'] === 'overdue');
            @endphp
            @if(count($overdueInstallments) > 0)
                <div class="inline-warning" style="margin-bottom: 1rem;">
                    <x-student-icon name="triangle-alert" size="19" />
                    <span>لديك {{ count($overdueInstallments) }} قسط متأخر.</span>
                </div>
            @endif

            <div class="pill-tabs" role="tablist">
                <button @click="tab = 'transactions'" :class="tab === 'transactions' ? 'selected' : ''" role="tab">سجل المدفوعات</button>
                <button @click="tab = 'installments'" :class="tab === 'installments' ? 'selected' : ''" role="tab">الأقساط</button>
            </div>

            <div x-show="tab === 'transactions'" style="margin-top: 1rem;">
                <div class="section-title">
                    <h2>سجل الدفعات</h2>
                </div>
                <div class="stack">
                    @forelse ($payments as $p)
                        <article class="payment-line">
                            <span class="payment-icon">
                                <x-student-icon name="receipt" size="21" />
                            </span>
                            <div>
                                <strong>{{ $p['course'] }}</strong>
                                <small>{{ $p['date'] }} • {{ $p['method'] }}</small>
                                <small>#{{ $p['id'] }}</small>
                            </div>
                            <div class="payment-side">
                                <strong>{{ number_format($p['amount'], 2) }}</strong>
                                <span class="status-pill status-completed">مكتمل</span>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <x-student-icon name="receipt" size="40" color="var(--text-muted)" />
                            <h3>لا توجد سجلات دفع</h3>
                            <p>المبالغ المدفوعة هتظهر بعد تسجيلها من الإدارة.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div x-show="tab === 'installments'" style="margin-top: 1rem; display: none;">
                <div class="section-title">
                    <h2>جدول الأقساط</h2>
                </div>
                <div class="stack">
                    @forelse ($installments as $i)
                        <article class="installment-line">
                            <div class="installment-top">
                                <strong>دفعة مستحقة</strong>
                                <span class="status-pill status-{{ $i['status'] }}">
                                    @if($i['status'] == 'completed') مدفوع
                                    @elseif($i['status'] == 'overdue') متأخر
                                    @else قادم
                                    @endif
                                </span>
                            </div>
                            <span>{{ $i['course'] }} • تاريخ الاستحقاق {{ $i['due'] }}</span>
                            <div class="installment-bottom">
                                <strong>{{ number_format($i['amount'], 2) }} ج.م</strong>
                                @if($i['status'] == 'overdue')
                                    <small style="color: var(--rose-dark);">متأخر {{ $i['ago_days'] }} يوم</small>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <x-student-icon name="calendar-days" size="40" color="var(--text-muted)" />
                            <h3>لا توجد أقساط</h3>
                            <p>جدول الأقساط سيظهر بعد ربط بيانات الاشتراكات.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="hint" style="margin-top: 1.5rem;">
                <x-student-icon name="info" size="19" />
                الأموال الحقيقية لا تتغير من واجهة الطالب؛ التسديد والتسوية من نظام الإدارة، والواجهة تعرض البيانات فقط.
            </div>
        </div>
    </div>
</x-filament-panels::page>

