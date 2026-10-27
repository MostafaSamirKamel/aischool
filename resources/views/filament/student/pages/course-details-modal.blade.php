@php
    $resolvedGroup = $group ?? $enrollmentCourse?->group;
    $course = $resolvedGroup?->course ?? $enrollmentCourse?->course;

    $days = [
        'saturday' => 'السبت',
        'sunday' => 'الأحد',
        'monday' => 'الاثنين',
        'tuesday' => 'الثلاثاء',
        'wednesday' => 'الأربعاء',
        'thursday' => 'الخميس',
        'friday' => 'الجمعة',
    ];

    $groupId = $resolvedGroup?->id;
    $completedSessions = $resolvedGroup?->attendanceSessions()?->count() ?? 0;
    $plannedSessions = (int) ($resolvedGroup?->total_sessions ?? 0);
    $totalSessions = $plannedSessions > 0 ? $plannedSessions : $completedSessions;

    $presentCount = 0;
    $absentCount = 0;
    if ($groupId && $student?->id) {
        $presentCount = \App\Models\AttendanceRecord::where('student_id', $student->id)
            ->whereHas('session', function ($q) use ($groupId) {
                $q->where('group_id', $groupId);
            })
            ->where('status', 'present')
            ->count();

        $absentCount = \App\Models\AttendanceRecord::where('student_id', $student->id)
            ->whereHas('session', function ($q) use ($groupId) {
                $q->where('group_id', $groupId);
            })
            ->where('status', 'absent')
            ->count();
    }

    $attendanceRate = $completedSessions > 0 ? round(($presentCount / $completedSessions) * 100) : 0;
    $attendanceSummary = $presentCount . '/' . $totalSessions;

    $schedules = [];
    if (!empty($resolvedGroup?->schedule)) {
        $parsed = is_string($resolvedGroup->schedule)
            ? json_decode($resolvedGroup->schedule, true)
            : $resolvedGroup->schedule;
        $schedules = is_array($parsed) ? $parsed : [];
    }

    $courseDescription = trim(strip_tags((string) ($course?->description ?? '')));
@endphp

<style>
    .scd-modal {
        direction: rtl;
        font-family: "Cairo", Tahoma, Arial, sans-serif;
        color: #0f172a;
    }

    .scd-box {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        background: #ffffff;
    }

    .scd-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .scd-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
    }

    .scd-title {
        margin: 0 0 10px;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }

    .scd-list {
        display: grid;
        gap: 8px;
        margin: 0;
    }

    .scd-label {
        margin: 0 0 2px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    .scd-value {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.5;
        word-break: break-word;
    }

    .scd-schedule {
        margin-top: 12px;
        padding: 14px;
        border-radius: 12px;
        border: 1px solid #E5EAF3;
        background: #F5F7FC;
    }

    .scd-schedule-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .scd-schedule-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 10px;
    }

    .scd-day {
        font-weight: 700;
        font-size: 13px;
        margin-bottom: 3px;
        color: #364FAC;
    }

    .scd-time {
        color: #334155;
        font-size: 13px;
    }

    .scd-stats {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 120px), 1fr));
        gap: 10px;
    }

    .scd-stat {
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }

    .scd-stat--green { background: linear-gradient(180deg, #F0FDF4, #EAF7E6); }
    .scd-stat--blue { background: linear-gradient(180deg, #F5F7FC, #EEF2FF); }
    .scd-stat--red { background: linear-gradient(180deg, #fef2f2, #fee2e2); }
    .scd-stat--purple { background: linear-gradient(180deg, #f8fafc, #f1f5f9); }

    .scd-stat-number {
        font-size: 24px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 6px;
    }

    .scd-stat-label {
        margin: 0;
        font-size: 12px;
        color: #334155;
        font-weight: 600;
    }

    .scd-inline-badge {
        margin-top: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 10px;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #1e3a8a;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.2px;
    }

    @media (max-width: 900px) {
        .scd-grid,
        .scd-schedule-grid,
        .scd-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="scd-modal">
    <div class="scd-box">
        <div class="scd-grid">
            <section class="scd-card">
                <h4 class="scd-title">{{ __('معلومات الكورس') }}</h4>
                <div class="scd-list">
                    <div>
                        <p class="scd-label">{{ __('اسم الكورس') }}</p>
                        <p class="scd-value">{{ $course?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="scd-label">{{ __('الوصف') }}</p>
                        <p class="scd-value">{{ $courseDescription !== '' ? $courseDescription : '-' }}</p>
                    </div>
                    <div>
                        <p class="scd-label">{{ __('عدد الساعات') }}</p>
                        <p class="scd-value">{{ $course?->duration_hours ?? '-' }} {{ __('ساعة') }}</p>
                    </div>
                </div>
            </section>

            <section class="scd-card">
                <h4 class="scd-title">{{ __('معلومات المجموعة') }}</h4>
                <div class="scd-list">
                    <div>
                        <p class="scd-label">{{ __('المجموعة') }}</p>
                        <p class="scd-value">{{ $resolvedGroup?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="scd-label">{{ __('المدرس') }}</p>
                        <p class="scd-value">{{ $resolvedGroup?->instructor?->user?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="scd-label">{{ __('تاريخ البداية') }}</p>
                        <p class="scd-value">{{ $resolvedGroup?->start_date ? $resolvedGroup->start_date->format('d/m/Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="scd-label">{{ __('تاريخ النهاية') }}</p>
                        <p class="scd-value">{{ $resolvedGroup?->end_date ? $resolvedGroup->end_date->format('d/m/Y') : '-' }}</p>
                    </div>
                </div>
            </section>
        </div>

        @if(!empty($schedules))
            <section class="scd-schedule">
                <h4 class="scd-title">{{ __('جدول المواعيد') }}</h4>
                <div class="scd-schedule-grid">
                    @foreach($schedules as $schedule)
                        <article class="scd-schedule-item">
                            <div class="scd-day">{{ $days[$schedule['day'] ?? ''] ?? ($schedule['day'] ?? '-') }}</div>
                            <div class="scd-time">
                                {{ $schedule['start_time'] ?? $schedule['time'] ?? '-' }}
                                @if(!empty($schedule['end_time']))
                                    - {{ $schedule['end_time'] }}
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="scd-stats">
            <article class="scd-stat scd-stat--green">
                <div class="scd-stat-number">{{ $totalSessions }}</div>
                <p class="scd-stat-label">{{ __('إجمالي الحصص') }}</p>
            </article>
            <article class="scd-stat scd-stat--blue">
                <div class="scd-stat-number">{{ $presentCount }}</div>
                <p class="scd-stat-label">{{ __('الحضور') }}</p>
            </article>
            <article class="scd-stat scd-stat--red">
                <div class="scd-stat-number">{{ $absentCount }}</div>
                <p class="scd-stat-label">{{ __('الغياب') }}</p>
            </article>
            <article class="scd-stat scd-stat--purple">
                <div class="scd-stat-number">{{ $attendanceRate }}%</div>
                <p class="scd-stat-label">{{ __('نسبة الحضور') }}</p>
                <span class="scd-inline-badge">{{ $attendanceSummary }}</span>
            </article>
        </section>
    </div>
