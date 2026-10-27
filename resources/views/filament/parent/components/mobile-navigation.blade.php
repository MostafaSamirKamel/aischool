@php
    $currentUrl = request()->url();
    $currentPath = request()->path();
    
    // Check active states for Parent Portal
    $isDashboard = request()->is('parent') || request()->is('parent/');
    $isCourses = request()->is('parent/my-courses*');
    $isAttendance = request()->is('parent/my-attendance*');
    $isPayments = request()->is('parent/my-payments*');
    $isReports = request()->is('parent/student-reports*');
    $isResources = request()->is('parent/view-learning-resource*');
    $isMessages = request()->is('parent/group-messages*');

    // Is any drawer item active?
    $isDrawerActive = $isResources || $isMessages;

    $parentUser = auth()->user();
    $parentName = $parentUser?->name ?? 'ولي أمر AI School';
@endphp

<div x-data="{ drawerOpen: false }" class="ai-parent-mobile-suite" dir="rtl">
    <!-- 1. LEFT-SIDE EXPANDABLE ICON MENU TRIGGER (Mobile Smartphone Only) -->
    <button 
        type="button" 
        @click="drawerOpen = true" 
        class="ai-parent-drawer-trigger"
        aria-label="فتح قائمة الأقسام الإضافية"
        title="جميع أقسام بوابة ولي الأمر"
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
        <span class="trigger-label">المزيد</span>
    </button>

    <!-- 2. LEFT-SIDE MOBILE DRAWER -->
    <div 
        x-show="drawerOpen" 
        x-cloak
        class="ai-parent-drawer-overlay"
        @keydown.escape.window="drawerOpen = false"
    >
        <!-- Backdrop -->
        <div 
            class="ai-parent-drawer-backdrop"
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
            class="ai-parent-drawer-panel"
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
                        <small>بوابة ولي الأمر الذكية</small>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="drawerOpen = false" 
                    class="ai-drawer-close"
                    aria-label="إغلاق القائمة"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Parent Profile Card Inside Drawer -->
            <div class="ai-drawer-profile-card">
                <div class="ai-profile-avatar">
                    <span>👨‍👦</span>
                </div>
                <div class="ai-profile-info">
                    <span class="ai-greeting">مرحباً بك</span>
                    <strong class="ai-parent-name">{{ $parentName }}</strong>
                </div>
            </div>

            <!-- Drawer Links List -->
            <div class="ai-drawer-body">
                <div class="ai-drawer-section-title">الأقسام والخدمات</div>
                <nav class="ai-drawer-nav">
                    <!-- رسائل المجموعات -->
                    <a 
                        href="{{ url('/parent/group-messages') }}" 
                        class="ai-drawer-link {{ $isMessages ? 'active' : '' }}"
                    >
                        <span class="ai-link-icon-box sky">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </span>
                        <div class="ai-link-text">
                            <strong>رسائل وإعلانات المدرسة</strong>
                            <small>متابعة التنبيهات والإعلانات</small>
                        </div>
                        @if ($isMessages)
                            <span class="ai-active-indicator"></span>
                        @endif
                    </a>

                    <!-- مصادر التعلم -->
                    <a 
                        href="{{ url('/parent/view-learning-resource') }}" 
                        class="ai-drawer-link {{ $isResources ? 'active' : '' }}"
                    >
                        <span class="ai-link-icon-box purple">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </span>
                        <div class="ai-link-text">
                            <strong>المصادر التعليمية</strong>
                            <small>الملفات والمذكرات الإرشادية</small>
                        </div>
                        @if ($isResources)
                            <span class="ai-active-indicator"></span>
                        @endif
                    </a>
                </nav>

                <div class="ai-drawer-divider"></div>

                <!-- Primary Shortcuts Inside Drawer -->
                <div class="ai-drawer-section-title">الوصول السريع</div>
                <div class="ai-drawer-shortcuts">
                    <a href="{{ url('/parent') }}" class="ai-shortcut-chip {{ $isDashboard ? 'active' : '' }}">
                        <span>الرئيسية</span>
                    </a>
                    <a href="{{ url('/parent/my-courses') }}" class="ai-shortcut-chip {{ $isCourses ? 'active' : '' }}">
                        <span>مقررات الأبناء</span>
                    </a>
                    <a href="{{ url('/parent/my-attendance') }}" class="ai-shortcut-chip {{ $isAttendance ? 'active' : '' }}">
                        <span>سجل الحضور</span>
                    </a>
                    <a href="{{ url('/parent/my-payments') }}" class="ai-shortcut-chip {{ $isPayments ? 'active' : '' }}">
                        <span>المدفوعات</span>
                    </a>
                </div>
            </div>

            <!-- Drawer Footer Branding -->
            <div class="ai-drawer-footer">
                <span class="ai-footer-logo-dot"></span>
                <span>AI School — بوابة ولي الأمر</span>
            </div>
        </aside>
    </div>

    <!-- 3. FIXED BOTTOM NAVIGATION BAR (Mobile Smartphone Only) -->
    <nav class="ai-parent-bottom-bar" aria-label="شريط التنقل الرئيسي لبوابة ولي الأمر">
        <div class="ai-bottom-nav-inner">
            <!-- 1. الرئيسية (Dashboard) -->
            <a 
                href="{{ url('/parent') }}" 
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

            <!-- 2. الأبناء / المقررات (My Courses) -->
            <a 
                href="{{ url('/parent/my-courses') }}" 
                class="ai-bottom-tab {{ $isCourses ? 'active' : '' }}"
                aria-label="المقررات"
            >
                <span class="ai-tab-icon-wrapper">
                    <svg viewBox="0 0 24 24" class="ai-tab-svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7c-3-2.5-6-3-10-2v15c4-1 7-.5 10 2 3-2.5 6-3 10-2V5c-4-1-7-.5-10 2Zm0 0v15"/>
                    </svg>
                </span>
                <span class="ai-tab-label">المقررات</span>
                @if ($isCourses)
                    <span class="ai-active-glow"></span>
                @endif
            </a>

            <!-- 3. الحضور (My Attendance) -->
            <a 
                href="{{ url('/parent/my-attendance') }}" 
                class="ai-bottom-tab {{ $isAttendance ? 'active' : '' }}"
                aria-label="الحضور"
            >
                <span class="ai-tab-icon-wrapper">
                    <svg viewBox="0 0 24 24" class="ai-tab-svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </span>
                <span class="ai-tab-label">الحضور</span>
                @if ($isAttendance)
                    <span class="ai-active-glow"></span>
                @endif
            </a>

            <!-- 4. المدفوعات (My Payments) -->
            <a 
                href="{{ url('/parent/my-payments') }}" 
                class="ai-bottom-tab {{ $isPayments ? 'active' : '' }}"
                aria-label="المدفوعات"
            >
                <span class="ai-tab-icon-wrapper">
                    <svg viewBox="0 0 24 24" class="ai-tab-svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                </span>
                <span class="ai-tab-label">المدفوعات</span>
                @if ($isPayments)
                    <span class="ai-active-glow"></span>
                @endif
            </a>

            <!-- 5. التقارير (Reports) -->
            <a 
                href="{{ url('/parent/student-reports') }}" 
                class="ai-bottom-tab {{ $isReports ? 'active' : '' }}"
                aria-label="التقارير"
            >
                <span class="ai-tab-icon-wrapper">
                    <svg viewBox="0 0 24 24" class="ai-tab-svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 21V14h4v7M10 21V9h4v12M16 21V3h4v18"></path>
                    </svg>
                </span>
                <span class="ai-tab-label">التقارير</span>
                @if ($isReports)
                    <span class="ai-active-glow"></span>
                @endif
            </a>
        </div>
    </nav>
