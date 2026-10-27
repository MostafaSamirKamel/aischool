<x-filament-panels::page>
    <div class="student-theme-wrapper" dir="rtl">
        <div class="dashboard">
            <!-- 1. Welcome Row -->
            <div class="welcome-row">
                <div>
                    <div class="eyebrow">رحلتك التعليمية ✨</div>
                    <h1>أهلاً {{ explode(' ', $student->user->name ?? auth()->user()->name)[0] }} <span>👋</span></h1>
                    <p>جاهز تكمل طريقك نحو المستقبل؟</p>
                </div>
                <a href="{{ App\Filament\Student\Pages\MyAttendance::getUrl() }}" class="date-chip" style="text-decoration:none;">
                    <x-student-icon name="calendar-days" size="17"/> {{ now()->translatedFormat('d F') }}
                </a>
            </div>
            
            <!-- 2. Hero Banner -->
            <div class="hero">
                <div class="hero-content">
                    <div class="hero-eyebrow"><x-student-icon name="sparkles" size="16"/> مكانك المفضل للتعلم</div>
                    <h2>كل يوم مهارة<br/>جديدة، وحلم أقرب!</h2>
                    <p>اتعلم، طبّق، وخلي تقدمك واضح قدامك خطوة بخطوة.</p>
                    <a href="{{ App\Filament\Student\Pages\MyCourses::getUrl() }}" class="hero-cta" style="text-decoration:none;">استكشف كورساتك <x-student-icon name="arrow-left" size="17"/></a>
                </div>
                <div class="hero-deco">
                    <div class="deco-orbit"></div>
                    <div class="deco-main"><x-student-icon name="code-2" size="54"/></div>
                    <div class="deco-float one"><x-student-icon name="star" size="18"/></div>
                    <div class="deco-float two"><x-student-icon name="sparkles" size="20"/></div>
                </div>
            </div>
            
            <!-- 3. Top Stats Grid -->
            <div class="stats-grid">
                <a href="{{ App\Filament\Student\Pages\MyCourses::getUrl() }}" class="stat-card" style="text-decoration:none; color:inherit;">
                    <div class="stat-top"><span class="ico-box violet"><x-student-icon name="book-open" size="19"/></span></div>
                    <strong>{{ $stats['total_courses'] }}</strong><span>الكورسات</span>
                </a>
                <a href="{{ App\Filament\Student\Pages\MyAttendance::getUrl() }}" class="stat-card" style="text-decoration:none; color:inherit;">
                    <div class="stat-top"><span class="ico-box mint"><x-student-icon name="calendar-check" size="19"/></span></div>
                    <strong>{{ $stats['attendance_rate'] }}%</strong><span>نسبة الحضور</span>
                </a>
                <a href="{{ App\Filament\Student\Pages\MyAssignments::getUrl() }}" class="stat-card" style="text-decoration:none; color:inherit;">
                    <div class="stat-top"><span class="ico-box peach"><x-student-icon name="notebook-pen" size="19"/></span></div>
                    <strong>{{ $stats['pending_assignments'] }}</strong><span>واجبات مطلوبة</span>
                </a>
                <a href="{{ App\Filament\Student\Pages\MyExams::getUrl() }}" class="stat-card" style="text-decoration:none; color:inherit;">
                    <div class="stat-top"><span class="ico-box blue"><x-student-icon name="clipboard-check" size="19"/></span></div>
                    <strong>{{ $stats['upcoming_exams'] }}</strong><span>امتحانات قادمة</span>
                </a>
            </div>

            <!-- 4. Two Columns Content -->
            <div class="dashboard-columns">
                <!-- Column 1: Primary -->
                <div class="dashboard-primary">
                    <!-- Section: My Courses -->
                    <div class="section-head">
                        <h2>كورساتي</h2>
                        <a href="{{ App\Filament\Student\Pages\MyCourses::getUrl() }}" class="text-button" style="text-decoration:none;">كل الكورسات <x-student-icon name="chevron-left" size="16"/></a>
                    </div>
                    @if($activeCourses->count())
                        <div class="course-grid">
                            @foreach($activeCourses as $course)
                                <article class="course-card slim">
                                    <div class="course-card-main">
                                        <div class="course-art violet"><x-student-icon name="code-2" size="25"/><span class="spark">✦</span></div>
                                        <div class="course-meta">
                                            <div class="course-card-name">{{ $course->name }}</div>
                                            <div class="meta-line">{{ $course->instructor_name ?? 'المدرس غير محدد' }} <span>•</span> {{ $course->group_name ?? '' }}</div>
                                        </div>
                                        <span class="status success">نشط</span>
                                    </div>
                                    <div class="course-card-footer">
                                        <span><x-student-icon name="calendar-days" size="15"/> التفاصيل</span>
                                        <a href="{{ App\Filament\Student\Pages\MyCourses::getUrl() }}" class="circle-arrow"><x-student-icon name="arrow-left" size="18"/></a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty">
                            <div class="empty-illustration"><x-student-icon name="book-open" size="34"/></div>
                            <strong>لا توجد كورسات نشطة</strong><p>الكورسات المسجلة هتظهر هنا.</p>
                        </div>
                    @endif

                    <!-- Section: Services You Need (خدمات تهمك - كامل الاختصارات السريعة) -->
                    <div class="section-head" style="margin-top: 1.5rem;">
                        <h2>خدمات تهمك</h2>
                        <span class="text-button" style="opacity: 0.75;">كل الخدمات</span>
                    </div>
                    <div class="service-grid">
                        <a href="{{ App\Filament\Student\Pages\StudentEnrollments::getUrl() }}" class="service-tile" style="text-decoration:none; color:inherit;">
                            <span class="ico-box violet"><x-student-icon name="clipboard-check" size="22"/></span>
                            <span>تسجيلاتي</span>
                        </a>
                        <a href="{{ App\Filament\Student\Pages\StudentLearningResources::getUrl() }}" class="service-tile" style="text-decoration:none; color:inherit;">
                            <span class="ico-box blue"><x-student-icon name="folder" size="22"/></span>
                            <span>المصادر التعليمية</span>
                        </a>
                        <a href="{{ App\Filament\Student\Pages\MyPayments::getUrl() }}" class="service-tile" style="text-decoration:none; color:inherit;">
                            <span class="ico-box peach"><x-student-icon name="wallet" size="22"/></span>
                            <span>مدفوعاتي</span>
                        </a>
                        <a href="{{ App\Filament\Student\Pages\MyAttendance::getUrl() }}" class="service-tile" style="text-decoration:none; color:inherit;">
                            <span class="ico-box mint"><x-student-icon name="calendar-check" size="22"/></span>
                            <span>سجل الحضور</span>
                        </a>
                        <a href="{{ App\Filament\Student\Pages\GroupMessages::getUrl() }}" class="service-tile" style="text-decoration:none; color:inherit;">
                            <span class="ico-box blue"><x-student-icon name="message-circle" size="22"/></span>
                            <span>رسائل المجموعة</span>
                        </a>
                        <a href="{{ App\Filament\Student\Pages\StudentReports::getUrl() }}" class="service-tile" style="text-decoration:none; color:inherit;">
                            <span class="ico-box violet"><x-student-icon name="chart-no-axes-column-increasing" size="22"/></span>
                            <span>تقاريري</span>
                        </a>
                        <a href="{{ App\Filament\Student\Resources\InstructorReviewResource::getUrl() }}" class="service-tile" style="text-decoration:none; color:inherit;">
                            <span class="ico-box peach"><x-student-icon name="star" size="22"/></span>
                            <span>تقييمات المدرسين</span>
                        </a>
                        <a href="{{ App\Filament\Student\Pages\MyExams::getUrl() }}" class="service-tile" style="text-decoration:none; color:inherit;">
                            <span class="ico-box mint"><x-student-icon name="check-square" size="22"/></span>
                            <span>امتحاناتي</span>
                        </a>
                    </div>
                </div>

                <!-- Column 2: Secondary Side -->
                <div class="dashboard-secondary">
                    <!-- Next Session -->
                    <div class="section-head"><h2>المحاضرة القادمة</h2></div>
                    <div class="empty">
                        <div class="empty-illustration"><x-student-icon name="calendar-days" size="34"/></div>
                        <strong>لا توجد سيشنات قادمة</strong><p>أي موعد جديد هيظهر هنا تلقائيًا.</p>
                    </div>

                    <!-- Portfolio Banner (بورتفوليو الطالب) -->
                    <div class="section-head" style="margin-top: 1.5rem;"><h2>بورتفوليو الطالب</h2></div>
                    <div class="portfolio" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; border-radius: 18px; padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                        <div style="display:flex; align-items:center; gap: 0.9rem;">
                            <div class="portfolio-symbol" style="width: 46px; height: 46px; border-radius: 14px; background: #fef3c7; color: #d97706; display:flex; align-items:center; justify-content:center;">
                                <x-student-icon name="medal" size="25"/>
                            </div>
                            <div>
                                <strong style="display:block; font-size: 0.95rem; color: #1e293b;">إنجازاتك في مكان واحد</strong>
                                <span style="font-size: 0.8rem; color: #64748b;">الحضور، النتائج، والتقدم في الكورسات.</span>
                            </div>
                        </div>
                        <a href="{{ App\Filament\Student\Pages\StudentReports::getUrl() }}" class="button-outline" style="text-decoration:none; padding: 0.45rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.8rem; font-weight: 700; color: #334155; white-space: nowrap; display:inline-flex; align-items:center; gap: 0.3rem;">
                            عرض تقريري <x-student-icon name="arrow-left" size="14"/>
                        </a>
                    </div>

                    <!-- Upcoming Exams Shortcut (الامتحانات) -->
                    <div class="section-head" style="margin-top: 1.5rem;">
                        <h2>الامتحانات</h2>
                        <a href="{{ App\Filament\Student\Pages\MyExams::getUrl() }}" class="text-button" style="text-decoration:none;">عرض الكل <x-student-icon name="chevron-left" size="16"/></a>
                    </div>
                    <div class="empty">
                        <div class="empty-illustration"><x-student-icon name="clipboard-check" size="34"/></div>
                        <strong>لا يوجد امتحان قريب</strong><p>هتظهر الاختبارات هنا أول ما تتاح.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
