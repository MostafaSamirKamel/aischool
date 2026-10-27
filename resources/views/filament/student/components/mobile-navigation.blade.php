@php
    $currentUrl = request()->url();
    $currentPath = request()->path();
    
    // Check if on an active exam page to guarantee test safety
    $isExamPage = request()->is('*my-exams/*/take*') || request()->is('*take-exam*') || str_contains($currentPath, 'take');
    
    // Check active states
    $isDashboard = request()->is('student') || request()->is('student/');
    $isCourses = request()->is('student/my-courses*');
    $isAssignments = request()->is('student/my-assignments*');
    $isExams = request()->is('student/my-exams*') && !$isExamPage;
    $isEnrollments = request()->is('student/student-enrollments*');
    $isResources = request()->is('student/student-learning-resources*');
    $isAttendance = request()->is('student/my-attendance*');
    $isPayments = request()->is('student/my-payments*');
    $isMessages = request()->is('student/group-messages*');
    $isReports = request()->is('student/student-reports*');
    $isReviews = request()->is('student/instructor-reviews*');

    // Is any drawer item active?
    $isDrawerActive = $isEnrollments || $isResources || $isAttendance || $isPayments || $isMessages || $isReports || $isReviews;

    $student = auth()->user()?->student;
    $studentName = auth()->user()?->name ?? 'طالب AI School';
    $firstName = explode(' ', trim($studentName))[0] ?? 'طالب';
@endphp

