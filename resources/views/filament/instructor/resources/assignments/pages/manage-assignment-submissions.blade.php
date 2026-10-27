<x-filament-panels::page>
    {{-- ========================================================================= --}}
    {{-- SCOPED DESIGN SYSTEM FOR MANAGE ASSIGNMENT SUBMISSIONS (AI SCHOOL)         --}}
    {{-- ========================================================================= --}}
    <style>
        :root {
            --mas-primary: #364FAC;
            --mas-primary-hover: #2B3E8C;
            --mas-primary-light: #EEF2FF;
            --mas-navy: #0F172A;
            --mas-card-bg: #FFFFFF;
            --mas-card-border: #E2E8F0;
            --mas-text-main: #1E293B;
            --mas-text-muted: #64748B;
            --mas-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06);
            --mas-shadow-hover: 0 10px 25px -5px rgba(15, 23, 42, 0.1);
            --mas-radius-md: 12px;
            --mas-radius-lg: 16px;
            --mas-radius-xl: 20px;

            /* Accent Status Colors */
            --mas-emerald: #059669;
            --mas-emerald-light: #ECFDF5;
            --mas-amber: #D97706;
            --mas-amber-light: #FFFBEB;
            --mas-blue: #2563EB;
            --mas-blue-light: #EFF6FF;
            --mas-rose: #E11D48;
            --mas-rose-light: #FFF1F2;
            --mas-purple: #7C3AED;
            --mas-purple-light: #F5F3FF;
        }

        .dark .mas-wrap {
            --mas-card-bg: #1E293B;
            --mas-card-border: #334155;
            --mas-text-main: #F8FAFC;
            --mas-text-muted: #94A3B8;
            --mas-primary-light: rgba(54, 79, 172, 0.2);
            --mas-emerald-light: rgba(5, 150, 105, 0.18);
            --mas-amber-light: rgba(217, 119, 6, 0.18);
            --mas-blue-light: rgba(37, 99, 235, 0.18);
            --mas-rose-light: rgba(225, 29, 72, 0.18);
            --mas-purple-light: rgba(124, 58, 237, 0.18);
        }

        .mas-wrap {
            font-family: 'Tajawal', system-ui, -apple-system, sans-serif;
            color: var(--mas-text-main);
            direction: rtl;
        }

        /* Top Header Card */
        .mas-header-card {
            background: var(--mas-card-bg);
            border: 1px solid var(--mas-card-border);
            border-radius: var(--mas-radius-xl);
            padding: 1.5rem;
            box-shadow: var(--mas-shadow);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .mas-header-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #364FAC 0%, #6366F1 50%, #10B981 100%);
        }

        .mas-header-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.25rem;
            flex-wrap: wrap;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--mas-card-border);
        }

        .mas-title {
            font-size: 1.4rem;
            font-weight: 850;
            color: var(--mas-text-main);
            margin: 0.35rem 0;
            line-height: 1.35;
        }

        .mas-desc {
            font-size: 0.88rem;
            color: var(--mas-text-muted);
            line-height: 1.6;
            margin: 0;
            max-width: 850px;
        }

        /* Chips & Badges */
        .mas-chip-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 0.4rem;
        }

        .mas-chip {
            font-size: 0.74rem;
            font-weight: 750;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }
        .mas-chip-primary {
            background: var(--mas-primary-light);
            color: var(--mas-primary);
        }
        .mas-chip-slate {
            background: #F1F5F9;
            color: #475569;
        }
        .dark .mas-chip-slate {
            background: #334155;
            color: #CBD5E1;
        }
        .mas-chip-success {
            background: var(--mas-emerald-light);
            color: var(--mas-emerald);
        }
        .mas-chip-amber {
            background: var(--mas-amber-light);
            color: var(--mas-amber);
        }

        /* Header Info Pills */
        .mas-info-pills {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .mas-info-pill {
            background: #F8FAFC;
            border: 1px solid var(--mas-card-border);
            padding: 0.5rem 1rem;
            border-radius: var(--mas-radius-md);
            text-align: center;
            min-width: 110px;
        }
        .dark .mas-info-pill {
            background: #1E293B;
        }
        .mas-info-pill-title {
            font-size: 0.7rem;
            color: var(--mas-text-muted);
            display: block;
            margin-bottom: 0.2rem;
        }
        .mas-info-pill-val {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--mas-primary);
        }

        /* KPI Strip */
        .mas-kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.85rem;
            margin-top: 1.25rem;
        }
        @media (min-width: 768px) {
            .mas-kpi-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        .mas-kpi-card {
            background: #F8FAFC;
            border: 1px solid var(--mas-card-border);
            border-radius: var(--mas-radius-md);
            padding: 1rem 1.15rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .dark .mas-kpi-card {
            background: #0F172A;
        }
        .mas-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--mas-shadow);
        }
        .mas-kpi-label {
            font-size: 0.75rem;
            color: var(--mas-text-muted);
            font-weight: 600;
            display: block;
            margin-bottom: 0.35rem;
        }
        .mas-kpi-num {
            font-size: 1.5rem;
            font-weight: 850;
            margin: 0;
            line-height: 1.2;
        }
        .kpi-blue .mas-kpi-num { color: var(--mas-blue); }
        .kpi-amber .mas-kpi-num { color: var(--mas-amber); }
        .kpi-emerald .mas-kpi-num { color: var(--mas-emerald); }
        .kpi-slate .mas-kpi-num { color: var(--mas-text-main); }

        /* Main Table Container */
        .mas-table-card {
            background: var(--mas-card-bg);
            border: 1px solid var(--mas-card-border);
            border-radius: var(--mas-radius-xl);
            box-shadow: var(--mas-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .mas-table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--mas-card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            background: #F8FAFC;
        }
        .dark .mas-table-header {
            background: #1E293B;
        }
        .mas-table-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--mas-text-main);
            margin: 0;
        }
        .mas-table-sub {
            font-size: 0.78rem;
            color: var(--mas-text-muted);
            margin: 0.2rem 0 0 0;
        }

        /* Table Element */
        .mas-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.85rem;
        }
        .mas-table th {
            background: #F8FAFC;
            color: var(--mas-text-muted);
            font-weight: 750;
            font-size: 0.75rem;
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--mas-card-border);
            white-space: nowrap;
        }
        .dark .mas-table th {
            background: #0F172A;
        }
        .mas-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--mas-card-border);
            vertical-align: middle;
        }
        .mas-table tr:hover td {
            background: #F8FAFC;
        }
        .dark .mas-table tr:hover td {
            background: #334155/30;
        }

        /* Student Identity */
        .mas-student-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .mas-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #364FAC 0%, #6366F1 100%);
            color: #ffffff;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(54, 79, 172, 0.25);
        }
        .mas-student-name {
            font-weight: 800;
            color: var(--mas-text-main);
            font-size: 0.92rem;
            margin-bottom: 0.15rem;
        }
        .mas-student-code {
            font-size: 0.72rem;
            font-family: monospace;
            color: var(--mas-text-muted);
        }

        /* Status Badges */
        .mas-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            white-space: nowrap;
        }
        .mas-pill-success {
            background: var(--mas-emerald-light);
            color: var(--mas-emerald);
            border: 1px solid rgba(5, 150, 105, 0.25);
        }
        .mas-pill-warning {
            background: var(--mas-amber-light);
            color: var(--mas-amber);
            border: 1px solid rgba(217, 119, 6, 0.25);
        }
        .mas-pill-danger {
            background: var(--mas-rose-light);
            color: var(--mas-rose);
            border: 1px solid rgba(225, 29, 72, 0.25);
        }
        .mas-pill-neutral {
            background: #F1F5F9;
            color: #64748B;
            border: 1px solid var(--mas-card-border);
        }
        .dark .mas-pill-neutral {
            background: #334155;
            color: #94A3B8;
        }

        /* Buttons */
        .mas-btn-primary {
            background: var(--mas-primary);
            color: #ffffff;
            border: none;
            padding: 0.45rem 1rem;
            border-radius: var(--mas-radius-md);
            font-weight: 750;
            font-size: 0.8rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(54, 79, 172, 0.25);
        }
        .mas-btn-primary:hover {
            background: var(--mas-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(54, 79, 172, 0.35);
        }
        .mas-btn-secondary {
            background: #FFFFFF;
            border: 1px solid var(--mas-card-border);
            color: var(--mas-text-main);
            padding: 0.45rem 0.95rem;
            border-radius: var(--mas-radius-md);
            font-weight: 750;
            font-size: 0.8rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s ease;
        }
        .dark .mas-btn-secondary {
            background: #1E293B;
            color: #F8FAFC;
        }
        .mas-btn-secondary:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
        }

        /* Review Modal Dialog */
        .mas-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(6px);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .mas-modal-card {
            background: var(--mas-card-bg);
            border: 1px solid var(--mas-card-border);
            border-radius: var(--mas-radius-xl);
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 1180px;
            max-height: 94vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: masModalIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes masModalIn {
            from { opacity: 0; transform: scale(0.96) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .mas-modal-header {
            padding: 1.15rem 1.5rem;
            border-bottom: 1px solid var(--mas-card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #F8FAFC;
        }
        .dark .mas-modal-header {
            background: #0F172A;
        }

        .mas-modal-body {
            display: grid;
            grid-template-columns: 1fr;
            flex: 1;
            overflow: hidden;
        }
        @media (min-width: 992px) {
            .mas-modal-body {
                grid-template-columns: 6.5fr 5.5fr;
            }
        }

        /* Image Viewport Canvas */
        .mas-canvas-wrap {
            background: #0F172A;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            min-height: 400px;
            border-inline-end: 1px solid var(--mas-card-border);
            position: relative;
        }
        .mas-canvas-toolbar {
            background: rgba(15, 23, 42, 0.95);
            padding: 0.65rem 1rem;
            border-bottom: 1px solid #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
        }
        .mas-canvas-viewport {
            flex: 1;
            background: #020617;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
        }
        .mas-canvas-viewport img {
            max-width: 95%;
            max-height: 520px;
            object-fit: contain;
            border-radius: 8px;
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        /* Thumbnails Strip */
        .mas-thumb-strip {
            background: #0B1120;
            padding: 0.65rem 1rem;
            border-top: 1px solid #1E293B;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            overflow-x: auto;
        }
        .mas-thumb-btn {
            width: 54px;
            height: 54px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #334155;
            cursor: pointer;
            position: relative;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .mas-thumb-btn.active {
            border-color: #6366F1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.4);
        }

        /* Grading Form Column */
        .mas-form-wrap {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;
            background: var(--mas-card-bg);
        }
        .mas-form-label {
            font-weight: 800;
            font-size: 0.88rem;
            color: var(--mas-text-main);
            margin-bottom: 0.4rem;
            display: block;
        }
        .mas-input {
            width: 100%;
            border: 1px solid var(--mas-card-border);
            background: #FFFFFF;
            border-radius: var(--mas-radius-md);
            padding: 0.65rem 1rem;
            font-size: 0.95rem;
            font-weight: 750;
            color: var(--mas-text-main);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .dark .mas-input {
            background: #0F172A;
            border-color: #334155;
            color: #F8FAFC;
        }
        .mas-input:focus {
            outline: none;
            border-color: var(--mas-primary);
            box-shadow: 0 0 0 3px rgba(54, 79, 172, 0.15);
        }

        /* Preset Chips */
        .mas-preset-chip {
            background: #F1F5F9;
            border: 1px solid var(--mas-card-border);
            color: var(--mas-text-main);
            border-radius: 6px;
            padding: 0.2rem 0.55rem;
            font-size: 0.72rem;
            font-weight: 750;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .dark .mas-preset-chip {
            background: #334155;
            color: #E2E8F0;
        }
        .mas-preset-chip:hover {
            background: var(--mas-primary-light);
            color: var(--mas-primary);
            border-color: var(--mas-primary);
        }

        /* Action Buttons Toolbar */
        .mas-btn-publish {
            background: linear-gradient(135deg, #059669 0%, #10B981 100%);
            color: #ffffff;
            border: none;
            padding: 0.7rem 1.4rem;
            border-radius: var(--mas-radius-md);
            font-weight: 850;
            font-size: 0.88rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            transition: all 0.2s ease;
        }
        .mas-btn-publish:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
        }

        .mas-btn-draft {
            background: #F59E0B;
            color: #ffffff;
            border: none;
            padding: 0.7rem 1.15rem;
            border-radius: var(--mas-radius-md);
            font-weight: 800;
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }
        .mas-btn-draft:hover {
            background: #D97706;
        }

        .mas-btn-next {
            background: var(--mas-primary);
            color: #ffffff;
            border: none;
            padding: 0.7rem 1.25rem;
            border-radius: var(--mas-radius-md);
            font-weight: 800;
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 4px 14px rgba(54, 79, 172, 0.3);
            transition: all 0.2s ease;
        }
        .mas-btn-next:hover {
            background: var(--mas-primary-hover);
        }
    </style>

    <div class="mas-wrap">
        {{-- ================================================================ --}}
        {{-- 1. ASSIGNMENT HEADER CARD                                        --}}
        {{-- ================================================================ --}}
        <div class="mas-header-card">
            <div class="mas-header-top">
                <div>
                    {{-- Badges Row --}}
                    <div class="mas-chip-row">
                        <span class="mas-chip mas-chip-primary">
                            📚 {{ $record->course?->name ?? ($record->group?->course?->name ?? 'مقرر عام') }}
                        </span>
                        <span class="mas-chip mas-chip-slate">
                            👥 مجموعة: {{ $record->group?->name ?? 'غير محددة' }}
                        </span>
                        @if($record->session_number || $record->attendance_session_id)
                            <span class="mas-chip" style="background: var(--mas-purple-light); color: var(--mas-purple);">
                                🎯 الجلسة {{ $record->session_number ?? 'المرتبطة' }}
                            </span>
                        @endif
                        <span class="mas-chip {{ $record->status === 'active' || $record->status === 'published' ? 'mas-chip-success' : 'mas-chip-amber' }}">
                            {{ $record->status === 'active' || $record->status === 'published' ? '● متاح ونشط' : '● مغلق' }}
                        </span>
                    </div>

                    {{-- Title & Description --}}
                    <h1 class="mas-title">{{ $record->title }}</h1>
                    @if($record->description)
                        <p class="mas-desc">{{ $record->description }}</p>
                    @endif
                </div>

                {{-- Action & Info Buttons --}}
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <div class="mas-info-pills">
                        <div class="mas-info-pill">
                            <span class="mas-info-pill-title">الدرجة العظمى</span>
                            <span class="mas-info-pill-val">{{ $record->max_score ?? 10 }} درجات</span>
                        </div>
                        <div class="mas-info-pill">
                            <span class="mas-info-pill-title">آخر موعد للتسليم</span>
                            <span class="mas-info-pill-val" style="color: var(--mas-text-main); font-size: 0.85rem;">
                                {{ $record->due_date ? \Carbon\Carbon::parse($record->due_date)->format('Y-m-d') : 'مفتوح' }}
                            </span>
                        </div>
                    </div>

                    {{-- Link to Group Homework Center & Refresh --}}
                    @if($record->group_id)
                        <a href="{{ route('filament.instructor.pages.homework-review-center', ['group' => $record->group_id, 'assignment' => $record->id]) }}"
                            class="mas-btn-primary" style="text-decoration: none; padding: 0.6rem 1rem;">
                            <x-heroicon-o-academic-cap class="w-4 h-4" />
                            <span>فتح في مركز المراجعة</span>
                        </a>
                    @endif
                    <button type="button" wire:click="loadSubmissions" class="mas-btn-secondary" title="تحديث القائمة">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                        <span>تحديث</span>
                    </button>
                </div>
            </div>

            {{-- KPI Statistics Strip --}}
            <div class="mas-kpi-grid">
                <div class="mas-kpi-card kpi-slate">
                    <span class="mas-kpi-label">إجمالي طلاب المجموعة</span>
                    <p class="mas-kpi-num">{{ $groupStudents->count() }}</p>
                </div>
                <div class="mas-kpi-card kpi-blue">
                    <span class="mas-kpi-label">تم التسليم</span>
                    <p class="mas-kpi-num">{{ $groupStudents->where('has_submission', true)->count() }}</p>
                </div>
                <div class="mas-kpi-card kpi-amber">
                    <span class="mas-kpi-label">بانتظار التصحيح</span>
                    <p class="mas-kpi-num">{{ $groupStudents->where('review_status', 'awaiting_grading')->count() }}</p>
                </div>
                <div class="mas-kpi-card kpi-emerald">
                    <span class="mas-kpi-label">تم التصحيح والاعتماد</span>
                    <p class="mas-kpi-num">{{ $groupStudents->where('review_status', 'graded')->count() }}</p>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- 2. STUDENT SUBMISSIONS ROSTER TABLE                              --}}
        {{-- ================================================================ --}}
        <div class="mas-table-card">
            <div class="mas-table-header">
                <div>
                    <h2 class="mas-table-title">كشف تسليمات وتصحيح الطلاب</h2>
                    <p class="mas-table-sub">استعراض صور الحلول وتصحيحها ورصد الدرجات والملاحظات بنقرة واحدة (الدرجة العظمى: {{ $record->max_score ?? 10 }})</p>
                </div>
                <span class="mas-chip mas-chip-slate">
                    عدد الطلاب: {{ $groupStudents->count() }}
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table class="mas-table">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">الطالب</th>
                            <th style="text-align: center; min-width: 160px;">حالة التسليم</th>
                            <th style="text-align: center; min-width: 100px;">المرفقات</th>
                            <th style="text-align: center; min-width: 130px;">تاريخ التسليم</th>
                            <th style="text-align: center; min-width: 100px;">الدرجة</th>
                            <th style="text-align: center; min-width: 150px;">حالة التقييم</th>
                            <th style="text-align: center; min-width: 140px;">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupStudents as $row)
                            @php
                                $student = $row['student'];
                                $submission = $row['submission'];
                                $imagesCount = $row['images_count'] ?? ($submission ? $submission->image_count : 0);
                                $isPublished = $row['is_published'] ?? false;
                                $isDraft = $row['is_draft'] ?? false;
                                $punctuality = $row['punctuality_status'] ?? 'pending';
                                $reviewStatus = $row['review_status'] ?? 'pending';
                            @endphp
                            <tr>
                                {{-- 1. Student Info --}}
                                <td>
                                    <div class="mas-student-cell">
                                        <div class="mas-avatar">
                                            {{ mb_substr($student->user->name ?? $student->name ?? 'ط', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="mas-student-name">{{ $student->user->name ?? $student->name }}</div>
                                            <div class="mas-student-code">{{ $student->student_code ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Submission Status --}}
                                <td style="text-align: center;">
                                    @if($punctuality === 'submitted_on_time')
                                        <span class="mas-pill mas-pill-success">
                                            ✓ تم التسليم في الموعد — بانتظار التصحيح
                                        </span>
                                    @elseif($punctuality === 'submitted_late')
                                        <span class="mas-pill mas-pill-warning">
                                            ⏱️ تم التسليم متأخرًا
                                        </span>
                                    @elseif($punctuality === 'overdue')
                                        <span class="mas-pill mas-pill-danger">
                                            ⚠️ متأخر ولم يسلّم
                                        </span>
                                    @else
                                        <span class="mas-pill mas-pill-neutral">
                                            ⏳ بانتظار التسليم
                                        </span>
                                    @endif
                                </td>

                                {{-- 3. Images Count --}}
                                <td style="text-align: center;">
                                    @if($imagesCount > 0)
                                        <span class="mas-chip" style="background: var(--mas-blue-light); color: var(--mas-blue); font-weight: 800;">
                                            🖼️ {{ $imagesCount }} {{ $imagesCount == 1 ? 'صورة' : 'صور' }}
                                        </span>
                                    @else
                                        <span style="color: var(--mas-text-muted); font-size: 0.8rem;">—</span>
                                    @endif
                                </td>

                                {{-- 4. Submission Date --}}
                                <td style="text-align: center; font-family: monospace; font-size: 0.8rem;" dir="ltr">
                                    {{ $submission?->submitted_at ? $submission->submitted_at->format('Y-m-d H:i') : '—' }}
                                </td>

                                {{-- 5. Score Display --}}
                                <td style="text-align: center; font-weight: 850; font-size: 0.95rem;">
                                    @if($submission && $isPublished && $submission->score !== null)
                                        <span class="mas-pill mas-pill-success" style="font-size: 0.85rem; padding: 0.35rem 0.85rem;">
                                            {{ $submission->score }} / {{ $record->max_score ?? 10 }}
                                        </span>
                                    @elseif($submission && $isDraft && $submission->score !== null)
                                        <span class="mas-pill" style="background: var(--mas-purple-light); color: var(--mas-purple); font-size: 0.85rem; padding: 0.35rem 0.85rem;">
                                            {{ $submission->score }} / {{ $record->max_score ?? 10 }} (مسودة)
                                        </span>
                                    @else
                                        <span style="color: var(--mas-text-muted); font-family: monospace;">—</span>
                                    @endif
                                </td>

                                {{-- 6. Review Status --}}
                                <td style="text-align: center;">
                                    @if($reviewStatus === 'graded' || ($submission && $isPublished))
                                        <span style="font-size: 0.82rem; font-weight: 800; color: var(--mas-emerald);">
                                            ✓ تم التصحيح والاعتماد
                                        </span>
                                    @elseif($reviewStatus === 'draft' || ($submission && $isDraft))
                                        <span style="font-size: 0.82rem; font-weight: 800; color: var(--mas-purple);">
                                            📝 مسودة تقييم
                                        </span>
                                    @elseif($submission)
                                        <span style="font-size: 0.82rem; font-weight: 800; color: var(--mas-amber);">
                                            ⏳ بانتظار التصحيح
                                        </span>
                                    @else
                                        <span style="color: var(--mas-text-muted); font-size: 0.8rem;">—</span>
                                    @endif
                                </td>

                                {{-- 7. Action --}}
                                <td style="text-align: center;">
                                    @if($submission)
                                        <button type="button"
                                            wire:click="openReview({{ $student->id }}, {{ $submission->id }})"
                                            class="{{ $isPublished ? 'mas-btn-secondary' : 'mas-btn-primary' }}">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                            <span>{{ $isPublished ? 'عرض التقييم' : 'عرض الإجابة' }}</span>
                                        </button>
                                    @else
                                        <span style="color: var(--mas-text-muted); font-size: 0.78rem; font-style: italic;">لا يوجد تسليم</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 3.5rem 1rem; text-align: center; color: var(--mas-text-muted);">
                                    <x-heroicon-o-users class="w-12 h-12" style="margin: 0 auto 0.75rem auto; opacity: 0.4;" />
                                    <div style="font-size: 1rem; font-weight: 750;">لا يوجد طلاب مقيدين في هذه المجموعة حالياً.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- 3. INTERACTIVE REVIEW & GRADING MODAL DIALOG                     --}}
        {{-- ================================================================ --}}
        @if($isReviewOpen && $selectedSubmission)
            <div class="mas-modal-backdrop" wire:click.self="closeReview">
                <div class="mas-modal-card">
                    {{-- Modal Header --}}
                    <div class="mas-modal-header">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                <span class="mas-chip mas-chip-primary">
                                    {{ $record->title }}
                                </span>
                                @if($selectedSubmission->isLate())
                                    <span class="mas-pill mas-pill-danger">
                                        ⏱️ سُلّم متأخرًا
                                    </span>
                                @else
                                    <span class="mas-pill mas-pill-success">
                                        ✓ سُلّم في الموعد
                                    </span>
                                @endif
                                @if($selectedSubmission->isPublished())
                                    <span class="mas-pill mas-pill-success">
                                        معتمد ({{ $selectedSubmission->score }} / {{ $record->max_score ?? 10 }})
                                    </span>
                                @endif
                            </div>
                            <h3 style="font-size: 1.15rem; font-weight: 850; color: var(--mas-text-main); margin: 0;">
                                حل الطالب: {{ $selectedStudent->user->name ?? $selectedStudent->name }}
                                <span style="font-size: 0.8rem; font-weight: normal; color: var(--mas-text-muted); margin-inline-start: 0.5rem;">
                                    (تاريخ التسليم: {{ $selectedSubmission->submitted_at ? $selectedSubmission->submitted_at->format('Y-m-d H:i') : '-' }})
                                </span>
                            </h3>
                        </div>

                        <button type="button" wire:click="closeReview" class="mas-btn-secondary" style="padding: 0.4rem 0.7rem;" title="إغلاق">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Modal Body: Split View (Image Viewer + Grading Form) --}}
                    <div class="mas-modal-body">
                        {{-- LEFT COLUMN: Image Viewer --}}
                        <div class="mas-canvas-wrap">
                            {{-- Toolbar --}}
                            <div class="mas-canvas-toolbar">
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <button type="button" wire:click="zoomIn" class="mas-btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="تكبير (+)">
                                        <x-heroicon-o-magnifying-glass-plus class="w-4 h-4" />
                                    </button>
                                    <button type="button" wire:click="zoomOut" class="mas-btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="تصغير (−)">
                                        <x-heroicon-o-magnifying-glass-minus class="w-4 h-4" />
                                    </button>
                                    <button type="button" wire:click="resetZoom" class="mas-btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="إعادة تعيين للأصل">
                                        <span>{{ round($imageZoom * 100) }}%</span>
                                    </button>
                                </div>

                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    @php $totImgs = $selectedSubmission->image_count; @endphp
                                    <span style="opacity: 0.85; font-size: 0.75rem;">
                                        صورة {{ $activeImageIndex + 1 }} من {{ max($totImgs, 1) }}
                                    </span>
                                    @if($totImgs > 1)
                                        <button type="button" wire:click="prevImage" class="mas-btn-secondary" style="padding: 0.3rem 0.5rem;" title="السابق">
                                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                                        </button>
                                        <button type="button" wire:click="nextImage" class="mas-btn-secondary" style="padding: 0.3rem 0.5rem;" title="التالي">
                                            <x-heroicon-o-chevron-left class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Canvas Viewport --}}
                            <div class="mas-canvas-viewport">
                                @if($selectedSubmission->image_count > 0)
                                    <img src="{{ route('assignments.submissions.image', ['submission' => $selectedSubmission->id, 'index' => $activeImageIndex]) }}"
                                        alt="صورة حل الواجب"
                                        style="transform: scale({{ $imageZoom }});" />
                                @else
                                    <div style="text-align: center; color: var(--mas-text-muted); padding: 2rem;">
                                        <x-heroicon-o-photo class="w-12 h-12" style="margin: 0 auto 0.5rem auto; opacity: 0.4;" />
                                        <div>لا توجد صور مرفوعة في هذا التسليم</div>
                                    </div>
                                @endif
                            </div>

                            {{-- Thumbnails Strip --}}
                            @if($selectedSubmission->image_count > 1)
                                <div class="mas-thumb-strip">
                                    @foreach($selectedSubmission->images_list as $idx => $img)
                                        <div wire:click="setActiveImage({{ $idx }})"
                                            class="mas-thumb-btn {{ $activeImageIndex === $idx ? 'active' : '' }}"
                                            title="صورة {{ $idx + 1 }}">
                                            <img src="{{ route('assignments.submissions.image', ['submission' => $selectedSubmission->id, 'index' => $idx]) }}"
                                                alt="صورة {{ $idx + 1 }}"
                                                style="width: 100%; height: 100%; object-fit: cover;" />
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- RIGHT COLUMN: Grading Form --}}
                        <div class="mas-form-wrap">
                            <div>
                                {{-- Student Notes --}}
                                @if(!empty($selectedSubmission->notes))
                                    <div style="background: var(--mas-blue-light); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: var(--mas-radius-md); padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                                        <div style="font-weight: 800; font-size: 0.78rem; color: var(--mas-blue); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
                                            <x-heroicon-o-chat-bubble-left-right class="w-4 h-4" />
                                            <span>ملاحظات الطالب مع التسليم:</span>
                                        </div>
                                        <p style="margin: 0; font-size: 0.82rem; color: var(--mas-text-main); white-space: pre-line; line-height: 1.5;">
                                            {{ $selectedSubmission->notes }}
                                        </p>
                                    </div>
                                @endif

                                {{-- Score Input --}}
                                <div style="margin-bottom: 1.25rem;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                                        <label for="masScoreInput" class="mas-form-label" style="margin: 0;">
                                            الدرجة (من {{ $record->max_score ?? 10 }}) *
                                        </label>
                                        <span style="font-size: 0.72rem; color: var(--mas-text-muted);">
                                            الحد الأقصى: {{ $record->max_score ?? 10 }}
                                        </span>
                                    </div>

                                    <div style="position: relative;">
                                        <input type="number"
                                            id="masScoreInput"
                                            wire:model.defer="score"
                                            min="0"
                                            max="{{ $record->max_score ?? 10 }}"
                                            step="0.5"
                                            placeholder="أدخل الدرجة (مثال: 9.5)"
                                            class="mas-input"
                                            style="padding-inline-end: 3.5rem; font-size: 1.1rem;" />
                                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-weight: 800; color: var(--mas-text-muted); font-size: 0.9rem;">
                                            / {{ $record->max_score ?? 10 }}
                                        </span>
                                    </div>
                                    @error('score')
                                        <span style="color: var(--mas-rose); font-size: 0.75rem; font-weight: 750; display: block; margin-top: 0.3rem;">
                                            {{ $message }}
                                        </span>
                                    @enderror

                                    {{-- Quick Score Select Chips --}}
                                    @php $maxS = (float)($record->max_score ?? 10); @endphp
                                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.5rem;">
                                        <span style="font-size: 0.72rem; color: var(--mas-text-muted); margin-inline-end: 0.25rem;">خيارات سريعة:</span>
                                        @foreach([$maxS, round($maxS * 0.95, 1), round($maxS * 0.9, 1), round($maxS * 0.85, 1), round($maxS * 0.8, 1), round($maxS * 0.5, 1), 0] as $preset)
                                            <button type="button"
                                                wire:click="$set('score', {{ $preset }})"
                                                class="mas-preset-chip">
                                                {{ $preset }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Instructor Feedback Textarea --}}
                                <div style="margin-bottom: 1.25rem;">
                                    <label for="masFeedbackInput" class="mas-form-label">
                                        ملاحظات وتوجيهات المهندس:
                                    </label>
                                    <textarea id="masFeedbackInput"
                                        wire:model.defer="instructor_feedback"
                                        rows="4"
                                        placeholder="اكتب ملاحظاتك وتوجيهاتك للطالب هنا لتظهر له في لوحته فور التصحيح..."
                                        class="mas-input"
                                        style="resize: vertical; font-size: 0.85rem; font-weight: normal; line-height: 1.6;"></textarea>
                                    @error('instructor_feedback')
                                        <span style="color: var(--mas-rose); font-size: 0.75rem; font-weight: 750; display: block; margin-top: 0.3rem;">
                                            {{ $message }}
                                        </span>
                                    @enderror

                                    {{-- Quick Feedback Suggestions --}}
                                    <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                        <button type="button"
                                            wire:click="$set('instructor_feedback', 'ممتاز جدًا! إجابة نموذجية ومنظمة وأفكار برمجية واضحة وموثقة بشكل احترافي.')"
                                            class="mas-preset-chip">
                                            + إجابة نموذجية ممتازة
                                        </button>
                                        <button type="button"
                                            wire:click="$set('instructor_feedback', 'مجهود طيب يا بطل، يرجى مراجعة الشرط والتأكد من مخرجات البرنامج بدقة.')"
                                            class="mas-preset-chip">
                                            + مجهود طيب مع تدقيق الشروط
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Actions --}}
                            <div style="border-top: 1px solid var(--mas-card-border); padding-top: 1.25rem; margin-top: 1rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                        {{-- Save Draft Button --}}
                                        <button type="button" wire:click="saveDraft" class="mas-btn-draft">
                                            <x-heroicon-o-document-text class="w-4 h-4" />
                                            <span>حفظ كمسودة</span>
                                        </button>

                                        {{-- Primary Action: تم التصحيح ✓ --}}
                                        <button type="button" wire:click="markCorrected" class="mas-btn-publish">
                                            <x-heroicon-o-check-badge class="w-5 h-5" />
                                            <span>تم التصحيح ✓</span>
                                        </button>
                                    </div>

                                    {{-- Next Student Navigation --}}
                                    @if($this->nextPendingStudent)
                                        <button type="button" wire:click="nextStudent" class="mas-btn-next">
                                            <span>الطالب التالي</span>
                                            <x-heroicon-o-arrow-left class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>

                                <div style="font-size: 0.72rem; color: var(--mas-text-muted); margin-top: 0.75rem; text-align: center; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                                    <x-heroicon-o-information-circle class="w-4 h-4" style="color: var(--mas-emerald);" />
                                    <span>النقر على <strong>تم التصحيح</strong> يعتمد ويحفظ الدرجة وينشرها فوراً للطالب وولي أمره دون أي تأخير.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
