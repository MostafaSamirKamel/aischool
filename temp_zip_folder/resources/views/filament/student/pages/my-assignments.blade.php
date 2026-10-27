<x-filament-panels::page>
    <div class="student-theme-wrapper" dir="rtl">
        <link rel="stylesheet" href="{{ asset('css/student-theme-scoped.css') }}">
        
        <div>
            <!-- Header Text -->
            <div class="page-header">
                <h1>واجباتي والتسليمات</h1>
                <p>كل الواجبات، مواعيد التسليم والدرجات</p>
            </div>

            @php
                $allCount = count($assignments);
                $submittedCount = count(array_filter($assignments->toArray(), fn($a) => $a['status'] === 'submitted' || $a['status'] === 'graded'));
                $gradedCount = count(array_filter($assignments->toArray(), fn($a) => $a['status'] === 'graded'));
                $overdueCount = count(array_filter($assignments->toArray(), fn($a) => $a['status'] === 'overdue'));
            @endphp

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--text-color);">
                        <x-student-icon name="notebook-pen" size="24" />
                    </div>
                    <div><strong>{{ $allCount }}</strong><span>إجمالي الواجبات</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon mint">
                        <x-student-icon name="check-circle" size="24" />
                    </div>
                    <div><strong>{{ $submittedCount }}</strong><span>تم التسليم</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <x-student-icon name="medal" size="24" />
                    </div>
                    <div><strong>{{ $gradedCount }}</strong><span>تم التقييم</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon peach">
                        <x-student-icon name="clock-3" size="24" />
                    </div>
                    <div><strong>{{ $overdueCount }}</strong><span>متأخر</span></div>
                </div>
            </div>

            <div class="section-title">
                <h2>قائمة الواجبات</h2>
            </div>

            <div class="stack">
                @forelse ($assignments as $a)
                    <article class="task-row">
                        <div class="task-row-icon peach">
                            <x-student-icon name="notebook-pen" size="23" />
                        </div>
                        <div class="task-row-info">
                            <strong>{{ $a['title'] }}</strong>
                            <small>{{ $a['course'] }} • التسليم {{ $a['due'] }}</small>
                            <div class="task-badges">
                                <span class="status-pill status-{{ $a['status'] }}">
                                    @if($a['status'] == 'submitted') مسلّم
                                    @elseif($a['status'] == 'graded') تم التقييم
                                    @elseif($a['status'] == 'overdue') متأخر
                                    @else مطلوب
                                    @endif
                                </span>
                                <span>{{ $a['total'] }} درجة</span>
                                @if($a['score'] !== null)
                                    <span class="score-label" style="background: var(--mint-bg); color: var(--mint-dark); padding: 0.1rem 0.5rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700;">
                                        درجتك: {{ $a['score'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-sm {{ $a['status'] === 'pending' ? 'btn-primary' : 'btn-outline' }}" disabled>
                            {{ $a['status'] === 'pending' ? 'تسليم (قريباً)' : 'التفاصيل' }}
                        </button>
                    </article>
                @empty
                    <div class="empty-state">
                        <x-student-icon name="notebook-pen" size="40" color="var(--text-muted)" />
                        <h3>لا توجد واجبات في القسم ده</h3>
                        <p>الواجبات الجديدة هتظهر هنا لما المدرس ينشرها.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>

