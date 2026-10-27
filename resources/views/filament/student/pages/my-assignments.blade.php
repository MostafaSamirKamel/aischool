<x-filament-panels::page>
    <div class="student-theme-wrapper" dir="rtl">
        <link rel="stylesheet" href="{{ asset('css/student-theme-scoped.css') }}">
        
        <style>
            /* Scoped Homework Styles */
            .hw-modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.65);
                backdrop-filter: blur(6px);
                z-index: 100000;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                overflow-y: auto;
            }
            .hw-modal-card {
                background: #ffffff;
                width: 100%;
                max-width: min(calc(100vw - 24px), 720px);
                max-height: calc(100dvh - 2rem);
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                animation: hwModalFadeIn 0.25s ease-out;
            }
            @keyframes hwModalFadeIn {
                from { opacity: 0; transform: scale(0.96) translateY(8px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
            .hw-modal-header {
                background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
                color: #ffffff;
                padding: 1.25rem 1.5rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
            }
            .hw-modal-body {
                padding: 1.5rem;
                overflow-y: auto;
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }
            .hw-meta-bar {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem 1.5rem;
                background: #f8fafc;
                padding: 1rem;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                font-size: 0.9rem;
            }
            .hw-meta-item {
                display: flex;
                align-items: center;
                gap: 0.4rem;
                color: #475569;
            }
            .hw-meta-item strong {
                color: #1e293b;
            }
            .hw-desc-box {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 1rem;
                font-size: 0.95rem;
                line-height: 1.6;
                color: #334155;
            }
            .hw-grade-card {
                background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
                color: white;
                border-radius: 16px;
                padding: 1.5rem;
                display: flex;
                justify-content: space-around;
                align-items: center;
                text-align: center;
                box-shadow: 0 8px 20px rgba(54, 79, 172, 0.25);
            }
            .hw-grade-val {
                font-size: 2.75rem;
                font-weight: 800;
                line-height: 1;
                color: #58BA47;
                text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            }
            .hw-feedback-box {
                background: #fffbeb;
                border: 1px solid #fde68a;
                border-inline-start: 5px solid #f59e0b;
                border-radius: 12px;
                padding: 1.25rem;
                color: #92400e;
                font-size: 0.95rem;
                line-height: 1.6;
            }
            .hw-upload-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }
            @media (max-width: 480px) {
                .hw-upload-actions {
                    grid-template-columns: 1fr;
                }
            }
            .hw-btn-upload {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.85rem 1rem;
                border-radius: 12px;
                border: 2px dashed #364FAC;
                background: #f0f4ff;
                color: #364FAC;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s;
                text-align: center;
                font-size: 0.92rem;
            }
            .hw-btn-upload:hover {
                background: #e0e9ff;
                border-color: #293C85;
            }
            .hw-images-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
                gap: 0.75rem;
                margin-top: 0.5rem;
            }
            .hw-image-thumb-wrap {
                position: relative;
                aspect-ratio: 1;
                border-radius: 12px;
                overflow: hidden;
                border: 2px solid #e2e8f0;
                background: #f8fafc;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            }
            .hw-image-thumb {
                width: 100%;
                height: 100%;
                object-fit: cover;
                cursor: pointer;
                transition: transform 0.2s;
            }
            .hw-image-thumb:hover {
                transform: scale(1.05);
            }
            .hw-thumb-remove {
                position: absolute;
                top: 4px;
                right: 4px;
                background: rgba(239, 68, 68, 0.9);
                color: white;
                border: none;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                cursor: pointer;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                transition: transform 0.15s;
            }
            .hw-thumb-remove:hover {
                transform: scale(1.15);
                background: #dc2626;
            }
            .hw-lightbox {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.92);
                z-index: 100005;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }
            .hw-lightbox-img {
                max-width: 95vw;
                max-height: 85vh;
                object-fit: contain;
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
                transition: transform 0.2s ease;
            }
            .hw-lightbox-close {
                position: absolute;
                top: 1.5rem;
                right: 1.5rem;
                background: rgba(255, 255, 255, 0.2);
                color: #ffffff;
                border: none;
                border-radius: 50%;
                width: 44px;
                height: 44px;
                font-size: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: background 0.2s;
            }
            .hw-lightbox-close:hover {
                background: rgba(255, 255, 255, 0.4);
            }
        </style>
        
        <div>
            {{-- Page Header Banner --}}
            <div class="page-header mb-4 position-relative" style="background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);">
                <div class="d-flex align-items-center" style="display: flex; align-items: center; gap: 1.25rem; position: relative; z-index: 1;">
                    <div style="width: 58px; height: 58px; border-radius: 16px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.35); display: flex; align-items: center; justify-content: center; font-size: 26px; color: #ffffff; flex-shrink: 0;">
                        <x-student-icon name="notebook-pen" size="28" />
                    </div>
                    <div>
                        <h2 style="font-size: 1.75rem; font-weight: 800; color: #ffffff; margin-bottom: 0.25rem;">واجباتي ومهامي</h2>
                        <p style="font-size: 1rem; color: rgba(255, 255, 255, 0.9); margin-bottom: 0;">تسليم حل الواجبات بالصور ومتابعة تقييم وملاحظات المعلمين</p>
                    </div>
                </div>
            </div>

            @php
                $allCount = count($assignments);
                $submittedCount = count(array_filter($assignments->toArray(), fn($a) => $a['status'] === 'submitted' || $a['status'] === 'graded'));
                $gradedCount = count(array_filter($assignments->toArray(), fn($a) => $a['status'] === 'graded'));
                $overdueCount = count(array_filter($assignments->toArray(), fn($a) => $a['status'] === 'overdue'));
            @endphp

            {{-- Stats Grid --}}
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--text-color);">
                        <x-student-icon name="notebook-pen" size="24" />
                    </div>
                    <div><strong>{{ $allCount }}</strong><span>إجمالي الواجبات</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon mint">
                        <x-student-icon name="check-circle" size="24" />
                    </div>
                    <div><strong>{{ $submittedCount }}</strong><span>تم التسليم</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <x-student-icon name="medal" size="24" />
                    </div>
                    <div><strong>{{ $gradedCount }}</strong><span>تم التقييم</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon peach">
                        <x-student-icon name="clock-3" size="24" />
                    </div>
                    <div><strong>{{ $overdueCount }}</strong><span>متأخر</span></div>
                </div>
            </div>

            {{-- Section Title --}}
            <div class="section-title">
                <h2>قائمة الواجبات المتاحة</h2>
            </div>

            {{-- Stack of Assignments --}}
            <div class="stack">
                @forelse ($assignments as $a)
                    <article class="task-row">
                        <div class="task-row-icon {{ $a['status'] === 'graded' ? 'mint' : ($a['status'] === 'submitted' ? 'blue' : 'peach') }}">
                            <x-student-icon name="notebook-pen" size="23" />
                        </div>
                        <div class="task-row-info">
                            <strong style="font-size: 1.05rem;">{{ $a['title'] }}</strong>
                            <small style="color: #64748b;">
                                <span>📚 {{ $a['course'] }}</span>
                                <span style="margin: 0 0.35rem;">•</span>
                                <span>👨‍🏫 {{ $a['instructor_name'] }}</span>
                                <span style="margin: 0 0.35rem;">•</span>
                                <span>📅 التسليم: {{ $a['due'] }}</span>
                            </small>
                            <div class="task-badges" style="margin-top: 0.35rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <span class="status-pill status-{{ $a['status'] }}">
                                    {{ $a['status_label'] }}
                                </span>
                                
                                <span style="font-weight: 600; font-size: 0.8rem; color: #475569;">
                                    الدرجة من 10
                                </span>

                                @if($a['images_count'] > 0)
                                    <span style="background: #f1f5f9; color: #475569; padding: 0.15rem 0.55rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">
                                        📷 {{ $a['images_count'] }} {{ $a['images_count'] == 1 ? 'صورة' : 'صور' }}
                                    </span>
                                @endif

                                @if($a['score'] !== null)
                                    <span class="score-label" style="background: #dcfce7; color: #166534; padding: 0.2rem 0.65rem; border-radius: 99px; font-size: 0.82rem; font-weight: 800; border: 1px solid #86efac;">
                                        ⭐ درجتك: {{ $a['score'] }} / 10
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button 
                            type="button"
                            wire:click="openAssignment({{ $a['id'] }})"
                            class="btn btn-sm {{ $a['status'] === 'pending' ? 'btn-primary' : ($a['status'] === 'graded' ? 'btn-success' : 'btn-outline') }}"
                            style="min-width: 140px; padding: 0.6rem 1.1rem; border-radius: 10px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;"
                        >
                            @if($a['status'] === 'pending')
                                <span>📤 تسليم الواجب</span>
                            @elseif($a['status'] === 'graded')
                                <span>🌟 عرض التقييم ({{ $a['score'] }}/10)</span>
                            @else
                                <span>👁️ عرض التسليم</span>
                            @endif
                        </button>
                    </article>
                @empty
                    <div class="empty-state">
                        <x-student-icon name="notebook-pen" size="40" color="var(--text-muted)" />
                        <h3>لا توجد واجبات حالياً</h3>
                        <p>الواجبات والمهام الدراسية الجديدة ستظهر هنا فور نشرها من قبل المعلمين.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Homework Detail & Image Submission Modal --}}
        @if($activeAssignment)
            <div class="hw-modal-overlay" wire:click.self="closeAssignment">
                <div class="hw-modal-card">
                    {{-- Modal Header --}}
                    <div class="hw-modal-header">
                        <div>
                            <h3 style="margin: 0; font-size: 1.35rem; font-weight: 800;">{{ $activeAssignment->title }}</h3>
                            <small style="opacity: 0.9; font-size: 0.85rem;">{{ $activeAssignment->group?->course?->name ?? 'مقرر دراسي' }} - {{ $activeAssignment->group?->name }}</small>
                        </div>
                        <button 
                            type="button" 
                            wire:click="closeAssignment" 
                            style="background: rgba(255, 255, 255, 0.2); border: none; color: white; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px;"
                        >✕</button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="hw-modal-body">
                        {{-- Meta Bar --}}
                        <div class="hw-meta-bar">
                            <div class="hw-meta-item">
                                <span>👨‍🏫 المعلم:</span>
                                <strong>{{ $activeAssignment->instructor_name }}</strong>
                            </div>
                            <div class="hw-meta-item">
                                <span>📅 الموعد النهائي:</span>
                                <strong>{{ $activeAssignment->due_date ? $activeAssignment->due_date->format('Y-m-d') : 'مفتوح' }}</strong>
                            </div>
                            <div class="hw-meta-item">
                                <span>🎯 الدرجة القصوى:</span>
                                <strong>10 درجات</strong>
                            </div>
                            <div class="hw-meta-item">
                                <span>🏷️ الحالة:</span>
                                <strong style="color: #364FAC;">
                                    @if($activeSubmission?->isPublished())
                                        تم التقييم
                                    @elseif($activeSubmission?->isDraft())
                                        قيد المراجعة
                                    @elseif($activeSubmission?->submitted_at)
                                        تم التسليم
                                    @else
                                        لم يتم التسليم
                                    @endif
                                </strong>
                            </div>
                        </div>

                        {{-- Assignment Description & Attachment --}}
                        @if($activeAssignment->description)
                            <div class="hw-desc-box">
                                <strong style="display: block; margin-bottom: 0.4rem; color: #1e293b;">📝 تعليمات الواجب:</strong>
                                {!! nl2br(e($activeAssignment->description)) !!}
                            </div>
                        @endif

                        @if($activeAssignment->attachment_path)
                            <div style="background: #f1f5f9; padding: 0.75rem 1rem; border-radius: 10px; display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 0.9rem; color: #334155;">📎 مرفق الواجب من المعلم:</span>
                                <a 
                                    href="{{ Storage::url($activeAssignment->attachment_path) }}" 
                                    target="_blank"
                                    style="background: #364FAC; color: white; padding: 0.4rem 0.85rem; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 700;"
                                >تحميل المرفق 📥</a>
                            </div>
                        @endif

                        {{-- CASE 1: GRADED AND PUBLISHED --}}
                        @if($activeSubmission && $activeSubmission->isPublished())
                            <div class="hw-grade-card">
                                <div>
                                    <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.25rem;">تقييم المهندس النهائي</div>
                                    <div class="hw-grade-val">{{ $activeSubmission->score }} <span style="font-size: 1.25rem; color: rgba(255, 255, 255, 0.75); font-weight: normal;">/ {{ $activeAssignment->max_score ?? 10 }}</span></div>
                                </div>
                                <div style="border-inline-start: 1px solid rgba(255, 255, 255, 0.2); padding-inline-start: 1.5rem; text-align: start;">
                                    <div style="font-weight: 700; font-size: 1.05rem;">تم اعتماد النتيجة ✓</div>
                                    <small style="opacity: 0.85;">بتاريخ {{ $activeSubmission->graded_at?->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>

                            @if($activeSubmission->instructor_feedback)
                                <div class="hw-feedback-box">
                                    <strong style="display: block; font-weight: 800; margin-bottom: 0.4rem; font-size: 1rem;">💬 ملاحظات المهندس:</strong>
                                    <p style="margin: 0; white-space: pre-wrap;">{{ $activeSubmission->instructor_feedback }}</p>
                                </div>
                            @endif

                            {{-- Submitted Images Gallery --}}
                            <div>
                                <strong style="display: block; margin-bottom: 0.6rem; color: #1e293b;">
                                    📷 صور الحل المسلمة ({{ $activeSubmission->image_count }} صور):
                                </strong>
                                <div class="hw-images-grid">
                                    @foreach($activeSubmission->images_list as $imgIdx => $imgPath)
                                        <div class="hw-image-thumb-wrap" wire:click="openLightbox({{ $imgIdx }})">
                                            <img 
                                                src="{{ route('assignments.submissions.image', ['submission' => $activeSubmission->id, 'index' => $imgIdx]) }}" 
                                                class="hw-image-thumb" 
                                                alt="صورة الواجب {{ $imgIdx + 1 }}"
                                                loading="lazy"
                                            >
                                        </div>
                                    @endforeach
                                </div>
                                <small style="color: #64748b; margin-top: 0.4rem; display: block;">💡 اضغط على أي صورة لتكبيرها واستعراضها بالحجم الكامل.</small>
                            </div>

                            @if($activeSubmission->submission_notes)
                                <div style="background: #f8fafc; padding: 0.85rem; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.9rem;">
                                    <strong>تعليقك أثناء التسليم:</strong>
                                    <p style="margin: 0.25rem 0 0; color: #475569;">{{ $activeSubmission->submission_notes }}</p>
                                </div>
                            @endif

                        {{-- CASE 2: SUBMITTED (PENDING OR DRAFT REVIEW) --}}
                        @elseif($activeSubmission && $activeSubmission->submitted_at)
                            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-inline-start: 4px solid #364FAC; padding: 1rem 1.25rem; border-radius: 12px; color: #1e3a8a;">
                                <div style="font-weight: 800; font-size: 1rem; margin-bottom: 0.25rem;">
                                    ✓ تم استلام واجبك بنجاح!
                                </div>
                                <p style="margin: 0; font-size: 0.92rem; color: #3b82f6;">
                                    الواجب قيد المراجعة والتدقيق من قبل المهندس. ستظهر درجتك من 10 وملاحظات التصحيح هنا فور اعتمادها ونشرها.
                                </p>
                            </div>

                            <div>
                                <strong style="display: block; margin-bottom: 0.6rem; color: #1e293b;">
                                    📷 صور الحل المسلمة ({{ $activeSubmission->image_count }} صور):
                                </strong>
                                <div class="hw-images-grid">
                                    @foreach($activeSubmission->images_list as $imgIdx => $imgPath)
                                        <div class="hw-image-thumb-wrap" wire:click="openLightbox({{ $imgIdx }})">
                                            <img 
                                                src="{{ route('assignments.submissions.image', ['submission' => $activeSubmission->id, 'index' => $imgIdx]) }}" 
                                                class="hw-image-thumb" 
                                                alt="صورة الواجب {{ $imgIdx + 1 }}"
                                                loading="lazy"
                                            >
                                        </div>
                                    @endforeach
                                </div>
                                <small style="color: #64748b; margin-top: 0.4rem; display: block;">💡 يمكنك النقر على أي صورة لمعاينتها بالحجم الكامل.</small>
                            </div>

                            @if($activeSubmission->submission_notes)
                                <div style="background: #f8fafc; padding: 0.85rem; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.9rem;">
                                    <strong>ملاحظاتك المسجلة مع التسليم:</strong>
                                    <p style="margin: 0.25rem 0 0; color: #475569;">{{ $activeSubmission->submission_notes }}</p>
                                </div>
                            @endif

                        {{-- CASE 3: NOT SUBMITTED YET (UPLOAD FORM) --}}
                        @else
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <strong style="display: block; margin-bottom: 0.5rem; color: #1e293b; font-size: 1rem;">
                                        📸 رفع صور حل الواجب (من 1 إلى 5 صور):
                                    </strong>
                                    
                                    {{-- Action Buttons: Camera + Gallery --}}
                                    <div class="hw-upload-actions">
                                        <label for="hwCameraInput" class="hw-btn-upload">
                                            <span style="font-size: 1.3rem;">📷</span>
                                            <span>التقاط صورة بالكاميرا</span>
                                            <input 
                                                type="file" 
                                                id="hwCameraInput" 
                                                wire:model="newImages" 
                                                accept="image/*" 
                                                capture="environment" 
                                                multiple 
                                                style="display: none;"
                                            >
                                        </label>

                                        <label for="hwGalleryInput" class="hw-btn-upload">
                                            <span style="font-size: 1.3rem;">🖼️</span>
                                            <span>اختيار صور من المعرض</span>
                                            <input 
                                                type="file" 
                                                id="hwGalleryInput" 
                                                wire:model="newImages" 
                                                accept="image/jpeg,image/png,image/webp,image/jpg" 
                                                multiple 
                                                style="display: none;"
                                            >
                                        </label>
                                    </div>

                                    {{-- Upload Loading State --}}
                                    <div wire:loading wire:target="newImages" style="margin-top: 0.75rem; text-align: center; color: #364FAC; font-weight: 700; font-size: 0.9rem;">
                                        ⏳ جارٍ قراءة ومعالجة الصور المحددة... يرجى الانتظار
                                    </div>

                                    {{-- Error Display --}}
                                    @error('newImages')
                                        <span style="color: #ef4444; font-size: 0.85rem; display: block; margin-top: 0.5rem; font-weight: 700;">{{ $message }}</span>
                                    @enderror
                                    @error('newImages.*')
                                        <span style="color: #ef4444; font-size: 0.85rem; display: block; margin-top: 0.5rem; font-weight: 700;">{{ $message }}</span>
                                    @enderror

                                    <small style="color: #64748b; display: block; margin-top: 0.5rem; font-size: 0.82rem;">
                                        ℹ️ مسموح بحد أقصى 5 صور بحجم حتى 5 ميجابايت لكل صورة (JPG, PNG, WEBP). احرص على وضوح خط اليد والإضاءة.
                                    </small>
                                </div>

                                {{-- Image Previews Before Submit --}}
                                @if(!empty($newImages))
                                    <div style="background: #f8fafc; padding: 1rem; border-radius: 14px; border: 1px solid #e2e8f0;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <strong style="font-size: 0.92rem; color: #1e293b;">الصور المحددة قبل التسليم:</strong>
                                            <span style="background: #364FAC; color: white; padding: 0.15rem 0.55rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700;">
                                                {{ count($newImages) }} من 5 صور
                                            </span>
                                        </div>

                                        <div class="hw-images-grid">
                                            @foreach($newImages as $tempIdx => $tempImg)
                                                @if(method_exists($tempImg, 'temporaryUrl'))
                                                    <div class="hw-image-thumb-wrap">
                                                        <img src="{{ $tempImg->temporaryUrl() }}" class="hw-image-thumb" alt="معاينة الصورة">
                                                        <button 
                                                            type="button" 
                                                            wire:click="removeSelectedImage({{ $tempIdx }})" 
                                                            class="hw-thumb-remove" 
                                                            title="حذف الصورة"
                                                        >×</button>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Student Notes Input --}}
                                <div>
                                    <label for="submissionNotesInput" style="display: block; font-weight: 700; margin-bottom: 0.4rem; font-size: 0.9rem; color: #334155;">
                                        ملاحظات إضافية للمهندس (اختياري):
                                    </label>
                                    <textarea 
                                        id="submissionNotesInput"
                                        wire:model="submissionNotes" 
                                        rows="3" 
                                        placeholder="اكتب هنا أي استفسار أو توضيح يخص الحل المرفق..." 
                                        style="width: 100%; border-radius: 12px; border: 1px solid #cbd5e1; padding: 0.75rem 1rem; font-size: 0.92rem; font-family: inherit; resize: vertical;"
                                    ></textarea>
                                </div>

                                {{-- Submit Actions --}}
                                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 0.5rem;">
                                    <button 
                                        type="button" 
                                        wire:click="closeAssignment" 
                                        class="btn btn-outline"
                                        style="padding: 0.65rem 1.25rem; border-radius: 10px; font-weight: 600;"
                                    >إلغاء</button>

                                    <button 
                                        type="button" 
                                        wire:click="submitHomework" 
                                        wire:loading.attr="disabled"
                                        class="btn btn-primary"
                                        style="background: #16a34a; border-color: #16a34a; color: white; padding: 0.65rem 1.75rem; border-radius: 10px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;"
                                    >
                                        <span wire:loading.remove wire:target="submitHomework">🚀 تسليم الواجب الآن</span>
                                        <span wire:loading wire:target="submitHomework">⏳ جارٍ الحفظ والتسليم...</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Lightbox Modal --}}
        @if($lightboxIndex !== null && $activeSubmission)
            @php
                $imgList = $activeSubmission->images_list;
                $currentImg = $imgList[$lightboxIndex] ?? null;
            @endphp
            @if($currentImg)
                <div class="hw-lightbox" wire:click.self="closeLightbox">
                    <button type="button" class="hw-lightbox-close" wire:click="closeLightbox">✕</button>

                    <img 
                        src="{{ route('assignments.submissions.image', ['submission' => $activeSubmission->id, 'index' => $lightboxIndex]) }}" 
                        class="hw-lightbox-img" 
                        alt="معاينة الصورة بالحجم الكامل"
                    >

                    <div style="display: flex; gap: 1rem; margin-top: 1rem; align-items: center;">
                        @if($lightboxIndex > 0)
                            <button 
                                type="button" 
                                wire:click="openLightbox({{ $lightboxIndex - 1 }})" 
                                style="background: rgba(255, 255, 255, 0.2); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; cursor: pointer;"
                            >السابقة ◀</button>
                        @endif

                        <span style="color: white; font-weight: 700; font-size: 0.95rem;">
                            {{ $lightboxIndex + 1 }} من {{ count($imgList) }}
                        </span>

                        @if($lightboxIndex < count($imgList) - 1)
                            <button 
                                type="button" 
                                wire:click="openLightbox({{ $lightboxIndex + 1 }})" 
                                style="background: rgba(255, 255, 255, 0.2); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; cursor: pointer;"
                            >▶ التالية</button>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
