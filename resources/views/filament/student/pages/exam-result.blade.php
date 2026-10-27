<x-filament-panels::page>
    @include('filament.student.layouts.student-page-styles')

    @php
        $attempt = $this->attemptRecord;
        $exam = $attempt->exam;
        $percentage = $attempt->percentage !== null ? (float) $attempt->percentage : 0.0;
        $passed = (bool) ($attempt->passed ?? false);
        $score = (float) ($attempt->score ?? 0);
        $totalScore = (float) ($attempt->total_score ?? $exam->total_marks);
        $timeTaken = $attempt->time_taken
            ? gmdate(($attempt->time_taken >= 3600 ? 'H:i:s' : 'i:s'), $attempt->time_taken)
            : null;
        $passColor   = $passed ? '#198754' : '#dc3545';
        $passBg      = $passed ? '#d1e7dd' : '#f8d7da';
        $passBorder  = $passed ? '#a3cfbb' : '#f1aeb5';
        $passLabel   = $passed ? 'ناجح' : 'راسب';
        $passGrad    = $passed
            ? 'linear-gradient(135deg,#58BA47 0%,#1F7A18 100%)'
            : 'linear-gradient(135deg,#dc3545 0%,#ff6b6b 100%)';
    @endphp

    <style>
        .result-stat-box { background:#f8f9fa; border-radius:14px; padding:18px 10px; text-align:center; flex:1; min-width:0; }
        .result-stat-value { font-size:1.8rem; font-weight:800; line-height:1; }
        .result-stat-label { font-size:0.72rem; color:#6c757d; margin-top:5px; }
        .result-stats-row { display:flex; gap:10px; margin-bottom:16px; }
        .question-card { border-radius:14px; border:1px solid #e5e7eb; margin-bottom:14px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06); }
        .question-header { padding:12px 16px; background:#f9fafb; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:10px; }
        .question-body { padding:16px; }
        .option-row { display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:10px; border:2px solid #e5e7eb; margin-bottom:8px; background:#fff; transition:all .15s; }
        .option-row.correct { border-color:#198754; background:#d1e7dd; }
        .option-row.wrong   { border-color:#dc3545; background:#f8d7da; }
        .option-row.show-correct { border-color:#198754; background:#f0fdf4; }
        .option-letter { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8rem; flex-shrink:0; }
        .badge-q { font-size:0.72rem; padding:4px 10px; border-radius:20px; font-weight:600; white-space:nowrap; }

        /* ==========================================================================
           EXAM RESULT DARK MODE (SECTION 23 & 25 SPECIFICATION)
           ========================================================================== */
        .dark .card,
        html.dark .card {
            background: #172033 !important;
            border-color: #334155 !important;
            color: #F1F5F9 !important;
        }

        .dark .result-stat-box,
        html.dark .result-stat-box {
            background: #1E293B !important;
            border: 1px solid #334155 !important;
        }

        .dark .result-stat-label,
        html.dark .result-stat-label {
            color: #CBD5E1 !important;
        }

        .dark .question-card,
        html.dark .question-card {
            background: #172033 !important;
            border-color: #334155 !important;
        }

        .dark .question-header,
        html.dark .question-header {
            background: #1E293B !important;
            border-bottom-color: #334155 !important;
            color: #F1F5F9 !important;
        }

        .dark .question-body,
        html.dark .question-body {
            background: #172033 !important;
            color: #F1F5F9 !important;
        }

        .dark .option-row,
        html.dark .option-row {
            background: #1E293B !important;
            border-color: #334155 !important;
            color: #F1F5F9 !important;
        }

        .dark .option-row.correct,
        html.dark .option-row.correct {
            border-color: #16A34A !important;
            background: rgba(22, 163, 74, 0.2) !important;
        }

        .dark .option-row.wrong,
        html.dark .option-row.wrong {
            border-color: #DC2626 !important;
            background: rgba(220, 38, 38, 0.2) !important;
        }

        .dark .option-row.show-correct,
        html.dark .option-row.show-correct {
            border-color: #16A34A !important;
            background: rgba(22, 163, 74, 0.12) !important;
        }

        .dark .option-letter,
        html.dark .option-letter {
            background: #334155 !important;
            color: #F1F5F9 !important;
        }
    </style>

    @if(!$exam->published_at)
        <div class="card border-0 shadow-sm p-4 text-center my-4" style="border-radius:18px; background:#fff;">
            <div style="font-size:3.5rem; margin-bottom:12px;">⏳</div>
            <h3 class="fw-bold text-dark mb-2">تم تسليم إجابتك للاختبار بنجاح!</h3>
            <p class="text-muted mb-4" style="font-size:1.05rem;">
                نتيجة هذا الاختبار ({{ $exam->title }}) قيد المراجعة والاعتماد من قبل إدارة المدرسة والمعلم.<br>
                سيتم إتاحة درجاتك وتفاصيل الإجابات فور نشر النتائج رسمياً.
            </p>
            <div class="d-flex justify-content-center">
                <a href="{{ \App\Filament\Student\Pages\MyExams::getUrl() }}" class="btn btn-primary px-4 py-2" style="border-radius:12px; font-weight:700;">
                    العودة لقائمة الامتحانات
                </a>
            </div>
        </div>
    @else
    {{-- Hero Result Card --}}
    <div class="card border-0 shadow mb-4" style="border-radius:18px; overflow:hidden;">
        <div style="background:{{ $passGrad }}; padding:28px 24px; text-align:center; color:#fff;">
            <div style="font-size:3rem; margin-bottom:8px;">{{ $passed ? '🏆' : '📋' }}</div>
            <h4 class="fw-bold mb-2">{{ $exam->title }}</h4>
            <span style="background:rgba(255,255,255,0.25); border-radius:20px; padding:4px 18px; font-size:0.9rem; font-weight:700;">
                {{ $passed ? 'ناجح بامتياز' : 'لم تجتز الامتحان' }}
            </span>
        </div>

        <div class="card-body p-4">
            {{-- Stats Row --}}
            <div class="result-stats-row">
                <div class="result-stat-box">
                    <div class="result-stat-value" style="color:{{ $passColor }};">{{ number_format($score, 0) }}</div>
                    <div class="result-stat-label">درجتك</div>
                </div>
                <div class="result-stat-box">
                    <div class="result-stat-value" style="color:#495057;">{{ number_format($totalScore, 0) }}</div>
                    <div class="result-stat-label">الدرجة الكلية</div>
                </div>
                <div class="result-stat-box">
                    <div class="result-stat-value" style="color:#364FAC;">{{ number_format($percentage, 1) }}%</div>
                    <div class="result-stat-label">النسبة المئوية</div>
                </div>
                <div class="result-stat-box">
                    <div class="result-stat-value" style="color:#0dcaf0;">{{ $timeTaken ?? '—' }}</div>
                    <div class="result-stat-label">وقت الأداء</div>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="mb-3">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>مستوى الأداء</span>
                    <span>{{ number_format($percentage, 1) }}%</span>
                </div>
                <div class="progress" style="height:10px; border-radius:8px;">
                    <div class="progress-bar {{ $passed ? 'bg-success' : 'bg-danger' }}"
                         role="progressbar"
                         style="width:{{ min($percentage, 100) }}%; border-radius:8px;">
                    </div>
                </div>
            </div>

            {{-- Dates --}}
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="bi bi-play-circle"></i>
                        <span>بدأ في: <strong class="text-dark">{{ $attempt->started_at?->format('d/m/Y h:i A') ?? '—' }}</strong></span>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="bi bi-check-circle"></i>
                        <span>سُلِّم في: <strong class="text-dark">{{ $attempt->completed_at?->format('d/m/Y h:i A') ?? '—' }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Answer Review --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:18px; overflow:hidden;">
        <div class="card-header bg-white border-bottom px-4 py-3 d-flex align-items-center gap-2">
            <i class="bi bi-clipboard-check text-primary"></i>
            <span class="fw-bold">مراجعة الإجابات</span>
            <span class="badge bg-secondary ms-auto">{{ $exam->questions->count() }} سؤال</span>
        </div>
        <div class="card-body p-4">
            @php $letters = ['أ', 'ب', 'ج', 'د', 'هـ']; @endphp

            @foreach($exam->questions as $index => $question)
                @php
                    $answer      = $attempt->answers->firstWhere('exam_question_id', $question->id);
                    $isAnswered  = $answer && $answer->selectedOption;
                    $isCorrect   = $isAnswered && $answer->selectedOption->is_correct;

                    if ($isCorrect)       { $qBadgeClass = 'bg-success'; $qBadgeLabel = 'صحيح ✓'; }
                    elseif ($isAnswered)  { $qBadgeClass = 'bg-danger';  $qBadgeLabel = 'خطأ ✗'; }
                    else                  { $qBadgeClass = 'bg-secondary'; $qBadgeLabel = 'لم يُجب'; }
                @endphp

                <div class="question-card">
                    <div class="question-header d-flex align-items-center gap-3">
                        <span class="badge rounded-circle d-flex align-items-center justify-content-center {{ $qBadgeClass }}"
                              style="width:28px;height:28px;font-size:0.8rem;">
                            {{ $index + 1 }}
                        </span>
                        <span class="fw-semibold flex-grow-1" style="font-size:0.9rem;">{{ $question->question_text }}</span>
                        <span class="badge {{ $qBadgeClass }} badge-q">{{ $qBadgeLabel }}</span>
                        <span class="badge bg-light text-dark badge-q">{{ $question->points }} درجة</span>
                    </div>
                    <div class="question-body">
                        @foreach($question->options as $optIndex => $option)
                            @php
                                $isSelected      = $isAnswered && $answer->selectedOption->id === $option->id;
                                $isOptionCorrect = $option->is_correct;

                                if ($isOptionCorrect && $isSelected)  $rowClass = 'correct';
                                elseif ($isSelected)                   $rowClass = 'wrong';
                                elseif ($isOptionCorrect)              $rowClass = 'show-correct';
                                else                                   $rowClass = '';

                                if ($isOptionCorrect && $isSelected)  { $letBg = '#198754'; $letColor = '#fff'; }
                                elseif ($isSelected)                   { $letBg = '#dc3545'; $letColor = '#fff'; }
                                elseif ($isOptionCorrect)              { $letBg = '#d1e7dd'; $letColor = '#198754'; }
                                else                                   { $letBg = '#f0f0f0'; $letColor = '#555'; }
                            @endphp
                            <div class="option-row {{ $rowClass }}">
                                <div class="option-letter" style="background:{{ $letBg }};color:{{ $letColor }};">
                                    {{ $letters[$optIndex] ?? ($optIndex + 1) }}
                                </div>
                                <span class="flex-grow-1" style="font-size:0.875rem;">{{ $option->option_text }}</span>
                                @if($isOptionCorrect && $isSelected)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif($isSelected)
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                @elseif($isOptionCorrect)
                                    <span class="badge bg-success-subtle text-success" style="font-size:0.7rem;">الإجابة الصحيحة</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @endif

    {{-- Back Button --}}
    <div class="d-flex justify-content-end pb-3">
        <a href="{{ \App\Filament\Student\Pages\MyExams::getUrl() }}"
           class="btn btn-primary px-4">
            <i class="bi bi-arrow-right me-2"></i>العودة إلى الامتحانات
        </a>
</x-filament-panels::page>