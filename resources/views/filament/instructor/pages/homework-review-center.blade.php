<x-filament-panels::page>
    <style>
        /* ==========================================================================
           AI SCHOOL — UNIFIED ACADEMIC CENTER DESIGN SYSTEM
           Scoped Styling for /instructor/homework-review-center
           ========================================================================== */
        :root {
            --hrc-primary: #364FAC;
            --hrc-primary-hover: #293C85;
            --hrc-primary-light: #EEF2FF;
            --hrc-accent: #58BA47;
            --hrc-accent-hover: #459637;
            --hrc-accent-light: #EAF7E6;
            --hrc-blue: #2563EB;
            --hrc-blue-light: #EFF6FF;
            --hrc-amber: #D97706;
            --hrc-amber-light: #FFFBEB;
            --hrc-red: #DC2626;
            --hrc-red-light: #FEF2F2;
            --hrc-purple: #7C3AED;
            --hrc-purple-light: #F5F3FF;
            --hrc-emerald: #059669;
            --hrc-emerald-light: #ECFDF5;
            --hrc-card-bg: #FFFFFF;
            --hrc-card-border: #E2E8F0;
            --hrc-text-main: #0F172A;
            --hrc-text-muted: #64748B;
            --hrc-radius-lg: 20px;
            --hrc-radius-md: 14px;
            --hrc-radius-sm: 8px;
            --hrc-shadow: 0 4px 16px -2px rgba(24, 36, 61, 0.05);
            --hrc-shadow-hover: 0 12px 28px -4px rgba(24, 36, 61, 0.12);
        }

        .dark {
            --hrc-card-bg: #1E293B;
            --hrc-card-border: #334155;
            --hrc-text-main: #F8FAFC;
            --hrc-text-muted: #94A3B8;
            --hrc-primary-light: rgba(54, 79, 172, 0.22);
            --hrc-blue-light: rgba(37, 99, 235, 0.22);
            --hrc-amber-light: rgba(217, 119, 6, 0.22);
            --hrc-red-light: rgba(220, 38, 38, 0.22);
            --hrc-purple-light: rgba(124, 58, 237, 0.22);
            --hrc-emerald-light: rgba(5, 150, 105, 0.22);
        }

        /* Container & Base Typography */
        .hrc-wrapper {
            font-family: 'Tajawal', 'Cairo', system-ui, -apple-system, sans-serif !important;
            direction: rtl;
            color: var(--hrc-text-main);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Header & Breadcrumb */
        .hrc-header-box {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--hrc-card-border);
        }
        @media (min-width: 640px) {
            .hrc-header-box {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .hrc-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--hrc-text-muted);
            margin-bottom: 0.35rem;
            flex-wrap: wrap;
        }
        .hrc-breadcrumb-btn {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0;
            font-weight: 700;
            transition: color 0.2s;
        }
        .hrc-breadcrumb-btn:hover {
            color: var(--hrc-primary);
        }
        .hrc-title-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .hrc-title-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: var(--hrc-primary-light);
            color: var(--hrc-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hrc-main-title {
            font-size: 1.45rem;
            font-weight: 850;
            color: var(--hrc-text-main);
            margin: 0;
            line-height: 1.2;
        }
        .hrc-subtitle {
            font-size: 0.85rem;
            color: var(--hrc-text-muted);
            margin-top: 0.25rem;
        }
        .hrc-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 750;
            background: var(--hrc-card-bg);
            border: 1px solid var(--hrc-card-border);
            border-radius: var(--hrc-radius-md);
            color: var(--hrc-text-main);
            cursor: pointer;
            box-shadow: var(--hrc-shadow);
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .hrc-back-btn:hover {
            background: var(--hrc-primary-light);
            color: var(--hrc-primary);
            border-color: var(--hrc-primary);
            transform: translateY(-2px);
        }

        /* 4 Main Academic Tabs Navigation */
        .hrc-academic-tabs-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 2px solid var(--hrc-card-border);
            margin-bottom: 1.25rem;
            padding-bottom: 2px;
            overflow-x: auto;
        }
        .hrc-academic-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.75rem 1.4rem;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--hrc-text-muted);
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 12px 12px 0 0;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            margin-bottom: -2px;
        }
        .hrc-academic-tab-btn:hover {
            color: var(--hrc-primary);
            background: var(--hrc-primary-light);
        }
        .hrc-academic-tab-btn.active {
            color: var(--hrc-primary);
            border-bottom-color: var(--hrc-primary);
            background: var(--hrc-primary-light);
            font-weight: 900;
        }
        .hrc-tab-badge {
            font-size: 0.72rem;
            font-weight: 850;
            padding: 2px 8px;
            border-radius: 12px;
            background: #E2E8F0;
            color: #475569;
        }
        .hrc-academic-tab-btn.active .hrc-tab-badge {
            background: var(--hrc-primary);
            color: #FFFFFF;
        }

        /* Group Info Card & Header Stats */
        .hrc-group-card {
            background: var(--hrc-card-bg);
            border: 1px solid var(--hrc-card-border);
            border-radius: var(--hrc-radius-lg);
            box-shadow: var(--hrc-shadow);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .hrc-group-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            justify-content: space-between;
        }
        @media (min-width: 768px) {
            .hrc-group-header {
                flex-direction: row;
                align-items: center;
            }
        }
        .hrc-group-title {
            font-size: 1.35rem;
            font-weight: 850;
            margin: 0.4rem 0 0.25rem 0;
            color: var(--hrc-text-main);
        }
        .hrc-group-desc {
            font-size: 0.85rem;
            color: var(--hrc-text-muted);
            margin: 0;
        }
        .hrc-header-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.85rem;
            margin-top: 1.25rem;
        }
        @media (min-width: 768px) {
            .hrc-header-stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        .hrc-header-stat-box {
            background: #F8FAFC;
            border: 1px solid var(--hrc-card-border);
            border-radius: var(--hrc-radius-md);
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .dark .hrc-header-stat-box {
            background: #1E293B;
        }
        .hrc-header-stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Chips & Badges */
        .hrc-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 8px;
            font-size: 0.76rem;
            font-weight: 750;
        }
        .hrc-chip-primary { background: var(--hrc-primary-light); color: var(--hrc-primary); }
        .hrc-chip-slate { background: #F1F5F9; color: #475569; }
        .dark .hrc-chip-slate { background: #334155; color: #CBD5E1; }
        .hrc-badge-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 0.4rem;
        }
        .hrc-pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 800;
        }
        .hrc-pill-success { background: var(--hrc-emerald-light); color: var(--hrc-emerald); }
        .hrc-pill-warning { background: var(--hrc-amber-light); color: var(--hrc-amber); }
        .hrc-pill-danger { background: var(--hrc-red-light); color: var(--hrc-red); }
        .hrc-pill-purple { background: var(--hrc-purple-light); color: var(--hrc-purple); }
        .hrc-pill-neutral { background: #F1F5F9; color: #64748B; }

        /* KPI Cards Grid */
        .hrc-kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .hrc-kpi-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (min-width: 1024px) {
            .hrc-kpi-grid { grid-template-columns: repeat(6, 1fr); }
        }
        .hrc-kpi-card {
            background: var(--hrc-card-bg);
            border: 1px solid var(--hrc-card-border);
            border-top: 4px solid var(--hrc-card-border);
            border-radius: var(--hrc-radius-lg);
            padding: 1.25rem 1rem;
            text-align: center;
            box-shadow: var(--hrc-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 120px;
        }
        .hrc-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--hrc-shadow-hover);
        }
        .hrc-kpi-card.kpi-total { border-top-color: var(--hrc-primary); }
        .hrc-kpi-card.kpi-review { border-top-color: var(--hrc-blue); }
        .hrc-kpi-card.kpi-missing { border-top-color: var(--hrc-amber); }
        .hrc-kpi-card.kpi-overdue { border-top-color: var(--hrc-red); }
        .hrc-kpi-card.kpi-drafts { border-top-color: var(--hrc-purple); }
        .hrc-kpi-card.kpi-graded { border-top-color: var(--hrc-emerald); }

        .hrc-kpi-label { font-size: 0.82rem; font-weight: 750; color: var(--hrc-text-muted); }
        .hrc-kpi-num { font-size: 2.1rem; font-weight: 900; line-height: 1.1; margin: 0.4rem 0; color: var(--hrc-text-main); }
        .hrc-kpi-sub { font-size: 0.72rem; font-weight: 600; color: var(--hrc-text-muted); border-radius: 6px; padding: 2px 8px; display: inline-block; align-self: center; }

        /* Cards & Grids */
        .hrc-card {
            background: var(--hrc-card-bg);
            border: 1px solid var(--hrc-card-border);
            border-radius: var(--hrc-radius-lg);
            padding: 1.25rem 1.5rem;
            box-shadow: var(--hrc-shadow);
        }
        .hrc-sessions-grid, .hrc-assignments-grid, .hrc-exams-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        @media (min-width: 640px) {
            .hrc-sessions-grid, .hrc-assignments-grid, .hrc-exams-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (min-width: 1100px) {
            .hrc-sessions-grid, .hrc-assignments-grid, .hrc-exams-grid { grid-template-columns: repeat(3, 1fr); }
        }
        .hrc-session-card, .hrc-assignment-card, .hrc-exam-card {
            background: var(--hrc-card-bg);
            border: 1px solid var(--hrc-card-border);
            border-radius: var(--hrc-radius-lg);
            box-shadow: var(--hrc-shadow);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            transition: all 0.25s ease;
        }
        .hrc-session-card:hover, .hrc-assignment-card:hover, .hrc-exam-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--hrc-shadow-hover);
            border-color: #CBD5E1;
        }
        .hrc-card-body { padding: 1.35rem; }
        .hrc-card-footer {
            padding: 0.85rem 1.35rem;
            background: #F8FAFC;
            border-top: 1px solid var(--hrc-card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .dark .hrc-card-footer { background: #1E293B; }
        .hrc-session-title, .hrc-assignment-title, .hrc-exam-title {
            font-size: 1.15rem;
            font-weight: 850;
            color: var(--hrc-text-main);
            margin: 0.4rem 0 0.5rem 0;
            line-height: 1.3;
        }
        .hrc-meta-row {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.78rem;
            color: var(--hrc-text-muted);
            margin-bottom: 0.85rem;
            flex-wrap: wrap;
        }
        .hrc-stats-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.4rem;
            background: #F8FAFC;
            border-radius: var(--hrc-radius-md);
            padding: 0.65rem 0.5rem;
            border: 1px solid var(--hrc-card-border);
        }
        .dark .hrc-stats-strip { background: #0F172A; }
        .hrc-stat-box { text-align: center; }
        .hrc-stat-title { font-size: 0.68rem; font-weight: 700; color: var(--hrc-text-muted); display: block; margin-bottom: 2px; }
        .hrc-stat-val { font-size: 1.05rem; font-weight: 900; color: var(--hrc-text-main); }
        .stat-blue .hrc-stat-val { color: var(--hrc-blue); }
        .stat-amber .hrc-stat-val { color: var(--hrc-amber); }
        .stat-emerald .hrc-stat-val { color: var(--hrc-emerald); }
        .stat-red .hrc-stat-val { color: var(--hrc-red); }

        /* Action Indicators & Buttons */
        .hrc-action-indicator {
            font-size: 0.78rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .hrc-action-indicator.needs-action { color: var(--hrc-amber); }
        .hrc-action-indicator.completed { color: var(--hrc-emerald); }

        .hrc-btn-primary {
            background: var(--hrc-primary);
            color: #FFFFFF;
            border: none;
            border-radius: var(--hrc-radius-md);
            padding: 0.55rem 1.15rem;
            font-size: 0.82rem;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .hrc-btn-primary:hover {
            background: var(--hrc-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(54, 79, 172, 0.25);
        }
        .hrc-btn-secondary {
            background: var(--hrc-card-bg);
            color: var(--hrc-text-main);
            border: 1px solid var(--hrc-card-border);
            border-radius: var(--hrc-radius-md);
            padding: 0.55rem 1.15rem;
            font-size: 0.82rem;
            font-weight: 750;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .hrc-btn-secondary:hover {
            background: #F1F5F9;
            border-color: #CBD5E1;
        }
        .hrc-btn-emerald {
            background: var(--hrc-emerald);
            color: #FFFFFF;
            border: none;
            border-radius: var(--hrc-radius-md);
            padding: 0.55rem 1.15rem;
            font-size: 0.82rem;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }
        .hrc-btn-emerald:hover { background: #047857; }
        .hrc-btn-danger {
            background: var(--hrc-red);
            color: #FFFFFF;
            border: none;
            border-radius: var(--hrc-radius-md);
            padding: 0.55rem 1rem;
            font-size: 0.8rem;
            font-weight: 800;
            cursor: pointer;
        }

        /* Tables & Rosters */
        .hrc-table-card {
            background: var(--hrc-card-bg);
            border: 1px solid var(--hrc-card-border);
            border-radius: var(--hrc-radius-lg);
            box-shadow: var(--hrc-shadow);
            overflow: hidden;
        }
        .hrc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            text-align: right;
        }
        .hrc-table th {
            background: #F8FAFC;
            padding: 0.9rem 1.25rem;
            font-weight: 800;
            font-size: 0.78rem;
            color: var(--hrc-text-muted);
            border-bottom: 1px solid var(--hrc-card-border);
            white-space: nowrap;
        }
        .dark .hrc-table th { background: #1E293B; }
        .hrc-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--hrc-card-border);
            vertical-align: middle;
        }
        .hrc-table tr:hover td {
            background: rgba(248, 250, 252, 0.6);
        }
        .dark .hrc-table tr:hover td {
            background: rgba(30, 41, 59, 0.6);
        }

        /* Filters Bar */
        .hrc-pills-bar {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 1px solid var(--hrc-card-border);
        }
        @media (min-width: 768px) {
            .hrc-pills-bar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .hrc-pills-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }
        .hrc-tab-pill {
            padding: 0.45rem 1rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 750;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            white-space: nowrap;
            background: #F1F5F9;
            color: #475569;
        }
        .dark .hrc-tab-pill { background: #334155; color: #CBD5E1; }
        .hrc-tab-pill.active {
            background: var(--hrc-primary) !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(54, 79, 172, 0.3);
        }
        .hrc-tab-pill.active-blue { background: var(--hrc-blue) !important; color: #FFFFFF !important; }
        .hrc-tab-pill.active-amber { background: var(--hrc-amber) !important; color: #FFFFFF !important; }
        .hrc-tab-pill.active-emerald { background: var(--hrc-emerald) !important; color: #FFFFFF !important; }
        .hrc-tab-pill.active-red { background: var(--hrc-red) !important; color: #FFFFFF !important; }

        .hrc-search-box {
            position: relative;
            width: 100%;
            max-width: 320px;
        }
        .hrc-search-icon {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--hrc-text-muted);
            pointer-events: none;
        }
        .hrc-search-input {
            padding-right: 2.5rem !important;
        }
        .hrc-input, .hrc-select {
            width: 100%;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: var(--hrc-radius-md);
            border: 1px solid var(--hrc-card-border);
            background-color: var(--hrc-card-bg);
            color: var(--hrc-text-main);
            padding: 0.65rem 0.85rem;
            outline: none;
            font-family: inherit;
        }
        .hrc-input:focus, .hrc-select:focus {
            border-color: var(--hrc-primary);
            box-shadow: 0 0 0 3px rgba(54, 79, 172, 0.18);
        }

        /* Empty States */
        .hrc-empty-box {
            text-align: center;
            padding: 4rem 1.5rem;
            background: var(--hrc-card-bg);
            border: 1px dashed var(--hrc-card-border);
            border-radius: var(--hrc-radius-lg);
        }
        .hrc-empty-icon {
            width: 56px;
            height: 56px;
            color: #94A3B8;
            margin: 0 auto 0.85rem auto;
        }
        .hrc-empty-title {
            font-size: 1.15rem;
            font-weight: 850;
            color: var(--hrc-text-main);
            margin-bottom: 0.35rem;
        }
        .hrc-empty-sub {
            font-size: 0.85rem;
            color: var(--hrc-text-muted);
        }

        /* Modal Backdrop & Dialog */
        .hrc-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1050;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            overflow-y: auto;
        }
        .hrc-modal-card {
            background: var(--hrc-card-bg);
            border: 1px solid var(--hrc-card-border);
            border-radius: 24px;
            width: 100%;
            max-width: 900px;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: hrcModalSlide 0.25s ease-out;
        }
        .hrc-modal-card.modal-md { max-width: 620px; }
        .hrc-modal-card.modal-lg { max-width: 1050px; }

        @keyframes hrcModalSlide {
            from { opacity: 0; transform: scale(0.96) translateY(12px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .hrc-modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--hrc-card-border);
            background: #F8FAFC;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dark .hrc-modal-header { background: #253347; }
        .hrc-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .hrc-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--hrc-card-border);
            background: #F8FAFC;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .dark .hrc-modal-footer { background: #253347; }

        /* Form Grids */
        .hrc-form-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .hrc-form-grid-2 { grid-template-columns: repeat(2, 1fr); }
        }
        .hrc-field label {
            font-size: 0.8rem;
            font-weight: 750;
            color: var(--hrc-text-main);
            margin-bottom: 0.35rem;
            display: block;
        }

        /* Image Viewer Theater */
        .hrc-theater {
            position: relative;
            background: #0B1120;
            border-radius: 18px;
            overflow: hidden;
            aspect-ratio: 16 / 10;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #1E293B;
        }
        .hrc-theater-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            cursor: zoom-in;
            transition: transform 0.2s;
        }
        .hrc-nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.6);
            color: #FFFFFF;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .hrc-nav-arrow:hover { background: rgba(0, 0, 0, 0.85); transform: translateY(-50%) scale(1.1); }
        .hrc-nav-prev { right: 1rem; }
        .hrc-nav-next { left: 1rem; }
        .hrc-thumb-strip {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            overflow-x: auto;
            padding: 0.65rem 0.25rem;
        }
        .hrc-thumb-btn {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer;
            opacity: 0.65;
            transition: all 0.2s;
            flex-shrink: 0;
            padding: 0;
        }
        .hrc-thumb-btn.active { opacity: 1; border-color: var(--hrc-primary); transform: scale(1.05); }
        .hrc-thumb-btn img { width: 100%; height: 100%; object-fit: cover; }

        /* Grading Card */
        .hrc-grading-card {
            background: var(--hrc-primary-light);
            border: 1px solid rgba(54, 79, 172, 0.25);
            border-radius: 18px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .hrc-score-wrapper { position: relative; }
        .hrc-score-input {
            width: 100%;
            font-size: 1.15rem;
            font-weight: 850;
            border-radius: var(--hrc-radius-md);
            border: 1px solid var(--hrc-card-border);
            background: var(--hrc-card-bg);
            color: var(--hrc-text-main);
            padding: 0.75rem 1rem 0.75rem 4rem;
            font-family: inherit;
        }
        .hrc-score-suffix {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.9rem;
            font-weight: 750;
            color: var(--hrc-text-muted);
            pointer-events: none;
        }
        .hrc-textarea {
            width: 100%;
            font-size: 0.88rem;
            border-radius: var(--hrc-radius-md);
            border: 1px solid var(--hrc-card-border);
            background: var(--hrc-card-bg);
            color: var(--hrc-text-main);
            padding: 0.75rem 1rem;
            font-family: inherit;
            outline: none;
            resize: vertical;
        }
        .hrc-textarea:focus {
            border-color: var(--hrc-primary);
            box-shadow: 0 0 0 3px rgba(54, 79, 172, 0.2);
        }

        /* Lightbox */
        .hrc-lightbox-overlay {
            position: fixed;
            inset: 0;
            z-index: 1200;
            background: rgba(0, 0, 0, 0.92);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            cursor: zoom-out;
        }
        .hrc-lightbox-toolbar {
            position: absolute;
            top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            background: rgba(0, 0, 0, 0.75);
            padding: 0.45rem 1rem;
            border-radius: 30px;
            backdrop-filter: blur(8px);
            z-index: 1300;
        }
        .hrc-zoom-btn {
            background: rgba(255, 255, 255, 0.22);
            color: #FFFFFF;
            border: none;
            border-radius: 50%;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.15s;
        }
        .hrc-zoom-btn:hover { background: rgba(255, 255, 255, 0.4); transform: scale(1.08); }
        .hrc-zoom-val { color: #FFFFFF; font-size: 0.85rem; font-weight: 800; min-width: 45px; text-align: center; font-family: monospace; }
    </style>

    <div class="hrc-wrapper">

        {{-- ======================================================== --}}
        {{-- TOP BREADCRUMB & HEADER                                  --}}
        {{-- ======================================================== --}}
        <div class="hrc-header-box">
            <div>
                <div class="hrc-breadcrumb">
                    <span>لوحة المعلم</span>
                    <span>/</span>
                    <button type="button" wire:click="backToGroups" class="hrc-breadcrumb-btn">المجموعات الدراسية</button>
                    @if($this->activeGroup)
                        <span>/</span>
                        <button type="button" wire:click="backToSessions" class="hrc-breadcrumb-btn" style="color: var(--hrc-primary); font-weight: 800;">
                            مجموعة: {{ $this->activeGroup->name }}
                        </button>
                    @endif
                    @if($this->selectedSessionId && $this->activeSession)
                        <span>/</span>
                        <span class="hrc-chip hrc-chip-primary">الجلسة {{ $this->activeSession['session_number'] }}</span>
                    @endif
                    @if($this->activeAssignment)
                        <span>/</span>
                        <span style="color: var(--hrc-text-main); font-weight: 750;">{{ $this->activeAssignment->title }}</span>
                    @elseif($this->activeExam)
                        <span>/</span>
                        <span style="color: var(--hrc-text-main); font-weight: 750;">امتحان: {{ $this->activeExam->title }}</span>
                    @endif
                </div>

                <div class="hrc-title-row">
                    <div class="hrc-title-icon">
                        <x-heroicon-o-academic-cap class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="hrc-main-title">
                            @if($this->activeAssignment)
                                تصحيح واجب: {{ $this->activeAssignment->title }}
                            @elseif($this->activeExam)
                                محاولات ونتائج امتحان: {{ $this->activeExam->title }}
                            @elseif($this->activeGroup)
                                المركز الأكاديمي لمجموعة: {{ $this->activeGroup->name }}
                                <span class="sr-only">واجبات مجموعة: {{ $this->activeGroup->name }}</span>
                            @else
                                المركز الأكاديمي الشامل — مركز مراجعة واعتماد الواجبات
                            @endif
                        </h1>
                        <p class="hrc-subtitle">
                            @if($this->activeAssignment)
                                استعراض حلول طلاب المجموعة، رصد الدرجات وملاحظات المعلم، واعتماد النتائج فوراً
                            @elseif($this->activeExam)
                                استعراض محاولات الطلاب، التحقق من نتائج التصحيح التلقائي، وإصدار أذونات الإعادة المصرح بها
                            @elseif($this->activeGroup)
                                إدارة موحدة وشاملة للواجبات، الامتحانات، التسليمات ونتائج طلاب المجموعة
                            @else
                                اختر المجموعة لمراجعة الواجبات والاختبارات والتسليمات ونتائج الطلاب الشاملة
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @if($this->activeAssignment)
                <button type="button" wire:click="backToAssignments" class="hrc-back-btn">
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                    <span>{{ $this->activeSession ? 'العودة لواجبات السيشن' : 'العودة لسيشنات المجموعة' }}</span>
                </button>
            @elseif($this->selectedExamId)
                <button type="button" wire:click="backToExamsList" class="hrc-back-btn">
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                    <span>العودة لقائمة الامتحانات</span>
                </button>
            @elseif($this->selectedSessionId)
                <button type="button" wire:click="backToSessions" class="hrc-back-btn">
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                    <span>العودة لسيشنات المجموعة</span>
                </button>
            @elseif($this->activeGroup)
                <button type="button" wire:click="backToGroups" class="hrc-back-btn">
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                    <span>كافة المجموعات</span>
                </button>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- VIEW MODE 1: DRILLDOWN INTO SELECTED HOMEWORK            --}}
        {{-- ======================================================== --}}
        @if($this->activeAssignment)
            @php $asg = $this->activeAssignment; @endphp
            {{-- Assignment Header Summary Card --}}
            <div class="hrc-card" style="border-inline-start: 4px solid var(--hrc-primary); padding: 1.35rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <div class="hrc-badge-row">
                            <span class="hrc-chip hrc-chip-primary">
                                📚 {{ $asg->group?->course?->name }}
                            </span>
                            <span class="hrc-chip hrc-chip-slate">
                                👥 مجموعة: {{ $asg->group?->name }}
                            </span>
                            <span class="hrc-chip" style="background: var(--hrc-amber-light); color: var(--hrc-amber); font-weight: 800;">
                                ⭐ الدرجة العظمى: {{ $asg->max_score ?? 10 }}
                            </span>
                            <span class="hrc-chip" style="background: var(--hrc-blue-light); color: var(--hrc-blue); font-weight: 800;">
                                📅 الموعد النهائي: {{ $asg->due_date ? $asg->due_date->format('Y-m-d') : 'مفتوح' }}
                            </span>
                        </div>
                        <h2 style="font-size: 1.3rem; font-weight: 850; margin: 0.35rem 0 0.2rem 0; color: var(--hrc-text-main);">{{ $asg->title }}</h2>
                        @if($asg->description)
                            <p style="font-size: 0.85rem; color: var(--hrc-text-muted); margin: 0; max-width: 800px;">{{ $asg->description }}</p>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <button type="button" wire:click="openEditHomeworkModal({{ $asg->id }})" class="hrc-btn-secondary">
                            <x-heroicon-o-pencil class="w-4 h-4" />
                            <span>تعديل بيانات الواجب</span>
                        </button>
                        <button type="button" wire:click="openExtensionModal('group')" class="hrc-btn-secondary" style="border-color: var(--hrc-amber); color: var(--hrc-amber);">
                            <x-heroicon-o-clock class="w-4 h-4" />
                            <span>تمديد موعد للمجموعة</span>
                        </button>
                    </div>
                </div>

                {{-- Quick Filters within Roster --}}
                <div class="hrc-pills-bar" style="margin-top: 1.25rem;">
                    <div class="hrc-pills-group">
                        <button type="button" wire:click="$set('studentPunctualityFilter', 'all'); $set('studentGradingFilter', 'all')"
                            class="hrc-tab-pill {{ $studentPunctualityFilter === 'all' && $studentGradingFilter === 'all' ? 'active' : '' }}">
                            كافة الطلاب
                        </button>
                        <button type="button" wire:click="$set('studentPunctualityFilter', 'overdue')"
                            class="hrc-tab-pill {{ $studentPunctualityFilter === 'overdue' ? 'active-red' : '' }}">
                            متأخر ولم يسلّم
                        </button>
                        <button type="button" wire:click="$set('studentGradingFilter', 'awaiting_grading')"
                            class="hrc-tab-pill {{ $studentGradingFilter === 'awaiting_grading' ? 'active-blue' : '' }}">
                            بانتظار التصحيح
                        </button>
                        <button type="button" wire:click="$set('studentGradingFilter', 'graded')"
                            class="hrc-tab-pill {{ $studentGradingFilter === 'graded' ? 'active-emerald' : '' }}">
                            تم الاعتماد
                        </button>
                    </div>

                    <div class="hrc-search-box">
                        <x-heroicon-o-magnifying-glass class="hrc-search-icon" />
                        <input type="text"
                            wire:model.live.debounce.250ms="studentSearch"
                            placeholder="بحث باسم الطالب أو الكود..."
                            class="hrc-input hrc-search-input" />
                    </div>
                </div>
            </div>

            {{-- 7-Columns Student Table for Homework Roster --}}
            @php $roster = $this->studentRoster; @endphp
            <div class="hrc-table-card">
                <div style="overflow-x: auto;">
                    <table class="hrc-table">
                        <thead>
                            <tr>
                                <th>اسم الطالب</th>
                                <th style="text-align: center;">حالة التسليم</th>
                                <th style="text-align: center;">موعد التسليم</th>
                                <th style="text-align: center;">التأخير</th>
                                <th style="text-align: center;">الدرجة الرسمية</th>
                                <th style="text-align: center;">حالة التصحيح</th>
                                <th style="text-align: center;">الإجراءات المتاحة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roster as $row)
                                <tr>
                                    {{-- 1. Student Name & Code --}}
                                    <td>
                                        <div style="font-weight: 800; color: var(--hrc-text-main); font-size: 0.95rem;">
                                            {{ $row['student_name'] }}
                                        </div>
                                        <span style="font-size: 0.75rem; font-family: monospace; color: var(--hrc-text-muted);">
                                            {{ $row['student_code'] }}
                                        </span>
                                    </td>

                                    {{-- 2. Submission Status --}}
                                    <td style="text-align: center;">
                                        @if($row['punctuality_status'] === 'submitted_on_time')
                                            <span class="hrc-pill-badge hrc-pill-success">✓ تم التسليم في الموعد</span>
                                        @elseif($row['punctuality_status'] === 'submitted_late')
                                            <span class="hrc-pill-badge hrc-pill-warning">⏱️ {{ $row['punctuality_label'] ?? 'سلّم متأخرًا' }}</span>
                                        @elseif($row['punctuality_status'] === 'overdue')
                                            <span class="hrc-pill-badge hrc-pill-danger">⚠️ متأخر ولم يسلّم</span>
                                        @else
                                            <span class="hrc-pill-badge hrc-pill-neutral">⏳ بانتظار التسليم</span>
                                        @endif
                                    </td>

                                    {{-- 3. Submission Timestamp --}}
                                    <td style="text-align: center; font-family: monospace; font-size: 0.8rem;" dir="ltr">
                                        {{ $row['submitted_at_formatted'] }}
                                    </td>

                                    {{-- 4. Punctuality --}}
                                    <td style="text-align: center;">
                                        @if($row['is_late'])
                                            <span class="hrc-chip" style="background: var(--hrc-red-light); color: var(--hrc-red); font-weight: 800;">متأخر</span>
                                        @elseif($row['submission'])
                                            <span class="hrc-chip" style="background: var(--hrc-emerald-light); color: var(--hrc-emerald); font-weight: 800;">في الموعد</span>
                                        @else
                                            <span style="color: var(--hrc-text-muted); font-size: 0.85rem;">—</span>
                                        @endif
                                    </td>

                                    {{-- 5. Score Display --}}
                                    <td style="text-align: center; font-weight: 850; font-size: 0.95rem;">
                                        @if($row['is_published'])
                                            <span class="hrc-pill-badge hrc-pill-success" style="font-size: 0.85rem; padding: 0.4rem 0.9rem;">
                                                {{ $row['score_display'] }}
                                            </span>
                                        @elseif($row['is_draft'])
                                            <span class="hrc-pill-badge hrc-pill-purple" style="font-size: 0.85rem; padding: 0.4rem 0.9rem;">
                                                {{ $row['score_display'] }} (مسودة)
                                            </span>
                                        @else
                                            <span style="color: var(--hrc-text-muted); font-family: monospace;">—</span>
                                        @endif
                                    </td>

                                    {{-- 6. Review Status --}}
                                    <td style="text-align: center;">
                                        @if($row['review_status'] === 'graded')
                                            <span style="font-size: 0.82rem; font-weight: 800; color: var(--hrc-emerald);">✓ تم التصحيح والاعتماد</span>
                                        @elseif($row['review_status'] === 'draft')
                                            <span style="font-size: 0.82rem; font-weight: 800; color: var(--hrc-purple);">📝 مسودة تقييم</span>
                                        @elseif($row['review_status'] === 'awaiting_grading')
                                            <span style="font-size: 0.82rem; font-weight: 800; color: var(--hrc-amber);">⏳ بانتظار التصحيح</span>
                                        @else
                                            <span style="color: var(--hrc-text-muted); font-size: 0.85rem;">—</span>
                                        @endif
                                    </td>

                                    {{-- 7. Actions --}}
                                    <td style="text-align: center;">
                                        <div style="display: flex; align-items: center; justify-content: center; gap: 0.35rem; flex-wrap: wrap;">
                                            @if($row['submission'])
                                                <button type="button"
                                                    wire:click="openReview({{ $row['student_id'] }}, {{ $row['submission']->id }})"
                                                    class="hrc-btn-primary"
                                                    style="padding: 0.4rem 0.85rem; font-size: 0.78rem;">
                                                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                                    <span>{{ $row['is_published'] ? 'عرض الإجابة والتقييم' : 'عرض الإجابة والتصحيح' }}</span>
                                                </button>

                                                @if($row['is_published'])
                                                    {{-- تصحيح الدرجة المنشورة --}}
                                                    <button type="button"
                                                        wire:click="openGradeCorrectionModal({{ $row['submission']->id }}, {{ (float) ($row['submission']->score ?? 0) }})"
                                                        class="hrc-btn-secondary"
                                                        title="تعديل الدرجة المنشورة مع ذكر السبب في سجل التدقيق"
                                                        style="padding: 0.4rem 0.65rem; font-size: 0.78rem; color: var(--hrc-purple); border-color: rgba(124, 58, 237, 0.3);">
                                                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                                        <span>تصحيح الدرجة</span>
                                                    </button>

                                                    {{-- منح إعادة تسليم --}}
                                                    <button type="button"
                                                        wire:click="openResubmissionModal({{ $row['student_id'] }}, '{{ addslashes($row['student_name']) }}')"
                                                        class="hrc-btn-secondary"
                                                        title="منح الطالب إذن إعادة تسليم إضافي لمرة واحدة"
                                                        style="padding: 0.4rem 0.65rem; font-size: 0.78rem; color: var(--hrc-blue); border-color: rgba(37, 99, 235, 0.3);">
                                                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                                                        <span>إذن إعادة تسليم</span>
                                                    </button>
                                                @endif
                                            @else
                                                {{-- الطالب لم يسلّم بعد: فتح استثناء موعد خاص به --}}
                                                <button type="button"
                                                    wire:click="openExtensionModal('student', {{ $row['student_id'] }}, '{{ addslashes($row['student_name']) }}')"
                                                    class="hrc-btn-secondary"
                                                    title="تمديد موعد تسليم خاص بهذا الطالب فقط"
                                                    style="padding: 0.4rem 0.75rem; font-size: 0.78rem; color: var(--hrc-amber); border-color: rgba(217, 119, 6, 0.3);">
                                                    <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                                    <span>استثناء موعد</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="padding: 3.5rem 1rem; text-align: center; color: var(--hrc-text-muted);">
                                        <x-heroicon-o-users class="w-12 h-12" style="margin: 0 auto 0.75rem auto; opacity: 0.4;" />
                                        <div style="font-size: 1rem; font-weight: 750;">لا يوجد طلاب يطابقون خيارات البحث في هذه المجموعة</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- ======================================================== --}}
        {{-- VIEW MODE 2: ACTIVE GROUP SELECTED (ACADEMIC CENTER)     --}}
        {{-- ======================================================== --}}
        @elseif($this->activeGroup)
            @php 
                $group = $this->activeGroup;
                $headerStats = $this->headerStats;
                $sessions = $this->groupSessions;
                $unlinkedHw = $this->groupUnlinkedAssignments;
            @endphp

            {{-- Group Info Card --}}
            <div class="hrc-group-card">
                <div class="hrc-group-header">
                    <div>
                        <div class="hrc-badge-row">
                            <span class="hrc-chip hrc-chip-primary">
                                📚 مقرر: {{ $group->course?->name ?? 'غير محدد' }}
                            </span>
                            <span class="hrc-chip hrc-chip-slate">
                                👥 مجموعة: {{ $group->name }}
                            </span>
                            @if($group->instructor?->user)
                                <span class="hrc-chip hrc-chip-slate">
                                    👨‍🏫 المعلم: {{ $group->instructor->user->name }}
                                </span>
                            @endif
                        </div>
                        <h2 class="hrc-group-title">المركز الأكاديمي لمجموعة: {{ $group->name }}</h2>
                        <p class="hrc-group-desc">إدارة شاملة وموحدة لكافة الأنشطة الأكاديمية (الواجبات، الامتحانات، التسليمات والنتائج) داخل المجموعة.</p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <button type="button" wire:click="backToGroups" class="hrc-btn-secondary">
                            <x-heroicon-o-arrows-right-left class="w-4 h-4" />
                            <span>اختيار مجموعة أخرى</span>
                        </button>
                    </div>
                </div>

                {{-- Compact, Authoritative Indicators Strip --}}
                <div class="hrc-header-stats-grid">
                    <div class="hrc-header-stat-box">
                        <div class="hrc-header-stat-icon" style="background: var(--hrc-primary-light); color: var(--hrc-primary);">
                            <x-heroicon-o-calendar-days class="w-5 h-5" />
                        </div>
                        <div>
                            <span style="font-size: 0.72rem; color: var(--hrc-text-muted); font-weight: 700; display: block;">جلسات المجموعة</span>
                            <span style="font-size: 1.25rem; font-weight: 900; color: var(--hrc-text-main);">{{ $headerStats['sessions_count'] }}</span>
                        </div>
                    </div>

                    <div class="hrc-header-stat-box">
                        <div class="hrc-header-stat-icon" style="background: var(--hrc-blue-light); color: var(--hrc-blue);">
                            <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                        </div>
                        <div>
                            <span style="font-size: 0.72rem; color: var(--hrc-text-muted); font-weight: 700; display: block;">واجبات المجموعة</span>
                            <span style="font-size: 1.25rem; font-weight: 900; color: var(--hrc-blue);">{{ $headerStats['homework_count'] }}</span>
                        </div>
                    </div>

                    <div class="hrc-header-stat-box">
                        <div class="hrc-header-stat-icon" style="background: var(--hrc-amber-light); color: var(--hrc-amber);">
                            <x-heroicon-o-clock class="w-5 h-5" />
                        </div>
                        <div>
                            <span style="font-size: 0.72rem; color: var(--hrc-text-muted); font-weight: 700; display: block;">واجبات تتطلب تصحيح</span>
                            <span style="font-size: 1.25rem; font-weight: 900; color: var(--hrc-amber);">{{ $headerStats['pending_grading_count'] }}</span>
                        </div>
                    </div>

                    <div class="hrc-header-stat-box">
                        <div class="hrc-header-stat-icon" style="background: var(--hrc-purple-light); color: var(--hrc-purple);">
                            <x-heroicon-o-document-check class="w-5 h-5" />
                        </div>
                        <div>
                            <span style="font-size: 0.72rem; color: var(--hrc-text-muted); font-weight: 700; display: block;">امتحانات المجموعة</span>
                            <span style="font-size: 1.25rem; font-weight: 900; color: var(--hrc-purple);">{{ $headerStats['exams_count'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4 MAIN TABS BAR --}}
            <div class="hrc-academic-tabs-bar">
                <button type="button" wire:click="switchTab('homework')"
                    class="hrc-academic-tab-btn {{ $activeTab === 'homework' ? 'active' : '' }}">
                    <x-heroicon-o-book-open class="w-5 h-5" />
                    <span>1. الواجبات</span>
                    <span class="hrc-tab-badge">{{ $headerStats['homework_count'] }}</span>
                </button>

                <button type="button" wire:click="switchTab('exams')"
                    class="hrc-academic-tab-btn {{ $activeTab === 'exams' ? 'active' : '' }}">
                    <x-heroicon-o-document-check class="w-5 h-5" />
                    <span>2. الامتحانات</span>
                    <span class="hrc-tab-badge">{{ $headerStats['exams_count'] }}</span>
                </button>

                <button type="button" wire:click="switchTab('submissions')"
                    class="hrc-academic-tab-btn {{ $activeTab === 'submissions' ? 'active' : '' }}">
                    <x-heroicon-o-inbox-arrow-down class="w-5 h-5" />
                    <span>3. التسليمات</span>
                    <span class="hrc-tab-badge">{{ $this->allGroupSubmissions->count() }}</span>
                </button>

                <button type="button" wire:click="switchTab('results')"
                    class="hrc-academic-tab-btn {{ $activeTab === 'results' ? 'active' : '' }}">
                    <x-heroicon-o-chart-bar class="w-5 h-5" />
                    <span>4. النتائج</span>
                    <span class="hrc-tab-badge">{{ $this->groupStudentsResults->count() }}</span>
                </button>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 1: HOMEWORK (الواجبات)                               --}}
            {{-- ======================================================== --}}
            @if($activeTab === 'homework')
                {{-- Action Bar --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                            @if($this->selectedSessionId && $this->activeSession)
                                واجبات السيشن رقم {{ $this->activeSession['session_number'] }}
                            @else
                                سيشنات وواجبات المجموعة ({{ $sessions->count() }} سيشن)
                            @endif
                        </h3>
                        <p style="font-size: 0.8rem; color: var(--hrc-text-muted); margin: 0.2rem 0 0 0;">
                            مسار العمل: اختر السيشن ← اختر الواجب ← استعراض قائمة الطلاب والحلول ← التصحيح الفوري
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <button type="button" wire:click="openCreateHomeworkModal" class="hrc-btn-primary">
                            <x-heroicon-o-plus-circle class="w-4 h-4" />
                            <span>إضافة واجب جديد</span>
                        </button>
                    </div>
                </div>

                @if($this->selectedSessionId && $this->activeSession)
                    {{-- SPECIFIC SESSION HOMEWORK LIST --}}
                    @php $sess = $this->activeSession; @endphp
                    <div class="hrc-card" style="margin-bottom: 1.25rem; border-inline-start: 4px solid var(--hrc-primary); padding: 1.25rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                    <span class="hrc-chip hrc-chip-primary" style="font-size: 0.85rem;">
                                        السيشن رقم {{ $sess['session_number'] }}
                                    </span>
                                    @if($sess['date'])
                                        <span class="hrc-chip hrc-chip-slate">
                                            📅 {{ $sess['date']->format('Y-m-d') }}
                                        </span>
                                    @endif
                                </div>
                                <h3 style="font-size: 1.15rem; font-weight: 850; color: var(--hrc-text-main); margin: 0;">
                                    {{ $sess['title'] }}
                                </h3>
                            </div>
                            <button type="button" wire:click="backToSessions" class="hrc-btn-secondary">
                                <x-heroicon-o-arrow-right class="w-4 h-4" />
                                <span>العودة لسيشنات المجموعة</span>
                            </button>
                        </div>
                    </div>

                    @if($sess['assignments']->isEmpty())
                        <div class="hrc-empty-box">
                            <x-heroicon-o-document-magnifying-glass class="hrc-empty-icon" />
                            <h3 class="hrc-empty-title">لا توجد واجبات مضافة لهذا السيشن حتى الآن</h3>
                            <p class="hrc-empty-sub">انقر فوق "إضافة واجب جديد" لإسناد واجب لهذا السيشن</p>
                        </div>
                    @else
                        <div class="hrc-assignments-grid">
                            @foreach($sess['assignments'] as $asg)
                                @php 
                                    $roster = app(\App\Services\HomeworkService::class)->getGroupStudentsWithSubmissions($asg);
                                    $subCount = $roster->where('has_submission', true)->count();
                                    $awaitCount = $roster->where('review_status', 'awaiting_grading')->count();
                                    $gradCount = $roster->where('review_status', 'graded')->count();
                                    $eligCount = $roster->count();
                                @endphp
                                <div class="hrc-assignment-card">
                                    <div class="hrc-card-body">
                                        <div class="hrc-badge-row">
                                            <span class="hrc-chip hrc-chip-primary">
                                                السيشن #{{ $sess['session_number'] }}
                                            </span>
                                            <span class="hrc-chip" style="background: var(--hrc-amber-light); color: var(--hrc-amber);">
                                                ⭐ الدرجة: {{ $asg->max_score ?? 10 }}
                                            </span>
                                        </div>
                                        <h3 class="hrc-assignment-title" title="{{ $asg->title }}">{{ $asg->title }}</h3>
                                        <div class="hrc-meta-row">
                                            <x-heroicon-o-calendar class="w-4 h-4" />
                                            <span>الاستحقاق: {{ $asg->due_date ? $asg->due_date->format('Y-m-d') : 'مفتوح' }}</span>
                                        </div>
                                        <div class="hrc-stats-strip">
                                            <div class="hrc-stat-box">
                                                <span class="hrc-stat-title">المؤهلون</span>
                                                <span class="hrc-stat-val">{{ $eligCount }}</span>
                                            </div>
                                            <div class="hrc-stat-box stat-blue">
                                                <span class="hrc-stat-title">تم التسليم</span>
                                                <span class="hrc-stat-val">{{ $subCount }}</span>
                                            </div>
                                            <div class="hrc-stat-box {{ $awaitCount > 0 ? 'stat-amber' : '' }}">
                                                <span class="hrc-stat-title">بانتظار التصحيح</span>
                                                <span class="hrc-stat-val">{{ $awaitCount }}</span>
                                            </div>
                                            <div class="hrc-stat-box stat-emerald">
                                                <span class="hrc-stat-title">معتمد</span>
                                                <span class="hrc-stat-val">{{ $gradCount }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hrc-card-footer">
                                        <span class="hrc-action-indicator {{ $awaitCount > 0 ? 'needs-action' : 'completed' }}">
                                            {{ $awaitCount > 0 ? '⚡ يتطلب تصحيح' : '✓ مصحح' }}
                                        </span>
                                        <button type="button" wire:click="selectAssignment({{ $asg->id }})" class="hrc-btn-primary">
                                            <span>فتح وتصحيح الواجب</span>
                                            <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                @else
                    {{-- SESSIONS LIST FOR ACTIVE GROUP --}}
                    @if($sessions->isEmpty())
                        <div class="hrc-empty-box">
                            <x-heroicon-o-calendar class="hrc-empty-icon" />
                            <h3 class="hrc-empty-title">لا توجد سيشنات مسجلة لهذه المجموعة بعد</h3>
                            <p class="hrc-empty-sub">سيتم عرض السيشنات هنا تلقائياً عند جدولة السيشنات أو رصد الحضور</p>
                        </div>
                    @else
                        <div class="hrc-sessions-grid">
                            @foreach($sessions as $s)
                                <div class="hrc-session-card">
                                    <div class="hrc-card-body">
                                        <div class="hrc-badge-row">
                                            <span class="hrc-chip hrc-chip-primary" style="font-size: 0.82rem; font-weight: 850;">
                                                الجلسة {{ $s['session_number'] }}
                                            </span>
                                            @if($s['date'])
                                                <span class="hrc-chip hrc-chip-slate">
                                                    📅 {{ $s['date']->format('Y-m-d') }}
                                                </span>
                                            @endif
                                        </div>

                                        <h4 class="hrc-session-title" title="{{ $s['title'] }}">
                                            {{ $s['title'] }}
                                        </h4>

                                        <div class="hrc-meta-row" style="margin-bottom: 0.85rem;">
                                            <span class="hrc-chip" style="background: var(--hrc-blue-light); color: var(--hrc-blue); font-weight: 800;">
                                                📚 {{ $s['assignments_count'] }} واجب مرتبط
                                            </span>
                                        </div>

                                        <div class="hrc-stats-strip">
                                            <div class="hrc-stat-box">
                                                <span class="hrc-stat-title">الواجبات</span>
                                                <span class="hrc-stat-val">{{ $s['assignments_count'] }}</span>
                                            </div>
                                            <div class="hrc-stat-box {{ $s['pending_count'] > 0 ? 'stat-amber' : '' }}">
                                                <span class="hrc-stat-title">بانتظار التصحيح</span>
                                                <span class="hrc-stat-val">{{ $s['pending_count'] }}</span>
                                            </div>
                                            <div class="hrc-stat-box stat-emerald">
                                                <span class="hrc-stat-title">حالة الجلسة</span>
                                                <span class="hrc-stat-val" style="font-size: 0.85rem;">{{ $s['status'] ?? 'مجدولة' }}</span>
                                            </div>
                                            <div class="hrc-stat-box">
                                                <span class="hrc-stat-title">الإجراء</span>
                                                <span class="hrc-stat-val" style="font-size: 0.85rem;">استعراض</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hrc-card-footer">
                                        <span class="hrc-action-indicator {{ $s['pending_count'] > 0 ? 'needs-action' : 'completed' }}">
                                            {{ $s['pending_count'] > 0 ? '⚡ يتطلب تصحيح' : '✓ منجز' }}
                                        </span>
                                        <button type="button" wire:click="selectSession({{ $s['id'] }})" class="hrc-btn-primary">
                                            <span>عرض واجبات السيشن</span>
                                            <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Preserved Unlinked Homework (Historical / General) --}}
                    @if($unlinkedHw->isNotEmpty())
                        <div style="margin-top: 2.5rem;">
                            <div style="margin-bottom: 1rem; border-bottom: 2px solid var(--hrc-card-border); padding-bottom: 0.5rem;">
                                <h3 style="font-size: 1.15rem; font-weight: 850; color: var(--hrc-text-main); margin: 0 0 0.25rem 0;">
                                    📂 واجبات عامة للمجموعة (غير مرتبطة بسيشن محدد)
                                </h3>
                                <p style="font-size: 0.8rem; color: var(--hrc-text-muted); margin: 0;">
                                    هذه الواجبات محفوظة في النظام ومتاحة للمراجعة والتصحيح دون ربط بسيشن معين، ومحفوظة تسليماتها ودرجاتها بالكامل.
                                </p>
                            </div>

                            <div class="hrc-assignments-grid">
                                @foreach($unlinkedHw as $u)
                                    <div class="hrc-assignment-card">
                                        <div class="hrc-card-body">
                                            <div class="hrc-badge-row">
                                                <span class="hrc-chip hrc-chip-slate">واجب عام</span>
                                                <span class="hrc-chip" style="background: var(--hrc-amber-light); color: var(--hrc-amber);">
                                                    ⭐ الدرجة: {{ $u->max_score ?? 10 }}
                                                </span>
                                            </div>
                                            <h4 class="hrc-assignment-title">{{ $u->title }}</h4>
                                            <div class="hrc-meta-row">
                                                <x-heroicon-o-calendar class="w-4 h-4" />
                                                <span>الاستحقاق: {{ $u->due_date ? $u->due_date->format('Y-m-d') : 'مفتوح' }}</span>
                                            </div>
                                        </div>
                                        <div class="hrc-card-footer">
                                            <span class="hrc-action-indicator completed">✓ متاح للتصحيح</span>
                                            <button type="button" wire:click="selectAssignment({{ $u->id }})" class="hrc-btn-primary">
                                                <span>مراجعة وتصحيح الواجب</span>
                                                <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

            {{-- ======================================================== --}}
            {{-- TAB 2: EXAMS (الامتحانات)                                 --}}
            {{-- ======================================================== --}}
            @elseif($activeTab === 'exams')
                @if($this->selectedExamId && $this->activeExam)
                    @php $exam = $this->activeExam; @endphp
                    {{-- Exam Detail Banner --}}
                    <div class="hrc-card" style="border-inline-start: 4px solid var(--hrc-purple); padding: 1.35rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <div class="hrc-badge-row">
                                    <span class="hrc-chip hrc-chip-primary">
                                        نوع الاختبار: {{ $exam->type }}
                                    </span>
                                    @if($exam->attendanceSession)
                                        <span class="hrc-chip hrc-chip-slate">
                                            مرتبط بسيشن: {{ $exam->attendanceSession->session_notes ?: 'سيشن محدد' }}
                                        </span>
                                    @else
                                        <span class="hrc-chip hrc-chip-slate">
                                            🌐 امتحان شامل للمجموعة (بدون سيشن)
                                        </span>
                                    @endif
                                    <span class="hrc-chip" style="background: var(--hrc-amber-light); color: var(--hrc-amber); font-weight: 800;">
                                        ⭐ الدرجة الكلية: {{ $exam->total_marks }}
                                    </span>
                                    <span class="hrc-chip" style="background: var(--hrc-emerald-light); color: var(--hrc-emerald); font-weight: 800;">
                                        درجة النجاح: {{ $exam->passing_marks }}
                                    </span>
                                    <span class="hrc-chip" style="background: var(--hrc-blue-light); color: var(--hrc-blue); font-weight: 800;">
                                        ⏱️ المدة: {{ $exam->duration }} دقيقة
                                    </span>
                                </div>
                                <h2 style="font-size: 1.3rem; font-weight: 850; margin: 0.35rem 0 0.2rem 0; color: var(--hrc-text-main);">{{ $exam->title }}</h2>
                                @if($exam->instructions)
                                    <p style="font-size: 0.85rem; color: var(--hrc-text-muted); margin: 0; max-width: 800px;">{{ $exam->instructions }}</p>
                                @endif
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                {{-- Publish / Hide Exam Toggle --}}
                                <button type="button" wire:click="togglePublishExam({{ $exam->id }})"
                                    class="{{ $exam->published_at ? 'hrc-btn-secondary' : 'hrc-btn-emerald' }}">
                                    @if($exam->published_at)
                                        <x-heroicon-o-eye-slash class="w-4 h-4" />
                                        <span>حجب النتيجة عن الطلاب</span>
                                    @else
                                        <x-heroicon-o-check-badge class="w-4 h-4" />
                                        <span>اعتماد ونشر النتيجة رسمياً</span>
                                    @endif
                                </button>

                                <button type="button" wire:click="backToExamsList" class="hrc-btn-secondary">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                                    <span>العودة لقائمة الامتحانات</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Exam Student Attempts Table --}}
                    @php $examRoster = $this->examStudentRoster; @endphp
                    <div class="hrc-table-card">
                        <div style="overflow-x: auto;">
                            <table class="hrc-table">
                                <thead>
                                    <tr>
                                        <th>اسم الطالب</th>
                                        <th style="text-align: center;">حالة أداء الامتحان</th>
                                        <th style="text-align: center;">عدد المحاولات</th>
                                        <th style="text-align: center;">الدرجة الرسمية (الأعلى)</th>
                                        <th style="text-align: center;">حالة النشر للطالب</th>
                                        <th style="text-align: center;">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($examRoster as $row)
                                        <tr>
                                            <td>
                                                <div style="font-weight: 800; color: var(--hrc-text-main); font-size: 0.95rem;">
                                                    {{ $row['student_name'] }}
                                                </div>
                                                <span style="font-size: 0.75rem; font-family: monospace; color: var(--hrc-text-muted);">
                                                    {{ $row['student_code'] }}
                                                </span>
                                            </td>

                                            <td style="text-align: center;">
                                                @if($row['status'] === 'completed')
                                                    <span class="hrc-pill-badge hrc-pill-success">✓ تم إتمام الاختبار</span>
                                                @elseif($row['status'] === 'in_progress')
                                                    <span class="hrc-pill-badge hrc-pill-warning">⏳ جاري أداء الاختبار</span>
                                                @else
                                                    <span class="hrc-pill-badge hrc-pill-neutral">لم يبدأ بعد</span>
                                                @endif
                                            </td>

                                            <td style="text-align: center; font-weight: 800;">
                                                {{ $row['attempts_count'] }}
                                            </td>

                                            <td style="text-align: center; font-weight: 850; font-size: 1rem;">
                                                @if($row['official_score'] !== null)
                                                    <span class="hrc-pill-badge hrc-pill-success">
                                                        {{ $row['score_display'] }}
                                                    </span>
                                                @else
                                                    <span style="color: var(--hrc-text-muted);">—</span>
                                                @endif
                                            </td>

                                            <td style="text-align: center;">
                                                @if($exam->published_at)
                                                    <span style="font-size: 0.8rem; font-weight: 800; color: var(--hrc-emerald);">
                                                        ✓ منشورة ومعتمدة
                                                    </span>
                                                @else
                                                    <span style="font-size: 0.8rem; font-weight: 800; color: var(--hrc-amber);">
                                                        ⏳ غير منشورة للطالب
                                                    </span>
                                                @endif
                                            </td>

                                            <td style="text-align: center;">
                                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                                                    @if($row['attempts_count'] > 0)
                                                        <button type="button"
                                                            wire:click="openStudentAttemptsModal({{ $exam->id }}, {{ $row['student_id'] }})"
                                                            class="hrc-btn-secondary"
                                                            style="padding: 0.4rem 0.85rem; font-size: 0.78rem;">
                                                            <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                                            <span>عرض المحاولات</span>
                                                        </button>
                                                    @endif

                                                    {{-- إذن إعادة الامتحان المستقل --}}
                                                    <button type="button"
                                                        wire:click="openRetakeModal({{ $exam->id }}, {{ $row['student_id'] }}, '{{ addslashes($row['student_name']) }}')"
                                                        class="hrc-btn-secondary"
                                                        title="منح الطالب إذن إعادة امتحان لمرة واحدة"
                                                        style="padding: 0.4rem 0.75rem; font-size: 0.78rem; color: var(--hrc-purple); border-color: rgba(124, 58, 237, 0.3);">
                                                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                                                        <span>منح فرصة إعادة</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="padding: 3rem 1rem; text-align: center; color: var(--hrc-text-muted);">
                                                لا يوجد طلاب مسجلون في هذه المجموعة
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                @else
                    {{-- EXAMS LIST FOR ACTIVE GROUP --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                                امتحانات المجموعة ({{ $this->groupExams->count() }} امتحان)
                            </h3>
                            <p style="font-size: 0.8rem; color: var(--hrc-text-muted); margin: 0.2rem 0 0 0;">
                                يمكنك ربط الامتحان بسيشن محدد أو جعله امتحاناً شاملاً لكافة المجموعة بدون سيشن.
                            </p>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <button type="button" wire:click="openCreateExamModal" class="hrc-btn-primary">
                                <x-heroicon-o-plus-circle class="w-4 h-4" />
                                <span>إنشاء امتحان جديد</span>
                            </button>
                        </div>
                    </div>

                    @php $exams = $this->groupExams; @endphp
                    @if($exams->isEmpty())
                        <div class="hrc-empty-box">
                            <x-heroicon-o-document-text class="hrc-empty-icon" />
                            <h3 class="hrc-empty-title">لا توجد امتحانات مضافة لهذه المجموعة بعد</h3>
                            <p class="hrc-empty-sub">انقر فوق "إنشاء امتحان جديد" لإضافة امتحان كويز أو امتحان شامل للمجموعة</p>
                        </div>
                    @else
                        <div class="hrc-exams-grid">
                            @foreach($exams as $ex)
                                <div class="hrc-exam-card">
                                    <div class="hrc-card-body">
                                        <div class="hrc-badge-row">
                                            <span class="hrc-chip hrc-chip-primary">
                                                {{ $ex->type }}
                                            </span>
                                            @if($ex->attendanceSession)
                                                <span class="hrc-chip hrc-chip-slate">
                                                    سيشن مرتبط
                                                </span>
                                            @else
                                                <span class="hrc-chip hrc-chip-slate">
                                                    امتحان شامل
                                                </span>
                                            @endif
                                            @if($ex->published_at)
                                                <span class="hrc-chip" style="background: var(--hrc-emerald-light); color: var(--hrc-emerald); font-weight: 800;">
                                                    ✓ منشور
                                                </span>
                                            @else
                                                <span class="hrc-chip" style="background: var(--hrc-amber-light); color: var(--hrc-amber); font-weight: 800;">
                                                    ⏳ غير منشور
                                                </span>
                                            @endif
                                        </div>

                                        <h4 class="hrc-exam-title" title="{{ $ex->title }}">
                                            {{ $ex->title }}
                                        </h4>

                                        <div class="hrc-meta-row">
                                            <x-heroicon-o-calendar class="w-4 h-4" />
                                            <span>الموعد: {{ $ex->exam_date ? $ex->exam_date->format('Y-m-d') : 'غير محدد' }}</span>
                                            <span>•</span>
                                            <span>المدة: {{ $ex->duration }} دقيقة</span>
                                            <span>•</span>
                                            <span>الدرجة: {{ $ex->total_marks }}</span>
                                        </div>

                                        <div class="hrc-stats-strip">
                                            <div class="hrc-stat-box">
                                                <span class="hrc-stat-title">الأسئلة</span>
                                                <span class="hrc-stat-val">{{ $ex->questions_count }}</span>
                                            </div>
                                            <div class="hrc-stat-box stat-blue">
                                                <span class="hrc-stat-title">المحاولات</span>
                                                <span class="hrc-stat-val">{{ $ex->attempts_count }}</span>
                                            </div>
                                            <div class="hrc-stat-box stat-emerald">
                                                <span class="hrc-stat-title">النجاح</span>
                                                <span class="hrc-stat-val">{{ $ex->passing_marks }}</span>
                                            </div>
                                            <div class="hrc-stat-box">
                                                <span class="hrc-stat-title">الحالة</span>
                                                <span class="hrc-stat-val" style="font-size: 0.85rem;">{{ $ex->status }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hrc-card-footer">
                                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                                            <button type="button" wire:click="openEditExamModal({{ $ex->id }})" class="hrc-btn-secondary" style="padding: 0.45rem 0.75rem;">
                                                <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                                                <span>تعديل</span>
                                            </button>
                                        </div>

                                        <button type="button" wire:click="selectExam({{ $ex->id }})" class="hrc-btn-primary">
                                            <span>استعراض محاولات الطلاب</span>
                                            <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif

            {{-- ======================================================== --}}
            {{-- TAB 3: SUBMISSIONS (التسليمات)                            --}}
            {{-- ======================================================== --}}
            @elseif($activeTab === 'submissions')
                @php $submissions = $this->allGroupSubmissions; @endphp
                {{-- Filters Toolbar for All Submissions --}}
                <div class="hrc-card" style="margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                                كافة تسليمات الواجبات في المجموعة ({{ $submissions->count() }} تسليم)
                            </h3>
                            <p style="font-size: 0.8rem; color: var(--hrc-text-muted); margin: 0.2rem 0 0 0;">
                                قائمة موحدة لكافة التسليمات عبر جميع السيشنات بدون الحاجة للتنقل بين الجلسات.
                            </p>
                        </div>

                        {{-- Search student input --}}
                        <div class="hrc-search-box">
                            <x-heroicon-o-magnifying-glass class="hrc-search-icon" />
                            <input type="text"
                                wire:model.live.debounce.250ms="submissionsSearchStudent"
                                placeholder="بحث باسم الطالب..."
                                class="hrc-input hrc-search-input" />
                        </div>
                    </div>

                    {{-- Filters Row --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--hrc-card-border);">
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 750; color: var(--hrc-text-muted); display: block; margin-bottom: 0.25rem;">تصفية بحسب السيشن</label>
                            <select wire:model.live="submissionsFilterSession" class="hrc-select">
                                <option value="all">كافة السيشنات</option>
                                @foreach($sessions as $sess)
                                    <option value="{{ $sess['id'] }}">الجلسة {{ $sess['session_number'] }}: {{ $sess['title'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 0.78rem; font-weight: 750; color: var(--hrc-text-muted); display: block; margin-bottom: 0.25rem;">تصفية بحسب حالة التصحيح</label>
                            <select wire:model.live="submissionsFilterStatus" class="hrc-select">
                                <option value="all">كافة الحالات</option>
                                <option value="awaiting_grading">بانتظار التصحيح أولاً</option>
                                <option value="graded">تم التصحيح والاعتماد</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Unified Submissions Table --}}
                <div class="hrc-table-card">
                    <div style="overflow-x: auto;">
                        <table class="hrc-table">
                            <thead>
                                <tr>
                                    <th>اسم الطالب</th>
                                    <th>الواجب والسيشن</th>
                                    <th style="text-align: center;">وقت التسليم</th>
                                    <th style="text-align: center;">المرفقات والصور</th>
                                    <th style="text-align: center;">الدرجة الرسمية</th>
                                    <th style="text-align: center;">حالة التصحيح</th>
                                    <th style="text-align: center;">الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($submissions as $sub)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 850; color: var(--hrc-text-main); font-size: 0.95rem;">
                                                {{ $sub->student?->user?->name ?? 'طالب' }}
                                            </div>
                                            <span style="font-size: 0.75rem; font-family: monospace; color: var(--hrc-text-muted);">
                                                {{ $sub->student?->student_code ?? "STD-{$sub->student_id}" }}
                                            </span>
                                        </td>

                                        <td>
                                            <div style="font-weight: 800; color: var(--hrc-text-main);">
                                                {{ $sub->assignment?->title }}
                                            </div>
                                            <span style="font-size: 0.76rem; color: var(--hrc-text-muted);">
                                                {{ $sub->assignment?->attendanceSession?->session_notes ?: 'سيشن عام' }}
                                            </span>
                                        </td>

                                        <td style="text-align: center; font-family: monospace; font-size: 0.8rem;" dir="ltr">
                                            {{ $sub->submitted_at ? $sub->submitted_at->format('Y-m-d H:i') : '—' }}
                                        </td>

                                        <td style="text-align: center;">
                                            <span class="hrc-chip hrc-chip-primary">
                                                📷 {{ $sub->image_count }} صورة
                                            </span>
                                        </td>

                                        <td style="text-align: center; font-weight: 900; font-size: 1rem;">
                                            @if($sub->score !== null)
                                                <span class="hrc-pill-badge hrc-pill-success">
                                                    {{ $sub->score }} / {{ $sub->assignment?->max_score ?? 10 }}
                                                </span>
                                            @else
                                                <span style="color: var(--hrc-text-muted);">—</span>
                                            @endif
                                        </td>

                                        <td style="text-align: center;">
                                            @if($sub->graded)
                                                <span style="font-size: 0.82rem; font-weight: 800; color: var(--hrc-emerald);">
                                                    ✓ مصحح ومعتمد
                                                </span>
                                            @else
                                                <span style="font-size: 0.82rem; font-weight: 800; color: var(--hrc-amber);">
                                                    ⏳ بانتظار التصحيح
                                                </span>
                                            @endif
                                        </td>

                                        <td style="text-align: center;">
                                            <button type="button"
                                                wire:click="openReview({{ $sub->student_id }}, {{ $sub->id }})"
                                                class="hrc-btn-primary"
                                                style="padding: 0.45rem 1rem; font-size: 0.8rem;">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>عرض الإجابة والتصحيح</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="padding: 3.5rem 1rem; text-align: center; color: var(--hrc-text-muted);">
                                            <x-heroicon-o-inbox class="w-12 h-12" style="margin: 0 auto 0.75rem auto; opacity: 0.4;" />
                                            <div style="font-size: 1rem; font-weight: 750;">لا توجد تسليمات تطابق معايير التصفية</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            {{-- ======================================================== --}}
            {{-- TAB 4: RESULTS BY STUDENT (النتائج)                      --}}
            {{-- ======================================================== --}}
            @elseif($activeTab === 'results')
                @php $results = $this->groupStudentsResults; @endphp
                {{-- Header & Search --}}
                <div class="hrc-card" style="margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                                السجل الأكاديمي الشامل لطلاب المجموعة ({{ $results->count() }} طالب)
                            </h3>
                            <p style="font-size: 0.8rem; color: var(--hrc-text-muted); margin: 0.2rem 0 0 0;">
                                استعراض درجات الواجبات، نتائج الامتحانات، والتنبيهات الأكاديمية (الدرجات الأقل من 50%) لكل طالب.
                            </p>
                        </div>

                        <div class="hrc-search-box">
                            <x-heroicon-o-magnifying-glass class="hrc-search-icon" />
                            <input type="text"
                                wire:model.live.debounce.250ms="resultsSearchStudent"
                                placeholder="بحث باسم الطالب أو الكود..."
                                class="hrc-input hrc-search-input" />
                        </div>
                    </div>
                </div>

                {{-- Results Table --}}
                <div class="hrc-table-card">
                    <div style="overflow-x: auto;">
                        <table class="hrc-table">
                            <thead>
                                <tr>
                                    <th>اسم الطالب</th>
                                    <th style="text-align: center;">إنجاز الواجبات</th>
                                    <th style="text-align: center;">متوسط درجات الواجبات</th>
                                    <th style="text-align: center;">الامتحانات المكتملة</th>
                                    <th style="text-align: center;">متوسط نسبة الامتحانات</th>
                                    <th style="text-align: center;">التنبيهات الأكاديمية</th>
                                    <th style="text-align: center;">السجل التفصيلي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results as $r)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 850; color: var(--hrc-text-main); font-size: 0.95rem;">
                                                {{ $r['name'] }}
                                            </div>
                                            <span style="font-size: 0.75rem; font-family: monospace; color: var(--hrc-text-muted);">
                                                {{ $r['code'] }}
                                            </span>
                                        </td>

                                        <td style="text-align: center; font-weight: 800;">
                                            <span class="hrc-chip hrc-chip-slate">
                                                {{ $r['hw_completed'] }} من {{ $r['hw_total'] }} واجب
                                            </span>
                                        </td>

                                        <td style="text-align: center; font-weight: 900; font-size: 1rem; color: var(--hrc-primary);">
                                            {{ $r['hw_avg'] }}
                                        </td>

                                        <td style="text-align: center; font-weight: 800;">
                                            <span class="hrc-chip hrc-chip-slate">
                                                {{ $r['exam_completed'] }} من {{ $r['exam_total'] }} امتحان
                                            </span>
                                        </td>

                                        <td style="text-align: center; font-weight: 900; font-size: 1rem; color: var(--hrc-purple);">
                                            {{ $r['exam_avg'] }}%
                                        </td>

                                        <td style="text-align: center;">
                                            @if($r['has_alert'])
                                                <span class="hrc-pill-badge hrc-pill-danger" title="يوجد نتائج درجاتها أقل من 50%">
                                                    ⚠️ تنبيه درجة منخفضة (< 50%)
                                                </span>
                                            @else
                                                <span class="hrc-pill-badge hrc-pill-success">
                                                    ✓ المستوى منتظم
                                                </span>
                                            @endif
                                        </td>

                                        <td style="text-align: center;">
                                            <button type="button"
                                                wire:click="openStudentAcademicProfile({{ $r['student_id'] }})"
                                                class="hrc-btn-primary"
                                                style="padding: 0.45rem 1rem; font-size: 0.8rem;">
                                                <x-heroicon-o-identification class="w-4 h-4" />
                                                <span>عرض الملف الأكاديمي</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="padding: 3.5rem 1rem; text-align: center; color: var(--hrc-text-muted);">
                                            <x-heroicon-o-user-group class="w-12 h-12" style="margin: 0 auto 0.75rem auto; opacity: 0.4;" />
                                            <div style="font-size: 1rem; font-weight: 750;">لا يوجد طلاب يطابقون البحث</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        {{-- ======================================================== --}}
        {{-- VIEW MODE 3: ALL GROUPS DASHBOARD (WHEN NO GROUP CHOSEN) --}}
        {{-- ======================================================== --}}
        @else
            @php $kpis = $this->kpiMetrics; @endphp
            <div class="hrc-kpi-grid">
                <div class="hrc-kpi-card kpi-total">
                    <span class="hrc-kpi-label">إجمالي الواجبات</span>
                    <p class="hrc-kpi-num">{{ $kpis['total_assignments'] }}</p>
                    <span class="hrc-kpi-sub">لكافة مجموعاتك</span>
                </div>
                <div class="hrc-kpi-card kpi-review">
                    <span class="hrc-kpi-label">واجبات تحتاج مراجعة</span>
                    <p class="hrc-kpi-num">{{ $kpis['needs_review'] }}</p>
                    <span class="hrc-kpi-sub">تسليمات جديدة</span>
                </div>
                <div class="hrc-kpi-card kpi-missing">
                    <span class="hrc-kpi-label">لم يسلّموا بعد</span>
                    <p class="hrc-kpi-num">{{ $kpis['missing_submissions'] }}</p>
                    <span class="hrc-kpi-sub">بانتظار التسليم</span>
                </div>
                <div class="hrc-kpi-card kpi-overdue">
                    <span class="hrc-kpi-label">طلاب متأخرون</span>
                    <p class="hrc-kpi-num">{{ $kpis['overdue_students'] }}</p>
                    <span class="hrc-kpi-sub">تجاوزوا الموعد</span>
                </div>
                <div class="hrc-kpi-card kpi-drafts">
                    <span class="hrc-kpi-label">مسودات تقييم</span>
                    <p class="hrc-kpi-num">{{ $kpis['drafts_awaiting_approval'] }}</p>
                    <span class="hrc-kpi-sub">مصححة كمسودة</span>
                </div>
                <div class="hrc-kpi-card kpi-graded">
                    <span class="hrc-kpi-label">واجبات تم تصحيحها</span>
                    <p class="hrc-kpi-num">{{ $kpis['graded_assignments'] }}</p>
                    <span class="hrc-kpi-sub">منشورة رسمياً</span>
                </div>
            </div>

            {{-- Filter by Group/Course --}}
            <div class="hrc-card">
                <div class="hrc-form-grid-2">
                    <div class="hrc-field">
                        <label>المقرر الدراسي</label>
                        <select wire:model.live="selectedCourseId" class="hrc-select">
                            <option value="">-- كل المقررات --</option>
                            @foreach($this->courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="hrc-field">
                        <label>المجموعة (اختر مجموعة لفتح المركز الأكاديمي الشامل)</label>
                        <select wire:model.live="selectedGroupId" class="hrc-select">
                            <option value="">-- اختر مجموعة دراسية --</option>
                            @foreach($this->groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->course?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL 1: HOMEWORK REVIEW & GRADING (تم التصحيح الفوري)      --}}
        {{-- ======================================================== --}}
        @if($isReviewOpen && $selectedStudent)
            <div class="hrc-modal-backdrop" wire:click.self="closeReview">
                <div class="hrc-modal-card modal-lg">
                    <div class="hrc-modal-header">
                        <div>
                            <div class="hrc-badge-row">
                                <span class="hrc-chip hrc-chip-primary">
                                    {{ $this->activeAssignment?->title }}
                                </span>
                                @if($selectedSubmission?->isLate())
                                    <span class="hrc-pill-badge hrc-pill-danger">⏱️ سُلّم متأخرًا</span>
                                @else
                                    <span class="hrc-pill-badge hrc-pill-success">✓ سُلّم في الموعد</span>
                                @endif
                            </div>
                            <h3 style="font-size: 1.25rem; font-weight: 850; color: var(--hrc-text-main); margin: 0;">
                                مراجعة وتصحيح حل الطالب: {{ $selectedStudent->user?->name }}
                            </h3>
                            <p style="font-size: 0.78rem; color: var(--hrc-text-muted); margin: 0.2rem 0 0 0;">
                                تاريخ التسليم: {{ $selectedSubmission?->submitted_at ? $selectedSubmission->submitted_at->format('Y-m-d H:i') : '—' }}
                            </p>
                        </div>

                        <button type="button" wire:click="closeReview" style="background: none; border: none; font-size: 1.5rem; color: var(--hrc-text-muted); cursor: pointer; padding: 0.5rem; line-height: 1;">
                            ✕
                        </button>
                    </div>

                    <div class="hrc-modal-body">
                        {{-- Solution Images Gallery --}}
                        @if($selectedSubmission && $selectedSubmission->image_count > 0)
                            @php $imgs = $selectedSubmission->images_list; @endphp
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <label style="font-size: 0.82rem; font-weight: 800; color: var(--hrc-text-main);">
                                        صور حل الطالب ({{ count($imgs) }} صورة):
                                    </label>
                                    <button type="button" wire:click="openLightbox({{ $activeImageIndex }})" style="background: none; border: none; font-size: 0.8rem; font-weight: 750; color: var(--hrc-primary); cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <x-heroicon-o-arrows-pointing-out class="w-4 h-4" />
                                        <span>تكبير وتفحص الصورة الحالية</span>
                                    </button>
                                </div>

                                <div class="hrc-theater">
                                    <img src="{{ route('assignments.submissions.image', ['submission' => $selectedSubmission->id, 'index' => $activeImageIndex]) }}"
                                        alt="صورة الواجب"
                                        class="hrc-theater-img"
                                        wire:click="openLightbox({{ $activeImageIndex }})" />

                                    @if(count($imgs) > 1)
                                        <button type="button" wire:click="prevImage" class="hrc-nav-arrow hrc-nav-prev">
                                            <x-heroicon-o-chevron-right class="w-5 h-5" />
                                        </button>
                                        <button type="button" wire:click="nextImage" class="hrc-nav-arrow hrc-nav-next">
                                            <x-heroicon-o-chevron-left class="w-5 h-5" />
                                        </button>
                                    @endif
                                </div>

                                @if(count($imgs) > 1)
                                    <div class="hrc-thumb-strip">
                                        @foreach($imgs as $idx => $thumb)
                                            <button type="button"
                                                wire:click="setActiveImage({{ $idx }})"
                                                class="hrc-thumb-btn {{ $activeImageIndex === $idx ? 'active' : '' }}">
                                                <img src="{{ route('assignments.submissions.image', ['submission' => $selectedSubmission->id, 'index' => $idx]) }}"
                                                    alt="thumb" />
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="hrc-empty-box" style="padding: 2.5rem 1.5rem;">
                                <x-heroicon-o-photo class="w-10 h-10" style="margin: 0 auto 0.5rem auto; color: var(--hrc-text-muted);" />
                                <div style="font-size: 0.9rem; font-weight: 750; color: var(--hrc-text-muted);">لم يرفق الطالب صوراً مع هذا التسليم.</div>
                            </div>
                        @endif

                        @if($selectedSubmission?->submission_notes)
                            <div style="background: #F8FAFC; border: 1px solid var(--hrc-card-border); border-radius: var(--hrc-radius-md); padding: 1rem;">
                                <span style="font-size: 0.78rem; font-weight: 800; color: var(--hrc-text-muted); display: block; margin-bottom: 0.25rem;">ملاحظات الطالب مع الحل:</span>
                                <p style="font-size: 0.88rem; color: var(--hrc-text-main); margin: 0; line-height: 1.4;">{{ $selectedSubmission->submission_notes }}</p>
                            </div>
                        @endif

                        {{-- Grading & Feedback --}}
                        <div class="hrc-grading-card">
                            <h4 style="font-size: 0.95rem; font-weight: 850; color: var(--hrc-primary); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                                <x-heroicon-o-academic-cap class="w-5 h-5" />
                                <span>رصد الدرجة وملاحظات المعلم (نشر فوري للطالب وولي الأمر)</span>
                            </h4>

                            <div class="hrc-form-grid-2">
                                <div>
                                    <label style="display: block; font-size: 0.8rem; font-weight: 750; color: var(--hrc-text-main); margin-bottom: 0.35rem;">
                                        الدرجة (من {{ $this->activeAssignment?->max_score ?? 10 }}) *
                                    </label>
                                    <div class="hrc-score-wrapper">
                                        <input type="number"
                                            step="0.5"
                                            min="0"
                                            max="{{ $this->activeAssignment?->max_score ?? 10 }}"
                                            wire:model="score"
                                            placeholder="أدخل الدرجة..."
                                            class="hrc-score-input" />
                                        <span class="hrc-score-suffix">
                                            / {{ $this->activeAssignment?->max_score ?? 10 }}
                                        </span>
                                    </div>
                                    @error('score')
                                        <span style="font-size: 0.75rem; color: var(--hrc-red); font-weight: 750; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label style="display: block; font-size: 0.8rem; font-weight: 750; color: var(--hrc-text-main); margin-bottom: 0.35rem;">
                                        حالة التقييم الحالية
                                    </label>
                                    <div style="padding: 0.75rem 1rem; background: var(--hrc-card-bg); border-radius: var(--hrc-radius-md); border: 1px solid var(--hrc-card-border); font-size: 0.82rem;">
                                        @if($selectedSubmission?->isPublished())
                                            <span style="font-weight: 850; color: var(--hrc-emerald);">
                                                ✓ منشورة ومعتمدة للطالب وولي الأمر والسكرتيرة
                                            </span>
                                        @else
                                            <span style="font-weight: 850; color: var(--hrc-amber);">
                                                ⏳ بانتظار اعتماد التصحيح
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 750; color: var(--hrc-text-main); margin-bottom: 0.35rem;">
                                    ملاحظات وتوجيهات المعلم للطالب (اختياري)
                                </label>
                                <textarea wire:model="instructor_feedback"
                                    rows="3"
                                    placeholder="اكتب توجيهاتك للطالب أو نقاط القوة ونقاط التحسين في حله..."
                                    class="hrc-textarea"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="hrc-modal-footer">
                        <button type="button" wire:click="closeReview" class="hrc-btn-secondary">
                            إغلاق النافذة
                        </button>

                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            {{-- Save as Draft --}}
                            <button type="button" wire:click="saveDraft" class="hrc-btn-secondary" style="color: var(--hrc-purple); border-color: rgba(124, 58, 237, 0.4);">
                                <x-heroicon-o-document-text class="w-4 h-4" />
                                <span>حفظ كمسودة</span>
                            </button>

                            {{-- Primary Action: تم التصحيح الفوري --}}
                            <button type="button" wire:click="markCorrected" class="hrc-btn-primary" style="background: linear-gradient(135deg, #059669 0%, #10B981 100%);">
                                <x-heroicon-o-check-badge class="w-5 h-5" />
                                <span>تم التصحيح ✓ (نشر فوري)</span>
                            </button>

                            {{-- Next Student Navigation --}}
                            @if($this->nextPendingStudent)
                                <button type="button" wire:click="nextStudent" class="hrc-btn-primary" style="background: #1E293B;">
                                    <span>الطالب التالي</span>
                                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL 2: CREATE / EDIT HOMEWORK                          --}}
        {{-- ======================================================== --}}
        @if($isHomeworkModalOpen)
            <div class="hrc-modal-backdrop" wire:click.self="closeHomeworkModal">
                <div class="hrc-modal-card modal-md">
                    <div class="hrc-modal-header">
                        <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                            {{ $hw_id ? 'تعديل بيانات الواجب' : 'إضافة واجب جديد للمجموعة' }}
                        </h3>
                        <button type="button" wire:click="closeHomeworkModal" style="background: none; border: none; font-size: 1.5rem; color: var(--hrc-text-muted); cursor: pointer;">✕</button>
                    </div>

                    <div class="hrc-modal-body">
                        <div class="hrc-field">
                            <label>عنوان الواجب *</label>
                            <input type="text" wire:model="hw_title" placeholder="أدخل عنوان الواجب..." class="hrc-input" />
                            @error('hw_title') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                        </div>

                        <div class="hrc-form-grid-2">
                            <div class="hrc-field">
                                <label>السيشن المرتبط</label>
                                <select wire:model="hw_session_id" class="hrc-select">
                                    <option value="">-- بدون سيشن (واجب عام للمجموعة) --</option>
                                    @foreach($this->groupSessions as $sess)
                                        <option value="{{ $sess['id'] }}">الجلسة {{ $sess['session_number'] }}: {{ $sess['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="hrc-field">
                                <label>الدرجة الكلية *</label>
                                <input type="number" step="0.5" wire:model="hw_max_score" class="hrc-input" />
                                @error('hw_max_score') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="hrc-form-grid-2">
                            <div class="hrc-field">
                                <label>تاريخ الاستحقاق (الموعد النهائي) *</label>
                                <input type="date" wire:model="hw_due_date" class="hrc-input" />
                                @error('hw_due_date') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                            </div>

                            <div class="hrc-field">
                                <label>حالة النشر</label>
                                <select wire:model="hw_status" class="hrc-select">
                                    <option value="published">منشور للطلاب فوراً</option>
                                    <option value="draft">مسودة غير منشورة</option>
                                </select>
                            </div>
                        </div>

                        <div class="hrc-field">
                            <label>تعليمات وملاحظات الواجب</label>
                            <textarea wire:model="hw_description" rows="3" placeholder="اكتب تعليمات الواجب والأسئلة المطلوبة..." class="hrc-textarea"></textarea>
                        </div>
                    </div>

                    <div class="hrc-modal-footer">
                        <button type="button" wire:click="closeHomeworkModal" class="hrc-btn-secondary">إلغاء</button>
                        <button type="button" wire:click="saveHomework" class="hrc-btn-primary">حفظ الواجب</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL 3: DEADLINE EXTENSION (تمديد الموعد)                --}}
        {{-- ======================================================== --}}
        @if($isExtensionModalOpen)
            <div class="hrc-modal-backdrop" wire:click.self="$set('isExtensionModalOpen', false)">
                <div class="hrc-modal-card modal-md">
                    <div class="hrc-modal-header">
                        <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                            {{ $extensionScope === 'student' ? 'استثناء تمديد موعد تسليم للطالب: ' . $extensionStudentName : 'تمديد موعد تسليم الواجب لكافة المجموعة' }}
                        </h3>
                        <button type="button" wire:click="$set('isExtensionModalOpen', false)" style="background: none; border: none; font-size: 1.5rem; color: var(--hrc-text-muted); cursor: pointer;">✕</button>
                    </div>

                    <div class="hrc-modal-body">
                        <div class="hrc-field">
                            <label>الموعد النهائي الجديد *</label>
                            <input type="datetime-local" wire:model="extensionNewDueDate" class="hrc-input" />
                            @error('extensionNewDueDate') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                        </div>

                        <div class="hrc-field">
                            <label>سبب التمديد (إلزامي للتوثيق والتدقيق) *</label>
                            <textarea wire:model="extensionReason" rows="3" placeholder="أدخل سبب التمديد..." class="hrc-textarea"></textarea>
                            @error('extensionReason') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="hrc-modal-footer">
                        <button type="button" wire:click="$set('isExtensionModalOpen', false)" class="hrc-btn-secondary">إلغاء</button>
                        <button type="button" wire:click="saveExtension" class="hrc-btn-primary">حفظ التمديد</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL 4: RESUBMISSION GRANT (منح إذن إعادة تسليم)         --}}
        {{-- ======================================================== --}}
        @if($isResubmissionModalOpen)
            <div class="hrc-modal-backdrop" wire:click.self="$set('isResubmissionModalOpen', false)">
                <div class="hrc-modal-card modal-md">
                    <div class="hrc-modal-header">
                        <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                            منح إذن إعادة تسليم واجب للطالب: {{ $resubmissionStudentName }}
                        </h3>
                        <button type="button" wire:click="$set('isResubmissionModalOpen', false)" style="background: none; border: none; font-size: 1.5rem; color: var(--hrc-text-muted); cursor: pointer;">✕</button>
                    </div>

                    <div class="hrc-modal-body">
                        <p style="font-size: 0.85rem; color: var(--hrc-text-muted); margin: 0;">
                            يسمح هذا الإذن للطالب بتقديم تسليم جديد إضافي لمرة واحدة للواجب المصحح. ستحتسب الدرجة الرسمية تلقائياً كأعلى درجة مصححة من بين تسليمات الطالب.
                        </p>

                        <div class="hrc-field">
                            <label>صلاحية إذن إعادة التسليم حتى *</label>
                            <input type="datetime-local" wire:model="resubmissionExpiresAt" class="hrc-input" />
                            @error('resubmissionExpiresAt') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                        </div>

                        <div class="hrc-field">
                            <label>سبب منح الإعادة (إلزامي للتوثيق والتدقيق الأكاديمي) *</label>
                            <textarea wire:model="resubmissionReason" rows="3" placeholder="أدخل سبب الموافقة على إعادة التسليم..." class="hrc-textarea"></textarea>
                            @error('resubmissionReason') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="hrc-modal-footer">
                        <button type="button" wire:click="$set('isResubmissionModalOpen', false)" class="hrc-btn-secondary">إلغاء</button>
                        <button type="button" wire:click="saveResubmissionGrant" class="hrc-btn-primary">إصدار إذن الإعادة</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL 5: GRADE CORRECTION (تصحيح الدرجة المنشورة)        --}}
        {{-- ======================================================== --}}
        @if($isGradeCorrectionModalOpen)
            <div class="hrc-modal-backdrop" wire:click.self="$set('isGradeCorrectionModalOpen', false)">
                <div class="hrc-modal-card modal-md">
                    <div class="hrc-modal-header">
                        <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                            تصحيح درجة منشورة رسميًا مع سجل التدقيق
                        </h3>
                        <button type="button" wire:click="$set('isGradeCorrectionModalOpen', false)" style="background: none; border: none; font-size: 1.5rem; color: var(--hrc-text-muted); cursor: pointer;">✕</button>
                    </div>

                    <div class="hrc-modal-body">
                        <div class="hrc-form-grid-2">
                            <div class="hrc-field">
                                <label>الدرجة الحالية المسجلة</label>
                                <input type="text" value="{{ $correctionOldScore }}" disabled class="hrc-input" style="background: #F1F5F9;" />
                            </div>

                            <div class="hrc-field">
                                <label>الدرجة المعدلة الجديدة *</label>
                                <input type="number" step="0.5" wire:model="correctionNewScore" class="hrc-input" />
                                @error('correctionNewScore') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="hrc-field">
                            <label>سبب تعديل الدرجة (إلزامي ومسجل في سجل التدقيق الأكاديمي) *</label>
                            <textarea wire:model="correctionReason" rows="3" placeholder="أدخل سبب تعديل الدرجة المنشورة..." class="hrc-textarea"></textarea>
                            @error('correctionReason') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="hrc-modal-footer">
                        <button type="button" wire:click="$set('isGradeCorrectionModalOpen', false)" class="hrc-btn-secondary">إلغاء</button>
                        <button type="button" wire:click="saveGradeCorrection" class="hrc-btn-primary">اعتماد وتوثيق التعديل</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL 6: CREATE / EDIT EXAM                              --}}
        {{-- ======================================================== --}}
        @if($isExamModalOpen)
            <div class="hrc-modal-backdrop" wire:click.self="closeExamModal">
                <div class="hrc-modal-card modal-md">
                    <div class="hrc-modal-header">
                        <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                            {{ $exam_id ? 'تعديل إعدادات الامتحان' : 'إنشاء امتحان جديد للمجموعة' }}
                        </h3>
                        <button type="button" wire:click="closeExamModal" style="background: none; border: none; font-size: 1.5rem; color: var(--hrc-text-muted); cursor: pointer;">✕</button>
                    </div>

                    <div class="hrc-modal-body">
                        <div class="hrc-field">
                            <label>عنوان الامتحان *</label>
                            <input type="text" wire:model="exam_title" placeholder="أدخل عنوان الامتحان..." class="hrc-input" />
                            @error('exam_title') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                        </div>

                        <div class="hrc-form-grid-2">
                            <div class="hrc-field">
                                <label>نوع الامتحان</label>
                                <select wire:model="exam_type" class="hrc-select">
                                    <option value="quiz">كويز سريع</option>
                                    <option value="midterm">امتحان نصفي</option>
                                    <option value="final">امتحان نهائي</option>
                                    <option value="comprehensive">امتحان شامل</option>
                                </select>
                            </div>

                            <div class="hrc-field">
                                <label>السيشن المرتبط (اختياري)</label>
                                <select wire:model="exam_session_id" class="hrc-select">
                                    <option value="">-- بدون سيشن (امتحان شامل للمجموعة) --</option>
                                    @foreach($this->groupSessions as $sess)
                                        <option value="{{ $sess['id'] }}">الجلسة {{ $sess['session_number'] }}: {{ $sess['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="hrc-form-grid-2">
                            <div class="hrc-field">
                                <label>موعد إجراء الامتحان *</label>
                                <input type="datetime-local" wire:model="exam_date" class="hrc-input" />
                                @error('exam_date') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                            </div>

                            <div class="hrc-field">
                                <label>مدة الامتحان (بالدقائق) *</label>
                                <input type="number" wire:model="exam_duration" class="hrc-input" />
                                @error('exam_duration') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="hrc-form-grid-2">
                            <div class="hrc-field">
                                <label>الدرجة الكلية *</label>
                                <input type="number" step="0.5" wire:model="exam_total_marks" class="hrc-input" />
                                @error('exam_total_marks') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                            </div>

                            <div class="hrc-field">
                                <label>درجة النجاح *</label>
                                <input type="number" step="0.5" wire:model="exam_passing_marks" class="hrc-input" />
                                @error('exam_passing_marks') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="hrc-field">
                            <label>تعليمات الامتحان للطلاب</label>
                            <textarea wire:model="exam_instructions" rows="2" placeholder="أدخل تعليمات الامتحان..." class="hrc-textarea"></textarea>
                        </div>
                    </div>

                    <div class="hrc-modal-footer">
                        <button type="button" wire:click="closeExamModal" class="hrc-btn-secondary">إلغاء</button>
                        <button type="button" wire:click="saveExam" class="hrc-btn-primary">حفظ الامتحان</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL 7: EXAM RETAKE GRANT (منح إذن إعادة امتحان)         --}}
        {{-- ======================================================== --}}
        @if($isRetakeModalOpen)
            <div class="hrc-modal-backdrop" wire:click.self="$set('isRetakeModalOpen', false)">
                <div class="hrc-modal-card modal-md">
                    <div class="hrc-modal-header">
                        <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                            منح إذن إعادة امتحان للطالب: {{ $retakeStudentName }}
                        </h3>
                        <button type="button" wire:click="$set('isRetakeModalOpen', false)" style="background: none; border: none; font-size: 1.5rem; color: var(--hrc-text-muted); cursor: pointer;">✕</button>
                    </div>

                    <div class="hrc-modal-body">
                        <p style="font-size: 0.85rem; color: var(--hrc-text-muted); margin: 0;">
                            يسمح هذا الإذن للطالب ببدء محاولة اختبار جديدة مستقلة لمرة واحدة. تحتسب الدرجة الرسمية تلقائياً كأعلى درجة من بين جميع المحاولات المكتملة.
                        </p>

                        <div class="hrc-field">
                            <label>صلاحية إذن الإعادة حتى *</label>
                            <input type="datetime-local" wire:model="retakeExpiresAt" class="hrc-input" />
                            @error('retakeExpiresAt') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                        </div>

                        <div class="hrc-field">
                            <label>سبب منح إعادة الامتحان (إلزامي ومسجل للتدقيق) *</label>
                            <textarea wire:model="retakeReason" rows="3" placeholder="أدخل سبب الموافقة على إعادة الامتحان..." class="hrc-textarea"></textarea>
                            @error('retakeReason') <span style="font-size: 0.75rem; color: var(--hrc-red);">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="hrc-modal-footer">
                        <button type="button" wire:click="$set('isRetakeModalOpen', false)" class="hrc-btn-secondary">إلغاء</button>
                        <button type="button" wire:click="saveRetakeGrant" class="hrc-btn-primary">إصدار إذن الإعادة</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL 8: STUDENT EXAM ATTEMPTS DETAILS                   --}}
        {{-- ======================================================== --}}
        @if($isStudentAttemptsModalOpen && $viewAttemptsStudent && $viewAttemptsExam)
            @php 
                $stAttempts = \App\Models\ExamAttempt::where('exam_id', $viewAttemptsExam->id)
                    ->where('student_id', $viewAttemptsStudent->id)
                    ->orderByDesc('score')
                    ->get();
            @endphp
            <div class="hrc-modal-backdrop" wire:click.self="closeStudentAttemptsModal">
                <div class="hrc-modal-card modal-md">
                    <div class="hrc-modal-header">
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                                محاولات الطالب: {{ $viewAttemptsStudent->user?->name }}
                            </h3>
                            <span style="font-size: 0.8rem; color: var(--hrc-text-muted);">
                                في امتحان: {{ $viewAttemptsExam->title }}
                            </span>
                        </div>
                        <button type="button" wire:click="closeStudentAttemptsModal" style="background: none; border: none; font-size: 1.5rem; color: var(--hrc-text-muted); cursor: pointer;">✕</button>
                    </div>

                    <div class="hrc-modal-body">
                        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                            @foreach($stAttempts as $att)
                                <div style="border: 1px solid var(--hrc-card-border); border-radius: var(--hrc-radius-md); padding: 1rem; background: {{ $att->is_official ? 'var(--hrc-emerald-light)' : '#F8FAFC' }};">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                        <span style="font-weight: 850; font-size: 0.95rem;">
                                            المحاولة رقم #{{ $att->attempt_number }}
                                            @if($att->is_official)
                                                <span class="hrc-chip" style="background: var(--hrc-emerald); color: #fff; margin-inline-start: 0.5rem;">
                                                    ⭐ المحاولة الرسمية (الأعلى درجة)
                                                </span>
                                            @endif
                                        </span>
                                        <span style="font-size: 1.15rem; font-weight: 900; color: var(--hrc-emerald);">
                                            {{ $att->score !== null ? $att->score . ' / ' . $att->total_score : 'قيد الأداء' }}
                                        </span>
                                    </div>

                                    <div style="display: flex; gap: 1rem; font-size: 0.78rem; color: var(--hrc-text-muted);">
                                        <span>بدأت: {{ $att->started_at ? $att->started_at->format('Y-m-d H:i') : '—' }}</span>
                                        <span>سُلّمت: {{ $att->completed_at ? $att->completed_at->format('Y-m-d H:i') : 'لم تُسلّم بعد' }}</span>
                                        <span>النسبة: {{ $att->percentage ? $att->percentage . '%' : '—' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="hrc-modal-footer">
                        <button type="button" wire:click="closeStudentAttemptsModal" class="hrc-btn-secondary">إغلاق</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL 9: COMPREHENSIVE STUDENT ACADEMIC PROFILE          --}}
        {{-- ======================================================== --}}
        @if($isStudentProfileModalOpen && $profileStudent && $this->activeGroup)
            @php 
                $profileDetails = app(\App\Services\AcademicCenterService::class)->getStudentComprehensiveAcademicProfile($this->activeGroup, $profileStudent);
            @endphp
            <div class="hrc-modal-backdrop" wire:click.self="closeStudentAcademicProfile">
                <div class="hrc-modal-card modal-lg">
                    <div class="hrc-modal-header">
                        <div>
                            <h3 style="font-size: 1.25rem; font-weight: 850; margin: 0; color: var(--hrc-text-main);">
                                الملف الأكاديمي الشامل: {{ $profileStudent->user?->name }}
                            </h3>
                            <span style="font-size: 0.8rem; color: var(--hrc-text-muted);">
                                في مجموعة: {{ $this->activeGroup->name }} ({{ $this->activeGroup->course?->name }})
                            </span>
                        </div>
                        <button type="button" wire:click="closeStudentAcademicProfile" style="background: none; border: none; font-size: 1.5rem; color: var(--hrc-text-muted); cursor: pointer;">✕</button>
                    </div>

                    <div class="hrc-modal-body">
                        {{-- Summary KPIs for this Student --}}
                        <div class="hrc-header-stats-grid">
                            <div class="hrc-header-stat-box">
                                <div>
                                    <span style="font-size: 0.72rem; color: var(--hrc-text-muted); font-weight: 700; display: block;">الواجبات المسلمة</span>
                                    <span style="font-size: 1.25rem; font-weight: 900; color: var(--hrc-blue);">
                                        {{ $profileDetails['homework_submissions']->where('is_submitted', true)->count() }} / {{ $profileDetails['homework_submissions']->count() }}
                                    </span>
                                </div>
                            </div>
                            <div class="hrc-header-stat-box">
                                <div>
                                    <span style="font-size: 0.72rem; color: var(--hrc-text-muted); font-weight: 700; display: block;">متوسط درجات الواجبات</span>
                                    <span style="font-size: 1.25rem; font-weight: 900; color: var(--hrc-emerald);">
                                        {{ $profileDetails['homework_avg'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="hrc-header-stat-box">
                                <div>
                                    <span style="font-size: 0.72rem; color: var(--hrc-text-muted); font-weight: 700; display: block;">الامتحانات المكتملة</span>
                                    <span style="font-size: 1.25rem; font-weight: 900; color: var(--hrc-purple);">
                                        {{ $profileDetails['exam_results']->where('attempts_count', '>', 0)->count() }} / {{ $profileDetails['exam_results']->count() }}
                                    </span>
                                </div>
                            </div>
                            <div class="hrc-header-stat-box">
                                <div>
                                    <span style="font-size: 0.72rem; color: var(--hrc-text-muted); font-weight: 700; display: block;">متوسط نسبة الامتحانات</span>
                                    <span style="font-size: 1.25rem; font-weight: 900; color: var(--hrc-purple);">
                                        {{ $profileDetails['exam_avg'] }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Homework Breakdown --}}
                        <div style="margin-top: 1rem;">
                            <h4 style="font-size: 1rem; font-weight: 850; color: var(--hrc-text-main); margin-bottom: 0.5rem;">
                                تفاصيل الواجبات
                            </h4>
                            <div class="hrc-table-card">
                                <table class="hrc-table">
                                    <thead>
                                        <tr>
                                            <th>الواجب</th>
                                            <th>السيشن</th>
                                            <th style="text-align: center;">الحالة</th>
                                            <th style="text-align: center;">الدرجة الرسمية</th>
                                            <th style="text-align: center;">الملاحظات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($profileDetails['homework_submissions'] as $hw)
                                            <tr>
                                                <td style="font-weight: 800;">{{ $hw['assignment_title'] }}</td>
                                                <td>{{ $hw['session_title'] }}</td>
                                                <td style="text-align: center;">
                                                    @if($hw['is_graded'])
                                                        <span class="hrc-pill-badge hrc-pill-success">تم التصحيح</span>
                                                    @elseif($hw['is_submitted'])
                                                        <span class="hrc-pill-badge hrc-pill-warning">بانتظار التصحيح</span>
                                                    @else
                                                        <span class="hrc-pill-badge hrc-pill-neutral">لم يسلّم</span>
                                                    @endif
                                                </td>
                                                <td style="text-align: center; font-weight: 900;">
                                                    {{ $hw['score'] !== null ? $hw['score'] . ' / ' . $hw['max_score'] : '—' }}
                                                </td>
                                                <td style="font-size: 0.8rem; color: var(--hrc-text-muted);">
                                                    {{ $hw['feedback'] ?: '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Exams Breakdown --}}
                        <div style="margin-top: 1rem;">
                            <h4 style="font-size: 1rem; font-weight: 850; color: var(--hrc-text-main); margin-bottom: 0.5rem;">
                                تفاصيل الامتحانات
                            </h4>
                            <div class="hrc-table-card">
                                <table class="hrc-table">
                                    <thead>
                                        <tr>
                                            <th>الامتحان</th>
                                            <th style="text-align: center;">المحاولات</th>
                                            <th style="text-align: center;">الدرجة الرسمية</th>
                                            <th style="text-align: center;">النسبة</th>
                                            <th style="text-align: center;">حالة النشر</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($profileDetails['exam_results'] as $exRes)
                                            <tr>
                                                <td style="font-weight: 800;">{{ $exRes['exam_title'] }}</td>
                                                <td style="text-align: center;">{{ $exRes['attempts_count'] }}</td>
                                                <td style="text-align: center; font-weight: 900;">
                                                    {{ $exRes['official_score'] !== null ? $exRes['official_score'] . ' / ' . $exRes['total_marks'] : '—' }}
                                                </td>
                                                <td style="text-align: center; font-weight: 900;">
                                                    {{ $exRes['percentage'] !== null ? $exRes['percentage'] . '%' : '—' }}
                                                </td>
                                                <td style="text-align: center;">
                                                    @if($exRes['is_published'])
                                                        <span class="hrc-pill-badge hrc-pill-success">منشورة للطالب</span>
                                                    @else
                                                        <span class="hrc-pill-badge hrc-pill-warning">غير منشورة</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="hrc-modal-footer">
                        <button type="button" wire:click="closeStudentAcademicProfile" class="hrc-btn-secondary">إغلاق</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Lightbox Zoom Modal --}}
        @if($lightboxOpen && $selectedSubmission && $selectedSubmission->image_count > 0)
            <div class="hrc-lightbox-overlay" wire:click.self="closeLightbox">
                <div class="hrc-lightbox-toolbar" wire:click.stop>
                    <button type="button" wire:click="zoomIn" class="hrc-zoom-btn" title="تكبير">+</button>
                    <span class="hrc-zoom-val">{{ round($imageZoom * 100) }}%</span>
                    <button type="button" wire:click="zoomOut" class="hrc-zoom-btn" title="تصغير">−</button>
                    <button type="button" wire:click="resetZoom" class="hrc-zoom-btn" title="إعادة ضبط 100%">100%</button>
                    <button type="button" wire:click="closeLightbox" class="hrc-zoom-btn" style="background: rgba(239, 68, 68, 0.7);" title="إغلاق">✕</button>
                </div>

                <div style="overflow: auto; max-width: 95vw; max-height: 90vh; display: flex; align-items: center; justify-content: center;" wire:click.stop>
                    <img src="{{ route('assignments.submissions.image', ['submission' => $selectedSubmission->id, 'index' => $activeImageIndex]) }}"
                        alt="عرض كامل للواجب"
                        style="transform: scale({{ $imageZoom }}); transform-origin: center center; transition: transform 0.2s ease; max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);" />
                </div>

                @if($selectedSubmission->image_count > 1)
                    <button type="button" wire:click="prevImage" class="hrc-nav-arrow hrc-nav-prev" style="z-index: 1250;">
                        <x-heroicon-o-chevron-right class="w-6 h-6" />
                    </button>
                    <button type="button" wire:click="nextImage" class="hrc-nav-arrow hrc-nav-next" style="z-index: 1250;">
                        <x-heroicon-o-chevron-left class="w-6 h-6" />
                    </button>
                @endif
            </div>
        @endif

    </div>
</x-filament-panels::page>
