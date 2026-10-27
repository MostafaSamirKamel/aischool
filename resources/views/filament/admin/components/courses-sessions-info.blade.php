{{-- resources/views/livewire/admin/admin-enrollment-courses-manager.blade.php --}}
<div dir="rtl">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <div class="container-fluid" dir="rtl">
        @if(!empty($getState()))
            @foreach($getState() as $course)
                {{-- Course Card with Bootstrap --}}
                <div class="card shadow-lg mb-4 border-0">

                    {{-- Course Header --}}
                    <div class="card-header bg-gradient-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1 fw-bold">
                                    <i class="fas fa-book-open me-2"></i>{{ $course['course_name'] }}
                                </h4>
                                <small class="text-white-50">
                                    <i class="fas fa-hashtag me-1"></i>{{ $course['course_code'] }}
                                </small>
                            </div>
                            <div class="bg-white text-primary px-4 py-2 rounded-3 shadow">
                                <h5 class="mb-0 fw-bold">
                                    {{ number_format($course['course_price'], 0) }} <small>ج.م</small>
                                </h5>
                            </div>
                        </div>
                    </div>

                    {{-- Stats Cards --}}
                    <div class="card-body bg-light">
                        <div class="row g-3 mb-3">

                            {{-- Total Sessions --}}
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100 bg-primary bg-gradient text-white">
                                    <div class="card-body text-center py-4">
                                        <div class="display-1 mb-2 opacity-75">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <h2 class="display-4 fw-bold mb-1">{{ $course['total_sessions'] }}</h2>
                                        <p class="mb-0 text-uppercase fw-bold">إجمالي المحاضرات</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Completed Sessions --}}
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100 bg-success bg-gradient text-white">
                                    <div class="card-body text-center py-4">
                                        <div class="display-1 mb-2 opacity-75">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <h2 class="display-4 fw-bold mb-1">{{ $course['completed_sessions'] }}</h2>
                                        <p class="mb-0 text-uppercase fw-bold">تم شرحها</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Remaining Sessions --}}
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100 bg-warning bg-gradient text-white">
                                    <div class="card-body text-center py-4">
                                        <div class="display-1 mb-2 opacity-75">
                                            <i class="fas fa-hourglass-half"></i>
                                        </div>
                                        <h2 class="display-4 fw-bold mb-1">{{ $course['remaining_sessions'] }}</h2>
                                        <p class="mb-0 text-uppercase fw-bold">المتبقية</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        @if(($course['total_sessions'] ?? 0) > 0)
                            @php
                                $percentage = round(($course['completed_sessions'] / $course['total_sessions']) * 100);
                            @endphp
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="fas fa-chart-line text-primary me-2"></i>نسبة الإنجاز
                                        </h5>
                                        <span class="badge bg-primary fs-4 px-3 py-2">{{ $percentage }}%</span>
                                    </div>
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                             role="progressbar"
                                             style="width: {{ $percentage }}%;"
                                             aria-valuenow="{{ $percentage }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <strong class="fs-6">{{ $percentage }}%</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Sessions Table --}}
                    @if(!empty($course['sessions']))
                        <div class="accordion accordion-flush" id="accordion{{ $loop->index }}">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $loop->index }}">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        جدول المحاضرات
                                        <span class="badge bg-primary ms-2">{{ count($course['sessions']) }} محاضرة</span>
                                    </button>
                                </h2>
                                <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead class="table-dark">
                                                    <tr class="text-center">
                                                        <th><i class="fas fa-calendar me-1"></i> التاريخ</th>
                                                        <th><i class="fas fa-calendar-day me-1"></i> اليوم</th>
                                                        <th><i class="fas fa-clock me-1"></i> من</th>
                                                        <th><i class="fas fa-clock me-1"></i> إلى</th>
                                                        <th><i class="fas fa-info-circle me-1"></i> الحالة</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($course['sessions'] as $session)
                                                        <tr class="text-center">
                                                            <td class="fw-bold">{{ $session['date'] }}</td>
                                                            <td>{{ $session['day'] }}</td>
                                                            <td>{{ $session['start_time'] }}</td>
                                                            <td>{{ $session['end_time'] }}</td>
                                                            <td>
                                                                @if($session['status'] == 'منتهية')
                                                                    <span class="badge bg-success px-3 py-2">
                                                                        <i class="fas fa-check-circle me-1"></i>{{ $session['status'] }}
                                                                    </span>
                                                                @elseif($session['status'] == 'جارية')
                                                                    <span class="badge bg-info px-3 py-2">
                                                                        <i class="fas fa-play-circle me-1"></i>{{ $session['status'] }}
                                                                    </span>
                                                                @elseif($session['status'] == 'مجدولة')
                                                                    <span class="badge bg-warning px-3 py-2">
                                                                        <i class="fas fa-clock me-1"></i>{{ $session['status'] }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger px-3 py-2">
                                                                        <i class="fas fa-times-circle me-1"></i>{{ $session['status'] }}
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
                            </div>
                        </div>
                    @else
                        <div class="card-footer bg-light text-center py-3">
                            <i class="fas fa-info-circle text-muted me-2"></i>
                            <span class="text-muted">لا توجد محاضرات مجدولة</span>
                        </div>
                    @endif

                </div>
            @endforeach
        @else
            <div class="alert alert-info text-center py-5 border-0 shadow-sm">
                <i class="fas fa-info-circle display-1 text-primary mb-3 d-block"></i>
                <h4 class="fw-bold">لا توجد كورسات مسجلة</h4>
            </div>
        @endif
    </div>

    {{-- Bootstrap JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>

</div>