</div>

<style>
/* ==========================================================================
   AI SCHOOL — PARENT MOBILE EXPERIENCE STYLES
   Matches the Student Mobile App-Like Design Language Exactly
   ========================================================================== */
.ai-parent-mobile-suite {
    font-family: 'Cairo', 'Tajawal', system-ui, sans-serif !important;
}

/* 1. Trigger Button (Desktop Hidden, Mobile Visible) */
.ai-parent-drawer-trigger {
    display: none;
}

@media (max-width: 768px) {
    .ai-parent-drawer-trigger {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: fixed;
        bottom: 84px;
        left: 14px;
        z-index: 998;
        background: #364FAC;
        color: #FFFFFF;
        width: 52px;
        height: 52px;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(54, 79, 172, 0.4);
        border: 2px solid rgba(255, 255, 255, 0.2);
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
    }

    .ai-parent-drawer-trigger:hover {
        transform: scale(1.05);
    }

    .ai-parent-drawer-trigger .trigger-icon {
        width: 22px;
        height: 22px;
    }

    .ai-parent-drawer-trigger .trigger-label {
        font-size: 0.62rem;
        font-weight: 700;
        margin-top: 1px;
    }

    .ai-parent-drawer-trigger .trigger-active-dot {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #58BA47;
        border: 2px solid #364FAC;
    }
}

