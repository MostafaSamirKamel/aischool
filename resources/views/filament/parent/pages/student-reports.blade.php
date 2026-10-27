<x-filament-panels::page>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @include('filament.student.layouts.student-page-styles')
    @include('filament.shared.report-theme')

    <style>
        .reports-grid-2 {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 1.25rem !important;
            margin-bottom: 1.5rem !important;
        }
        @media (max-width: 991px) {
            .reports-grid-2 {
                grid-template-columns: 1fr !important;
            }
        }
        .stat-card-horizontal .card-body {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            text-align: right !important;
            width: 100% !important;
            padding: 1.5rem !important;
        }
        .stat-card-horizontal .stat-icon {
            margin-bottom: 0 !important;
            flex-shrink: 0;
        }
        .chart-container-box {
            position: relative;
            height: 320px;
            width: 100%;
        }
    </style>

    <div class="report-shell">
        @php
            $selectedStudent = $this->getSelectedStudent();
            $stats = $this->getStats();
            $attendanceByMonth = $this->getAttendanceByMonth();
            $examScores = $this->getExamScores();
            $assignmentsProgress = $this->getAssignmentsProgress();
            $performanceByGroup = $this->getPerformanceByGroup();
            $scoresTrend = $this->getScoresTrend();
        @endphp

        {{-- Page Header --}}
        <div class="page-header mb-4 position-relative" style="background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);">
            <div class="d-flex align-items-center justify-content-between position-relative" style="z-index: 1; gap: 1rem; flex-wrap: wrap;">
                <div class="d-flex align-items-center" style="gap: 1.25rem;">
                    <div class="header-icon bg-white bg-opacity-20 text-white">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <h2 class="mb-1 fw-bold text-white" style="font-size: 1.75rem;">تقرير الطالب الأكاديمي</h2>
                        <p class="mb-0 text-white opacity-90" style="font-size: 1rem;">
                            متابعة شاملة لأداء الطالب الدراسي وسجل الحضور
                            @if($selectedStudent)
                                للطالب: <span class="badge bg-white bg-opacity-25 text-white ms-1" style="font-size: 0.95rem; font-weight: 700;">{{ $selectedStudent->user->name ?? $selectedStudent->name }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if(!$selectedStudent)
            <div class="card border-0 shadow-sm p-5 text-center my-4" style="border-radius: 20px; background: #fff;">
                <div class="py-4">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3" style="width: 64px; height: 64px; font-size: 28px;">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <h4 class="fw-bold mb-2">لم يتم تحديد أي طالب</h4>
                    <p class="text-muted mb-0">يرجى اختيار طالب من قائمة الأبناء لعرض التقرير الأكاديمي وسجل الحضور والدرجات.</p>
                </div>
            </div>
        @else
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
                                <p class="stat-value">{{ $stats['avgScore'] }}%</p>
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
                                <p class="stat-value">{{ $stats['attendanceRate'] }}%</p>
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
                                <p class="stat-value">{{ $stats['completedExams'] }}</p>
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
                                <p class="stat-value">{{ $stats['submittedAssignments'] }}</p>
                                <p class="stat-label mt-2">الواجبات المسلمة</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Absent and Late Days (2-Column Grid) --}}
            <div class="reports-grid-2">
                <div>
                    <div class="card stat-card stat-card-horizontal card-danger h-100">
                        <div class="card-body">
                            <div>
                                <p class="stat-value">{{ $stats['absentDays'] }}</p>
                                <p class="stat-label mt-2">أيام الغياب (آخر 30 يوم)</p>
                            </div>
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="card stat-card stat-card-horizontal card-warning h-100">
                        <div class="card-body">
                            <div>
                                <p class="stat-value">{{ $stats['lateDays'] }}</p>
                                <p class="stat-label mt-2">أيام التأخير (آخر 30 يوم)</p>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts Section (2-Column Grid) --}}
            <div class="reports-grid-2">
                <div>
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; overflow: hidden; background: #fff;">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-graph-up me-2 text-primary"></i>
                                تطور متوسط درجات الطالب (آخر 6 أشهر)
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="chart-container-box">
                                <canvas id="scoresChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; overflow: hidden; background: #fff;">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-calendar-week me-2 text-success"></i>
                                إحصائيات حضور الطالب (آخر 6 أشهر)
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="chart-container-box">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Performance by Group --}}
            @if(count($performanceByGroup) > 0)
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden; background: #fff;">
                    <div class="card-header bg-white border-0 p-4">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-people-fill me-2 text-primary"></i>
                            أداء الطالب في المجموعات المسجل بها
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-end fw-bold">المادة والمجموعة</th>
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
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden; background: #fff;">
                    <div class="card-header bg-white border-0 p-4">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-clipboard2-check me-2 text-purple" style="color: #8b5cf6;"></i>
                            درجات الطالب في الامتحانات (آخر 20 امتحان)
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
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
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden; background: #fff;">
                    <div class="card-header bg-white border-0 p-4">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-list-check me-2 text-success"></i>
                            تقدم واجبات الطالب (آخر 20 واجب)
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-end fw-bold">الواجب</th>
                                        <th class="text-end fw-bold">تاريخ التسليم</th>
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
                                        <td class="text-end text-muted">{{ $assignment['due_date'] ?? '-' }}</td>
                                        <td class="text-end">
                                            @if(($assignment['status'] ?? '') === 'submitted')
                                                <span class="badge bg-warning px-3 py-2">
                                                    <i class="bi bi-clock-fill me-1"></i>
                                                    تم التسليم
                                                </span>
                                            @elseif(($assignment['status'] ?? '') === 'graded')
                                                <span class="badge bg-success px-3 py-2">
                                                    <i class="bi bi-check-circle-fill me-1"></i>
                                                    تم التصحيح
                                                </span>
                                            @elseif(($assignment['status'] ?? '') === 'late')
                                                <span class="badge bg-danger px-3 py-2">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                    متأخر
                                                </span>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2">
                                                    {{ $assignment['status'] ?? 'مسجل' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(!empty($assignment['graded']))
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
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            function renderStudentCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(renderStudentCharts, 100);
                    return;
                }

                // Scores Trend Chart
                const scoresCanvas = document.getElementById('scoresChart');
                if (scoresCanvas) {
                    const existingScoresChart = Chart.getChart(scoresCanvas);
                    if (existingScoresChart) {
                        existingScoresChart.destroy();
                    }

                    new Chart(scoresCanvas, {
                        type: 'line',
                        data: {
                            labels: @js(array_column($scoresTrend, 'month')),
                            datasets: [{
                                label: 'متوسط الدرجات',
                                data: @js(array_column($scoresTrend, 'avg_score')),
                                backgroundColor: 'rgba(54, 79, 172, 0.15)',
                                borderColor: '#364FAC',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#364FAC',
                                pointRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: { family: 'Cairo' }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        stepSize: 20
                                    }
                                }
                            }
                        }
                    });
                }

                // Attendance Chart
                const attendanceCanvas = document.getElementById('attendanceChart');
                if (attendanceCanvas) {
                    const existingAttendanceChart = Chart.getChart(attendanceCanvas);
                    if (existingAttendanceChart) {
                        existingAttendanceChart.destroy();
                    }

                    new Chart(attendanceCanvas, {
                        type: 'bar',
                        data: {
                            labels: @js(array_column($attendanceByMonth, 'month')),
                            datasets: [
                                {
                                    label: 'حاضر',
                                    data: @js(array_column($attendanceByMonth, 'present')),
                                    backgroundColor: 'rgba(88, 186, 71, 0.8)',
                                    borderColor: '#58BA47',
                                    borderWidth: 1,
                                    borderRadius: 6
                                },
                                {
                                    label: 'غائب',
                                    data: @js(array_column($attendanceByMonth, 'absent')),
                                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                    borderColor: '#EF4444',
                                    borderWidth: 1,
                                    borderRadius: 6
                                },
                                {
                                    label: 'متأخر',
                                    data: @js(array_column($attendanceByMonth, 'late')),
                                    backgroundColor: 'rgba(245, 158, 11, 0.8)',
                                    borderColor: '#F59E0B',
                                    borderWidth: 1,
                                    borderRadius: 6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: { family: 'Cairo' }
                                    }
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
            }

            document.addEventListener('DOMContentLoaded', renderStudentCharts);
            document.addEventListener('livewire:navigated', renderStudentCharts);
            document.addEventListener('livewire:initialized', renderStudentCharts);
            setTimeout(renderStudentCharts, 250);
            setTimeout(renderStudentCharts, 600);
        })();
    </script>
    @endpush
</x-filament-panels::page>
