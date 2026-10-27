<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $enrollment = $this->getEnrollment();
        @endphp

        @if($enrollment && $enrollment->enrollmentCourses->count() > 0)
            <style>
                .scp-container {
                    display: flex;
                    flex-direction: column;
                    gap: 1.25rem;
                    font-family: inherit;
                }
                .scp-card {
                    background: #ffffff;
                    border: 1px solid #e5eaf3;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 4px 16px rgba(24, 36, 61, 0.05);
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }
                .scp-card:hover {
                    box-shadow: 0 8px 24px rgba(54, 79, 172, 0.08);
                }
                .scp-header {
                    background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
                    color: #ffffff;
                    padding: 0.9rem 1.25rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 0.75rem;
                }
                .scp-header-title {
                    font-size: 1.05rem;
                    font-weight: 800;
                    margin: 0;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    color: #ffffff;
                }
                .scp-header-badge {
                    padding: 0.25rem 0.85rem;
                    border-radius: 999px;
                    font-size: 0.8rem;
                    font-weight: 750;
                    white-space: nowrap;
                }
                .scp-badge-success { background: #dcfce7; color: #166534; }
                .scp-badge-primary { background: #dbeafe; color: #1e40af; }
                .scp-badge-warning { background: #fef3c7; color: #92400e; }

                .scp-body {
                    padding: 1.15rem;
                    background: #fcfdfe;
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                }
                .scp-stats-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 0.75rem;
                }
                @media (max-width: 768px) {
                    .scp-stats-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }
                @media (max-width: 380px) {
                    .scp-stats-grid {
                        grid-template-columns: 1fr;
                    }
                }
                .scp-stat-item {
                    border-radius: 12px;
                    padding: 0.85rem 0.5rem;
                    text-align: center;
                    color: #ffffff;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                }
                .scp-bg-price { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
                .scp-bg-total { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
                .scp-bg-completed { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
                .scp-bg-remaining { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
                .scp-stat-value {
                    font-size: 1.25rem;
                    font-weight: 850;
                    margin-bottom: 0.15rem;
                    line-height: 1.2;
                }
                .scp-stat-label {
                    font-size: 0.75rem;
                    font-weight: 700;
                    opacity: 0.95;
                    margin: 0;
                }

                .scp-progress-box {
                    background: #ffffff;
                    border: 1px solid #e5eaf3;
                    border-radius: 12px;
                    padding: 0.85rem 1rem;
                }
                .scp-progress-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 0.5rem;
                }
                .scp-progress-title {
                    font-size: 0.875rem;
                    font-weight: 750;
                    color: #18243d;
                    margin: 0;
                }
                .scp-progress-percent {
                    font-size: 0.825rem;
                    font-weight: 800;
                    padding: 0.2rem 0.6rem;
                    border-radius: 999px;
                }
                .scp-progress-track {
                    height: 18px;
                    background: #e2e8f0;
                    border-radius: 999px;
                    overflow: hidden;
                    position: relative;
                }
                .scp-progress-bar {
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #ffffff;
                    font-size: 0.75rem;
                    font-weight: 800;
                    transition: width 0.4s ease;
                    border-radius: 999px;
                }
                .scp-bar-success { background: linear-gradient(135deg, #58BA47 0%, #1F7A18 100%); }
                .scp-bar-primary { background: linear-gradient(135deg, #364FAC 0%, #293C85 100%); }
                .scp-bar-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }

                .scp-group-info {
                    border-top: 1px solid #e5eaf3;
                    padding-top: 0.85rem;
                }
                .scp-group-title {
                    font-size: 0.875rem;
                    font-weight: 750;
                    color: #18243d;
                    margin: 0 0 0.65rem 0;
                }
                .scp-group-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 0.75rem;
                }
                @media (max-width: 580px) {
                    .scp-group-grid {
                        grid-template-columns: 1fr;
                    }
                }
                .scp-group-cell {
                    border-radius: 10px;
                    padding: 0.65rem 0.85rem;
                    text-align: center;
                    color: #ffffff;
                }
                .scp-group-cell.bg-instructor { background: linear-gradient(135deg, #364FAC 0%, #293C85 100%); }
                .scp-group-cell.bg-group { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); }
                .scp-group-cell-label {
                    font-size: 0.72rem;
                    opacity: 0.9;
                    font-weight: 700;
                    margin-bottom: 0.2rem;
                }
                .scp-group-cell-val {
                    font-size: 0.875rem;
                    font-weight: 800;
                    margin: 0;
                }
            </style>

            <div class="scp-container" dir="rtl">
                @foreach($enrollment->enrollmentCourses as $enrollmentCourse)
                    @php
                        $stats = $this->getCourseStats($enrollmentCourse);
                        $progress = $stats['progress'];
                        $totalSessions = $stats['total_sessions'];
                        $completedSessions = $stats['completed_sessions'];
                        $remainingSessions = $stats['remaining_sessions'];
                        $attendedSessions = $stats['attended_sessions'];

                        // اعتبار الكورس مكتملاً إذا وصلت نسبة التقدم 100% حتى لو لم يُحدَّث الـ DB بعد
                        $isEffectivelyCompleted = $enrollmentCourse->isCompleted()
                            || ($totalSessions > 0 && $completedSessions >= $totalSessions);
                        $isActive = !$isEffectivelyCompleted && $enrollmentCourse->isActive();

                        $statusLabel = $isEffectivelyCompleted ? 'مكتمل' : ($isActive ? 'نشط' : 'غير نشط');
                        $statusBadgeClass = $isEffectivelyCompleted ? 'scp-badge-success' : ($isActive ? 'scp-badge-primary' : 'scp-badge-warning');
                        $progressBarClass = $isEffectivelyCompleted ? 'scp-bar-success' : ($isActive ? 'scp-bar-primary' : 'scp-bar-warning');

                        // تحديث الـ DB تلقائياً إن لزم
                        if ($isEffectivelyCompleted && !$enrollmentCourse->isCompleted()) {
                            $enrollmentCourse->update(['status' => 'completed']);
                        }
                    @endphp

                    <div class="scp-card">
                        <div class="scp-header">
                            <h4 class="scp-header-title">
                                <span>🎓</span>
                                <span>{{ optional($enrollmentCourse->course)->name ?? 'كورس محذوف' }}</span>
                            </h4>
                            <span class="scp-header-badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="scp-body">
                            <div class="scp-stats-grid">
                                <div class="scp-stat-item scp-bg-price">
                                    <div class="scp-stat-value">{{ number_format($enrollmentCourse->course_price, 0) }}</div>
                                    <div class="scp-stat-label">السعر (ج.م)</div>
                                </div>

                                <div class="scp-stat-item scp-bg-total">
                                    <div class="scp-stat-value">{{ $totalSessions }}</div>
                                    <div class="scp-stat-label">إجمالي الحصص</div>
                                </div>

                                <div class="scp-stat-item scp-bg-completed">
                                    <div class="scp-stat-value">{{ $completedSessions }}</div>
                                    <div class="scp-stat-label">تم إنجازه</div>
                                </div>

                                <div class="scp-stat-item scp-bg-remaining">
                                    <div class="scp-stat-value">{{ $remainingSessions }}</div>
                                    <div class="scp-stat-label">المتبقي</div>
                                </div>
                            </div>

                            <div class="scp-progress-box">
                                <div class="scp-progress-header">
                                    <span class="scp-progress-title">نسبة الإنجاز</span>
                                    <span class="scp-progress-percent {{ $statusBadgeClass }}">{{ $progress }}%</span>
                                </div>
                                <div class="scp-progress-track">
                                    <div class="scp-progress-bar {{ $progressBarClass }}" style="width: {{ $progress }}%;">
                                        @if($progress >= 15) {{ $progress }}% @endif
                                    </div>
                                </div>
                            </div>

                            @if($enrollmentCourse->group)
                                <div class="scp-group-info">
                                    <div class="scp-group-title">معلومات المجموعة</div>
                                    <div class="scp-group-grid">
                                        <div class="scp-group-cell bg-instructor">
                                            <div class="scp-group-cell-label">المعلم</div>
                                            <p class="scp-group-cell-val">{{ optional(optional($enrollmentCourse->group->instructor)->user)->name ?? 'غير محدد' }}</p>
                                        </div>
                                        <div class="scp-group-cell bg-group">
                                            <div class="scp-group-cell-label">المجموعة</div>
                                            <p class="scp-group-cell-val">{{ $enrollmentCourse->group->name }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <h5 class="text-base font-semibold text-gray-600 dark:text-gray-400">
                    لا توجد كورسات مسجلة
                </h5>
                <p class="text-gray-500 dark:text-gray-500 mt-2 text-sm">
                    لم يتم تسجيلك في أي كورسات حتى الآن
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
