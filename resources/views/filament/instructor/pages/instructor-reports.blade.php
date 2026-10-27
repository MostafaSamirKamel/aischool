@php
    $stats = $this->getStats();
    $groupsPerformance = $this->getGroupsPerformance();
    $sessionsByMonth = $this->getSessionsByMonth();
    $examsStatistics = $this->getExamsStatistics();
    $assignmentsStatistics = $this->getAssignmentsStatistics();
@endphp

<x-filament-panels::page>
    @include('filament.student.layouts.student-page-styles')

    <style>
        .page-header {
            background: linear-gradient(135deg, #364FAC 0%, #293C85 100%) !important;
            box-shadow: 0 8px 30px rgba(54, 79, 172, 0.2) !important;
        }
    </style>

    {{-- Page Header --}}
    <div class="page-header mb-4 position-relative">
        <div class="d-flex align-items-center position-relative" style="z-index: 1;">
            <div class="header-icon bg-white bg-opacity-20 text-white me-3">
                <i class="bi bi-bar-chart-fill"></i>
            </div>
            <div>
                <h2 class="mb-1 fw-bold" style="font-size: 1.75rem;">تقاريري التدريسية</h2>
                <p class="mb-0 opacity-90" style="font-size: 1rem;">متابعة شاملة لأداء المجموعات والطلاب</p>
            </div>
        </div>
    </div>

    {{-- إحصائيات سريعة --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label">المجموعات النشطة</div>
                            <div class="stat-value text-primary">{{ $stats['activeGroups'] }}<span
                                    class="fs-5 text-muted">/{{ $stats['totalGroups'] }}</span></div>
                            <div class="progress progress-custom mt-2">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ $stats['totalGroups'] > 0 ? ($stats['activeGroups'] / $stats['totalGroups']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-collection"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label">إجمالي الطلاب</div>
                            <div class="stat-value text-success">{{ $stats['totalStudents'] }}</div>
                            <small class="text-muted"><i class="bi bi-people-fill"></i> في جميع المجموعات</small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label">الجلسات (30 يوم)</div>
                            <div class="stat-value text-info">{{ $stats['totalSessions'] }}</div>
                            <small class="text-muted"><i class="bi bi-calendar-event"></i> آخر شهر</small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-calendar3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label">نسبة الحضور</div>
                            <div class="stat-value text-warning">{{ $stats['attendanceRate'] }}%</div>
                            <div class="progress progress-custom mt-2">
                                <div class="progress-bar bg-warning" role="progressbar"
                                    style="width: {{ $stats['attendanceRate'] }}%"></div>
                            </div>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- الامتحانات والواجبات --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card section-card">
                <div class="card-body p-4 text-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger mx-auto mb-3">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h5 class="card-title mb-2">الامتحانات (30 يوم)</h5>
                    <div class="display-4 fw-bold text-danger">{{ $stats['totalExams'] }}</div>
                    <p class="text-muted mt-2 mb-0"><i class="bi bi-clock-history"></i> تم إجراؤها خلال الشهر الماضي</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card section-card">
                <div class="card-body p-4 text-center">
                    <div class="stat-icon bg-orange bg-opacity-10 text-orange mx-auto mb-3" style="color: #fd7e14;">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h5 class="card-title mb-2">الواجبات (30 يوم)</h5>
                    <div class="display-4 fw-bold" style="color: #fd7e14;">{{ $stats['totalAssignments'] }}</div>
                    <p class="text-muted mt-2 mb-0"><i class="bi bi-clock-history"></i> تم تكليفها خلال الشهر الماضي</p>
                </div>
            </div>
        </div>
    </div>

    {{-- رسم بياني: الجلسات حسب الشهر --}}
    <div class="card section-card mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="card-title mb-0"><i class="bi bi-bar-chart-fill text-primary"></i> عدد الجلسات (آخر 6 أشهر)</h5>
        </div>
        <div class="card-body">
            <div wire:ignore>
                <canvas id="sessionsChart" height="80"></canvas>
            </div>
        </div>
    </div>

    {{-- أداء المجموعات --}}
    <div class="card section-card mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="card-title mb-0"><i class="bi bi-graph-up text-success"></i> أداء المجموعات</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3 text-end">اسم المجموعة</th>
                            <th class="px-4 py-3 text-center">عدد الطلاب</th>
                            <th class="px-4 py-3 text-center">متوسط الدرجات</th>
                            <th class="px-4 py-3 text-center">نسبة الحضور</th>
                            <th class="px-4 py-3 text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupsPerformance as $group)
                            <tr>
                                <td class="px-4 py-3 fw-semibold text-end">
                                    <i class="bi bi-folder2-open text-primary"></i> {{ $group['name'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge bg-secondary badge-custom">
                                        <i class="bi bi-people-fill"></i> {{ $group['students_count'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="badge badge-custom
                                        @if ($group['avg_score'] >= 85) bg-success
                                        @elseif($group['avg_score'] >= 70) bg-primary
                                        @elseif($group['avg_score'] >= 50) bg-warning
                                        @else bg-danger @endif">
                                        <i class="bi bi-trophy-fill"></i> {{ $group['avg_score'] }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="badge badge-custom
                                        @if ($group['attendance_rate'] >= 90) bg-success
                                        @elseif($group['attendance_rate'] >= 75) bg-primary
                                        @elseif($group['attendance_rate'] >= 60) bg-warning
                                        @else bg-danger @endif">
                                        <i class="bi bi-check2-circle"></i> {{ $group['attendance_rate'] }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="badge badge-custom
                                        @if ($group['status'] === 'active') bg-success
                                        @elseif($group['status'] === 'completed') bg-info
                                        @else bg-secondary @endif">
                                        @if ($group['status'] === 'active')
                                            <i class="bi bi-play-circle-fill"></i> نشطة
                                        @elseif($group['status'] === 'completed')
                                            <i class="bi bi-check-circle-fill"></i> مكتملة
                                        @else
                                            <i class="bi bi-dash-circle-fill"></i> أخرى
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا توجد مجموعات متاحة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- إحصائيات الامتحانات --}}
    @if (count($examsStatistics) > 0)
        <div class="card section-card mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="card-title mb-0"><i class="bi bi-file-earmark-text-fill text-danger"></i> إحصائيات
                    الامتحانات (آخر 3 أشهر)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3 text-end">عنوان الامتحان</th>
                                <th class="px-4 py-3 text-center">التاريخ</th>
                                <th class="px-4 py-3 text-center">عدد المحاولات</th>
                                <th class="px-4 py-3 text-center">متوسط الدرجة</th>
                                <th class="px-4 py-3 text-center">نسبة النجاح</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($examsStatistics as $exam)
                                <tr>
                                    <td class="px-4 py-3 fw-semibold text-end">
                                        <i class="bi bi-file-text text-danger"></i> {{ $exam['title'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <small class="text-muted"><i class="bi bi-calendar3"></i>
                                            {{ $exam['date'] }}</small>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge bg-info badge-custom">
                                            <i class="bi bi-pencil-square"></i> {{ $exam['attempts_count'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <strong>{{ $exam['avg_score'] }}</strong><span
                                            class="text-muted">/{{ $exam['total_marks'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="badge badge-custom
                                            @if ($exam['pass_rate'] >= 75) bg-success
                                            @elseif($exam['pass_rate'] >= 50) bg-warning
                                            @else bg-danger @endif">
                                            <i class="bi bi-graph-up-arrow"></i> {{ $exam['pass_rate'] }}%
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

    {{-- إحصائيات الواجبات --}}
    @if (count($assignmentsStatistics) > 0)
        <div class="card section-card mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="card-title mb-0" style="color: #fd7e14;"><i class="bi bi-clipboard-check-fill"></i>
                    إحصائيات الواجبات (آخر 3 أشهر)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3 text-end">عنوان الواجب</th>
                                <th class="px-4 py-3 text-center">الموعد النهائي</th>
                                <th class="px-4 py-3 text-center">التسليمات</th>
                                <th class="px-4 py-3 text-center">المُصحح</th>
                                <th class="px-4 py-3 text-center">متوسط الدرجة</th>
                                <th class="px-4 py-3 text-center">نسبة التسليم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignmentsStatistics as $assignment)
                                <tr>
                                    <td class="px-4 py-3 fw-semibold text-end">
                                        <i class="bi bi-clipboard-data" style="color: #fd7e14;"></i>
                                        {{ $assignment['title'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <small class="text-muted"><i class="bi bi-alarm"></i>
                                            {{ $assignment['due_date'] }}</small>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge bg-primary badge-custom">
                                            <i class="bi bi-upload"></i> {{ $assignment['submissions_count'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge bg-secondary badge-custom">
                                            <i class="bi bi-check2-square"></i> {{ $assignment['graded_count'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <strong>{{ $assignment['avg_score'] }}</strong><span
                                            class="text-muted">/{{ $assignment['max_score'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="badge badge-custom
                                            @if ($assignment['submission_rate'] >= 80) bg-success
                                            @elseif($assignment['submission_rate'] >= 60) bg-warning
                                            @else bg-danger @endif">
                                            <i class="bi bi-pie-chart-fill"></i> {{ $assignment['submission_rate'] }}%
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Sessions Chart with enhanced styling
                const sessionsCtx = document.getElementById('sessionsChart');
                if (sessionsCtx) {
                    new Chart(sessionsCtx, {
                        type: 'bar',
                        data: {
                            labels: @js(array_column($sessionsByMonth, 'month')),
                            datasets: [{
                                label: 'عدد الجلسات',
                                data: @js(array_column($sessionsByMonth, 'count')),
                                backgroundColor: [
                                    'rgba(99, 102, 241, 0.8)',
                                    'rgba(59, 130, 246, 0.8)',
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(251, 146, 60, 0.8)',
                                    'rgba(239, 68, 68, 0.8)',
                                    'rgba(168, 85, 247, 0.8)'
                                ],
                                borderColor: [
                                    'rgb(99, 102, 241)',
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(251, 146, 60)',
                                    'rgb(239, 68, 68)',
                                    'rgb(168, 85, 247)'
                                ],
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 14,
                                            family: 'Cairo, sans-serif'
                                        },
                                        padding: 15,
                                        usePointStyle: true,
                                        pointStyle: 'rectRounded'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    cornerRadius: 8,
                                    titleFont: {
                                        size: 14,
                                        family: 'Cairo, sans-serif'
                                    },
                                    bodyFont: {
                                        size: 13,
                                        family: 'Cairo, sans-serif'
                                    },
                                    displayColors: true,
                                    callbacks: {
                                        label: function(context) {
                                            return ' ' + context.dataset.label + ': ' + context.parsed.y +
                                                ' جلسة';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 12,
                                            family: 'Cairo, sans-serif'
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)',
                                        drawBorder: false
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            size: 12,
                                            family: 'Cairo, sans-serif'
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            animation: {
                                duration: 1000,
                                easing: 'easeInOutQuart'
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
