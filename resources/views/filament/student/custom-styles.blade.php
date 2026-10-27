<style>
    /* ==========================================================================
       AI SCHOOL — STUDENT PANEL CUSTOM STYLES
       ========================================================================== */
    
    /* RTL Support for Arabic */
    [dir="rtl"] {
        direction: rtl;
    }

    /* Brand Variables */
    :root {
        --student-primary: #364FAC;
        --student-primary-hover: #293C85;
        --student-accent: #58BA47;
        --student-success: #58BA47;
        --student-warning: #F59E0B;
        --student-danger: #EF4444;
        --student-bg: #F5F7FC;
        --student-card: #FFFFFF;
        --student-line: #E5EAF3;
        --student-text: #18243D;
    }

    /* Topbar Modern AI School Branding */
    .fi-topbar,
    .fi-topbar-nav,
    header.fi-topbar {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        border-bottom: 1px solid #E5EAF3 !important;
        box-shadow: 0 1px 4px rgba(24, 36, 61, 0.03) !important;
    }

    .dark .fi-topbar,
    .dark .fi-topbar-nav,
    .dark header.fi-topbar {
        background: rgba(30, 41, 59, 0.95) !important;
        border-bottom-color: #2A374F !important;
    }

    .fi-logo,
    .fi-topbar .fi-logo,
    .fi-sidebar-header,
    .fi-topbar [href*="student"] span,
    a[href*="student"] > span,
    nav a[href*="student"]:first-child span {
        color: #364FAC !important;
        font-weight: 850 !important;
        font-size: 1.15rem !important;
        letter-spacing: -0.02em !important;
    }

    .dark .fi-logo,
    .dark .fi-topbar .fi-logo,
    .dark .fi-sidebar-header,
    .dark .fi-topbar [href*="student"] span,
    .dark a[href*="student"] > span {
        color: #8CA4FF !important;
    }

    .fi-topbar a,
    .fi-topbar button,
    .fi-topbar span,
    .fi-topbar-item,
    .fi-topbar-item-label {
        color: #18243D !important;
        font-weight: 600 !important;
    }

    .dark .fi-topbar a,
    .dark .fi-topbar button,
    .dark .fi-topbar span,
    .dark .fi-topbar-item,
    .dark .fi-topbar-item-label {
        color: #E2E8F0 !important;
    }

    .fi-topbar svg,
    .fi-topbar-item svg {
        color: #64748B !important;
    }

    .dark .fi-topbar svg,
    .dark .fi-topbar-item svg {
        color: #94A3B8 !important;
    }

    .fi-topbar nav a:hover,
    .fi-topbar button:hover {
        color: #364FAC !important;
        background: #EEF2FF !important;
    }

    .dark .fi-topbar nav a:hover,
    .dark .fi-topbar button:hover {
        color: #8CA4FF !important;
        background: #25334D !important;
    }

    /* Enhanced Card Styling */
    .fi-section-content-ctn {
        background: #FFFFFF !important;
    }

    .dark .fi-section-content-ctn {
        background: #1E293B !important;
    }

    /* Transitions */
    .fi-ta-cell,
    .fi-ta-header-cell {
        transition: all 0.2s ease;
    }

    /* Custom Button for Student Actions */
    .fi-btn-student-primary {
        background-color: var(--student-primary);
        color: white;
        border-radius: 0.65rem;
        padding: 0.55rem 1.15rem;
        font-weight: 750;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(54, 79, 172, 0.25);
    }

    .fi-btn-student-primary:hover {
        background-color: var(--student-primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(54, 79, 172, 0.35);
    }

    /* Widget Enhancements */
    .fi-wi-stats-overview {
        border-radius: 1rem;
        overflow: hidden;
    }

    /* Custom Scrollbar */
    .fi-main::-webkit-scrollbar {
        width: 6px;
    }

    .fi-main::-webkit-scrollbar-track {
        background: #F5F7FC;
    }

    .fi-main::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 4px;
    }

    .fi-main::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }

    /* Enhanced Focus States */
    .fi-input:focus,
    .fi-select:focus {
        border-color: var(--student-primary) !important;
        box-shadow: 0 0 0 3px rgba(54, 79, 172, 0.2) !important;
        outline: none !important;
    }

    /* Table Hover Effects */
    .fi-ta-row:hover {
        background-color: #F8FAFD !important;
        transition: background-color 0.18s ease;
    }

    .dark .fi-ta-row:hover {
        background-color: #25334D !important;
    }
</style>
