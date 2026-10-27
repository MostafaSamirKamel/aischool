@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

<x-filament-panels::page>
    <style>
        * {
            box-sizing: border-box;
        }

        .barcode-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }

        .barcode-hero {
            background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
            padding: 60px 40px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            border-radius: 0;
        }

        .barcode-hero::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.1), transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        .barcode-hero::after {
            content: "";
            position: absolute;
            bottom: -40%;
            left: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.08), transparent 65%);
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(20px) translateX(10px); }
        }

        .barcode-hero-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .barcode-hero-icon {
            width: 120px;
            height: 120px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15),
                        inset 0 0 32px rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
        }

        .barcode-hero-icon i {
            font-size: 4rem;
            color: #ffffff;
        }

        .barcode-title {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .barcode-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 400;
            line-height: 1.6;
            margin: 0;
        }

        .barcode-container {
            max-width: 900px;
            margin: -40px auto 0;
            padding: 0 20px;
            position: relative;
            z-index: 3;
        }

        .barcode-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.15);
        }

        .barcode-label {
            font-size: 13px;
            font-weight: 700;
            color: #364FAC;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            display: block;
            text-align: right;
        }

        .barcode-input-wrapper {
            position: relative;
        }

        .barcode-input {
            width: 100%;
            height: 56px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            background: #ffffff;
            font-size: 18px;
            text-align: center;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            padding: 12px;
        }

        .barcode-input::placeholder {
            color: #cbd5e0;
            font-weight: 400;
        }

        .barcode-input:focus {
            border-color: #364FAC;
            box-shadow: 0 0 0 4px rgba(54, 79, 172, 0.12),
                        0 4px 16px rgba(54, 79, 172, 0.18);
            background: #f8f9ff;
        }

        .barcode-input:hover {
            border-color: #cbd5e0;
        }

        .barcode-hint {
            font-size: 12px;
            color: #718096;
            margin-top: 10px;
            text-align: center;
            font-weight: 500;
        }

        .barcode-actions {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            margin-top: 14px;
        }

        .barcode-result {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 16px 48px rgba(15, 23, 42, 0.1);
            padding: 28px;
            margin-top: 24px;
        }

        .barcode-status {
            padding: 16px 20px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .barcode-status.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #6ee7b7;
        }

        .barcode-status.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #fcd34d;
        }

        .barcode-status-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            flex-shrink: 0;
        }

        .barcode-status.success .barcode-status-icon {
            background: linear-gradient(135deg, #059669, #10b981);
        }

        .barcode-status.warning .barcode-status-icon {
            background: linear-gradient(135deg, #d97706, #f59e0b);
        }

        .barcode-status-text h3 {
            margin: 0 0 4px 0;
            font-size: 15px;
            font-weight: 700;
        }

        .barcode-status.success .barcode-status-text h3 {
            color: #065f46;
        }

        .barcode-status.warning .barcode-status-text h3 {
            color: #78350f;
        }

        .barcode-status-text p {
            margin: 0;
            font-size: 13px;
            font-weight: 500;
        }

        .barcode-status.success .barcode-status-text p {
            color: #059669;
        }

        .barcode-status.warning .barcode-status-text p {
            color: #d97706;
        }

        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .barcode-metric {
            border-radius: 16px;
            padding: 18px;
            border: 2px solid;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .barcode-metric::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.5);
        }

        .barcode-metric:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        }

        .barcode-metric-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .barcode-metric-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #ffffff;
        }

        .barcode-metric-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .barcode-metric-value {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.3px;
            margin: 8px 0;
        }

        .barcode-metric-sub {
            font-size: 12px;
            font-weight: 600;
        }

        /* Color variants */
        .barcode-metric.blue {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-color: #7dd3fc;
        }
        .barcode-metric.blue .barcode-metric-title { color: #0c4a6e; }
        .barcode-metric.blue .barcode-metric-value { color: #075985; }
        .barcode-metric.blue .barcode-metric-sub { color: #0284c7; }
        .barcode-metric.blue .barcode-metric-icon { background: linear-gradient(135deg, #0284c7, #0ea5e9); }

        .barcode-metric.purple {
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            border-color: #c4b5fd;
        }
        .barcode-metric.purple .barcode-metric-title { color: #4c1d95; }
        .barcode-metric.purple .barcode-metric-value { color: #6b21a8; }
        .barcode-metric.purple .barcode-metric-sub { color: #7c3aed; }
        .barcode-metric.purple .barcode-metric-icon { background: linear-gradient(135deg, #7c3aed, #a855f7); }

        .barcode-metric.amber {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-color: #fcd34d;
        }
        .barcode-metric.amber .barcode-metric-title { color: #78350f; }
        .barcode-metric.amber .barcode-metric-value { color: #92400e; }
        .barcode-metric.amber .barcode-metric-sub { color: #d97706; }
        .barcode-metric.amber .barcode-metric-icon { background: linear-gradient(135deg, #d97706, #f59e0b); }

        .barcode-metric.green {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-color: #6ee7b7;
        }
        .barcode-metric.green .barcode-metric-title { color: #065f46; }
        .barcode-metric.green .barcode-metric-value { color: #047857; }
        .barcode-metric.green .barcode-metric-sub { color: #059669; }
        .barcode-metric.green .barcode-metric-icon { background: linear-gradient(135deg, #059669, #10b981); }

        @media (max-width: 768px) {
            .barcode-page { padding: 0; }
            
            .barcode-hero {
                padding: 40px 24px;
            }
            
            .barcode-hero-icon {
                width: 90px;
                height: 90px;
            }
            
            .barcode-hero-icon i {
                font-size: 2.5rem;
            }
            
            .barcode-title {
                font-size: 28px;
            }
            
            .barcode-subtitle {
                font-size: 14px;
            }
            
            .barcode-container {
                margin: -30px auto 0;
                padding: 0 16px;
            }
            
            .barcode-panel {
                padding: 24px;
                border-radius: 16px;
            }
            
            .barcode-input {
                height: 50px;
                font-size: 16px;
            }
            
            .barcode-actions {
                grid-template-columns: 1fr;
                gap: 10px;
                margin-top: 12px;
            }
            
            .barcode-result {
                padding: 20px;
                margin-top: 20px;
                border-radius: 16px;
            }
            
            .barcode-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .barcode-metric {
                padding: 16px;
                border-radius: 12px;
            }
        }

        /* ============ طباعة الباركود الحراري ============ */
        @media print {
            * {
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
            body, .barcode-page {
                background: white !important;
                width: 100%;
                height: 100%;
            }
            .barcode-hero, .barcode-container, .fi-sidebar, .fi-topbar, .fi-breadcrumbs, header, nav, form {
                display: none !important;
            }
            .barcode-result {
                background: white !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                page-break-after: always;
                width: 40mm !important;
                height: 25mm !important;
                overflow: hidden;
                position: relative;
                border-radius: 0 !important;
            }
            .barcode-status {
                display: none !important;
            }
            .barcode-grid {
                display: flex !important;
                flex-direction: column !important;
                gap: 0 !important;
                width: 100%;
                height: 100%;
                justify-content: space-between;
                padding: 0 !important;
            }
            .barcode-metric {
                padding: 2px 3px !important;
                border: none !important;
                border-radius: 0 !important;
                background: white !important;
                box-shadow: none !important;
                font-size: 7px !important;
                flex: 0 !important;
                height: auto !important;
                margin: 0 !important;
            }
            .barcode-metric::before {
                display: none !important;
            }
            .barcode-metric-header {
                display: none !important;
            }
            .barcode-metric-title {
                font-size: 5px !important;
                font-weight: bold !important;
                display: none !important;
            }
            .barcode-metric-value {
                font-size: 8px !important;
                font-weight: bold !important;
                margin: 0 !important;
                color: #000 !important;
            }
            .barcode-metric-sub {
                font-size: 6px !important;
                color: #333 !important;
            }
            .barcode-metric i {
                display: none !important;
            }
            svg {
                max-width: 35mm !important;
                max-height: 20mm !important;
                display: block !important;
            }
            .barcode-metric:first-child {
                order: 2;
                text-align: center;
            }
            .barcode-metric:first-child svg {
                width: 30mm !important;
                height: auto !important;
            }
            .barcode-metric:nth-child(2) {
                order: 1;
                text-align: center;
                padding: 1px 2px !important;
            }
            .barcode-metric:nth-child(3),
            .barcode-metric:nth-child(4) {
                display: none !important;
            }
        }
        /* ============ End طباعة الباركود ============ */
    </style>

    <div class="barcode-page">
        <!-- Main Barcode Scanner Section -->
        <div class="barcode-hero">
            <div class="barcode-hero-content">
                <div class="barcode-hero-icon">
                    <i class="bi bi-qr-code"></i>
                </div>
                <h1 class="barcode-title">تسجيل حضور الطلاب</h1>
                <p class="barcode-subtitle">امسح باركود الطالب أو اكتب الكود مباشرة للتسجيل التلقائي</p>
            </div>
        </div>

        <!-- Barcode Input Form -->
        <div class="barcode-container">
            <form wire:submit.prevent="scanBarcode" class="barcode-panel">
                <label class="barcode-label">إدخال الباركود</label>
                <div class="barcode-input-wrapper">
                    <input
                        type="text"
                        wire:model.defer="barcode"
                        autofocus
                        placeholder="امسح الباركود أو اكتب كود الطالب..."
                        class="barcode-input"
                    />
                </div>
                <p class="barcode-hint">ضع المؤشر في الحقل وامسح الباركود مباشرة</p>
                <div class="barcode-actions">
                    <div></div>
                    <x-filament::button 
                        type="submit" 
                        color="success" 
                        icon="heroicon-o-qr-code"
                    >
                        طباعة
                    </x-filament::button>
                </div>
            </form>

            <!-- Last Result Section -->
            @if($lastResult)
                <div class="barcode-result">
                    <!-- Status Message -->
                    <div class="barcode-status {{ $lastResult['recorded'] ? 'success' : 'warning' }}">
                        <div class="barcode-status-icon">
                            @if($lastResult['recorded'])
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-info-circle"></i>
                            @endif
                        </div>
                        <div class="barcode-status-text">
                            <h3>{{ $lastResult['recorded'] ? '✓ تم التسجيل بنجاح!' : '⏱ مسجل مسبقاً' }}</h3>
                            <p>{{ $lastResult['recorded'] ? 'تم تسجيل الحضور للطالب' : 'الطالب مسجل في هذه الجلسة' }}</p>
                        </div>
                    </div>

                    <!-- Info Cards -->
                    <div class="barcode-grid">
                        <div class="barcode-metric blue">
                            <div class="barcode-metric-header">
                                <div class="barcode-metric-icon">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <span class="barcode-metric-title">الطالب</span>
                            </div>
                            <div class="barcode-metric-value">{{ $lastResult['student_name'] }}</div>
                            <div class="barcode-metric-sub">{{ $lastResult['student_code'] }}</div>
                        </div>

                        <div class="barcode-metric purple">
                            <div class="barcode-metric-header">
                                <div class="barcode-metric-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <span class="barcode-metric-title">المجموعة</span>
                            </div>
                            <div class="barcode-metric-value">{{ $lastResult['group_name'] }}</div>
                            <div class="barcode-metric-sub">{{ $lastResult['course_name'] }}</div>
                        </div>

                        <div class="barcode-metric amber">
                            <div class="barcode-metric-header">
                                <div class="barcode-metric-icon">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <span class="barcode-metric-title">الجلسة</span>
                            </div>
                            <div class="barcode-metric-value">{{ $lastResult['session_time'] }}</div>
                            <div class="barcode-metric-sub">الوقت المسجل</div>
                        </div>

                        <div class="barcode-metric green">
                            <div class="barcode-metric-header">
                                <div class="barcode-metric-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <span class="barcode-metric-title">الحالة</span>
                            </div>
                            <div class="barcode-metric-value">{{ $lastResult['recorded'] ? '✓ حاضر' : '⏱ مسجل' }}</div>
                            <div class="barcode-metric-sub">{{ $lastResult['recorded'] ? 'تم التحديث' : 'جديد' }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
