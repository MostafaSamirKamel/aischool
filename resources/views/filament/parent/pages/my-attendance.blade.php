<x-filament-panels::page>
    @include('filament.student.layouts.student-page-styles')

    <style>
        .page-header {
            background: linear-gradient(135deg, #364FAC 0%, #293C85 100%) !important;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .stat-card:nth-child(1) {
            animation-delay: 0.05s;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.15s;
        }

        .stat-card:nth-child(4) {
            animation-delay: 0.2s;
        }

        .stat-card:nth-child(5) {
            animation-delay: 0.25s;
        }

        .stat-card:nth-child(6) {
            animation-delay: 0.3s;
        }
    </style>

    {{-- Page Header --}}
    <div class="page-header mb-4 position-relative"
        style="background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);">
        <div class="d-flex align-items-center position-relative" style="z-index: 1;">
            <div class="header-icon bg-white bg-opacity-20 text-white me-3">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <h2 class="mb-1 fw-bold" style="font-size: 1.75rem;">سجل الحضور والغياب</h2>
                <p class="mb-0 opacity-90" style="font-size: 1rem;">تتبع حضورك وانتظامك في الحصص الدراسية</p>
            </div>
        </div>
    </div>

    @php
        $selectedStudentId = session('selected_student_id');
        $student = $selectedStudentId ? \App\Models\Student::find($selectedStudentId) : null;
        $studentId = $student?->id;

        if ($studentId) {
            $totalSessions = \App\Models\AttendanceRecord::where('student_id', $studentId)->count();
            $presentCount = \App\Models\AttendanceRecord::where('student_id', $studentId)->where('status', 'present')->count();
            $absentCount = \App\Models\AttendanceRecord::where('student_id', $studentId)->where('status', 'absent')->count();
            $lateCount = \App\Models\AttendanceRecord::where('student_id', $studentId)->where('status', 'late')->count();
            $excusedCount = \App\Models\AttendanceRecord::where('student_id', $studentId)->where('status', 'excused')->count();
        } else {
            $totalSessions = 0;
            $presentCount = 0;
            $absentCount = 0;
            $lateCount = 0;
            $excusedCount = 0;
        }

        $attendanceRate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100) : 0;
    @endphp

    {{-- Statistics Cards Row --}}
    <div class="row g-4 mb-4">
        {{-- Total Sessions Card --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="card stat-card card-primary h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-3">
                            <i class="bi bi-calendar-week"></i>
                        </div>
                        <p class="stat-value">{{ $totalSessions }}</p>
                        <p class="stat-label mt-2">إجمالي الحصص</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Present Card --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="card stat-card card-success h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success mb-3">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <p class="stat-value">{{ $presentCount }}</p>
                        <p class="stat-label mt-2">حاضر</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Absent Card --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="card stat-card card-danger h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger mb-3">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <p class="stat-value">{{ $absentCount }}</p>
                        <p class="stat-label mt-2">غائب</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Late Card --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="card stat-card card-warning h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning mb-3">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <p class="stat-value">{{ $lateCount }}</p>
                        <p class="stat-label mt-2">متأخر</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Excused Card --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="card stat-card card-info h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info mb-3">
                            <i class="bi bi-info-circle-fill"></i>
                        </div>
                        <p class="stat-value">{{ $excusedCount }}</p>
                        <p class="stat-label mt-2">بعذر</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Rate Card --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="card stat-card card-purple h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center text-center">
                        <div class="stat-icon bg-purple bg-opacity-10 text-purple mb-3" style="color: #8b5cf6;">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <p class="stat-value">{{ $attendanceRate }}%</p>
                        <p class="stat-label mt-2">نسبة الحضور</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance by Group --}}
    @php
        $groups = $studentId
            ? \App\Models\Group::whereHas('enrollmentCourses', function ($q) use ($studentId) {
                $q->whereHas('enrollment', function($e) use ($studentId) {
                    $e->where('student_id', $studentId)
                      ->where('enrollments.status', 'active');
                });
            })->with('course')->get()
            : collect();
    @endphp

    @if ($groups->count() > 0)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="card-header bg-white border-0 p-4">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-graph-up me-2 text-info"></i>
                    نسبة الحضور حسب المجموعة
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach ($groups as $group)
                        @php
                            $groupTotal = \App\Models\AttendanceRecord::where('student_id', $studentId)
                                ->whereHas('session', fn($q) => $q->where('group_id', $group->id))
                                ->count();
                            $groupPresent = \App\Models\AttendanceRecord::where('student_id', $studentId)
                                ->whereHas('session', fn($q) => $q->where('group_id', $group->id))
                                ->where('status', 'present')
                                ->count();
                            $groupRate = $groupTotal > 0 ? round(($groupPresent / $groupTotal) * 100) : 0;
                        @endphp
                        <div class="col-12 col-md-6">
                            <div class="card group-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark">
                                                <i class="bi bi-book-fill text-info me-2"></i>
                                                {{ $group->course->name }}
                                            </h6>
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-people-fill me-1"></i>
                                                {{ $group->name }}
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <div class="fs-3 fw-bold"
                                                style="color: {{ $groupRate >= 90 ? '#10b981' : ($groupRate >= 75 ? '#f59e0b' : '#ef4444') }};">
                                                {{ $groupRate }}%
                                            </div>
                                            <small class="text-muted">{{ $groupPresent }} / {{ $groupTotal }}
                                                حصة</small>
                                        </div>
                                    </div>

                                    <div class="progress mb-3" style="height: 12px; border-radius: 10px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $groupRate }}%; 
                                                    background: linear-gradient(90deg, 
                                                    {{ $groupRate >= 90 ? '#10b981' : ($groupRate >= 75 ? '#f59e0b' : '#ef4444') }}, 
                                                    {{ $groupRate >= 90 ? '#059669' : ($groupRate >= 75 ? '#d97706' : '#dc2626') }});">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span
                                            class="badge bg-{{ $groupRate >= 90 ? 'success' : ($groupRate >= 75 ? 'warning' : 'danger') }} px-3 py-2">
                                            <i
                                                class="bi bi-{{ $groupRate >= 90 ? 'check-circle' : ($groupRate >= 75 ? 'exclamation-triangle' : 'x-circle') }}-fill me-1"></i>
                                            @if ($groupRate >= 90)
                                                ممتاز
                                            @elseif($groupRate >= 75)
                                                جيد
                                            @else
                                                يحتاج تحسين
                                            @endif
                                        </span>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            إجمالي: {{ $groupTotal }} حصة
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    {{ $this->table }}
</x-filament-panels::page>
