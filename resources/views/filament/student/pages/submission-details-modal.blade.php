<style>
    .submission-modal-header {
        background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.25rem;
    }

    .submission-modal-section {
        background: #f8f9fa;
        border-inline-start: 4px solid #364FAC;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
    }

    .submission-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .submission-info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.95rem;
    }

    .info-value {
        font-weight: 500;
        color: #212529;
        font-size: 0.95rem;
    }

    .badge-pill {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-success {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .badge-info {
        background-color: #cfe2ff;
        color: #084298;
    }

    .badge-warning {
        background-color: #fff3cd;
        color: #997404;
    }

    .badge-primary {
        background-color: #cfe2ff;
        color: #084298;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .action-button-primary {
        background-color: #364FAC;
        color: white;
    }

    .action-button-primary:hover {
        background-color: #293C85;
        color: white;
        text-decoration: none;
    }

    .grade-card {
        background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 8px;
        text-align: center;
    }

    .grade-display {
        display: flex;
        gap: 2rem;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
    }

    .grade-item {
        flex: 1;
        min-width: 150px;
    }

    .grade-value {
        font-size: 3rem;
        font-weight: bold;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .grade-label {
        font-size: 0.9rem;
        opacity: 0.95;
    }

    .notes-box {
        background-color: #f0f7ff;
        border: 1px solid #b6d4fe;
        padding: 1.25rem;
        border-radius: 6px;
        line-height: 1.6;
        color: #084298;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .feedback-box {
        background-color: #fff8e1;
        border: 1px solid #ffe082;
        padding: 1.25rem;
        border-radius: 6px;
        line-height: 1.6;
        color: #664d03;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .status-badge-submitted {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .status-badge-graded {
        background-color: #d4edda;
        color: #155724;
    }
</style>

<div style="direction: rtl;">
    {{-- Header --}}
    <div class="submission-modal-header">
        <h5 class="mb-1" style="font-size: 1.5rem; font-weight: bold;">{{ $assignment->title }}</h5>
        <p class="mb-0 opacity-90">{{ __('filament/student/my_assignments.submission_modal_heading') }}</p>
    </div>

    {{-- Basic Information --}}
    <div class="submission-modal-section">
        <div class="submission-info-row">
            <span class="info-label">📅 {{ __('filament/student/my_assignments.submission_date') }}</span>
            <span class="info-value">{{ $submission->submitted_at ? $submission->submitted_at->format('d/m/Y H:i') : '-' }}</span>
        </div>
        <div class="submission-info-row">
            <span class="info-label">🏷️ {{ __('filament/student/my_assignments.status') }}</span>
            <span>
                @if($submission->graded)
                    <span class="badge-pill status-badge-graded">✓ {{ __('filament/student/my_assignments.status_graded') }}</span>
                @else
                    <span class="badge-pill status-badge-submitted">⬆️ {{ __('filament/student/my_assignments.status_submitted') }}</span>
                @endif
            </span>
        </div>
    </div>

    {{-- Your Notes --}}
    @if($submission->notes)
        <div style="margin-bottom: 1.5rem;">
            <h6 style="font-weight: bold; margin-bottom: 0.75rem; color: #212529;">💭 {{ __('filament/student/my_assignments.your_notes') }}</h6>
            <div class="notes-box">
                {!! nl2br(e($submission->notes)) !!}
            </div>
        </div>
    @endif

    {{-- Uploaded File --}}
    @if($submission->file_path)
        <div style="margin-bottom: 1.5rem;">
            <h6 style="font-weight: bold; margin-bottom: 0.75rem; color: #212529;">📎 {{ __('filament/student/my_assignments.uploaded_file') }}</h6>
            <a href="{{ Storage::url($submission->file_path) }}" 
               target="_blank" 
               class="action-button action-button-primary">
                👁️ {{ __('filament/student/my_assignments.view_file') }}
            </a>
        </div>
    @endif

    {{-- Grading Section --}}
    @if($submission->graded)
        <div style="margin-bottom: 1.5rem;">
            <h6 style="font-weight: bold; margin-bottom: 1rem; color: #212529;">⭐ {{ __('filament/student/my_assignments.grading') }}</h6>
            <div class="grade-card">
                <div class="grade-display">
                    <div class="grade-item">
                        <div class="grade-value">{{ $submission->grade ?? 0 }}</div>
                        <div class="grade-label">{{ __('filament/student/my_assignments.your_grade') }} / {{ $assignment->max_grade }}</div>
                    </div>
                    <div style="font-size: 1.5rem; opacity: 0.5;">|</div>
                    <div class="grade-item">
                        <div class="grade-value">
                            {{ $assignment->max_grade > 0 ? round(($submission->grade / $assignment->max_grade) * 100) : 0 }}%
                        </div>
                        <div class="grade-label">{{ __('filament/student/my_assignments.percentage') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Teacher Feedback --}}
    @if($submission->feedback)
        <div style="margin-bottom: 1.5rem;">
            <h6 style="font-weight: bold; margin-bottom: 0.75rem; color: #212529;">💬 {{ __('filament/student/my_assignments.teacher_notes') }}</h6>
            <div class="feedback-box">
                {!! nl2br(e($submission->feedback)) !!}
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 1.5rem; border-top: 2px solid #dee2e6;">
        <small style="color: #6c757d;">
            ℹ️ تم تصحيح هذا التسليم من قبل 
            <strong>{{ $assignment->group->instructor->first_name ?? 'المدرس' }}</strong>
            بتاريخ {{ $submission->graded_at ? $submission->graded_at->format('d/m/Y H:i') : '-' }}
        </small>
    </div>
</div>
