<x-filament-panels::page>
    @include('filament.student.layouts.student-page-styles')
    
    <style>
        .page-header {
            background: linear-gradient(135deg, #364FAC 0%, #293C85 100%) !important;
        }
    </style>

    {{-- Page Header --}}
    <div class="page-header mb-4 position-relative">
        <div class="d-flex align-items-center position-relative" style="z-index: 1;">
            <div class="header-icon bg-white bg-opacity-20 text-white me-3">
                <i class="bi bi-credit-card"></i>
            </div>
            <div>
                <h2 class="mb-1 fw-bold" style="font-size: 1.75rem;">مدفوعاتي واشتراكاتي</h2>
                <p class="mb-0 opacity-90" style="font-size: 1rem;">تتبع المدفوعات والاشتراكات الدراسية</p>
            </div>
        </div>
    </div>

    @php
        $selectedStudentId = session('selected_student_id');
        $student = $selectedStudentId ? \App\Models\Student::find($selectedStudentId) : null;

        // Get student enrollments
        $enrollmentIds = $student
            ? \App\Models\Enrollment::where('student_id', $student->id)->pluck('id')
            : collect();
        
        // Use enrollment_id to get payments from payments table
        $totalPaid = \App\Models\Payment::whereIn('enrollment_id', $enrollmentIds)
            ->where('status', 'completed')
            ->sum('amount');
        $pendingPayments = \App\Models\Payment::whereIn('enrollment_id', $enrollmentIds)
            ->where('status', 'pending')
            ->sum('amount');
        $totalPayments = \App\Models\Payment::whereIn('enrollment_id', $enrollmentIds)->count();
        
        // Calculate total expected from active enrollments
        $activeEnrollments = $student
            ? \App\Models\Enrollment::where('student_id', $student->id)
                ->where('status', 'active')
                ->with(['enrollmentCourses'])
                ->get()
            : collect();
        $totalExpected = $activeEnrollments->sum(fn($e) => $e->enrollmentCourses->sum('course_price'));
        $remaining = $totalExpected - $totalPaid;
    @endphp

    {{-- Statistics Cards Row --}}
    <div class="row g-4 mb-4">
        {{-- Total Paid Card --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card stat-card card-success h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-value">{{ number_format($totalPaid, 2) }}</p>
                            <p class="stat-label mt-2">إجمالي المدفوع (جنيه)</p>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background: rgba(16, 185, 129, 0.1);">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalExpected > 0 ? ($totalPaid / $totalExpected * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Payments Card --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card stat-card card-warning h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-value">{{ number_format($pendingPayments, 2) }}</p>
                            <p class="stat-label mt-2">قيد الانتظار (جنيه)</p>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background: rgba(245, 158, 11, 0.1);">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $totalExpected > 0 ? ($pendingPayments / $totalExpected * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Remaining Card --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card stat-card card-danger h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-value">{{ number_format(max(0, $remaining), 2) }}</p>
                            <p class="stat-label mt-2">المتبقي (جنيه)</p>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background: rgba(239, 68, 68, 0.1);">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $totalExpected > 0 ? (max(0, $remaining) / $totalExpected * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Payments Count Card --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card stat-card card-primary h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-value">{{ $totalPayments }}</p>
                            <p class="stat-label mt-2">عدد المدفوعات</p>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-credit-card-fill"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background: rgba(59, 130, 246, 0.1);">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Enrollments --}}
    @if($activeEnrollments->count() > 0)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="card-header bg-white border-0 p-4">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-bookmark-check-fill me-2 text-primary"></i>
                    الاشتراكات النشطة
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach($activeEnrollments as $enrollment)
                        @php
                            $coursePaid = \App\Models\Payment::where('enrollment_id', $enrollment->id)
                                ->where('status', 'completed')
                                ->sum('amount');
                            $coursePrice = $enrollment->total_amount ?? 0;
                            $courseRemaining = $coursePrice - $coursePaid;
                            $paymentPercentage = $coursePrice > 0 ? round(($coursePaid / $coursePrice) * 100) : 0;
                            
                            // Get all group names and course names
                            $groupNames = $enrollment->groups->pluck('name')->join(' | ');
                            $courseNames = $enrollment->groups->pluck('course.name')->unique()->join(' | ');
                        @endphp
                        <div class="col-12">
                            <div class="card enrollment-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark">
                                                <i class="bi bi-mortarboard-fill text-primary me-2"></i>
                                                {{ $courseNames ?: 'غير محدد' }}
                                            </h6>
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-people-fill me-1"></i>
                                                {{ $groupNames ?: 'غير محدد' }}
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <div class="fs-5 fw-bold text-primary">
                                                {{ number_format($coursePaid, 2) }} / {{ number_format($coursePrice, 2) }}
                                            </div>
                                            <small class="text-muted">جنيه</small>
                                        </div>
                                    </div>
                                    
                                    <div class="progress mb-3" style="height: 12px; border-radius: 10px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $paymentPercentage }}%; 
                                                    background: linear-gradient(90deg, 
                                                    {{ $paymentPercentage >= 100 ? '#10b981' : ($paymentPercentage >= 50 ? '#3b82f6' : '#f59e0b') }}, 
                                                    {{ $paymentPercentage >= 100 ? '#059669' : ($paymentPercentage >= 50 ? '#2563eb' : '#d97706') }});">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-{{ $paymentPercentage >= 100 ? 'success' : ($paymentPercentage >= 50 ? 'primary' : 'warning') }} px-3 py-2">
                                            <i class="bi bi-{{ $paymentPercentage >= 100 ? 'check-circle' : 'clock' }}-fill me-1"></i>
                                            {{ $paymentPercentage }}% مدفوع
                                        </span>
                                        @if($courseRemaining > 0)
                                            <span class="text-danger fw-bold">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>
                                                المتبقي: {{ number_format($courseRemaining, 2) }} جنيه
                                            </span>
                                        @else
                                            <span class="text-success fw-bold">
                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                مكتمل
                                            </span>
                                        @endif
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
