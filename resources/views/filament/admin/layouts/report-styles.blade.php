<style>
    /* تنسيقات إضافية للتقرير */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .table-responsive {
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .badge {
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* تنسيقات الطباعة */
    @media print {
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
        
        .bg-gradient-primary {
            background: #667eea !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table {
            font-size: 12px;
        }

        .badge {
            border: 1px solid currentColor;
        }
    }

    /* تنسيقات RTL */
    body[dir="rtl"] .table th,
    body[dir="rtl"] .table td {
        text-align: right;
    }

    body[dir="rtl"] .table th:last-child,
    body[dir="rtl"] .table td:last-child {
        text-align: left;
    }
</style>

{{-- Bootstrap JS Bundle --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
