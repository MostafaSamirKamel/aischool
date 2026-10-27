<style>
    /* Student Unified Layout Fixes */
    .row.g-4 {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 1.25rem !important;
        margin: 0 0 2rem 0 !important;
    }
    .col-12, .col-sm-6, .col-xl-3, .col-md-4, .col-xl-2 {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
    }
    .stat-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.06) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        height: 100% !important;
        overflow: hidden !important;
    }
    .stat-card:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 10px 25px -3px rgba(15, 23, 42, 0.09) !important;
        border-color: #cbd5e1 !important;
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
    }
    .stat-value {
        font-size: 1.85rem !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
        margin: 0 !important;
    }
    .stat-label {
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: #64748b !important;
        margin-top: 0.35rem !important;
    }

    /* Group Attendance Cards Grid */
    .row.g-3 {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 1.25rem !important;
    }
    .group-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        transition: transform 0.2s ease !important;
    }
    .group-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08) !important;
    }
    .group-card .card-body {
        padding: 1.35rem !important;
    }

    @media (max-width: 1024px) {
        .row.g-4 {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
        .row.g-3 {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 640px) {
        .row.g-4 {
            grid-template-columns: 1fr !important;
        }
    }
</style>
