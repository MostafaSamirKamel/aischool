@php
    use Filament\Support\Facades\FilamentAsset;
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
    .gradient-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .gradient-orange {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .gradient-cyan {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .gradient-purple {
        background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
    }
    .gradient-green {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
    }
    .gradient-badge-active {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .gradient-badge-inactive {
        background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
    }
    .progress-gradient-purple {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    .progress-gradient-green {
        background: linear-gradient(90deg, #56ab2f 0%, #a8e063 100%);
    }
    .stat-card {
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .modal-content {
        max-height: 80vh;
        overflow-y: auto;
    }
</style>

<div class="container-fluid p-0">
    @if($enrollment && $enrollment->enrollmentCourses && $enrollment->enrollmentCourses->count() > 0)
        @foreach($enrollment->enrollmentCourses as $enrollmentCourse)
            @php
                $course = $enrollmentCourse->course;
                
                // Skip if course not loaded
                if (!$course || !$course->sessionSchedules) {
                    continue;
                }
                
                // Session Progress Calculation
                $totalSessions = $course->sessionSchedules->count();
                $now = now();
                $completedSessions = $course->sessionSchedules->filter(function ($schedule) use ($now) {
                    return $schedule->scheduled_date < $now->toDateString() || 
                           ($schedule->scheduled_date == $now->toDateString() && $schedule->end_time < $now->toTimeString());
                })->count();
                
                $sessionProgress = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0;
                
                // Attendance Progress Calculation
                $groupId = $enrollmentCourse->group_id;
                $studentId = $enrollment->student_id;
                
                $attendedCount = 0;
                if ($groupId && $completedSessions > 0) {
                    $attendedCount = \App\Models\Attendance::where('student_id', $studentId)
                        ->where('group_id', $groupId)
                        ->where('status', 'present')
                        ->whereIn('date', $course->sessionSchedules
                            ->filter(function ($schedule) use ($now) {
                                return $schedule->scheduled_date < $now->toDateString() || 
                                       ($schedule->scheduled_date == $now->toDateString() && $schedule->end_time < $now->toTimeString());
                            })
                            ->pluck('scheduled_date')
                        )
                        ->count();
                }
                
                $attendanceProgress = $completedSessions > 0 ? round(($attendedCount / $completedSessions) * 100, 1) : 0;
                
                // Calculate statistics
                $remainingSessions = max(0, $totalSessions - $completedSessions);
                $absentCount = max(0, $completedSessions - $attendedCount);
                $price = $enrollmentCourse->price ?? 0;
                
                // Calculate difference for warning
                $progressDifference = abs($sessionProgress - $attendanceProgress);
                $isActive = $enrollmentCourse->status === 'active';
                
                // Get available groups for this course
                $availableGroups = \App\Models\Group::withoutGlobalScopes()
                    ->where('course_id', $course->id)
                    ->with('instructor.user')
                    ->get()
                    ->map(function($group) {
                        return [
                            'id' => $group->id,
                            'name' => $group->name,
                            'instructor_name' => $group->instructor->user->name ?? 'غير محدد'
                        ];
                    })
                    ->toArray();
                    
                $currentGroupId = $enrollmentCourse->group_id;
                $currentGroupName = $currentGroupId ? (\App\Models\Group::withoutGlobalScopes()->find($currentGroupId)?->name ?? 'غير متاح') : null;
            @endphp
            
            <div class="card shadow-sm mb-4" x-data="{ 
                selectedGroup: {{ $currentGroupId ?? 'null' }},
                async assignGroup(enrollmentCourseId) {
                    if (!this.selectedGroup) {
                        alert('الرجاء اختيار مجموعة أولاً');
                        return;
                    }
                    
                    try {
                        const response = await fetch('/admin/enrollment-courses/' + enrollmentCourseId + '/assign-group', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                group_id: this.selectedGroup
                            })
                        });
                        
                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert('حدث خطأ أثناء تفعيل الكورس');
                        }
                    } catch (error) {
                        alert('حدث خطأ أثناء تفعيل الكورس');
                    }
                }
            }">
                {{-- Course Header --}}
                <div class="card-header gradient-card text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold">
                                <i class="fas fa-graduation-cap me-2"></i>
                                {{ $course->name }}
                            </h5>
                            <small class="opacity-75">{{ $course->description }}</small>
                        </div>
                        <span class="badge {{ $isActive ? 'gradient-badge-active' : 'gradient-badge-inactive' }} px-3 py-2">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                            {{ $isActive ? 'نشط' : 'غير نشط' }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- Group Assignment Section --}}
                    @if(!$isActive)
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    تنشيط الكورس - اختيار المجموعة
                                </h6>
                            </div>
                            <div class="card-body">
                                @if(count($availableGroups) > 0)
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold">اختر المجموعة:</label>
                                            <select 
                                                class="form-select form-select-lg" 
                                                x-model="selectedGroup"
                                            >
                                                <option value="">-- اختر مجموعة --</option>
                                                @foreach($availableGroups as $group)
                                                    <option value="{{ $group['id'] }}">
                                                        {{ $group['name'] }} - المدرس: {{ $group['instructor_name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button 
                                                type="button" 
                                                class="btn btn-success btn-lg w-100"
                                                @click="assignGroup({{ $enrollmentCourse->id }})"
                                                :disabled="!selectedGroup"
                                            >
                                                <i class="fas fa-save me-2"></i>
                                                حفظ وتفعيل
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-danger mb-0">
                                        <i class="fas fa-times-circle me-2"></i>
                                        لا توجد مجموعات متاحة لهذا الكورس
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($isActive)
                        {{-- Statistics Cards --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="stat-card gradient-orange text-white p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 opacity-75" style="font-size: 0.85rem;">المحاضرات المتبقية</h6>
                                            <h3 class="mb-0 fw-bold mt-2">{{ $remainingSessions }}</h3>
                                        </div>
                                        <i class="fas fa-clock fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card gradient-cyan text-white p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 opacity-75" style="font-size: 0.85rem;">المحاضرات المكتملة</h6>
                                            <h3 class="mb-0 fw-bold mt-2">{{ $completedSessions }}</h3>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card gradient-purple text-white p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 opacity-75" style="font-size: 0.85rem;">إجمالي المحاضرات</h6>
                                            <h3 class="mb-0 fw-bold mt-2">{{ $totalSessions }}</h3>
                                        </div>
                                        <i class="fas fa-list fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card gradient-green text-white p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 opacity-75" style="font-size: 0.85rem;">السعر</h6>
                                            <h3 class="mb-0 fw-bold mt-2">{{ number_format($price, 0) }} ج.م</h3>
                                        </div>
                                        <i class="fas fa-coins fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Progress Section --}}
                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body p-4">
                                <h6 class="mb-3 fw-bold text-primary">
                                    <i class="fas fa-chart-line me-2"></i>
                                    تقدم الطالب
                                </h6>
                                
                                {{-- Session Progress --}}
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold text-secondary">
                                            <i class="fas fa-book-open me-1"></i>
                                            تقدم المحاضرات
                                        </span>
                                        <span class="badge bg-primary">{{ $sessionProgress }}%</span>
                                    </div>
                                    <div class="progress" style="height: 25px; border-radius: 10px;">
                                        <div class="progress-bar progress-gradient-purple" 
                                             role="progressbar" 
                                             style="width: {{ $sessionProgress }}%;" 
                                             aria-valuenow="{{ $sessionProgress }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <strong>{{ $sessionProgress }}%</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Attendance Progress --}}
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold text-secondary">
                                            <i class="fas fa-user-check me-1"></i>
                                            تقدم الحضور
                                        </span>
                                        <span class="badge bg-success">{{ $attendanceProgress }}%</span>
                                    </div>
                                    <div class="progress" style="height: 25px; border-radius: 10px;">
                                        <div class="progress-bar progress-gradient-green" 
                                             role="progressbar" 
                                             style="width: {{ $attendanceProgress }}%;" 
                                             aria-valuenow="{{ $attendanceProgress }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <strong>{{ $attendanceProgress }}%</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Attendance Statistics --}}
                                <div class="row g-2 mt-3">
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <div class="card-body text-white text-center py-2">
                                                <i class="fas fa-calendar-check mb-1"></i>
                                                <h6 class="mb-0 fw-bold">{{ $completedSessions }}</h6>
                                                <small class="opacity-75">محاضرات مكتملة</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);">
                                            <div class="card-body text-white text-center py-2">
                                                <i class="fas fa-user-check mb-1"></i>
                                                <h6 class="mb-0 fw-bold">{{ $attendedCount }}</h6>
                                                <small class="opacity-75">محاضرات حضر فيها</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                                            <div class="card-body text-white text-center py-2">
                                                <i class="fas fa-user-times mb-1"></i>
                                                <h6 class="mb-0 fw-bold">{{ $absentCount }}</h6>
                                                <small class="opacity-75">محاضرات غاب عنها</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Warning Alert --}}
                                @if($progressDifference > 10)
                                    <div class="alert alert-warning mt-3 mb-0" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>تحذير:</strong> 
                                        يوجد فرق كبير بين تقدم المحاضرات وتقدم الحضور ({{ $progressDifference }}%). 
                                        يرجى متابعة حضور الطالب.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Inactive Course Alert --}}
                        <div class="alert alert-info mb-0" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>الكورس غير نشط:</strong>
                            لم يتم تفعيل هذا الكورس بعد. يرجى تنشيط الكورس لعرض التقدم والإحصائيات.
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            لا توجد كورسات مسجلة لهذا الطالب.
        </div>
    @endif
</div>
