<x-filament-panels::page>
    @include('filament.student.layouts.student-page-styles')

    <style>
        .te-wrap {
            max-width: 980px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .te-sticky {
            position: sticky;
            top: 10px;
            z-index: 20;
            border: 1px solid #dbe6f3;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(13, 42, 74, 0.08);
            background: #ffffff;
        }

        .te-hero {
            padding: 16px;
            background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
            color: #ffffff;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .te-title {
            margin: 0 0 4px;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.2;
        }

        .te-desc {
            margin: 0;
            font-size: 13px;
            opacity: 0.9;
        }

        .te-chips {
            margin-top: 10px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .te-chip {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .te-timer {
            min-width: 150px;
            text-align: center;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 10px;
        }

        .te-timer-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 2px;
        }

        .te-time {
            margin: 0;
            font-weight: 800;
            font-size: 30px;
            letter-spacing: 1px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, Courier New, monospace;
        }

        .te-time-urgent {
            color: #ffcccc;
            animation: tePulse 1s infinite;
        }

        @keyframes tePulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }

        .te-progress-wrap {
            padding: 12px 16px;
            background: #f7fbff;
            border-top: 1px solid #e6f0fb;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .te-progress-main {
            flex: 1;
            min-width: 0;
        }

        .te-progress-head {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
            color: #5f7287;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .te-progress-track {
            width: 100%;
            background: #d9e7f7;
            border-radius: 999px;
            height: 8px;
            overflow: hidden;
        }

        .te-progress-fill {
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(90deg, #364FAC 0%, #58BA47 100%);
        }

        .te-progress-percent {
            white-space: nowrap;
            font-weight: 800;
            font-size: 14px;
            color: #18243D;
        }

        .te-alert {
            border-radius: 14px;
            border: 1px solid;
            padding: 12px;
            font-size: 14px;
            line-height: 1.6;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .te-alert-info {
            background: #EEF2FF;
            border-color: #E5EAF3;
            color: #18243D;
        }

        .te-alert-warn {
            background: #fff6e8;
            border-color: #ffdba6;
            color: #8a5a00;
        }

        .te-empty {
            border: 1px solid #dbe6f3;
            border-radius: 16px;
            background: #ffffff;
            text-align: center;
            padding: 36px 16px;
            color: #6b7d92;
        }

        .te-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .te-question {
            border: 1px solid #dbe6f3;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(13, 42, 74, 0.05);
        }

        .te-question.answered {
            border-color: #96d5ad;
        }

        .te-q-head {
            padding: 12px 16px;
            background: #f7fbff;
            border-bottom: 1px solid #e6f0fb;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .te-question.answered .te-q-head {
            background: #eefbf3;
            border-bottom-color: #ccefd8;
        }

        .te-q-meta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .te-q-num {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            color: #364FAC;
            background: #EEF2FF;
        }

        .te-question.answered .te-q-num {
            background: #58BA47;
            color: #ffffff;
        }

        .te-q-state {
            font-size: 12px;
            font-weight: 700;
            color: #5f7287;
        }

        .te-question.answered .te-q-state {
            color: #1F7A18;
        }

        .te-q-points {
            font-size: 12px;
            font-weight: 700;
            color: #5f7287;
            background: #eaf3ff;
            border-radius: 999px;
            padding: 4px 10px;
        }

        .te-q-body {
            padding: 16px;
        }

        .te-q-text {
            margin: 0 0 14px;
            font-size: 16px;
            font-weight: 800;
            color: #0d3a63;
            line-height: 1.65;
        }

        .te-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .te-option {
            border: 2px solid #dbe6f3;
            border-radius: 12px;
            padding: 11px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            background: #ffffff;
            transition: 0.15s;
        }

        .te-option:hover {
            border-color: #364FAC;
            background: #F5F7FC;
        }

        .te-option.selected {
            border-color: #364FAC;
            background: #EEF2FF;
        }

        .te-option input[type='radio'] {
            display: none;
        }

        .te-letter {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            background: #edf2f7;
            color: #49617a;
            flex-shrink: 0;
        }

        .te-option.selected .te-letter {
            background: #364FAC;
            color: #fff;
        }

        .te-option-text {
            flex: 1;
            min-width: 0;
            color: #1d3f5c;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.7;
        }

        .te-submit-box {
            border: 1px solid #dbe6f3;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(13, 42, 74, 0.05);
            padding: 14px;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .te-summary {
            margin: 0;
            color: #3a5771;
            font-size: 14px;
            font-weight: 700;
        }

        .te-summary strong {
            color: #0d3a63;
        }

        .te-actions {
            display: inline-flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .te-btn {
            border: 0;
            border-radius: 10px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .te-btn-back {
            background: #eaf1f9;
            color: #36526b;
        }

        .te-btn-back:hover {
            background: #dde9f4;
        }

        .te-btn-submit {
            background: #58BA47;
            color: #ffffff;
        }

        .te-btn-submit:hover {
            background: #469A37;
        }

        @media (max-width: 768px) {
            .te-title {
                font-size: 18px;
            }

            .te-time {
                font-size: 25px;
            }

            .te-progress-wrap {
                flex-wrap: wrap;
            }

            .te-timer {
                min-width: 125px;
            }

            .te-option {
                min-height: 48px;
                padding: 12px 14px;
            }
        }

        /* ==========================================================================
           TAKE EXAM DARK MODE (SECTION 23 & 25 SPECIFICATION)
           ========================================================================== */
        .dark .te-sticky,
        html.dark .te-sticky {
            background: #172033;
            border-color: #334155;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .dark .te-progress-wrap,
        html.dark .te-progress-wrap {
            background: #1E293B;
            border-top-color: #334155;
        }

        .dark .te-progress-head,
        html.dark .te-progress-head {
            color: #CBD5E1;
        }

        .dark .te-progress-track,
        html.dark .te-progress-track {
            background: #334155;
        }

        .dark .te-progress-percent,
        html.dark .te-progress-percent {
            color: #F1F5F9;
        }

        .dark .te-alert-info,
        html.dark .te-alert-info {
            background: #1E293B;
            border-color: #334155;
            color: #CBD5E1;
        }

        .dark .te-empty,
        html.dark .te-empty {
            background: #172033;
            border-color: #334155;
            color: #CBD5E1;
        }

        .dark .te-question,
        html.dark .te-question {
            background: #172033;
            border-color: #334155;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        }

        .dark .te-question.answered,
        html.dark .te-question.answered {
            border-color: #16A34A;
        }

        .dark .te-q-head,
        html.dark .te-q-head {
            background: #1E293B;
            border-bottom-color: #334155;
        }

        .dark .te-question.answered .te-q-head,
        html.dark .te-question.answered .te-q-head {
            background: rgba(22, 163, 74, 0.15);
            border-bottom-color: rgba(22, 163, 74, 0.3);
        }

        .dark .te-q-num,
        html.dark .te-q-num {
            background: rgba(37, 99, 235, 0.2);
            color: #60A5FA;
        }

        .dark .te-question.answered .te-q-num,
        html.dark .te-question.answered .te-q-num {
            background: #16A34A;
            color: #FFFFFF;
        }

        .dark .te-q-state,
        html.dark .te-q-state {
            color: #CBD5E1;
        }

        .dark .te-question.answered .te-q-state,
        html.dark .te-question.answered .te-q-state {
            color: #4ADE80;
        }

        .dark .te-q-points,
        html.dark .te-q-points {
            background: #1E293B;
            color: #CBD5E1;
        }

        .dark .te-q-text,
        html.dark .te-q-text {
            color: #F1F5F9;
        }

        .dark .te-option,
        html.dark .te-option {
            background: #1E293B;
            border-color: #334155;
            color: #F1F5F9;
        }

        .dark .te-option:hover,
        html.dark .te-option:hover {
            border-color: #2563EB;
            background: #25334D;
        }

        .dark .te-option.selected,
        html.dark .te-option.selected {
            border-color: #2563EB;
            background: rgba(37, 99, 235, 0.2);
        }

        .dark .te-letter,
        html.dark .te-letter {
            background: #334155;
            color: #CBD5E1;
        }

        .dark .te-option.selected .te-letter,
        html.dark .te-option.selected .te-letter {
            background: #2563EB;
            color: #FFFFFF;
        }

        .dark .te-option-text,
        html.dark .te-option-text {
            color: #F1F5F9;
        }

        .dark .te-submit-box,
        html.dark .te-submit-box {
            background: #172033;
            border-color: #334155;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        }

        .dark .te-summary,
        html.dark .te-summary {
            color: #CBD5E1;
        }

        .dark .te-summary strong,
        html.dark .te-summary strong {
            color: #F1F5F9;
        }

        .dark .te-btn-back,
        html.dark .te-btn-back {
            background: #1E293B;
            color: #CBD5E1;
        }

        .dark .te-btn-back:hover,
        html.dark .te-btn-back:hover {
            background: #25334D;
            color: #F1F5F9;
        }
    </style>

    @php
        $exam = $this->examRecord;
        // حساب وقت الانتهاء من وقت بدء المحاولة + مدة الامتحان
        $attempt   = isset($this->attempt) ? $this->attempt : null;
        $endsAt    = $attempt?->started_at?->copy()->addMinutes(max((int) $exam->duration, 0))
                     ?? $exam->ends_at;
        $endsAtMs  = $endsAt ? ($endsAt->getTimestamp() * 1000) : 0;
        $questions = $exam->questions;
        $totalAnswered = count(array_filter($this->selectedAnswers));
        $totalQuestions = $questions->count();
    @endphp

    <div
        x-data="{
            endAt: {{ $endsAtMs }},
            formatted: '00:00:00',
            urgentTime: false,
            submitted: false,
            _timer: null,
            init() {
                if (!this.endAt) { this.formatted = '--:--:--'; return; }
                const tick = () => {
                    const rem = Math.max(0, Math.floor((this.endAt - Date.now()) / 1000));
                    this.formatted =
                        String(Math.floor(rem / 3600)).padStart(2,'0') + ':' +
                        String(Math.floor((rem % 3600) / 60)).padStart(2,'0') + ':' +
                        String(rem % 60).padStart(2,'0');
                    this.urgentTime = rem <= 300;
                    if (rem === 0 && !this.submitted) {
                        this.submitted = true;
                        clearInterval(this._timer);
                        $wire.autoSubmitExam();
                    }
                };
                tick();
                this._timer = setInterval(tick, 1000);
            }
        }"
        class="te-wrap"
    >
        <div class="te-sticky">
            <div class="te-hero">
                <div>
                    <h2 class="te-title">{{ $exam->title }}</h2>
                    @if($exam->description)
                        <p class="te-desc">{{ strip_tags($exam->description) }}</p>
                    @endif
                    <div class="te-chips">
                        <span class="te-chip">
                            <x-heroicon-o-document-text class="w-3.5 h-3.5" />
                            {{ $totalQuestions }} {{ __('سؤال') }}
                        </span>
                        <span class="te-chip">
                            <x-heroicon-o-star class="w-3.5 h-3.5" />
                            {{ $exam->total_marks }} {{ __('درجة') }}
                        </span>
                        <span class="te-chip">
                            <x-heroicon-o-clock class="w-3.5 h-3.5" />
                            {{ $exam->duration }} {{ __('دقيقة') }}
                        </span>
                    </div>
                </div>

                <div class="te-timer" wire:ignore>
                    <div class="te-timer-label">{{ __('الوقت المتبقي') }}</div>
                    <p
                        class="te-time"
                        x-text="formatted"
                        :class="{ 'te-time-urgent': urgentTime }"
                    >00:00:00</p>
                </div>
            </div>

            <div class="te-progress-wrap">
                <div class="te-progress-main">
                    <div class="te-progress-head">
                        <span>{{ __('تقدمك') }}</span>
                        <span dir="ltr">{{ $totalAnswered }} / {{ $totalQuestions }}</span>
                    </div>
                    <div class="te-progress-track">
                        <div
                            class="te-progress-fill"
                            style="width: {{ $totalQuestions > 0 ? round(($totalAnswered / $totalQuestions) * 100) : 0 }}%"
                        ></div>
                    </div>
                </div>
                <span class="te-progress-percent">
                    {{ $totalQuestions > 0 ? round(($totalAnswered / $totalQuestions) * 100) : 0 }}%
                </span>
            </div>
        </div>

        @if($exam->instructions)
            <div class="te-alert te-alert-info">
                <x-heroicon-o-information-circle class="w-5 h-5 flex-shrink-0 mt-0.5" />
                <div>
                    <strong>{{ __('تعليمات الامتحان') }}:</strong>
                    {!! nl2br(e(strip_tags($exam->instructions))) !!}
                </div>
            </div>
        @endif

        <div class="te-alert te-alert-warn">
            <x-heroicon-o-shield-check class="w-5 h-5 flex-shrink-0 mt-0.5" />
            <span>{{ __('يتم حفظ إجاباتك تلقائياً عند كل اختيار. سيتم التسليم تلقائياً عند انتهاء الوقت.') }}</span>
        </div>

        @if($questions->isEmpty())
            <div class="te-empty">
                <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                <p>{{ __('لا توجد أسئلة مضافة لهذا الامتحان حتى الآن.') }}</p>
            </div>
        @else
            <form wire:submit="submitExam" class="te-form">
                @foreach($questions as $index => $question)
                    @php
                        $isAnswered = !empty($this->selectedAnswers[$question->id]);
                    @endphp
                    <div class="te-question {{ $isAnswered ? 'answered' : '' }}">
                        <div class="te-q-head">
                            <div class="te-q-meta">
                                <span class="te-q-num">{{ $index + 1 }}</span>
                                <span class="te-q-state">
                                    {{ $isAnswered ? __('تمت الإجابة') : __('لم يتم الإجابة بعد') }}
                                </span>
                            </div>
                            <span class="te-q-points">{{ $question->points }} {{ __('درجة') }}</span>
                        </div>

                        <div class="te-q-body">
                            <p class="te-q-text">{{ $question->question_text }}</p>

                            <div class="te-options">
                                @foreach($question->options as $optionIndex => $option)
                                    @php
                                        $letters = ['أ', 'ب', 'ج', 'د', 'هـ'];
                                        $letter = $letters[$optionIndex] ?? ($optionIndex + 1);
                                        $isSelected = isset($this->selectedAnswers[$question->id]) && (int)$this->selectedAnswers[$question->id] === $option->id;
                                    @endphp
                                    <label class="te-option {{ $isSelected ? 'selected' : '' }}">
                                        <input
                                            type="radio"
                                            wire:model.live="selectedAnswers.{{ $question->id }}"
                                            value="{{ $option->id }}"
                                        >
                                        <span class="te-letter">{{ $letter }}</span>
                                        <span class="te-option-text">{{ $option->option_text }}</span>
                                        @if($isSelected)
                                            <x-heroicon-s-check-circle class="w-5 h-5 text-primary-500 flex-shrink-0" />
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="te-submit-box">
                    <p class="te-summary">
                        <strong>{{ $totalAnswered }}</strong>
                        {{ __('من') }}
                        <strong>{{ $totalQuestions }}</strong>
                        {{ __('سؤال تمت الإجابة عليه') }}
                        @if($totalAnswered < $totalQuestions)
                            - <span style="color:#d17f00;">{{ $totalQuestions - $totalAnswered }} {{ __('سؤال غير مجاب') }}</span>
                        @else
                            - <span style="color:#198754;">{{ __('أجبت على جميع الأسئلة!') }}</span>
                        @endif
                    </p>

                    <div class="te-actions">
                        <a
                            href="{{ \App\Filament\Student\Pages\MyExams::getUrl() }}"
                            class="te-btn te-btn-back"
                        >
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                            {{ __('رجوع') }}
                        </a>

                        <button
                            type="submit"
                            class="te-btn te-btn-submit"
                            onclick="return confirm('{{ __('هل أنت متأكد من تسليم الامتحان؟ لن تتمكن من التعديل بعد التسليم.') }}')"
                        >
                            <x-heroicon-o-paper-airplane class="w-4 h-4" />
                            {{ __('تسليم الامتحان') }}
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>


</x-filament-panels::page>