@if (!$isExamPage)
<div x-data="{ drawerOpen: false }" class="ai-student-mobile-suite" dir="rtl">
    <!-- 1. LEFT-SIDE EXPANDABLE ICON MENU TRIGGER (Mobile Smartphone Only) -->
    <button 
        type="button" 
        @click="drawerOpen = true" 
        class="ai-student-drawer-trigger"
        aria-label="فتح قائمة الأقسام الإضافية"
        title="جميع أقسام بوابة الطالب"
    >
        <span class="trigger-icon-wrap">
            <svg class="trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
            @if ($isDrawerActive)
                <span class="trigger-active-dot"></span>
            @endif
        </span>
        <span class="trigger-label">الأقسام</span>
    </button>

    <!-- 2. LEFT-SIDE MOBILE DRAWER (Remaining Student Sections) -->
    <div 
        x-show="drawerOpen" 
        x-cloak
        class="ai-student-drawer-overlay"
        @keydown.escape.window="drawerOpen = false"
    >
        <!-- Backdrop -->
        <div 
            class="ai-student-drawer-backdrop"
            @click="drawerOpen = false"
            x-show="drawerOpen"
            x-transition:enter="transition-opacity ease-out duration-250"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        <!-- Slide-out Drawer Panel -->
        <aside 
            class="ai-student-drawer-panel"
            x-show="drawerOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-250 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        >
            <!-- Drawer Header -->
            <div class="ai-drawer-header">
                <div class="ai-drawer-brand">
                    <div class="ai-brand-badge">
                        <svg viewBox="0 0 192 192" class="ai-logo-svg" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="192" height="192" rx="44" fill="#364FAC"/>
                            <path d="M36 129V64h17l42 48V64h19v65H96L55 83v46z" fill="white"/>
                            <path d="M129 64h27v18h-27zm0 31h27v34h-27z" fill="#58BA47"/>
                        </svg>
                    </div>
                    <div class="ai-brand-text">
                        <strong>AI SCHOOL</strong>
                        <small>بوابة الطالب الذكية</small>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="drawerOpen = false" 
                    class="ai-drawer-close-btn"
                    aria-label="إغلاق القائمة"
                >
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Student User Card in Drawer -->
            <div class="ai-drawer-student-card">
                <div class="ai-student-avatar">
                    {{ mb_substr($firstName, 0, 1, 'utf-8') }}
                </div>
                <div class="ai-student-info">
                    <span class="ai-student-welcome">أهلاً بك،</span>
                    <strong class="ai-student-fullname">{{ $studentName }}</strong>
                    @if ($student?->student_code)
                        <span class="ai-student-code">كود: {{ $student->student_code }}</span>
                    @endif
                </div>
            </div>

            <!-- Drawer Navigation Items: Remaining Student Sections -->
            <div class="ai-drawer-content">
                <div class="ai-drawer-section-title">الأقسام التعليمية والإدارية</div>
                <nav class="ai-drawer-nav">
                    <!-- تسجيلاتي -->
                    <a 
                        href="{{ url('/student/student-enrollments') }}" 
                        class="ai-drawer-link {{ $isEnrollments ? 'active' : '' }}"
                    >
                        <span class="ai-link-icon-box violet">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="4" width="14" height="18" rx="2"></rect>
                                <rect x="9" y="2" width="6" height="4" rx="1"></rect>
                                <path d="m9 14 2 2 4-4"></path>
                            </svg>
                        </span>
                        <div class="ai-link-text">
                            <strong>تسجيلاتي</strong>
                            <small>تفاصيل الكورسات والرسوم</small>
                        </div>
                        @if ($isEnrollments)
                            <span class="ai-active-indicator"></span>
                        @endif
                    </a>

                    <!-- المصادر التعليمية -->
                    <a 
                        href="{{ url('/student/student-learning-resources') }}" 
                        class="ai-drawer-link {{ $isResources ? 'active' : '' }}"
                    >
                        <span class="ai-link-icon-box blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v10H3z"></path>
                            </svg>
                        </span>
                        <div class="ai-link-text">
                            <strong>المصادر التعليمية</strong>
                            <small>الملفات والمراجع والكتب</small>
                        </div>
                        @if ($isResources)
                            <span class="ai-active-indicator"></span>
                        @endif
                    </a>

                    <!-- سجل الحضور -->
                    <a 
                        href="{{ url('/student/my-attendance') }}" 
                        class="ai-drawer-link {{ $isAttendance ? 'active' : '' }}"
                    >
                        <span class="ai-link-icon-box mint">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                                <path d="M16 3v4M8 3v4M3 10h18m5 6 2 2 4-4"></path>
                            </svg>
                        </span>
                        <div class="ai-link-text">
                            <strong>سجل الحضور</strong>
                            <small>متابعة السيشنات والانتظام</small>
                        </div>
                        @if ($isAttendance)
                            <span class="ai-active-indicator"></span>
                        @endif
                    </a>

                    <!-- مدفوعاتي -->
                    <a 
                        href="{{ url('/student/my-payments') }}" 
                        class="ai-drawer-link {{ $isPayments ? 'active' : '' }}"
                    >
                        <span class="ai-link-icon-box peach">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="6" width="20" height="15" rx="3"></rect>
                                <path d="M2 10h20m-5 6h2M5 6V4a2 2 0 0 1 2-2h11"></path>
                            </svg>
                        </span>
                        <div class="ai-link-text">
                            <strong>مدفوعاتي</strong>
                            <small>سجل الأقساط والاشتراكات</small>
                        </div>
                        @if ($isPayments)
                            <span class="ai-active-indicator"></span>
                        @endif
                    </a>

                    <!-- رسائل المجموعة -->
                    <a 
                        href="{{ url('/student/group-messages') }}" 
                        class="ai-drawer-link {{ $isMessages ? 'active' : '' }}"
                    >
                        <span class="ai-link-icon-box sky">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </span>
                        <div class="ai-link-text">
                            <strong>رسائل المجموعة</strong>
                            <small>إعلانات المدرسين والمجموعات</small>
                        </div>
                        @if ($isMessages)
                            <span class="ai-active-indicator"></span>
                        @endif
                    </a>

                    <!-- تقاريري -->
                    <a 
                        href="{{ url('/student/student-reports') }}" 
                        class="ai-drawer-link {{ $isReports ? 'active' : '' }}"
                    >
                        <span class="ai-link-icon-box green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 21V14h4v7M10 21V9h4v12M16 21V3h4v18"></path>
                            </svg>
                        </span>
                        <div class="ai-link-text">
                            <strong>تقاريري</strong>
                            <small>التقرير الأكاديمي والبياني</small>
                        </div>
                        @if ($isReports)
                            <span class="ai-active-indicator"></span>
                        @endif
                    </a>

                    <!-- تقييمات المدرسين -->
                    <a 
                        href="{{ url('/student/instructor-reviews') }}" 
                        class="ai-drawer-link {{ $isReviews ? 'active' : '' }}"
                    >
                        <span class="ai-link-icon-box gold">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12 2 3.1 6.5 7.1 1-5.1 5 1.2 7-6.3-3.3-6.3 3.3 1.2-7-5.1-5 7.1-1z"></path>
                            </svg>
                        </span>
                        <div class="ai-link-text">
                            <strong>تقييمات المدرسين</strong>
                            <small>شارك رأيك في التجربة</small>
                        </div>
                        @if ($isReviews)
                            <span class="ai-active-indicator"></span>
                        @endif
                    </a>
                </nav>

                <div class="ai-drawer-divider"></div>

                <!-- Primary Shortcuts Inside Drawer (Convenience) -->
                <div class="ai-drawer-section-title">الوصول السريع</div>
                <div class="ai-drawer-shortcuts">
                    <a href="{{ url('/student') }}" class="ai-shortcut-chip {{ $isDashboard ? 'active' : '' }}">
                        <span>الرئيسية</span>
                    </a>
                    <a href="{{ url('/student/my-courses') }}" class="ai-shortcut-chip {{ $isCourses ? 'active' : '' }}">
                        <span>كورساتي</span>
                    </a>
                    <a href="{{ url('/student/my-assignments') }}" class="ai-shortcut-chip {{ $isAssignments ? 'active' : '' }}">
                        <span>واجباتي</span>
                    </a>
                    <a href="{{ url('/student/my-exams') }}" class="ai-shortcut-chip {{ $isExams ? 'active' : '' }}">
                        <span>امتحاناتي</span>
                    </a>
                </div>
            </div>

            <!-- Drawer Footer Branding -->
            <div class="ai-drawer-footer">
                <span class="ai-footer-logo-dot"></span>
                <span>AI School Management Platform</span>
            </div>
        </aside>
    </div>

    <!-- 3. FIXED BOTTOM NAVIGATION BAR (Mobile Smartphone Only) -->
    <nav class="ai-student-bottom-bar" aria-label="شريط التنقل الرئيسي للتطبيق">
        <div class="ai-bottom-nav-inner">
            <!-- 1. الرئيسية (Dashboard) -->
            <a 
                href="{{ url('/student') }}" 
                class="ai-bottom-tab {{ $isDashboard ? 'active' : '' }}"
                aria-label="الرئيسية"
            >
                <span class="ai-tab-icon-wrapper">
                    <svg viewBox="0 0 24 24" class="ai-tab-svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>
                    </svg>
                </span>
                <span class="ai-tab-label">الرئيسية</span>
                @if ($isDashboard)
                    <span class="ai-active-glow"></span>
                @endif
            </a>

            <!-- 2. مجموعاتي / كورساتي (My Courses) -->
            <a 
                href="{{ url('/student/my-courses') }}" 
                class="ai-bottom-tab {{ $isCourses ? 'active' : '' }}"
                aria-label="كورساتي"
            >
                <span class="ai-tab-icon-wrapper">
                    <svg viewBox="0 0 24 24" class="ai-tab-svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7c-3-2.5-6-3-10-2v15c4-1 7-.5 10 2 3-2.5 6-3 10-2V5c-4-1-7-.5-10 2Zm0 0v15"/>
                    </svg>
                </span>
                <span class="ai-tab-label">كورساتي</span>
                @if ($isCourses)
                    <span class="ai-active-glow"></span>
                @endif
            </a>

            <!-- 3. واجباتي (My Assignments) -->
            <a 
                href="{{ url('/student/my-assignments') }}" 
                class="ai-bottom-tab {{ $isAssignments ? 'active' : '' }}"
                aria-label="واجباتي"
            >
                <span class="ai-tab-icon-wrapper">
                    <svg viewBox="0 0 24 24" class="ai-tab-svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="3" width="15" height="18" rx="2"></rect>
                        <path d="M8 3v18M12 8h5M12 12h3m2 5 5-5"></path>
                    </svg>
                </span>
                <span class="ai-tab-label">واجباتي</span>
                @if ($isAssignments)
                    <span class="ai-active-glow"></span>
                @endif
            </a>

            <!-- 4. امتحاناتي (My Exams) -->
            <a 
                href="{{ url('/student/my-exams') }}" 
                class="ai-bottom-tab {{ $isExams ? 'active' : '' }}"
                aria-label="امتحاناتي"
            >
                <span class="ai-tab-icon-wrapper">
                    <svg viewBox="0 0 24 24" class="ai-tab-svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="3"></rect>
                        <path d="m7 12 3 3 7-7"></path>
                    </svg>
                </span>
                <span class="ai-tab-label">امتحاناتي</span>
                @if ($isExams)
                    <span class="ai-active-glow"></span>
                @endif
            </a>

            <!-- 5. المزيد / الأقسام (More Drawer Trigger) -->
            <button 
                type="button" 
                @click="drawerOpen = !drawerOpen" 
                class="ai-bottom-tab {{ $isDrawerActive ? 'active' : '' }}"
                aria-label="المزيد من الأقسام"
            >
                <span class="ai-tab-icon-wrapper special-more">
                    <svg viewBox="0 0 24 24" class="ai-tab-svg" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                    </svg>
                </span>
                <span class="ai-tab-label">المزيد</span>
                @if ($isDrawerActive)
                    <span class="ai-active-glow"></span>
                @endif
            </button>
        </div>
    </nav>
