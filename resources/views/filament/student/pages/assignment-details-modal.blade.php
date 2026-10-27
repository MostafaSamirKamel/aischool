<style>
    .assignment-modal-header {
        background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 8px 8px 0 0;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    }

    .assignment-modal-section {
        background: #f8f9fa;
        border-left: 4px solid #364FAC;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
    }

    .assignment-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .assignment-info-row:last-child {
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

    .badge-info {
        background-color: #cfe2ff;
        color: #084298;
    }

    .badge-success {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .badge-warning {
        background-color: #fff3cd;
        color: #997404;
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

    .action-button-success {
        background-color: #58BA47;
        color: white;
    }

    .action-button-success:hover {
        background-color: #469A37;
        color: white;
        text-decoration: none;
    }

    .action-button-secondary {
        background-color: #6c757d;
        color: white;
    }

    .action-button-secondary:hover {
        background-color: #5c636a;
        color: white;
        text-decoration: none;
    }

    .description-box {
        background-color: #f0f7ff;
        border: 1px solid #b6d4fe;
        padding: 1.25rem;
        border-radius: 6px;
        line-height: 1.6;
        color: #084298;
    }
</style>

<div style="direction: rtl;">
    {{-- Header --}}
    <div class="assignment-modal-header">
        <h5 class="mb-1" style="font-size: 1.5rem; font-weight: bold;">{{ $assignment->title }}</h5>
        <p class="mb-0 opacity-90">{{ __('filament/student/my_assignments.assignment_description') }}</p>
    </div>

    {{-- Basic Information --}}
    <div class="assignment-modal-section">
        <div class="assignment-info-row">
            <span class="info-label">{{ __('filament/student/my_assignments.assignment_course') }}</span>
            <span class="badge-pill badge-info">{{ $assignment->group->course->name }}</span>
        </div>
        <div class="assignment-info-row">
            <span class="info-label">{{ __('filament/student/my_assignments.assignment_group') }}</span>
            <span class="badge-pill badge-success">{{ $assignment->group->name }}</span>
        </div>
        <div class="assignment-info-row">
            <span class="info-label">{{ __('filament/student/my_assignments.assignment_due_date') }}</span>
            <span class="info-value" style="{{ $assignment->due_date && $assignment->due_date->isPast() ? 'color: #dc3545;' : '' }}">
                📅 {{ $assignment->due_date ? $assignment->due_date->format('d/m/Y') : '-' }}
                @if($assignment->due_date && $assignment->due_date->isPast())
                    <span class="badge-pill badge-warning" style="margin-right: 0.5rem;">متأخر</span>
                @endif
            </span>
        </div>
        <div class="assignment-info-row">
            <span class="info-label">{{ __('filament/student/my_assignments.assignment_total_grade') }}</span>
            <span class="badge-pill badge-info">⭐ {{ $assignment->max_grade ?? $assignment->max_score }} نقطة</span>
        </div>
    </div>

    {{-- Description --}}
    @if($assignment->description)
        <div style="margin-bottom: 1.5rem;">
            <h6 style="font-weight: bold; margin-bottom: 0.75rem; color: #212529;">📝 {{ __('filament/student/my_assignments.assignment_description') }}</h6>
            <div class="description-box">
                {!! nl2br(e($assignment->description)) !!}
            </div>
        </div>
    @endif

    {{-- Attached File --}}
    @if($assignment->attachment_path)
        <div style="margin-bottom: 1.5rem;">
            <h6 style="font-weight: bold; margin-bottom: 0.75rem; color: #212529;">📎 {{ __('filament/student/my_assignments.attached_file') }}</h6>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('student.assignment.view', $assignment->id) }}"
                   target="_blank"
                   class="action-button action-button-primary">
                    👁️ {{ __('filament/student/my_assignments.view_file_btn') }}
                </a>
                <a href="{{ route('student.assignment.download', $assignment->id) }}"
                   class="action-button action-button-success">
                    ⬇️ {{ __('filament/student/my_assignments.download_file_btn') }}
                </a>
            </div>
        </div>
    @endif

    {{-- External Link --}}
    @if($assignment->external_link)
        <div style="margin-bottom: 1.5rem;">
            <h6 style="font-weight: bold; margin-bottom: 0.75rem; color: #212529;">🔗 {{ __('filament/student/my_assignments.external_link_label') }}</h6>
            <a href="{{ $assignment->external_link }}"
               target="_blank"
               class="action-button action-button-secondary">
                🌐 {{ __('filament/student/my_assignments.open_link_btn') }}
            </a>
        </div>
    @endif

    {{-- Footer Info --}}
    <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 1.5rem; border-top: 2px solid #dee2e6;">
        <small style="color: #6c757d;">
            ℹ️ تم إنشاء هذا الواجب من قبل 
            <strong>{{ $assignment->group->instructor->first_name ?? 'المدرس' }}</strong>
        </small>
    </div>
</div>
