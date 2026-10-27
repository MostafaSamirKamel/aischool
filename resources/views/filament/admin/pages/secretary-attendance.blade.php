<x-filament-panels::page>
    @php
        $todayRecord = $this->getTodayRecord();
        $monthStats = $this->getMonthStats();
        $monthRecords = $this->getMonthRecords();
        $disableCheckIn = !is_null($todayRecord);
        $disableCheckOut = is_null($todayRecord) || !is_null($todayRecord?->check_out_at);
    @endphp

    <div>
    <style>
        .attendance-container {
            background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
            min-height: 100vh;
            padding: 2rem;
            margin: -2rem -2rem 0 -2rem;
        }

        .attendance-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .attendance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2);
        }

        .gradient-header {
            background: linear-gradient(135deg, #364FAC 0%, #58BA47 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 900;
            text-align: center;
            margin-bottom: 1rem;
        }

        .action-button {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            padding: 1.5rem;
            font-size: 1.125rem;
            font-weight: 700;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .action-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .action-button:hover::before {
            left: 100%;
        }

        .check-in-btn {
            background: linear-gradient(135deg, #58BA47 0%, #1F7A18 100%);
            color: white;
            box-shadow: 0 10px 30px rgba(88, 186, 71, 0.4);
        }

        .check-in-btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(88, 186, 71, 0.5);
        }

        .check-out-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
        }

        .check-out-btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(245, 158, 11, 0.5);
        }

        .action-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #364FAC;
            box-shadow: 0 20px 50px rgba(54, 79, 172, 0.16);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-box {
            background: linear-gradient(135deg, #e0e7ff 0%, #fce7f3 100%);
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            border-left: 4px solid #667eea;
        }

        .info-box-title {
            font-weight: 700;
            color: #4c1d95;
            margin-bottom: 0.5rem;
            font-size: 1.125rem;
        }

        .info-box-text {
            color: #6b21a8;
            font-size: 0.95rem;
        }

        .today-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .today-stat-item {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            border: 2px solid #d1d5db;
        }

        .today-stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .today-stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
        }

        .work-hours-highlight {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            color: white;
            margin-top: 1.5rem;
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.3);
        }

        .work-hours-label {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .work-hours-value {
            font-size: 3rem;
            font-weight: 900;
        }

        .records-table {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .records-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .records-table thead th {
            padding: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
        }

        .records-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }

        .records-table tbody tr:hover {
            background: linear-gradient(135deg, #eef2ff 0%, #faf5ff 100%);
        }

        .records-table tbody td {
            padding: 1rem;
        }

        .month-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .month-stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid #e5e7eb;
            text-align: center;
            transition: all 0.3s ease;
        }

        .month-stat-card:hover {
            transform: scale(1.05);
            border-color: #667eea;
        }

        @media print {
            .attendance-container {
                background: white;
                padding: 0;
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            .attendance-container {
                padding: 1rem;
                margin: -1rem -1rem 0 -1rem;
            }

            .gradient-header {
                font-size: 1.75rem;
            }

            .action-button {
                padding: 1rem;
                font-size: 1rem;
            }
        }
    </style>

    <div class="attendance-container">
        <h1 class="gradient-header">⏰ تسجيل الحضور والانصراف</h1>

        <div class="info-box">
            <div class="flex items-start gap-3">
                <div style="font-size: 2rem;">⏱️</div>
                <div>
                    <div class="info-box-title">ملاحظة هامة</div>
                    <div class="info-box-text">
                        يتم اعتماد توقيت السيرفر فقط، ولا يسمح بالتعديل إلا بصلاحيات إدارية.
                    </div>
                </div>
            </div>
        </div>

        <div class="attendance-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #111827; margin-bottom: 0.5rem;">
                    📋 سجل حضورك وانصرافك
                </h2>
                <p style="color: #6b7280;">اضغط على الزر المناسب لتسجيل حضورك أو انصرافك اليوم</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <button
                    wire:click="checkIn"
                    class="action-button check-in-btn"
                    @if($disableCheckIn) disabled @endif
                >
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                        <span style="font-size: 1.5rem;">✅</span>
                        <span>تسجيل حضور</span>
                    </div>
                </button>

                <button
                    wire:click="checkOut"
                    class="action-button check-out-btn"
                    @if($disableCheckOut) disabled @endif
                >
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                        <span style="font-size: 1.5rem;">👋</span>
                        <span>تسجيل انصراف</span>
                    </div>
                </button>
            </div>

            <div class="today-stats">
                <div class="today-stat-item">
                    <div class="today-stat-label">📅 تاريخ اليوم</div>
                    <div class="today-stat-value">
                        {{ now()->format('Y-m-d') }}
                    </div>
                </div>

                <div class="today-stat-item">
                    <div class="today-stat-label">🕐 وقت الحضور</div>
                    <div class="today-stat-value">
                        {{ $todayRecord?->check_in_at?->format('h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="today-stat-item">
                    <div class="today-stat-label">🕐 وقت الانصراف</div>
                    <div class="today-stat-value">
                        {{ $todayRecord?->check_out_at?->format('h:i A') ?? '—' }}
                    </div>
                </div>
            </div>

            <div class="work-hours-highlight">
                <div class="work-hours-label">⏱️ ساعات العمل اليوم</div>
                <div class="work-hours-value">
                    {{ $todayRecord?->work_minutes ? $this->formatMinutes($todayRecord->work_minutes) : '00:00' }}
                </div>
            </div>
        </div>

        <div class="attendance-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #111827; margin-bottom: 0.5rem;">
                    📊 إحصائيات الشهر الحالي
                </h2>
                <p style="color: #6b7280;">{{ $this->getMonthLabel() }}</p>
            </div>

            <div class="month-stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📆</div>
                    <div class="stat-value">{{ $monthStats['daysCount'] }}</div>
                    <div class="stat-label">عدد أيام الحضور</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-value">{{ $this->formatMinutes($monthStats['totalMinutes']) }}</div>
                    <div class="stat-label">إجمالي ساعات العمل</div>
                </div>
            </div>
        </div>

        <div class="attendance-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #111827; margin-bottom: 0.5rem;">
                    📜 سجل الحضور لهذا الشهر
                </h2>
                <p style="color: #6b7280;">عرض تفصيلي لجميع أيام الحضور والانصراف</p>
            </div>

            <div class="records-table">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align: right;">📅 التاريخ</th>
                            <th style="text-align: right;">🕐 الحضور</th>
                            <th style="text-align: right;">🕐 الانصراف</th>
                            <th style="text-align: right;">⏱️ ساعات العمل</th>
                            @if ($this->isAdminUser())
                                <th style="text-align: right;">⚙️ إدارة</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($monthRecords as $record)
                            <tr>
                                <td style="font-weight: 600; color: #111827;">
                                    {{ $record->date->format('Y-m-d') }}
                                </td>
                                <td style="color: #4b5563;">
                                    {{ $record->check_in_at?->format('h:i A') ?? '—' }}
                                </td>
                                <td style="color: #4b5563;">
                                    {{ $record->check_out_at?->format('h:i A') ?? '—' }}
                                </td>
                                <td style="font-weight: 700; color: #10b981;">
                                    {{ $record->work_minutes ? $this->formatMinutes($record->work_minutes) : '00:00' }}
                                </td>
                                @if ($this->isAdminUser())
                                    <td>
                                        <button
                                            wire:click="startEdit({{ $record->id }})"
                                            style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 0.5rem 1rem; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; transition: all 0.3s ease;"
                                            onmouseover="this.style.transform='scale(1.05)'"
                                            onmouseout="this.style.transform='scale(1)'"
                                        >
                                            ✏️ تعديل
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 3rem; text-align: center; color: #9ca3af;">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                                    <div style="font-size: 1.125rem; font-weight: 600;">لا توجد سجلات لهذا الشهر</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($this->isAdminUser())
            <div class="attendance-card">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #111827; margin-bottom: 0.5rem;">
                        🔧 تعديل إداري للسجلات
                    </h2>
                    <p style="color: #6b7280;">يسمح بالتعديل فقط للمسؤولين (Branch Manager / Super Admin)</p>
                </div>

                @if ($editRecordId)
                    <div style="background: linear-gradient(135deg, #eef2ff 0%, #faf5ff 100%); border-radius: 16px; padding: 2rem; border: 2px solid #a78bfa;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label style="display: block; font-weight: 600; color: #4b5563; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                    🕐 وقت الحضور
                                </label>
                                <input type="time" wire:model.defer="editCheckIn" 
                                    style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 2px solid #d1d5db; font-size: 1rem; transition: all 0.2s ease;"
                                    onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                                    onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                                />
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; color: #4b5563; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                    🕐 وقت الانصراف
                                </label>
                                <input type="time" wire:model.defer="editCheckOut" 
                                    style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 2px solid #d1d5db; font-size: 1rem; transition: all 0.2s ease;"
                                    onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                                    onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                                />
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <button wire:click="saveEdit" 
                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 0.75rem 2rem; border-radius: 12px; border: none; cursor: pointer; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(16, 185, 129, 0.4)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(16, 185, 129, 0.3)'"
                            >
                                ✅ حفظ التعديلات
                            </button>
                            <button wire:click="cancelEdit" 
                                style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 0.75rem 2rem; border-radius: 12px; border: none; cursor: pointer; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(239, 68, 68, 0.4)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(239, 68, 68, 0.3)'"
                            >
                                ❌ إلغاء
                            </button>
                        </div>
                    </div>
                @else
                    <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 16px; padding: 2rem; text-align: center; border: 2px dashed #d1d5db;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">👆</div>
                        <div style="font-size: 1.125rem; color: #6b7280; font-weight: 600;">
                            اختر سجلًا من الجدول واضغط "تعديل" لبدء التعديل الإداري
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
    </div>
</x-filament-panels::page>
