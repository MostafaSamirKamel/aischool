@php
$student = auth()->user()->student;
$enrollments = \App\Models\Enrollment::where('student_id', optional($student)->id)
    ->where('status', 'active')
    ->with(['enrollmentCourses.course', 'enrollmentCourses.group', 'track'])
    ->get();
@endphp

<x-filament-panels::page>
    <style>
        .enrollments-page { display: flex; flex-direction: column; gap: 20px; }
        .card { background: #FFFFFF; border: 1px solid #E5EAF3; border-radius: 16px; padding: 20px; box-shadow: 0 4px 16px -2px rgba(24, 36, 61, 0.05); }
        .title { font-size: 20px; font-weight: 850; color: #18243D; }
        .subtitle { margin-top: 6px; font-size: 13px; color: #64748B; font-weight: 600; }
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; min-width: 0; }
        .stat-label { font-size: 12px; color: #64748B; font-weight: 700; }
        .stat-value { margin-top: 6px; font-size: 22px; font-weight: 850; color: #18243D; }
        .stack { display: flex; flex-direction: column; gap: 16px; }
        .row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
        .meta { margin-top: 4px; font-size: 13px; color: #64748B; }
        .pill { display: inline-flex; align-items: center; border: 1px solid #B4E5AA; border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 750; color: #1F7A18; background: #EAF7E6; }
        .divider { height: 1px; background: #E5EAF3; margin: 16px 0; }
        .pay-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; min-width: 0; }
        .pay-card { background: #FAFCFF; border: 1px solid #E5EAF3; border-radius: 12px; padding: 12px; min-width: 0; overflow: hidden; }
        .pay-label { font-size: 12px; color: #64748B; font-weight: 700; white-space: nowrap; }
        .pay-value { margin-top: 6px; font-size: 18px; font-weight: 850; color: #18243D; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .section-title { font-size: 15px; font-weight: 850; color: #18243D; border-inline-start: 4px solid #58BA47; padding-inline-start: 10px; }
        .course-card { border: 1px solid #E5EAF3; border-radius: 14px; padding: 14px; background: #FFFFFF; }
        .course-grid { margin-top: 10px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; font-size: 13px; }
        .course-label { color: #64748B; font-weight: 600; }
        .course-value { font-weight: 750; color: #18243D; display: block; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #EEF2FF; color: #364FAC; font-weight: 750; }
        .progress { margin-top: 12px; border: 1px solid #E5EAF3; border-radius: 10px; padding: 10px; background: #FAFCFF; }
        .progress-row { display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748B; font-weight: 700; }
        .bar { margin-top: 8px; height: 8px; background: #E5EAF3; border-radius: 999px; overflow: hidden; }
        .bar-fill { height: 8px; background: linear-gradient(90deg, #364FAC, #58BA47); border-radius: 999px; }
        .empty { text-align: center; padding: 28px; background: #FAFCFF; border: 1px dashed #E5EAF3; border-radius: 14px; color: #64748B; }
        .btn-muted { white-space: nowrap; border-radius: 8px; background: #94A3B8; color: #FFFFFF; padding: 8px 12px; font-size: 12px; font-weight: 750; opacity: 0.85; cursor: not-allowed; }
        @media (max-width: 768px) {
            .stat-grid, .pay-grid { grid-template-columns: repeat(2, 1fr); }
            .row { flex-direction: column; }
        }
    </style>

    <div class="enrollments-page">
        <div class="card">
            <div class="title">تسجيلاتي والكورسات المختارة</div>
            <div class="subtitle">ملخص سريع لأحدث التسجيلات والمبالغ المرتبطة بها.</div>
        </div>

        <div class="stat-grid">
            <div class="card">
                <div class="stat-label">التسجيلات النشطة</div>
                <div class="stat-value">{{ $enrollments->count() }}</div>
            </div>
            <div class="card">
                <div class="stat-label">إجمالي الكورسات</div>
                <div class="stat-value">{{ $enrollments->sum(fn($e) => $e->enrollmentCourses->count()) }}</div>
            </div>
            <div class="card">
                <div class="stat-label">كورسات مكتملة</div>
                <div class="stat-value">{{ $enrollments->sum(fn($e) => $e->enrollmentCourses->where('status', 'completed')->count()) }}</div>
            </div>
            <div class="card">
                <div class="stat-label">المجموع الكلي</div>
                <div class="stat-value">{{ number_format((float) $enrollments->sum('total_amount'), 2) }} ج.م</div>
            </div>
        </div>

        <div class="stack">
            @forelse($enrollments as $enrollment)
                @php
                    $enrollmentNetAmount = max(0, (float) $enrollment->total_amount - (float) $enrollment->discount);
                    $enrollmentPaidAmount = $enrollment->getEffectivePaidAmount();
                    $coursePaymentRatio = $enrollmentNetAmount > 0
                        ? min(1, $enrollmentPaidAmount / $enrollmentNetAmount)
                        : 1;
                @endphp
                <div class="card">
                    <div class="row">
                        <div>
                            <div class="title">تسجيل #{{ $enrollment->enrollment_code }}</div>
                            <div class="meta">{{ optional($enrollment->enrollment_date)->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="pill">{{ $enrollment->status === 'active' ? 'نشط' : 'مكتمل' }}</div>
                    </div>

                    <div class="divider"></div>

                    <div class="pay-grid">
                        <div class="pay-card">
                            <div class="pay-label">المبلغ الإجمالي</div>
                            <div class="pay-value">{{ number_format((float) $enrollment->total_amount, 2) }} ج.م</div>
                        </div>
                        <div class="pay-card">
                            <div class="pay-label">المبلغ المدفوع</div>
                            <div class="pay-value">{{ number_format((float) $enrollmentPaidAmount, 2) }} ج.م</div>
                        </div>
                        @if($enrollment->discount > 0)
                        <div class="pay-card" style="background: #fef3c7; border-color: #fcd34d;">
                            <div class="pay-label" style="color: #92400e;">الخصم</div>
                            <div class="pay-value" style="color: #92400e;">{{ number_format((float) $enrollment->discount, 2) }} ج.م</div>
                        </div>
                        @endif
                        <div class="pay-card">
                            <div class="pay-label">المتبقي</div>
                            <div class="pay-value">{{ number_format((float) $enrollment->getRemainingBalance(), 2) }} ج.م</div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    @if($enrollment->enrollmentCourses->isNotEmpty())
                        <div class="stack">
                            <div class="section-title">الكورسات المختارة ({{ $enrollment->enrollmentCourses->count() }})</div>
                            <div class="stack">
                                @foreach($enrollment->enrollmentCourses as $enrollmentCourse)
                                    @php
                                        $coursePrice = (float) ($enrollmentCourse->course_price ?? 0);
                                        $coursePaidAmount = round($coursePrice * $coursePaymentRatio, 2);
                                        $courseRemainingAmount = max(0, round($coursePrice - $coursePaidAmount, 2));
                                        $coursePaymentStatus = $courseRemainingAmount <= 0
                                            ? 'مدفوع'
                                            : ($coursePaidAmount > 0 ? 'مدفوع جزئي' : 'غير مدفوع');
                                    @endphp
                                    <div class="course-card">
                                        <div class="row">
                                            <div style="flex: 1;">
                                                <div class="title" style="font-size: 16px;">{{ $enrollmentCourse->course->name }}</div>

                                                @if($enrollmentCourse->group)
                                                    <div class="course-grid">
                                                        <div>
                                                            <span class="course-label">المعلم</span>
                                                            <span class="course-value">{{ optional(optional($enrollmentCourse->group->instructor)->user)->name ?? 'غير محدد' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="course-label">المجموعة</span>
                                                            <span class="badge">{{ $enrollmentCourse->group->name }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="course-label">السعر</span>
                                                            <span class="course-value">{{ number_format((float) $enrollmentCourse->course_price, 2) }} ج.م</span>
                                                        </div>
                                                        <div>
                                                            <span class="course-label">حالة السداد</span>
                                                            <span class="course-value">{{ $coursePaymentStatus }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="course-label">مدفوع للكورس</span>
                                                            <span class="course-value">{{ number_format($coursePaidAmount, 2) }} ج.م</span>
                                                        </div>
                                                        <div>
                                                            <span class="course-label">متبقي على الكورس</span>
                                                            <span class="course-value">{{ number_format($courseRemainingAmount, 2) }} ج.م</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="meta" style="color: #b45309; font-weight: 600;">لم تختر مجموعة بعد</div>
                                                @endif

                                                <div class="progress">
                                                    <div class="progress-row">
                                                        <span>السيشن: <span style="color: #111827;">{{ $enrollmentCourse->current_session }}</span> من {{ $enrollmentCourse->total_sessions }}</span>
                                                        <span class="badge">{{ $enrollmentCourse->getProgressPercentage() }}%</span>
                                                    </div>
                                                    <div class="bar">
                                                        <div class="bar-fill" style="width: {{ $enrollmentCourse->getProgressPercentage() }}%"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                                @if($enrollmentCourse->isInactive())
                                                    <livewire:student.course-status-button 
                                                        :enrollmentCourseId="$enrollmentCourse->id"
                                                        :enrollmentId="$enrollment->id" />
                                                @elseif($enrollmentCourse->isActive())
                                                    <livewire:student.course-status-button 
                                                        :enrollmentCourseId="$enrollmentCourse->id"
                                                        :enrollmentId="$enrollment->id" />
                                                @else
                                                    <button disabled class="btn-muted">مكتمل</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="empty">
                            <div class="title" style="font-size: 16px;">لم يتم اختيار أي كورسات لهذا التسجيل حتى الآن</div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty">
                    <div class="title" style="font-size: 16px;">لا توجد تسجيلات نشطة</div>
                    <div class="subtitle">يمكنك البدء بالتسجيل من خلال الاتصال بالإدارة</div>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
