<x-filament-panels::page>
    <div class="student-theme-wrapper" dir="rtl">
        <link rel="stylesheet" href="{{ asset('css/student-theme-scoped.css') }}">
        
        <div>
            <!-- Header Text -->
            <div class="page-header">
                <h1>امتحاناتي ونتائجي</h1>
                <p>تابع الاختبارات والنتائج وابدأ الاختبارات المتاحة</p>
            </div>

            @php
                $totalExams = count($exams);
                $completedExams = count(array_filter($exams->toArray(), fn($e) => $e['status'] === 'completed'));
                $availableExams = count(array_filter($exams->toArray(), fn($e) => $e['status'] === 'available' || $e['status'] === 'upcoming'));
                
                $scoredExams = array_filter($exams->toArray(), fn($e) => $e['score'] !== null && $e['total'] > 0);
                $mean = count($scoredExams) ? array_reduce($scoredExams, fn($s, $e) => $s + (100 * $e['score'] / $e['total']), 0) / count($scoredExams) : null;
            @endphp

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <x-student-icon name="clipboard-check" size="24" />
                    </div>
                    <div><strong>{{ $totalExams }}</strong><span>إجمالي الامتحانات</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon mint">
                        <x-student-icon name="check-circle" size="24" />
                    </div>
                    <div><strong>{{ $completedExams }}</strong><span>امتحانات مكتملة</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <x-student-icon name="calendar-days" size="24" />
                    </div>
                    <div><strong>{{ $availableExams }}</strong><span>متاحة وقادمة</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon peach">
                        <x-student-icon name="medal" size="24" />
                    </div>
                    <div><strong>{{ $mean !== null ? round($mean) . '%' : '—' }}</strong><span>متوسط الدرجات</span></div>
                </div>
            </div>

            <div class="section-title">
                <h2>قائمة الامتحانات</h2>
            </div>

            <div class="stack">
                @forelse ($exams as $e)
                    <article class="task-row">
                        <div class="task-row-icon blue">
                            <x-student-icon name="clipboard-check" size="23" />
                        </div>
                        <div class="task-row-info">
                            <strong>{{ $e['title'] }}</strong>
                            <small>{{ $e['course'] }} • {{ $e['date'] }}</small>
                            <div class="task-badges">
                                <span class="status-pill status-{{ $e['status'] }}">
                                    @if($e['status'] == 'completed') مكتمل
                                    @elseif($e['status'] == 'upcoming') قادم
                                    @elseif($e['status'] == 'expired') منتهي
                                    @else متاح الآن
                                    @endif
                                </span>
                                <span><x-student-icon name="clock-3" size="13" /> {{ $e['duration'] }} دقيقة</span>
                                <span>{{ $e['total'] }} درجة</span>
                                @if($e['score'] !== null)
                                    <span class="score-label" style="background: var(--mint); color: var(--mint-ink); padding: 0.15rem 0.6rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700;">
                                        درجتك: {{ $e['score'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ $e['actionUrl'] }}" class="btn btn-sm {{ $e['actionClass'] }}" style="text-decoration: none; text-align: center;">
                            {{ $e['actionLabel'] }}
                        </a>
                    </article>
                @empty
                    <div class="empty">
                        <div class="empty-illustration">
                            <x-student-icon name="clipboard-check" size="34" />
                        </div>
                        <strong>لا توجد امتحانات في هذا القسم</strong>
                        <p>أي اختبار جديد ستجده هنا بمجرد توفره.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
