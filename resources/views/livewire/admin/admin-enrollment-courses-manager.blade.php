<div>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .admin-courses-mgr {
            font-family: inherit;
        }
        .admin-courses-mgr .card {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(24, 36, 61, 0.05);
            border: 1px solid #e5eaf3;
            margin-bottom: 1.5rem;
        }
        .admin-courses-mgr .card-header {
            padding: 1rem 1.25rem;
        }
        .admin-courses-mgr .card-body {
            padding: 1.25rem;
        }
        .admin-courses-mgr .bg-gradient-primary,
        .admin-courses-mgr .bg-primary {
            background: linear-gradient(135deg, #364FAC 0%, #293C85 100%) !important;
            color: #ffffff;
        }
        .admin-courses-mgr .bg-gradient-success,
        .admin-courses-mgr .bg-success {
            background: linear-gradient(135deg, #58BA47 0%, #1F7A18 100%) !important;
            color: #ffffff;
        }
        .admin-courses-mgr .bg-gradient-warning,
        .admin-courses-mgr .bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff;
        }
        .admin-courses-mgr .bg-gradient-info,
        .admin-courses-mgr .bg-info {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
            color: #ffffff;
        }
        .admin-courses-mgr .bg-dark {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            color: #ffffff;
        }
        .admin-courses-mgr .bg-light { background: #f8fafc !important; }
        .admin-courses-mgr .bg-white { background: #ffffff !important; }

        .admin-courses-mgr .text-white { color: #ffffff !important; }
        .admin-courses-mgr .text-white-50 { color: rgba(255, 255, 255, 0.7) !important; }
        .admin-courses-mgr .text-primary { color: #364FAC !important; }
        .admin-courses-mgr .text-success { color: #58BA47 !important; }
        .admin-courses-mgr .text-warning { color: #d97706 !important; }
        .admin-courses-mgr .text-info { color: #0284c7 !important; }
        .admin-courses-mgr .text-danger { color: #ef4444 !important; }
        .admin-courses-mgr .text-muted { color: #64748b !important; }

        .admin-courses-mgr .fw-bold { font-weight: 700 !important; }
        .admin-courses-mgr .fw-semibold { font-weight: 600 !important; }
        .admin-courses-mgr .text-uppercase { text-transform: uppercase !important; }
        .admin-courses-mgr .text-center { text-align: center !important; }
        .admin-courses-mgr .d-block { display: block !important; }
        .admin-courses-mgr .opacity-75 { opacity: 0.75 !important; }
        .admin-courses-mgr .h-100 { height: 100% !important; }

        .admin-courses-mgr .fs-2 { font-size: 1.75rem !important; }
        .admin-courses-mgr .fs-4 { font-size: 1.25rem !important; }
        .admin-courses-mgr .fs-6 { font-size: 0.95rem !important; }
        .admin-courses-mgr .display-1 { font-size: 3.5rem !important; }
        .admin-courses-mgr .display-4 { font-size: 2.25rem !important; }

        .admin-courses-mgr .border-0 { border: none !important; }
        .admin-courses-mgr .border-top { border-top: 1px solid #e2e8f0 !important; }
        .admin-courses-mgr .rounded-3 { border-radius: 12px !important; }
        .admin-courses-mgr .shadow { box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important; }
        .admin-courses-mgr .shadow-sm { box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important; }
        .admin-courses-mgr .shadow-lg { box-shadow: 0 10px 25px -5px rgba(24, 36, 61, 0.08) !important; }

        .admin-courses-mgr .me-1 { margin-inline-end: 0.25rem !important; }
        .admin-courses-mgr .me-2 { margin-inline-end: 0.5rem !important; }
        .admin-courses-mgr .me-3 { margin-inline-end: 1rem !important; }
        .admin-courses-mgr .mb-0 { margin-bottom: 0 !important; }
        .admin-courses-mgr .mb-1 { margin-bottom: 0.25rem !important; }
        .admin-courses-mgr .mb-2 { margin-bottom: 0.5rem !important; }
        .admin-courses-mgr .mb-3 { margin-bottom: 1rem !important; }
        .admin-courses-mgr .mb-4 { margin-bottom: 1.5rem !important; }
        .admin-courses-mgr .mt-3 { margin-top: 1rem !important; }
        .admin-courses-mgr .py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
        .admin-courses-mgr .py-3 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
        .admin-courses-mgr .py-5 { padding-top: 3rem !important; padding-bottom: 3rem !important; }
        .admin-courses-mgr .px-3 { padding-left: 1rem !important; padding-right: 1rem !important; }
        .admin-courses-mgr .px-4 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }

        .admin-courses-mgr .row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 0;
        }
        .admin-courses-mgr .col-md-3 {
            flex: 1 1 calc(20% - 0.8rem);
            min-width: 140px;
        }
        .admin-courses-mgr .col-md-6 {
            flex: 1 1 calc(50% - 0.5rem);
            min-width: 240px;
        }
        .admin-courses-mgr .col-lg-12 {
            flex: 1 1 100%;
            width: 100%;
        }
        @media (max-width: 768px) {
            .admin-courses-mgr .col-md-3,
            .admin-courses-mgr .col-md-6 {
                flex: 1 1 100%;
                min-width: 100%;
            }
        }
        .admin-courses-mgr .d-flex { display: flex; }
        .admin-courses-mgr .align-items-center { align-items: center; }
        .admin-courses-mgr .justify-content-between { justify-content: space-between; }
        .admin-courses-mgr .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .admin-courses-mgr .progress {
            height: 24px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .admin-courses-mgr .progress-bar {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 800;
            transition: width 0.4s ease;
            border-radius: 999px;
        }
        .admin-courses-mgr .form-select {
            width: 100%;
            padding: 0.6rem 1rem;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            font-family: inherit;
            background: #ffffff;
        }
        .admin-courses-mgr .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .admin-courses-mgr .btn-success {
            background: #16a34a;
            color: #ffffff;
        }
        .admin-courses-mgr .btn-success:hover {
            background: #15803d;
        }
        .admin-courses-mgr .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
        }
        .admin-courses-mgr .alert-success { background: #dcfce7; color: #166534; }
        .admin-courses-mgr .alert-info { background: #f0f9ff; color: #0369a1; }
    </style>

    <div class="admin-courses-mgr p-4" dir="rtl">

    @forelse($enrollment->enrollmentCourses as $enrollmentCourse)
        @php
            $isCompleted = $enrollmentCourse->isCompleted();
            $isActive = $enrollmentCourse->isActive();
            $statusLabel = $isCompleted ? 'مكتمل' : ($isActive ? 'نشط' : 'غير نشط');
            $statusColor = $isCompleted ? 'success' : ($isActive ? 'primary' : 'warning');
            $progress = (int) $enrollmentCourse->getProgressPercentage();
            $remaining = max(0, ($enrollmentCourse->total_sessions - $enrollmentCourse->current_session));
            $enrollmentNet = max(0, (float) $enrollment->total_amount - (float) $enrollment->discount);
            $enrollmentPaid = $enrollment->getEffectivePaidAmount();
            $paymentRatio = $enrollmentNet > 0 ? min(1, $enrollmentPaid / $enrollmentNet) : 1;
            $coursePrice = (float) ($enrollmentCourse->course_price ?? 0);
            $coursePaid = round($coursePrice * $paymentRatio, 2);
            $courseRemaining = max(0, round($coursePrice - $coursePaid, 2));
            $paymentStatus = $courseRemaining <= 0 ? 'مدفوع' : ($coursePaid > 0 ? 'مدفوع جزئي' : 'غير مدفوع');
        @endphp

        {{-- Course Card with Bootstrap --}}
        <div class="card shadow-lg mb-4 border-0">
            
            {{-- Course Header --}}
            <div class="card-header bg-gradient-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-graduation-cap me-2"></i>{{ optional($enrollmentCourse->course)->name ?? 'كورس محذوف' }}
                        </h4>
                        <small class="text-white-50">
                            <i class="fas fa-hashtag me-1"></i>{{ $enrollmentCourse->id }}
                        </small>
                    </div>
                    <div class="bg-white text-{{ $statusColor }} px-4 py-2 rounded-3 shadow">
                        <h6 class="mb-0 fw-bold">{{ $statusLabel }}</h6>
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="card-body bg-light">
                <div class="row g-3 mb-3">
                    
                    {{-- Price --}}
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 bg-success bg-gradient text-white">
                            <div class="card-body text-center py-3">
                                <div class="fs-2 mb-2 opacity-75">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <h4 class="fw-bold mb-1">{{ number_format($enrollmentCourse->course_price, 0) }}</h4>
                                <p class="mb-0 small text-uppercase fw-semibold">السعر</p>
                            </div>
                        </div>
                    </div>

                    {{-- Total Sessions --}}
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 bg-primary bg-gradient text-white">
                            <div class="card-body text-center py-3">
                                <div class="fs-2 mb-2 opacity-75">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <h4 class="fw-bold mb-1">{{ $enrollmentCourse->total_sessions }}</h4>
                                <p class="mb-0 small text-uppercase fw-semibold">إجمالي المحاضرات</p>
                            </div>
                        </div>
                    </div>

                    {{-- Completed Sessions --}}
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 bg-info bg-gradient text-white">
                            <div class="card-body text-center py-3">
                                <div class="fs-2 mb-2 opacity-75">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h4 class="fw-bold mb-1">{{ $enrollmentCourse->current_session }}</h4>
                                <p class="mb-0 small text-uppercase fw-semibold">تم إنجازه</p>
                            </div>
                        </div>
                    </div>

                    {{-- Remaining Sessions --}}
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 bg-warning bg-gradient text-white">
                            <div class="card-body text-center py-3">
                                <div class="fs-2 mb-2 opacity-75">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <h4 class="fw-bold mb-1">{{ $remaining }}</h4>
                                <p class="mb-0 small text-uppercase fw-semibold">المتبقي</p>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Status --}}
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100 bg-dark bg-gradient text-white">
                            <div class="card-body text-center py-3">
                                <div class="fs-2 mb-2 opacity-75">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <h6 class="fw-bold mb-1">{{ $paymentStatus }}</h6>
                                <p class="mb-0 small fw-semibold">مدفوع {{ number_format($coursePaid, 2) }} ج.م</p>
                                <p class="mb-0 small fw-semibold">متبقي {{ number_format($courseRemaining, 2) }} ج.م</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-chart-line text-primary me-2"></i>نسبة الإنجاز
                            </h5>
                            <span class="badge bg-{{ $statusColor }} fs-4 px-3 py-2">{{ $progress }}%</span>
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $statusColor }}" 
                                 role="progressbar" 
                                 style="width: {{ $progress }}%;" 
                                 aria-valuenow="{{ $progress }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <strong class="fs-6">{{ $progress }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Details Section --}}
            <div class="card-body border-top">
                <h5 class="fw-bold mb-4">
                    <i class="fas fa-cog text-primary me-2"></i>تفاصيل المجموعة والإعدادات
                </h5>

                <div class="row g-4">

                {{-- Group Selection --}}
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">
                                    <i class="fas fa-users text-primary me-2"></i>اختيار المجموعة
                                </h5>
                                @if($enrollmentCourse->group)
                                    <span class="badge bg-info">{{ $enrollmentCourse->group->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if(!$isCompleted)
                                @php
                                    $courseGroups = \App\Models\Group::where('course_id', $enrollmentCourse->course_id)
                                        ->where('status', 'active')
                                        ->with('instructor.user')
                                        ->get();
                                @endphp

                                <div class="mb-3">
                                    <select class="form-select" wire:model="selectedGroups.{{ $enrollmentCourse->id }}">
                                        <option value="">— اختر مجموعة —</option>
                                        @foreach($courseGroups as $group)
                                            @php
                                                $instructorName = optional(optional($group->instructor)->user)->name ?? 'غير محدد';
                                            @endphp
                                            <option value="{{ $group->id }}">
                                                {{ $group->name }} - {{ $instructorName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">تأكد أن المجموعة Active</small>
                                </div>

                                <button class="btn btn-success" wire:click="saveGroup({{ $enrollmentCourse->id }})">
                                    <i class="fas fa-save me-2"></i>حفظ
                                </button>
                            @else
                                <div class="alert alert-success border-0 shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle display-4 text-success me-3"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">الكورس مكتمل</h6>
                                            <p class="mb-1"><strong>المجموعة:</strong> {{ optional($enrollmentCourse->group)->name ?? 'لا توجد مجموعة' }}</p>
                                            <p class="mb-0 text-muted"><strong>المعلم:</strong> {{ optional(optional(optional($enrollmentCourse->group)->instructor)->user)->name ?? 'غير محدد' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($enrollmentCourse->group)
                                <div class="row g-3 mt-3">
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-primary bg-gradient text-white">
                                            <div class="card-body text-center">
                                                <i class="fas fa-user-tie display-4 mb-2 opacity-75"></i>
                                                <h6 class="text-uppercase fw-bold mb-1">المعلم</h6>
                                                <h5 class="fw-bold mb-0">{{ optional(optional($enrollmentCourse->group->instructor)->user)->name ?? 'غير محدد' }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-info bg-gradient text-white">
                                            <div class="card-body text-center">
                                                <i class="fas fa-users display-4 mb-2 opacity-75"></i>
                                                <h6 class="text-uppercase fw-bold mb-1">المجموعة</h6>
                                                <h5 class="fw-bold mb-0">{{ $enrollmentCourse->group->name }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @empty
        <div class="alert alert-info text-center py-5 border-0 shadow-sm">
            <i class="fas fa-info-circle display-1 text-primary mb-3 d-block"></i>
            <h4 class="fw-bold">لا توجد كورسات في هذا التسجيل</h4>
            <p class="text-muted mb-0">يرجى إضافة كورسات للطالب من قائمة الكورسات المتاحة</p>
        </div>
    @endforelse
</div>
    </div>
