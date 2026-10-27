<x-filament-widgets::widget>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        .course-card {
            transition: all 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
        .gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .badge-active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .badge-inactive {
            background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
        }
    </style>

    <div class="container-fluid px-0">
        @if(count($courses) > 0)
            @foreach($courses as $course)
                {{-- Course Header Card --}}
                <div class="card course-card mb-4 border-0 shadow-sm">
                    <div class="card-header gradient-header text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas fa-book-open fs-4"></i>
                                <div>
                                    <h5 class="mb-0 fw-bold">{{ $course['course_name'] }}</h5>
                                    <small class="opacity-75">{{ $course['course_code'] }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                @if(($course['course_status'] ?? 'active') === 'completed')
                                    <span class="badge bg-info px-3 py-2">
                                        <i class="fas fa-award me-1"></i> مكتمل
                                    </span>
                                @elseif(($course['course_status'] ?? 'active') === 'active')
                                    <span class="badge badge-active px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i> نشط
                                    </span>
                                @else
                                    <span class="badge badge-inactive px-3 py-2">
                                        <i class="fas fa-exclamation-circle me-1"></i> غير نشط
                                    </span>
                                @endif
                                <span class="badge bg-white text-dark px-3 py-2 fs-6">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    {{ number_format($course['course_price'], 0) }} ج.م
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- Course Info --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-chalkboard-teacher text-primary"></i>
                                    <span class="fw-bold text-muted">المجموعة:</span>
                                    <span>{{ $course['group_name'] ?? 'غير محدد' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-user text-primary"></i>
                                    <span class="fw-bold text-muted">المدرس:</span>
                                    <span>{{ $course['instructor_name'] ?? 'غير محدد' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-wallet text-primary"></i>
                                    <span class="fw-bold text-muted">حالة السداد:</span>
                                    @if(($course['payment_status'] ?? 'pending') === 'paid')
                                        <span class="badge bg-success">مدفوع</span>
                                    @elseif(($course['payment_status'] ?? 'pending') === 'partial')
                                        <span class="badge bg-warning text-dark">مدفوع جزئي</span>
                                    @else
                                        <span class="badge bg-secondary">غير مدفوع</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="fw-bold text-muted">مدفوع:</span>
                                    <span class="text-success fw-bold">{{ number_format((float) ($course['course_paid_amount'] ?? 0), 2) }} ج.م</span>
                                    <span class="fw-bold text-muted">المتبقي:</span>
                                    <span class="text-danger fw-bold">{{ number_format((float) ($course['course_remaining_amount'] ?? 0), 2) }} ج.م</span>
                                </div>
                            </div>
                        </div>

                        {{-- Change Group Section (for active courses) --}}
                        @if(($course['course_status'] ?? 'active') === 'active' && count($course['available_groups']) > 0)
                            <div class="alert alert-info border-info mb-4" x-data="{ selectedGroup: '{{ $course['group_id'] }}' }">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="fas fa-exchange-alt"></i>
                                    <strong>تغيير المجموعة</strong>
                                </div>
                                
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold mb-2">
                                            <i class="fas fa-users me-1"></i>
                                            اختر مجموعة أخرى
                                        </label>
                                        <select class="form-select form-select-lg" x-model="selectedGroup">
                                            @foreach($course['available_groups'] as $group)
                                                <option value="{{ $group['id'] }}" {{ $group['id'] == $course['group_id'] ? 'selected' : '' }}>
                                                    {{ $group['name'] }} 
                                                    @if($group['instructor_name'])
                                                        - {{ $group['instructor_name'] }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button 
                                            type="button" 
                                            class="btn btn-primary btn-lg w-100"
                                            @click="if (!selectedGroup) { alert('الرجاء اختيار مجموعة أولاً'); } else if (selectedGroup == '{{ $course['group_id'] }}') { alert('هذه المجموعة محددة بالفعل'); } else { $wire.assignGroup({{ $course['enrollment_course_id'] }}, selectedGroup); }"
                                        >
                                            <i class="fas fa-save me-2"></i>
                                            تحديث المجموعة
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Group Assignment (for inactive courses) --}}
                        @if(!$course['has_group'] && ($course['course_status'] ?? 'inactive') === 'inactive')
                            <div class="alert alert-warning border-warning mb-0" x-data="{ selectedGroup: '' }">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>تنبيه:</strong>
                                    <span>هذا الكورس غير نشط. اختر مجموعة لتنشيطه</span>
                                </div>
                                
                                @if(count($course['available_groups']) > 0)
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold mb-2">
                                                <i class="fas fa-users me-1"></i>
                                                اختر المجموعة
                                            </label>
                                            <select class="form-select form-select-lg" x-model="selectedGroup">
                                                <option value="">-- اختر مجموعة --</option>
                                                @foreach($course['available_groups'] as $group)
                                                    <option value="{{ $group['id'] }}">
                                                        {{ $group['name'] }} 
                                                        @if($group['instructor_name'])
                                                            - {{ $group['instructor_name'] }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button 
                                                type="button" 
                                                class="btn btn-success btn-lg w-100"
                                                @click="if (!selectedGroup) { alert('الرجاء اختيار مجموعة أولاً'); } else { $wire.assignGroup({{ $course['enrollment_course_id'] }}, selectedGroup); }"
                                            >
                                                <i class="fas fa-save me-2"></i>
                                                حفظ وتفعيل
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-danger mt-3 mb-0">
                                        <i class="fas fa-times-circle me-2"></i>
                                        لا توجد مجموعات متاحة لهذا الكورس
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Statistics Cards (Always show for active courses) --}}
                @if($course['has_group'])
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <div class="card-body text-center py-4">
                                    <i class="fas fa-hourglass-half fs-1 mb-2 opacity-75"></i>
                                    <h2 class="fw-bold mb-1">{{ $course['remaining_sessions'] }}</h2>
                                    <p class="mb-0 small text-uppercase fw-semibold">المتبقي</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 text-white" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                                <div class="card-body text-center py-4">
                                    <i class="fas fa-check-circle fs-1 mb-2 opacity-75"></i>
                                    <h2 class="fw-bold mb-1">{{ $course['completed_sessions'] }}</h2>
                                    <p class="mb-0 small text-uppercase fw-semibold">تم إنجازه</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
                                <div class="card-body text-center py-4">
                                    <i class="fas fa-chalkboard-teacher fs-1 mb-2 opacity-75"></i>
                                    <h2 class="fw-bold mb-1">{{ $course['total_sessions'] }}</h2>
                                    <p class="mb-0 small text-uppercase fw-semibold">إجمالي المحاضرات</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <div class="card-body text-center py-4">
                                    <i class="fas fa-money-bill-wave fs-1 mb-2 opacity-75"></i>
                                    <h2 class="fw-bold mb-1">{{ number_format($course['course_price'], 0) }}</h2>
                                    <p class="mb-0 small text-uppercase fw-semibold">السعر</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dual Progress Bars --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4">
                                <i class="fas fa-chart-line me-2 text-primary"></i>
                                مقارنة التقدم والحضور
                            </h6>

                            {{-- Progress Bar 1: Session Completion --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-book text-primary"></i>
                                        <span class="fw-bold text-muted">المحاضرات المكتملة</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted">{{ $course['completed_sessions'] }} من {{ $course['total_sessions'] }}</small>
                                        <span class="badge bg-primary px-3 py-2">{{ $course['session_progress'] }}%</span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 30px;">
                                    <div 
                                        class="progress-bar progress-bar-striped progress-bar-animated" 
                                        role="progressbar" 
                                        style="width: {{ $course['session_progress'] }}%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"
                                        aria-valuenow="{{ $course['session_progress'] }}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100"
                                    >
                                        @if($course['session_progress'] > 10)
                                            <strong class="fs-6">{{ $course['completed_sessions'] }}/{{ $course['total_sessions'] }}</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Progress Bar 2: Attendance --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span class="fw-bold text-muted">المحاضرات المحضورة</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted">{{ $course['attended_sessions'] }} من {{ $course['total_sessions'] }}</small>
                                        <span class="badge bg-success px-3 py-2">{{ $course['attendance_progress'] }}%</span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 30px;">
                                    <div 
                                        class="progress-bar progress-bar-striped progress-bar-animated" 
                                        role="progressbar" 
                                        style="width: {{ $course['attendance_progress'] }}%; background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);"
                                        aria-valuenow="{{ $course['attendance_progress'] }}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100"
                                    >
                                        @if($course['attendance_progress'] > 10)
                                            <strong class="fs-6">{{ $course['attended_sessions'] }}/{{ $course['total_sessions'] }}</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Statistics Cards --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="text-center bg-primary bg-opacity-10 rounded p-3 border border-primary">
                                        <div class="fs-4 fw-bold text-primary">{{ $course['completed_sessions'] }}</div>
                                        <small class="text-muted">محاضرات مكتملة</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center bg-success bg-opacity-10 rounded p-3 border border-success">
                                        <div class="fs-4 fw-bold text-success">{{ $course['attended_sessions'] }}</div>
                                        <small class="text-muted">محاضرات محضورة</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center bg-danger bg-opacity-10 rounded p-3 border border-danger">
                                        <div class="fs-4 fw-bold text-danger">{{ $course['completed_sessions'] - $course['attended_sessions'] }}</div>
                                        <small class="text-muted">محاضرات غائب</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Alert for Difference --}}
                            @php
                                $difference = $course['session_progress'] - $course['attendance_progress'];
                            @endphp
                            @if(abs($difference) > 10)
                                <div class="alert alert-warning border-warning mb-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <div>
                                            <strong>
                                                @if($difference > 0)
                                                    يوجد فارق {{ round($difference, 1) }}% بين المحاضرات المكتملة والحضور
                                                @else
                                                    خطأ: الحضور أعلى من المحاضرات المكتملة!
                                                @endif
                                            </strong>
                                            <div>
                                                <small>الطالب غائب عن {{ $course['completed_sessions'] - $course['attended_sessions'] }} محاضرة من المحاضرات المكتملة</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="alert alert-info text-center py-5 border-0 shadow-sm">
                <i class="fas fa-info-circle display-1 text-primary mb-3 d-block"></i>
                <h4 class="fw-bold">لا توجد كورسات في هذا التسجيل</h4>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