/* 2. Drawer Panel Overlay & Animation */
.ai-parent-drawer-overlay {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
}

.ai-parent-drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.ai-parent-drawer-panel {
    position: relative;
    width: 310px;
    max-width: 86vw;
    height: 100%;
    background: #FFFFFF;
    box-shadow: 4px 0 25px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    z-index: 10001;
    overflow-y: auto;
}

.ai-drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.25rem 1rem;
    border-bottom: 1px solid #E2E8F0;
    background: #F8FAFC;
}

.ai-drawer-brand {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.ai-brand-badge .ai-logo-svg {
    width: 36px;
    height: 36px;
    border-radius: 10px;
}

.ai-brand-text strong {
    display: block;
    font-size: 1rem;
    font-weight: 850;
    color: #18243D;
    line-height: 1.1;
}

.ai-brand-text small {
    display: block;
    font-size: 0.72rem;
    color: #64748B;
}

.ai-drawer-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748B;
    cursor: pointer;
}

.ai-drawer-close svg {
    width: 18px;
    height: 18px;
}

/* Parent Profile Card */
.ai-drawer-profile-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: #EEF2FF;
    border-bottom: 1px solid #E2E8F0;
}

.ai-profile-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    box-shadow: 0 2px 6px rgba(54, 79, 172, 0.15);
}

.ai-profile-info .ai-greeting {
    display: block;
    font-size: 0.72rem;
    color: #64748B;
}

.ai-profile-info .ai-parent-name {
    display: block;
    font-size: 0.92rem;
    font-weight: 750;
    color: #18243D;
}

/* Drawer Body */
.ai-drawer-body {
    padding: 1rem 1.15rem;
    flex: 1;
}

.ai-drawer-section-title {
    font-size: 0.74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94A3B8;
    margin-bottom: 0.65rem;
    padding-inline: 0.25rem;
}

.ai-drawer-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.75rem;
    border-radius: 12px;
    color: #18243D;
    text-decoration: none;
    margin-bottom: 0.35rem;
    transition: all 0.18s ease;
    border: 1px solid transparent;
}

.ai-drawer-link:hover,
.ai-drawer-link.active {
    background: #EEF2FF;
    color: #364FAC;
    border-color: rgba(54, 79, 172, 0.15);
}

.ai-link-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-link-icon-box.sky {
    background: #F0F9FF;
    color: #0284C7;
}

.ai-link-icon-box.purple {
    background: #F5F3FF;
    color: #7C3AED;
}

.ai-link-icon-box svg {
    width: 18px;
    height: 18px;
}

.ai-link-text strong {
    display: block;
    font-size: 0.86rem;
    font-weight: 700;
}

.ai-link-text small {
    display: block;
    font-size: 0.7rem;
    color: #64748B;
}

.ai-drawer-divider {
    height: 1px;
    background: #E2E8F0;
    margin: 1rem 0;
}

.ai-drawer-shortcuts {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}

.ai-shortcut-chip {
    padding: 0.55rem 0.65rem;
    border-radius: 10px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    text-align: center;
    font-size: 0.78rem;
    font-weight: 650;
    color: #334155;
    text-decoration: none;
    transition: all 0.15s ease;
}

.ai-shortcut-chip:hover,
.ai-shortcut-chip.active {
    background: #364FAC;
    color: #FFFFFF;
    border-color: #364FAC;
}

