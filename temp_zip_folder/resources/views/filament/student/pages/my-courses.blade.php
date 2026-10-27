<x-filament-panels::page>
    <div class="student-theme-wrapper" dir="rtl">
        <link rel="stylesheet" href="{{ asset('css/student-theme-scoped.css') }}">
        
        <div>
            <!-- Header Text -->
            <div class="page-header">
                <h1>كورساتي</h1>
                <p>تابع المجموعات والمحاضرات والتقدم الدراسي</p>
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