</div>
@endif

<style>
/* ==========================================================================
   AI SCHOOL — STUDENT MOBILE APP NAVIGATION & DRAWER STYLES
   Strictly active on smartphone viewports (<= 820px).
   Completely hidden on desktop (> 820px).
   ========================================================================== */

/* Hide on desktop */
@media (min-width: 821px) {
    .ai-student-mobile-suite,
    .ai-student-drawer-trigger,
    .ai-student-drawer-overlay,
    .ai-student-bottom-bar {
        display: none !important;
    }
}

/* Smartphone Presentation */
@media (max-width: 820px) {
    /* Push content upward so nothing is hidden behind the fixed bottom bar */
    body.fi-body,
    .fi-main,
    .fi-page {
        padding-bottom: calc(78px + env(safe-area-inset-bottom, 0px)) !important;
    }

    /* 1. LEFT-SIDE EXPANDABLE MENU TRIGGER */
    .ai-student-drawer-trigger {
        position: fixed;
        bottom: calc(88px + env(safe-area-inset-bottom, 0px));
        inset-inline-start: 16px;
        z-index: 899;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px 8px 10px;
        background: #364FAC;
        color: #ffffff;
        border: 2px solid rgba(255, 255, 255, 0.25);
        border-radius: 999px;
        box-shadow: 0 8px 24px -2px rgba(54, 79, 172, 0.45), 0 3px 8px rgba(0, 0, 0, 0.12);
        cursor: pointer;
        font-family: 'Cairo', system-ui, sans-serif;
        font-size: 11px;
        font-weight: 700;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.2s ease, box-shadow 0.2s ease;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    .ai-student-drawer-trigger:hover,
    .ai-student-drawer-trigger:active {
        transform: scale(1.05);
        background: #293C85;
        box-shadow: 0 10px 28px rgba(54, 79, 172, 0.55);
    }

    .trigger-icon-wrap {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .trigger-icon {
        width: 17px;
        height: 17px;
        color: #ffffff;
    }

    .trigger-active-dot {
        position: absolute;
        top: -2px;
        right: -3px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #58BA47;
        border: 1.5px solid #364FAC;
    }

    .trigger-label {
        letter-spacing: -0.01em;
        white-space: nowrap;
    }

    /* 2. DRAWER OVERLAY & PANEL */
    .ai-student-drawer-overlay {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        justify-content: flex-start;
    }

    .ai-student-drawer-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 1;
    }

    .ai-student-drawer-panel {
        position: relative;
        z-index: 2;
        width: min(84vw, 320px);
        height: 100%;
        background: #ffffff;
        box-shadow: -4px 0 30px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        font-family: 'Cairo', system-ui, sans-serif;
        color: #18243D;
        text-align: right;
    }

    .dark .ai-student-drawer-panel {
        background: #1E293B;
        color: #F8FAFC;
        box-shadow: -4px 0 30px rgba(0, 0, 0, 0.5);
    }

    /* Drawer Header */
    .ai-drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 18px 14px;
        border-bottom: 1px solid #E5EAF3;
        background: #F8FAFD;
    }

    .dark .ai-drawer-header {
        background: #162032;
        border-bottom-color: #334155;
    }

    .ai-drawer-brand {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ai-brand-badge {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(54, 79, 172, 0.25);
    }

    .ai-logo-svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    .ai-brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
    }

    .ai-brand-text strong {
        font-size: 15px;
        font-weight: 900;
        color: #364FAC;
        letter-spacing: -0.02em;
    }

    .dark .ai-brand-text strong {
        color: #7B94FF;
    }

    .ai-brand-text small {
        font-size: 10px;
        font-weight: 600;
        color: #64748B;
    }

    .ai-drawer-close-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #E5EAF3;
        color: #64748B;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }

    .dark .ai-drawer-close-btn {
        background: #334155;
        color: #94A3B8;
    }

    .ai-drawer-close-btn:hover {
        background: #d8e0ec;
        color: #18243D;
    }

    /* Student User Card in Drawer */
    .ai-drawer-student-card {
        margin: 14px 16px;
        padding: 12px 14px;
        background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
        border: 1px solid #D5E0FA;
        border-radius: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dark .ai-drawer-student-card {
        background: linear-gradient(135deg, #25334D 0%, #1A2538 100%);
        border-color: #374A69;
    }

    .ai-student-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #364FAC, #293C85);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(54, 79, 172, 0.3);
    }

    .ai-student-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
    }

    .ai-student-welcome {
        font-size: 10px;
        color: #64748B;
        font-weight: 600;
    }

    .ai-student-fullname {
        font-size: 13px;
        font-weight: 800;
        color: #18243D;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dark .ai-student-fullname {
        color: #F8FAFC;
    }

    .ai-student-code {
        font-size: 10px;
        font-weight: 700;
        color: #364FAC;
        margin-top: 1px;
    }

    .dark .ai-student-code {
        color: #8CA4FF;
    }

    /* Drawer Navigation Links */
    .ai-drawer-content {
        flex: 1;
        padding: 4px 14px 20px;
        overflow-y: auto;
    }

    .ai-drawer-section-title {
        font-size: 11px;
        font-weight: 800;
        color: #64748B;
        margin: 14px 6px 8px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .ai-drawer-nav {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .ai-drawer-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        text-decoration: none;
        color: #18243D;
        background: transparent;
        transition: all 0.2s ease;
        position: relative;
    }

    .dark .ai-drawer-link {
        color: #E2E8F0;
    }

    .ai-drawer-link:hover {
        background: #F5F7FC;
        transform: translateX(-2px);
    }

    .dark .ai-drawer-link:hover {
        background: #28364F;
    }

    .ai-drawer-link.active {
        background: #EEF2FF;
        color: #364FAC;
        font-weight: 800;
    }

    .dark .ai-drawer-link.active {
        background: #25334D;
        color: #7B94FF;
    }

    .ai-link-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .ai-link-icon-box svg {
        width: 18px;
        height: 18px;
    }

    .ai-link-icon-box.violet { background: #EEF2FF; color: #364FAC; }
    .ai-link-icon-box.blue   { background: #E0F2FE; color: #0284C7; }
    .ai-link-icon-box.mint   { background: #EAF7E6; color: #58BA47; }
    .ai-link-icon-box.peach  { background: #FFEDD5; color: #EA580C; }
    .ai-link-icon-box.sky    { background: #F0F9FF; color: #0369A1; }
    .ai-link-icon-box.green  { background: #DCFCE7; color: #16A34A; }
    .ai-link-icon-box.gold   { background: #FEF3C7; color: #D97706; }

    .dark .ai-link-icon-box.violet { background: #2A3654; color: #8CA4FF; }
    .dark .ai-link-icon-box.blue   { background: #1C3347; color: #38BDF8; }
    .dark .ai-link-icon-box.mint   { background: #1D3B27; color: #6EE7B7; }
    .dark .ai-link-icon-box.peach  { background: #3B2A22; color: #FDBA74; }
    .dark .ai-link-icon-box.sky    { background: #183042; color: #7DD3FC; }
    .dark .ai-link-icon-box.green  { background: #1A3828; color: #86EFAC; }
    .dark .ai-link-icon-box.gold   { background: #3B311E; color: #FDE047; }

    .ai-link-text {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    .ai-link-text strong {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
    }

    .ai-link-text small {
        font-size: 10px;
        color: #64748B;
        margin-top: 1px;
    }

    .ai-active-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #58BA47;
        box-shadow: 0 0 6px rgba(88, 186, 71, 0.8);
    }

    .ai-drawer-divider {
        height: 1px;
        background: #E5EAF3;
        margin: 14px 6px;
    }

    .dark .ai-drawer-divider {
        background: #334155;
    }

    .ai-drawer-shortcuts {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 4px 6px;
    }

    .ai-shortcut-chip {
        padding: 6px 12px;
        border-radius: 8px;
        background: #F5F7FC;
        border: 1px solid #E5EAF3;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
    }

    .dark .ai-shortcut-chip {
        background: #25334D;
        border-color: #374A69;
        color: #94A3B8;
    }

    .ai-shortcut-chip.active {
        background: #364FAC;
        border-color: #364FAC;
        color: #ffffff;
    }

    .ai-drawer-footer {
        padding: 12px 18px;
        border-top: 1px solid #E5EAF3;
        font-size: 10px;
        color: #94A3B8;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }

    .dark .ai-drawer-footer {
        border-top-color: #334155;
    }

    .ai-footer-logo-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #58BA47;
    }

    /* 3. FIXED BOTTOM NAVIGATION BAR */
    .ai-student-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 900;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-top: 1px solid #E5EAF3;
        box-shadow: 0 -4px 20px rgba(24, 36, 61, 0.08);
        height: calc(64px + env(safe-area-inset-bottom, 0px));
        padding-bottom: env(safe-area-inset-bottom, 0px);
        font-family: 'Cairo', system-ui, sans-serif;
    }

    .dark .ai-student-bottom-bar {
        background: rgba(30, 41, 59, 0.96);
        border-top-color: #334155;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.35);
    }

    .ai-bottom-nav-inner {
        height: 64px;
        max-width: 600px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        align-items: center;
        padding: 0 4px;
    }

    .ai-bottom-tab {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        text-decoration: none;
        background: transparent;
        border: none;
        cursor: pointer;
        color: #64748B;
        transition: color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 4px 2px;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    .dark .ai-bottom-tab {
        color: #94A3B8;
    }

    .ai-tab-icon-wrapper {
        position: relative;
        width: 38px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .ai-tab-svg {
        width: 21px;
        height: 21px;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .ai-tab-label {
        font-size: 10px;
        font-weight: 700;
        margin-top: 2px;
        letter-spacing: -0.01em;
        line-height: 1.1;
        transition: color 0.2s;
    }

    /* Active Tab State */
    .ai-bottom-tab.active {
        color: #364FAC;
    }

    .dark .ai-bottom-tab.active {
        color: #7B94FF;
    }

    .ai-bottom-tab.active .ai-tab-icon-wrapper {
        background: #EEF2FF;
        color: #364FAC;
        transform: translateY(-2px);
    }

    .dark .ai-bottom-tab.active .ai-tab-icon-wrapper {
        background: #25334D;
        color: #7B94FF;
    }

    .ai-bottom-tab.active .ai-tab-svg {
        transform: scale(1.1);
    }

    .ai-bottom-tab.active .ai-tab-label {
        font-weight: 800;
    }

    .ai-active-glow {
        position: absolute;
        top: 2px;
        width: 16px;
        height: 3px;
        border-radius: 2px;
        background: #58BA47;
    }

    /* Special More Button Pulse Effect */
    .special-more {
        background: #F5F7FC;
    }

    .dark .special-more {
        background: #28364F;
    }
}
</style>