.ai-drawer-footer {
    padding: 0.85rem 1.25rem;
    border-top: 1px solid #E2E8F0;
    background: #F8FAFC;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #94A3B8;
}

.ai-footer-logo-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #58BA47;
}

/* 3. Fixed Bottom Bar on Mobile Devices */
.ai-parent-bottom-bar {
    display: none;
}

@media (max-width: 768px) {
    .ai-parent-bottom-bar {
        display: block;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 997;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-top: 1px solid #E2E8F0;
        box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.06);
        padding-bottom: env(safe-area-inset-bottom, 6px);
    }

    .ai-bottom-nav-inner {
        display: flex;
        align-items: center;
        justify-content: space-around;
        height: 64px;
        padding-inline: 0.5rem;
    }

    .ai-bottom-tab {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1;
        height: 100%;
        color: #64748B;
        text-decoration: none;
        position: relative;
        transition: color 0.18s ease;
    }

    .ai-bottom-tab.active {
        color: #364FAC;
        font-weight: 750;
    }

    .ai-tab-svg {
        width: 20px;
        height: 20px;
        transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .ai-bottom-tab:hover .ai-tab-svg,
    .ai-bottom-tab.active .ai-tab-svg {
        transform: translateY(-2px);
    }

    .ai-tab-label {
        font-size: 0.68rem;
        margin-top: 2px;
    }

    .ai-active-glow {
        position: absolute;
        top: 0;
        width: 32px;
        height: 3px;
        border-radius: 0 0 4px 4px;
        background: #58BA47;
    }

    /* Extra padding at bottom of pages to prevent bar overlapping content */
    .fi-main,
    .fi-body,
    main {
        padding-bottom: calc(78px + env(safe-area-inset-bottom, 0px)) !important;
    }
}

/* ==========================================================================
   DARK MODE SUPPORT FOR PARENT MOBILE NAVIGATION
   ========================================================================== */
.dark .ai-parent-drawer-panel {
    background: #172033;
    color: #F1F5F9;
    box-shadow: -4px 0 30px rgba(0, 0, 0, 0.5);
}

.dark .ai-drawer-header {
    background: #0F172A;
    border-bottom-color: #334155;
}

.dark .ai-brand-text strong {
    color: #38BDF8;
}

.dark .ai-brand-text small {
    color: #94A3B8;
}

.dark .ai-drawer-close {
    background: #1E293B;
    border-color: #334155;
    color: #CBD5E1;
}

.dark .ai-drawer-profile-card {
    background: #1E293B;
    border-bottom-color: #334155;
}

.dark .ai-profile-avatar {
    background: #172033;
    color: #38BDF8;
}

.dark .ai-profile-info .ai-greeting {
    color: #94A3B8;
}

.dark .ai-profile-info .ai-parent-name {
    color: #F1F5F9;
}

.dark .ai-drawer-link {
    color: #CBD5E1;
}

.dark .ai-drawer-link:hover,
.dark .ai-drawer-link.active {
    background: #1E293B;
    color: #38BDF8;
    border-color: rgba(56, 189, 248, 0.25);
}

.dark .ai-link-icon-box.sky {
    background: #0C4A6E;
    color: #38BDF8;
}

.dark .ai-link-icon-box.purple {
    background: #4C1D95;
    color: #C084FC;
}

.dark .ai-link-text small {
    color: #94A3B8;
}

.dark .ai-drawer-divider {
    background: #334155;
}

.dark .ai-shortcut-chip {
    background: #1E293B;
    border-color: #334155;
    color: #CBD5E1;
}

.dark .ai-shortcut-chip:hover,
.dark .ai-shortcut-chip.active {
    background: #2563EB;
    border-color: #2563EB;
    color: #FFFFFF;
}

.dark .ai-drawer-footer {
    background: #0F172A;
    border-top-color: #334155;
    color: #64748B;
}

.dark .ai-parent-bottom-bar {
    background: rgba(23, 32, 51, 0.96);
    border-top-color: #334155;
    box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.4);
}

.dark .ai-bottom-tab {
    color: #94A3B8;
}

.dark .ai-bottom-tab.active {
    color: #38BDF8;
}
</style>
