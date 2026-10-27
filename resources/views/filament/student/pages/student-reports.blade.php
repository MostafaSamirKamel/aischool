<x-filament-panels::page>
    @include('filament.student.layouts.student-page-styles')
    @include('filament.shared.report-theme')

    <div class="report-shell">

    {{-- Page Header --}}
    <div class="page-header mb-4 position-relative" style="background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);">
        <div class="d-flex align-items-center justify-content-between position-relative" style="z-index: 1; gap: 1rem; flex-wrap: wrap;">
            <div class="d-flex align-items-center" style="gap: 1.25rem;">
                <div class="header-icon bg-white bg-opacity-20 text-white">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <h2 class="mb-1 fw-bold" style="font-size: 1.75rem;">تقريري الأكاديمي</h2>
                    <p class="mb-0 opacity-90" style="font-size: 1rem;">متابعة شاملة لأدائك الدراسي والحضور</p>
                </div>
            </div>
            <div class="d-flex gap-2 ms-auto align-items-center" style="gap: 0.75rem;">
                <button type="button" wire:click="exportPortfolio('csv')" class="report-btn">
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    تصدير Excel
                </button>
                <button type="button" wire:click="exportPortfolio('pdf')" class="report-btn report-btn-solid">
                    <i class="bi bi-file-earmark-pdf me-1"></i>
                    تصدير PDF
                </button>
            </div>
        </div>
    </div>

    @php
        $stats = $this->getStats();
        $attendanceByMonth = $this->getAttendanceByMonth();
        $examScores = $this->getExamScores();
        $assignmentsProgress = $this->getAssignmentsProgress();
        $performanceByGroup = $this->getPerformanceByGroup();
        $scoresTrend = $this->getScoresTrend();
    @endphp

    @if(!auth()->user()->student)
        <div class="alert alert-info border-0 shadow-sm mb-4" style="background: #eff6ff; border-inline-start: 4px solid #364FAC; border-radius: 12px; padding: 1rem 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i class="bi bi-info-circle-fill" style="color: #364FAC; font-size: 1.5rem;"></i>
                <div>
                    <h6 style="font-weight: 700; margin-bottom: 0.25rem; color: #1e293b;">ملاحظة الحساب الأكاديمي</h6>
                    <p style="margin-bottom: 0; font-size: 0.95rem; color: #64748b;">هذا الحساب لم يتم ربطه بملف طالب مكتمل أو مجموعات دراسية بعد. ستظهر كافة التقارير والإحصائيات والرسوم البيانية فور التحاقك بالمجموعات.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Statistics Cards Row --}}
    <div class="row g-4 mb-4">
        {{-- Average Score Card --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card card-primary h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-3">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <p class="stat-value">{{ $stats['avgScore'] ?? 0 }}%</p>
                        <p class="stat-label mt-2">متوسط الدرجات</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Rate Card --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card card-success h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success mb-3">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <p class="stat-value">{{ $stats['attendanceRate'] ?? 0 }}%</p>
                        <p class="stat-label mt-2">نسبة الحضور</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Completed Exams Card --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card card-purple h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-purple bg-opacity-10 text-purple mb-3" style="color: #8b5cf6;">
                            <i class="bi bi-clipboard-check-fill"></i>
                        </div>
                        <p class="stat-value">{{ $stats['completedExams'] ?? 0 }}</p>
                        <p class="stat-label mt-2">الامتحانات المكتملة</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submitted Assignments Card --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card card-orange h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-orange bg-opacity-10 text-orange mb-3" style="color: #f97316;">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <p class="stat-value">{{ $stats['submittedAssignments'] ?? 0 }}</p>
                        <p class="stat-label mt-2">الواجبات المسلمة</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Absent and Late Days --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card stat-card card-danger h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-value">{{ $stats['absentDays'] ?? 0 }}</p>
                            <p class="stat-label mt-2">أيام الغياب (30 يوم)</p>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card stat-card card-warning h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-value">{{ $stats['lateDays'] ?? 0 }}</p>
                            <p class="stat-label mt-2">أيام التأخير (30 يوم)</p>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-graph-up me-2 text-purple"></i>
                        تطور متوسط الدرجات (آخر 6 أشهر)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div wire:ignore>
                        <canvas id="scoresChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-calendar-week me-2 text-success"></i>
                        إحصائيات الحضور (آخر 6 أشهر)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div wire:ignore>
                        <canvas id="attendanceChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance by Group --}}
    @if(count($performanceByGroup) > 0)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="card-header bg-white border-0 p-4">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-people-fill me-2 text-primary"></i>
                    أدائي في كل مجموعة
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-end fw-bold">اسم المجموعة</th>
                                <th class="text-end fw-bold">متوسط الدرجات</th>
                                <th class="text-end fw-bold">نسبة الحضور</th>
                                <th class="text-end fw-bold">عدد الامتحانات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($performanceByGroup as $group)
                                <tr>
                                    <td class="text-end fw-bold">{{ $group['group_name'] }}</td>
                                    <td class="text-end">
                                        <span class="badge px-3 py-2
                                            @if($group['avg_score'] >= 85) bg-success
                                            @elseif($group['avg_score'] >= 70) bg-primary
                                            @elseif($group['avg_score'] >= 50) bg-warning
                                            @else bg-danger
                                            @endif">
                                            <i class="bi bi-trophy-fill me-1"></i>
                                            {{ $group['avg_score'] }}%
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge px-3 py-2
                                            @if($group['attendance_rate'] >= 90) bg-success
                                            @elseif($group['attendance_rate'] >= 75) bg-primary
                                            @elseif($group['attendance_rate'] >= 60) bg-warning
                                            @else bg-danger
                                            @endif">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            {{ $group['attendance_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info px-3 py-2">
                                            <i class="bi bi-file-earmark-text me-1"></i>
                                            {{ $group['exams_count'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Exam Scores --}}
    @if(count($examScores) > 0)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="card-header bg-white border-0 p-4">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-clipboard2-check me-2 text-purple"></i>
                    درجاتي في الامتحانات (آخر 20 امتحان)
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-end fw-bold">الامتحان</th>
                                <th class="text-end fw-bold">التاريخ</th>
                                <th class="text-end fw-bold">الدرجة</th>
                                <th class="text-end fw-bold">النسبة المئوية</th>
                                <th class="text-end fw-bold">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($examScores as $exam)
                            <tr>
                                <td class="text-end fw-medium">{{ $exam['exam_title'] }}</td>
                                <td class="text-end text-muted">{{ $exam['date'] }}</td>
                                <td class="text-end">
                                    <span class="badge bg-info px-3 py-2">
                                        {{ $exam['score'] }}/{{ $exam['total_marks'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="badge px-3 py-2
                                        @if($exam['percentage'] >= 85) bg-success
                                        @elseif($exam['percentage'] >= 70) bg-primary
                                        @elseif($exam['percentage'] >= 50) bg-warning
                                        @else bg-danger
                                        @endif">
                                        {{ $exam['percentage'] }}%
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($exam['passed'])
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            ناجح
                                        </span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="bi bi-x-circle-fill me-1"></i>
                                            راسب
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Assignment Progress --}}
    @if(count($assignmentsProgress) > 0)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="card-header bg-white border-0 p-4">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-list-check me-2 text-success"></i>
                    تقدم الواجبات (آخر 20 واجب)
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-end fw-bold">الواجب</th>
                                <th class="text-end fw-bold">تاريخ الاستحقاق</th>
                                <th class="text-end fw-bold">الحالة</th>
                                <th class="text-end fw-bold">الدرجة</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($assignmentsProgress as $assignment)
                            <tr>
                                <td class="text-end fw-medium">{{ $assignment['assignment_title'] }}</td>
                                <td class="text-end text-muted">{{ $assignment['submitted_at'] }}</td>
                                <td class="text-end">
                                    @if($assignment['status'] === 'submitted')
                                        <span class="badge bg-warning px-3 py-2">
                                            <i class="bi bi-clock-fill me-1"></i>
                                            تم التسليم
                                        </span>
                                    @elseif($assignment['status'] === 'graded')
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            تم التصحيح
                                        </span>
                                    @elseif($assignment['status'] === 'late')
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            متأخر
                                        </span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2">
                                            {{ $assignment['status'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($assignment['graded'])
                                        <span class="badge bg-info px-3 py-2">
                                            {{ $assignment['score'] }}/{{ $assignment['max_score'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">لم يُصحح</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scores Trend Chart
            const scoresCtx = document.getElementById('scoresChart');
            if (scoresCtx) {
                new Chart(scoresCtx, {
                    type: 'line',
                    data: {
                        labels: @js(array_column($scoresTrend, 'month')),
                        datasets: [{
                            label: 'متوسط الدرجات',
                            data: @js(array_column($scoresTrend, 'avg_score')),
                            backgroundColor: 'rgba(99, 102, 241, 0.2)',
                            borderColor: 'rgb(99, 102, 241)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }

            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart');
            if (attendanceCtx) {
                new Chart(attendanceCtx, {
                    type: 'bar',
                    data: {
                        labels: @js(array_column($attendanceByMonth, 'month')),
                        datasets: [
                            {
                                label: 'حاضر',
                                data: @js(array_column($attendanceByMonth, 'present')),
                                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                borderColor: 'rgb(16, 185, 129)',
                                borderWidth: 1
                            },
                            {
                                label: 'غائب',
                                data: @js(array_column($attendanceByMonth, 'absent')),
                                backgroundColor: 'rgba(239, 68, 68, 0.7)',
                                borderColor: 'rgb(239, 68, 68)',
                                borderWidth: 1
                            },
                            {
                                label: 'متأخر',
                                data: @js(array_column($attendanceByMonth, 'late')),
                                backgroundColor: 'rgba(245, 158, 11, 0.7)',
                                borderColor: 'rgb(245, 158, 11)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush

    </div>
</x-filament-panels::page>
