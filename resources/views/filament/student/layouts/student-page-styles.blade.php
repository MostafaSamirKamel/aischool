<style>
    /* ==========================================================================
       AI SCHOOL — STUDENT PAGE UNIFIED STYLES & LAYOUTS
       ========================================================================== */

    /* Flex & Layout Utilities */
    .d-flex { display: flex !important; }
    .flex-column { flex-direction: column !important; }
    .flex-row { flex-direction: row !important; }
    .align-items-center { align-items: center !important; }
    .align-items-start { align-items: flex-start !important; }
    .justify-content-between { justify-content: space-between !important; }
    .justify-content-center { justify-content: center !important; }
    .gap-2 { gap: 0.5rem !important; }
    .gap-3 { gap: 1rem !important; }
    .me-3 { margin-inline-end: 1rem !important; }
    .me-2 { margin-inline-end: 0.5rem !important; }
    .me-1 { margin-inline-end: 0.25rem !important; }
    .ms-auto { margin-inline-start: auto !important; }
    .position-relative { position: relative !important; }

    /* Student Unified Layout Fixes */
    .row.g-4 {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 160px), 1fr)) !important;
        gap: 1.25rem !important;
        margin: 0 0 2rem 0 !important;
    }
    @media (min-width: 1200px) {
        .row.g-4:has(> :nth-child(6)) {
            grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
        }
        .row.g-4:not(:has(> :nth-child(5))) {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
    }
    .col-12, .col-sm-6, .col-xl-3, .col-md-4, .col-xl-2 {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
    }
    .stat-card {
        background: #FFFFFF !important;
        border: 1px solid #E5EAF3 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 16px -2px rgba(24, 36, 61, 0.05) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease !important;
        height: 100% !important;
        overflow: hidden !important;
    }
    .dark .stat-card,
    html.dark .stat-card {
        background: #172033 !important;
        border-color: #334155 !important;
        color: #F1F5F9 !important;
        box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.4) !important;
    }
    .stat-card:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 10px 25px -3px rgba(54, 79, 172, 0.1) !important;
        border-color: #CBD5E1 !important;
    }
    .dark .stat-card:hover,
    html.dark .stat-card:hover {
        border-color: #475569 !important;
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.5) !important;
    }
    .stat-card .card-body {
        padding: 1.35rem 1rem !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
    }
    .stat-icon {
        width: 48px !important;
        height: 48px !important;
        border-radius: 14px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 20px !important;
        margin-bottom: 0.65rem !important;
        background: #EEF2FF !important;
        color: #364FAC !important;
    }
    .dark .stat-icon,
    html.dark .stat-icon {
        background: rgba(37, 99, 235, 0.2) !important;
        color: #60A5FA !important;
    }
    .stat-value {
        font-size: 1.85rem !important;
        font-weight: 850 !important;
        line-height: 1.2 !important;
        color: #18243D !important;
        margin: 0 !important;
    }
    .dark .stat-value,
    html.dark .stat-value {
        color: #F1F5F9 !important;
    }
    .stat-label {
        font-size: 0.85rem !important;
        font-weight: 650 !important;
        color: #64748B !important;
        margin-top: 0.35rem !important;
    }
    .dark .stat-label,
    html.dark .stat-label {
        color: #CBD5E1 !important;
    }

    /* Group Attendance Cards Grid */
    .row.g-3 {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 1.25rem !important;
    }
    .group-card {
        background: #FFFFFF !important;
        border: 1px solid #E5EAF3 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 14px rgba(24, 36, 61, 0.04) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }
    .dark .group-card,
    html.dark .group-card {
        background: #172033 !important;
        border-color: #334155 !important;
        color: #F1F5F9 !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35) !important;
    }
    .group-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(54, 79, 172, 0.08) !important;
    }
    .dark .group-card:hover,
    html.dark .group-card:hover {
        border-color: #475569 !important;
    }
    .group-card .card-body {
        padding: 1.35rem !important;
    }

    /* Page Header Unified Styling (AI School Primary Blue) */
    .page-header {
        background: linear-gradient(135deg, #364FAC 0%, #293C85 100%) !important;
        color: #FFFFFF !important;
        padding: 2.25rem 2.5rem !important;
        border-radius: 20px !important;
        margin-bottom: 2rem !important;
        position: relative !important;
        overflow: hidden !important;
        box-shadow: 0 10px 25px -5px rgba(54, 79, 172, 0.3) !important;
    }
    .page-header h1,
    .page-header h2,
    .page-header h3 {
        color: #FFFFFF !important;
        font-weight: 850 !important;
        letter-spacing: -0.02em !important;
        margin-bottom: 0.35rem !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15) !important;
    }
    .page-header p {
        color: rgba(255, 255, 255, 0.92) !important;
        font-size: 1rem !important;
        margin-bottom: 0 !important;
    }
    .page-header::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -40px;
        width: 240px;
        height: 240px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .page-header::after {
        content: '';
        position: absolute;
        bottom: -50px;
        left: -30px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(88, 186, 71, 0.35) 0%, rgba(88, 186, 71, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .header-icon {
        width: 58px !important;
        height: 58px !important;
        min-width: 58px !important;
        border-radius: 16px !important;
        background: rgba(255, 255, 255, 0.22) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 26px !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }

    /* Page Header Action Buttons */
    .page-header .report-btn,
    .page-header .header-action-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
        padding: 0.65rem 1.35rem !important;
        font-size: 0.925rem !important;
        font-weight: 750 !important;
        border-radius: 12px !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        background: rgba(255, 255, 255, 0.18) !important;
        color: #FFFFFF !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        cursor: pointer !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
        text-decoration: none !important;
    }
    .page-header .report-btn:hover,
    .page-header .header-action-btn:hover {
        background: rgba(255, 255, 255, 0.32) !important;
        border-color: rgba(255, 255, 255, 0.7) !important;
        color: #FFFFFF !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2) !important;
    }
    .page-header .report-btn.report-btn-solid,
    .page-header .header-action-btn-solid {
        background: #FFFFFF !important;
        color: #364FAC !important;
        border-color: #FFFFFF !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;
    }
    .page-header .report-btn.report-btn-solid:hover,
    .page-header .header-action-btn-solid:hover {
        background: #F8FAFD !important;
        color: #293C85 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22) !important;
    }

    @media (max-width: 1024px) {
        .row.g-4 {
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 140px), 1fr)) !important;
        }
        .row.g-3 {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 640px) {
        .row.g-4 {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.75rem !important;
        }
        .stat-card .card-body {
            padding: 1rem 0.5rem !important;
        }
        .stat-value {
            font-size: 1.35rem !important;
        }
        .stat-icon {
            width: 40px !important;
            height: 40px !important;
            font-size: 18px !important;
        }
        .page-header {
            padding: 1.25rem 1rem !important;
            border-radius: 16px !important;
        }
        .page-header > .d-flex {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 1.25rem !important;
        }
        .page-header .ms-auto {
            margin-inline-start: 0 !important;
            width: 100% !important;
            justify-content: flex-start !important;
        }
    }
    @media (max-width: 360px) {
        .row.g-4 {
            grid-template-columns: 1fr !important;
            gap: 0.65rem !important;
        }
    }
</style>
