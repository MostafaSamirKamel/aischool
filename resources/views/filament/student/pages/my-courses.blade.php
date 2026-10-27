<x-filament-panels::page>
    <div class="student-theme-wrapper" dir="rtl">
        <link rel="stylesheet" href="{{ asset('css/student-theme-scoped.css') }}">
        
        <div>
            {{-- Page Header Banner --}}
            <div class="page-header mb-4 position-relative" style="background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);">
                <div class="d-flex align-items-center" style="display: flex; align-items: center; gap: 1.25rem; position: relative; z-index: 1;">
                    <div style="width: 58px; height: 58px; border-radius: 16px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.35); display: flex; align-items: center; justify-content: center; font-size: 26px; color: #ffffff; flex-shrink: 0;">
                        <x-student-icon name="book-open" size="28" />
                    </div>
                    <div>
                        <h2 style="font-size: 1.75rem; font-weight: 800; color: #ffffff; margin-bottom: 0.25rem;">مجموعاتي وكورساتي</h2>
                        <p style="font-size: 1rem; color: rgba(255, 255, 255, 0.9); margin-bottom: 0;">استعراض المجموعات والمقررات المسجل بها وجدول المواعيد</p>
                    </div>
                </div>
            </div>

            <!-- List of Courses -->
            <div class="course-grid spacious">
                @forelse ($activeCourses as $c)
                    <article class="course-card">
                        <div class="course-art {{ $c['color'] }}">
                            <x-student-icon name="book-open" size="30" />
                        </div>
                        <div class="course-body">
                            <span class="course-status status-{{ $c['status'] == 'completed' ? 'completed' : 'active' }}">
                                {{ $c['status'] == 'completed' ? 'مكتمل' : 'قيد الدراسة' }}
                            </span>
                            <h3>{{ $c['title'] }}</h3>
                            <div class="course-meta">
                                <x-student-icon name="users" size="15" /> {{ $c['group'] }} 
                                <span>•</span> 
                                <x-student-icon name="user-round" size="15" /> {{ $c['teacher'] }}
                            </div>
                            <div class="course-meta" style="margin-top:0.3rem;">
                                <x-student-icon name="clock-3" size="15" /> {!! $c['schedule'] !!}
                            </div>
                            <!-- Future extension: Button to go to Course Details -->
                            <button class="btn btn-outline full" style="margin-top: 1rem; border-color: var(--border-color);" disabled>
                                التفاصيل (قريباً)
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <x-student-icon name="book-open" size="40" color="var(--text-muted)" />
                        <h3>مافيش كورسات في القسم ده</h3>
                        <p>لم تلتحق بأي كورسات بعد.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>

