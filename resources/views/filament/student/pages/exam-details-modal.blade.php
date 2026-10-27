@php
    $attempt = $exam->attempts->first();
    $descriptionText = $exam->description ? trim(strip_tags($exam->description)) : null;
    $score = (float) ($attempt?->score ?? 0);
    $total = (float) $exam->total_marks;
    $percentage = $total > 0 ? round(($score / $total) * 100) : 0;
    $passed = $score >= $exam->passing_marks;
@endphp

<style>
    .mi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 115px), 1fr)); gap: 10px; margin-bottom: 14px; }
    .mi-cell { background: #f8f9fa; border-radius: 12px; padding: 12px 8px; text-align: center; border: 1px solid #e9ecef; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .mi-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin: 0 auto 6px; }
    .mi-label { font-size: 0.72rem; color: #64748b; margin-bottom: 3px; font-weight: 600; }
    .mi-value { font-size: 0.85rem; font-weight: 750; color: #18243d; word-break: break-word; }
</style>

<div style="padding:4px 0;">

    {{-- Info Grid: 4 per row --}}
    <div class="mi-grid">
        <div class="mi-cell">
            <div class="mi-icon" style="background:#e0e7ff;">🎓</div>
            <div class="mi-label">الكورس</div>
            <div class="mi-value">{{ $exam->group->course->name }}</div>
        </div>
        <div class="mi-cell">
            <div class="mi-icon" style="background:#d1fae5;">👥</div>
            <div class="mi-label">المجموعة</div>
            <div class="mi-value">{{ $exam->group->name }}</div>
        </div>
        <div class="mi-cell">
            <div class="mi-icon" style="background:#dbeafe;">📅</div>
            <div class="mi-label">تاريخ الامتحان</div>
            <div class="mi-value">{{ $exam->exam_date ? $exam->exam_date->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="mi-cell">
            <div class="mi-icon" style="background:#fef9c3;">⏱️</div>
            <div class="mi-label">المدة</div>
            <div class="mi-value">{{ $exam->duration }} د</div>
        </div>
        <div class="mi-cell">
            <div class="mi-icon" style="background:#fef3c7;">⭐</div>
            <div class="mi-label">الدرجة الكلية</div>
            <div class="mi-value">{{ $exam->total_marks }}</div>
        </div>
        <div class="mi-cell">
            <div class="mi-icon" style="background:#d1fae5;">✅</div>
            <div class="mi-label">درجة النجاح</div>
            <div class="mi-value">{{ $exam->passing_marks }}</div>
        </div>
    </div>

    {{-- Description --}}
    @if($descriptionText)
        <div class="alert alert-info d-flex gap-2 py-2 px-3 mb-3" style="border-radius:10px; font-size:0.875rem;">
            <span>ℹ️</span>
            <div><strong>الوصف:</strong> {{ $descriptionText }}</div>
        </div>
    @endif

    {{-- Result --}}
    @if($attempt && $attempt->completed_at)
        <div class="card border-0 mb-0"
             style="border-radius:12px; border:2px solid {{ $passed ? '#a3cfbb' : '#f1aeb5' }} !important; background:{{ $passed ? '#d1e7dd' : '#f8d7da' }};">
            <div class="card-body px-4 py-3">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span>{{ $passed ? '🏆' : '📋' }}</span>
                    <strong style="color:{{ $passed ? '#0a3622' : '#58151c' }};">
                        نتيجتك — {{ $passed ? 'ناجح' : 'راسب' }}
                    </strong>
                </div>

                <div class="d-flex gap-2 text-center mb-3">
                    <div style="flex:1; background:#fff; border-radius:10px; padding:12px 8px;">
                        <div style="font-size:1.5rem; font-weight:700; color:{{ $passed ? '#198754' : '#dc3545' }};">{{ $score }}</div>
                        <div style="font-size:0.72rem; color:#6c757d;">درجتك</div>
                    </div>
                    <div style="flex:1; background:#fff; border-radius:10px; padding:12px 8px;">
                        <div style="font-size:1.5rem; font-weight:700; color:#0d6efd;">{{ $percentage }}%</div>
                        <div style="font-size:0.72rem; color:#6c757d;">النسبة</div>
                    </div>
                    <div style="flex:1; background:#fff; border-radius:10px; padding:12px 8px;">
                        <div style="font-size:1.5rem; font-weight:700; color:#6c757d;">{{ $total }}</div>
                        <div style="font-size:0.72rem; color:#6c757d;">الكلية</div>
                    </div>
                </div>

                <div class="progress mb-2" style="height:8px; border-radius:8px;">
                    <div class="progress-bar {{ $passed ? 'bg-success' : 'bg-danger' }}"
                         role="progressbar"
                         style="width:{{ $percentage }}%; border-radius:8px;"></div>
                </div>

                <div class="text-center" style="font-size:0.75rem; color:{{ $passed ? '#0a3622' : '#58151c' }};">
                    تم الإكمال في: {{ $attempt->completed_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    @elseif($attempt && !$attempt->completed_at)
        <div class="alert alert-warning d-flex gap-2 py-2 px-3 mb-0" style="border-radius:10px; font-size:0.875rem;">
            <span>✏️</span> الامتحان جاري الحل ولم يكتمل بعد
        </div>
    @else
        <div class="alert alert-secondary d-flex gap-2 py-2 px-3 mb-0" style="border-radius:10px; font-size:0.875rem;">
            <span>📄</span> لم تؤدِ هذا الامتحان بعد
        </div>
    @endif

</div>