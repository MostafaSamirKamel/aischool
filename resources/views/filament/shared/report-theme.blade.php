<style>
    /* ==========================================================================
       AI SCHOOL — SHARED REPORTS THEME
       Brand:
         - Primary: #364FAC
         - Accent: #58BA47
         - Background: #F5F7FC
         - Border: #E5EAF3
         - Text: #18243D
         - Muted: #64748B
       ========================================================================== */

    .report-shell {
        --report-primary: #364FAC;
        --report-primary-hover: #293C85;
        --report-accent: #58BA47;
        --report-bg: #F5F7FC;
        --report-border: #E5EAF3;
        --report-text: #18243D;
        --report-muted: #64748B;
        --report-heading-font: "Cairo", "Alexandria", system-ui, sans-serif;
        --report-body-font: "Cairo", "Alexandria", system-ui, sans-serif;

        font-family: var(--report-body-font);
        color: var(--report-text);
    }

    .report-shell h1,
    .report-shell h2,
    .report-shell h3,
    .report-shell h4,
    .report-shell h5,
    .report-shell h6 {
        font-family: var(--report-heading-font);
        letter-spacing: -0.01em;
    }

    .report-shell .report-panel,
    .report-shell .card,
    .report-shell .rounded-2xl,
    .report-shell .rounded-xl {
        border: 1px solid var(--report-border) !important;
        box-shadow: 0 4px 16px -2px rgba(24, 36, 61, 0.05) !important;
        background: #FFFFFF !important;
    }

    .report-shell .table,
    .report-shell table {
        border-color: var(--report-border);
    }

    .report-shell .table thead,
    .report-shell table thead {
        background: #F8FAFD !important;
    }

    .report-shell .table thead th,
    .report-shell table thead th {
        color: #64748B;
        font-weight: 800;
        border-bottom: 1px solid var(--report-border);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .report-shell .table tbody tr:hover,
    .report-shell table tbody tr:hover {
        background: #F8FAFD;
    }

    .report-shell .report-btn {
        border: 1px solid var(--report-border);
        background: #FFFFFF;
        color: var(--report-primary);
        font-weight: 750;
        border-radius: 0.65rem;
        padding: 0.45rem 0.85rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .report-shell .report-btn:hover {
        background: #EEF2FF;
        border-color: rgba(54, 79, 172, 0.3);
        color: var(--report-primary-hover);
    }

    .report-shell .report-btn.report-btn-solid {
        background: var(--report-primary);
        border-color: transparent;
        color: #FFFFFF;
        box-shadow: 0 3px 8px rgba(54, 79, 172, 0.25);
    }

    .report-shell .report-btn.report-btn-solid:hover {
        background: var(--report-primary-hover);
        filter: none;
    }

    .report-shell .report-subtle {
        color: var(--report-muted);
    }

    /* ==========================================================================
       AI SCHOOL — SHARED REPORTS THEME (DARK MODE SECTION 23 SPECIFICATION)
       ========================================================================== */
    .dark .report-shell,
    html.dark .report-shell {
        --report-primary: #2563EB;
        --report-primary-hover: #1D4ED8;
        --report-accent: #16A34A;
        --report-bg: #0B1220;
        --report-border: #334155;
        --report-text: #F1F5F9;
        --report-muted: #CBD5E1;
        color: var(--report-text);
    }

    .dark .report-shell .report-panel,
    html.dark .report-shell .report-panel,
    .dark .report-shell .card,
    html.dark .report-shell .card,
    .dark .report-shell .rounded-2xl,
    html.dark .report-shell .rounded-2xl,
    .dark .report-shell .rounded-xl,
    html.dark .report-shell .rounded-xl {
        border-color: var(--report-border) !important;
        background: #172033 !important;
        color: var(--report-text) !important;
        box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.4) !important;
    }

    .dark .report-shell .table,
    html.dark .report-shell .table,
    .dark .report-shell table,
    html.dark .report-shell table {
        border-color: var(--report-border) !important;
        color: var(--report-text) !important;
    }

    .dark .report-shell .table thead,
    html.dark .report-shell .table thead,
    .dark .report-shell table thead,
    html.dark .report-shell table thead {
        background: #1E293B !important;
    }

    .dark .report-shell .table thead th,
    html.dark .report-shell .table thead th,
    .dark .report-shell table thead th,
    html.dark .report-shell table thead th {
        color: var(--report-muted) !important;
        border-bottom-color: var(--report-border) !important;
    }

    .dark .report-shell .table tbody td,
    html.dark .report-shell .table tbody td,
    .dark .report-shell table tbody td,
    html.dark .report-shell table tbody td {
        color: var(--report-text) !important;
        border-bottom-color: var(--report-border) !important;
    }

    .dark .report-shell .table tbody tr:hover,
    html.dark .report-shell .table tbody tr:hover,
    .dark .report-shell table tbody tr:hover,
    html.dark .report-shell table tbody tr:hover {
        background: #1E293B !important;
    }

    .dark .report-shell .report-btn,
    html.dark .report-shell .report-btn {
        border-color: var(--report-border) !important;
        background: #1E293B !important;
        color: #93C5FD !important;
    }

    .dark .report-shell .report-btn:hover,
    html.dark .report-shell .report-btn:hover {
        background: #25334D !important;
        border-color: var(--report-primary) !important;
        color: #FFFFFF !important;
    }

    .dark .report-shell .report-btn.report-btn-solid,
    html.dark .report-shell .report-btn.report-btn-solid {
        background: var(--report-primary) !important;
        color: #FFFFFF !important;
    }
</style>
