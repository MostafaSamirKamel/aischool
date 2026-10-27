<x-filament-panels::page>
    @php
        $coursesList = collect($courses ?? []);
        $resourcesList = collect($resources ?? []);
    @endphp

    <style>
        .slr-page {
            direction: rtl;
            font-family: "Cairo", Tahoma, Arial, sans-serif;
            color: #0f172a;
            display: grid;
            gap: 20px;
        }

        .slr-title {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
        }

        .slr-subtitle {
            margin: 0;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
        }

        .slr-empty {
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 14px;
            padding: 36px 18px;
            text-align: center;
            color: #64748b;
            font-size: 15px;
            font-weight: 600;
        }

        .slr-courses {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .slr-course-btn {
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
            text-align: right;
            width: 100%;
            cursor: pointer;
            transition: all .2s ease;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
        }

        .slr-course-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.10);
        }

        .slr-course-btn--active {
            border-color: #364FAC;
            box-shadow: 0 0 0 3px rgba(54, 79, 172, 0.16);
            background: #f0f4ff;
        }

        .slr-course-cover {
            height: 120px;
            background: linear-gradient(135deg, #364FAC, #293C85);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 30px;
            font-weight: 800;
        }

        .slr-course-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slr-course-body {
            padding: 12px;
            display: grid;
            gap: 8px;
        }

        .slr-course-name {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.4;
            word-break: break-word;
        }

        .slr-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            width: fit-content;
        }

        .slr-badge--active { background: #dcfce7; color: #166534; }
        .slr-badge--completed { background: #dbeafe; color: #1d4ed8; }
        .slr-badge--default { background: #e2e8f0; color: #334155; }

        .slr-course-desc {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
            max-height: 42px;
            overflow: hidden;
        }

        .slr-resources {
            border-top: 2px solid #e2e8f0;
            padding-top: 20px;
            display: grid;
            gap: 12px;
        }

        .slr-resource {
            display: grid;
            grid-template-columns: 56px 1fr auto;
            gap: 12px;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            background: #ffffff;
            padding: 12px;
            align-items: start;
        }

        .slr-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            background: #f1f5f9;
        }

        .slr-resource-title {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.5;
        }

        .slr-resource-desc {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .slr-meta {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .slr-meta span {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            padding: 4px 9px;
            font-size: 12px;
            font-weight: 700;
        }

        .slr-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .slr-btn {
            border: 0;
            text-decoration: none;
            border-radius: 10px;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .slr-btn--view { background: #eef2ff; color: #364FAC; font-weight: 700; }
        .slr-btn--download { background: #eaf7e6; color: #1F7A18; font-weight: 700; }
        .slr-btn--link { background: #f0fdf4; color: #15803d; font-weight: 700; }

        @media (max-width: 1100px) {
            .slr-courses { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 780px) {
            .slr-courses { grid-template-columns: 1fr; }
            .slr-resource { grid-template-columns: 1fr; }
            .slr-actions { justify-content: flex-start; }
        }
    </style>

    <div class="slr-page">
        <h2 class="slr-title">المصادر التعليمية</h2>
        <p class="slr-subtitle">اختر الكورس لعرض الملفات والمواد التعليمية الخاصة به</p>

        @if($coursesList->isEmpty())
            <div class="slr-empty">لا توجد كورسات متاحة حالياً</div>
        @else
            <section class="slr-courses">
                @foreach($coursesList as $course)
                    @php
                        $normalizedStatus = strtolower(trim((string) ($course->status ?? '')));
                        $statusText = match($normalizedStatus) {
                            'active' => 'نشط',
                            'completed' => 'مكتمل',
                            default => 'غير محدد',
                        };
                        $statusClass = match($normalizedStatus) {
                            'active' => 'slr-badge--active',
                            'completed' => 'slr-badge--completed',
                            default => 'slr-badge--default',
                        };
                        $isSelected = $selectedCourseId === $course->id;
                    @endphp

                    <button
                        wire:click="selectCourse({{ $course->id }})"
                        class="slr-course-btn {{ $isSelected ? 'slr-course-btn--active' : '' }}"
                    >
                        <div class="slr-course-cover">
                            @if(!empty($course->image))
                                <img src="{{ asset($course->image) }}" alt="{{ $course->name }}">
                            @else
                                📘
                            @endif
                        </div>

                        <div class="slr-course-body">
                            <h3 class="slr-course-name">{{ $course->name ?? '-' }}</h3>
                            <span class="slr-badge {{ $statusClass }}">{{ $statusText }}</span>
                            @if(!empty($course->description))
                                <p class="slr-course-desc">{{ strip_tags((string) $course->description) }}</p>
                            @endif
                        </div>
                    </button>
                @endforeach
            </section>
        @endif

        @if($selectedCourseId)
            <section class="slr-resources">
                <h3 class="slr-title" style="font-size:22px;">
                    مصادر {{ $this->getSelectedCourseName() }}
                </h3>
                <p class="slr-subtitle">الحالة: {{ $this->getSelectedCourseStatus() }}</p>

                @if($resources->isEmpty())
                    <div class="slr-empty">لا توجد مصادر تعليمية متاحة لهذا الكورس</div>
                @else
                    @foreach($resources as $resource)
                        <article class="slr-resource">
                            <div class="slr-icon">
                                @php
                                    $icon = match($resource->type) {
                                        'pdf' => 'PDF',
                                        'video' => 'VID',
                                        'image' => 'IMG',
                                        'audio' => 'AUD',
                                        'link' => 'URL',
                                        default => 'DOC',
                                    };
                                @endphp
                                {{ $icon }}
                            </div>

                            <div>
                                <h4 class="slr-resource-title">{{ $resource->title }}</h4>
                                @if(!empty($resource->description))
                                    <p class="slr-resource-desc">{{ strip_tags((string) $resource->description) }}</p>
                                @endif

                                <div class="slr-meta">
                                    <span>{{ $this->getResourceTypeLabel($resource->type) }}</span>
                                    @if(!empty($resource->session_number))
                                        <span>محاضرة #{{ $resource->session_number }}</span>
                                    @endif
                                    @if($resource->sessionSchedule && $resource->sessionSchedule->date)
                                        <span>{{ $resource->sessionSchedule->date->format('Y-m-d') }}</span>
                                    @endif
                                    <span>المشاهدات {{ (int) ($resource->view_count ?? 0) }}</span>
                                </div>
                            </div>

                            <div class="slr-actions">
                                @if($resource->type === 'link' && $resource->external_link)
                                    <a class="slr-btn slr-btn--link" href="{{ $resource->external_link }}" target="_blank">فتح الرابط</a>
                                @elseif($resource->file_path && $resource->is_downloadable)
                                    <a class="slr-btn slr-btn--download" href="{{ route('student.resources.download', $resource->id) }}">تحميل الملف</a>
                                    <a class="slr-btn slr-btn--view" href="{{ route('student.resources.view', $resource->id) }}" target="_blank">عرض الملف</a>
                                @elseif($resource->file_path)
                                    <a class="slr-btn slr-btn--view" href="{{ route('student.resources.view', $resource->id) }}" target="_blank">عرض الملف</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                @endif
            </section>
        @endif
    </div>
</x-filament-panels::page>
