<div class="gs-modal">
    <style>
        .gs-modal {
            direction: rtl;
            font-family: "Cairo", "Tajawal", "Segoe UI", Tahoma, Arial, sans-serif;
            color: #111827;
        }
        .gs-hero {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 28px;
            background: linear-gradient(135deg, #0f766e 0%, #0ea5e9 55%, #22c55e 100%);
            box-shadow: 0 20px 40px rgba(2, 6, 23, 0.15);
        }
        .gs-hero h2 {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 6px 0;
        }
        .gs-hero p {
            margin: 0;
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
        }
        .gs-hero::before,
        .gs-hero::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(30px);
            opacity: 0.25;
        }
        .gs-hero::before {
            width: 140px;
            height: 140px;
            top: -40px;
            right: -20px;
            background: #ffffff;
        }
        .gs-hero::after {
            width: 200px;
            height: 200px;
            bottom: -70px;
            left: -40px;
            background: #ffffff;
        }
        .gs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 130px), 1fr));
            gap: 14px;
            margin-top: 18px;
        }
        .gs-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 16px rgba(2, 6, 23, 0.06);
            position: relative;
        }
        .gs-card strong {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .gs-card span {
            font-size: 18px;
            font-weight: 800;
            color: #111827;
            display: block;
        }
        .gs-card--count {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            border: none;
            color: #ffffff;
        }
        .gs-card--count strong,
        .gs-card--count span {
            color: #ffffff;
        }
        .gs-table {
            margin-top: 18px;
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 12px 24px rgba(2, 6, 23, 0.08);
        }
        .gs-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .gs-table thead th {
            background: #f8fafc;
            color: #475569;
            text-align: right;
            font-size: 12px;
            font-weight: 800;
            padding: 14px 16px;
            letter-spacing: 0.02em;
        }
        .gs-table tbody td {
            padding: 14px 16px;
            border-top: 1px solid #f1f5f9;
            font-size: 13px;
            color: #1f2937;
        }
        .gs-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .gs-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            background: #eef2ff;
            color: #1e3a8a;
            border: 1px solid #c7d2fe;
        }
        .gs-avatar {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            color: #ffffff;
        }
        .gs-badge {
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
        }
        .gs-badge--active { background: #dcfce7; color: #166534; }
        .gs-badge--pending { background: #fef9c3; color: #854d0e; }
        .gs-badge--completed { background: #dbeafe; color: #1e3a8a; }
        .gs-badge--cancelled { background: #fee2e2; color: #991b1b; }
        .gs-badge--other { background: #e5e7eb; color: #374151; }
        .gs-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 130px), 1fr));
            gap: 14px;
            margin-top: 18px;
        }
        .gs-stat {
            border-radius: 16px;
            padding: 18px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }
        .gs-stat strong {
            font-size: 12px;
            opacity: 0.9;
            display: block;
            margin-bottom: 6px;
        }
        .gs-stat span {
            font-size: 26px;
            font-weight: 800;
        }
        .gs-stat--success { background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); }
        .gs-stat--warning { background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); }
        .gs-stat--info { background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%); }
        .gs-stat--total { background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%); }
        @media (max-width: 1024px) {
            .gs-grid,
            .gs-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .gs-grid,
            .gs-stats {
                grid-template-columns: 1fr;
            }
            .gs-table thead {
                display: none;
            }
            .gs-table tbody td {
                display: block;
                padding: 10px 16px;
            }
            .gs-table tbody tr {
                border-bottom: 1px solid #e5e7eb;
            }
        }
    </style>
    @if($enrollmentCourses->count() > 0)
        <div class="gs-hero">
            <h2>قائمة الطلاب</h2>
            <p>عرض تفاصيل طلاب المجموعة بشكل منظم وواضح</p>
        </div>

        <div class="gs-grid">
            <div class="gs-card">
                <strong>المجموعة</strong>
                <span>{{ $group->name }}</span>
            </div>
            <div class="gs-card">
                <strong>الكورس</strong>
                <span>{{ $group->course->name ?? 'غير محدد' }}</span>
            </div>
            <div class="gs-card">
                <strong>المدرس</strong>
                <span>{{ $group->instructor?->user?->name ?? 'غير محدد' }}</span>
            </div>
            <div class="gs-card gs-card--count">
                <strong>عدد الطلاب</strong>
                <span>{{ $enrollmentCourses->count() }}</span>
            </div>
        </div>

        <div class="gs-table">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>كود الطالب</th>
                            <th>اسم الطالب</th>
                            <th>البريد الإلكتروني</th>
                            <th>رقم الهاتف</th>
                            <th>تاريخ التسجيل</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollmentCourses as $index => $enrollmentCourse)
                            @php
                                $enrollment = $enrollmentCourse->enrollment;
                                // Skip if enrollment or student is null
                                if (!$enrollment || !$enrollment->student) {
                                    continue;
                                }
                                $student = $enrollment->student;
                                $statusClass = match ($enrollmentCourse->status) {
                                    'active' => 'gs-badge--active',
                                    'inactive' => 'gs-badge--pending',
                                    'completed' => 'gs-badge--completed',
                                    default => 'gs-badge--other',
                                };
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="gs-chip">{{ $student->student_code }}</span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        @if($student->user->profile_photo)
                                            <img class="gs-avatar" src="{{ Storage::url($student->user->profile_photo) }}" alt="{{ $student->user->name }}">
                                        @else
                                            <div class="gs-avatar">{{ mb_substr($student->user->name, 0, 1) }}</div>
                                        @endif
                                        <div>{{ $student->user->name }}</div>
                                    </div>
                                </td>
                                <td>{{ $student->user->email }}</td>
                                <td>{{ $student->phone ?? $student->user->phone ?? 'غير محدد' }}</td>
                                <td>{{ $enrollment->enrollment_date?->format('d/m/Y') ?? 'غير محدد' }}</td>
                                <td>
                                    <span class="gs-badge {{ $statusClass }}">
                                        {{ match ($enrollmentCourse->status) {
                                            'inactive' => 'غير نشط',
                                            'active' => 'نشط',
                                            'completed' => 'مكتمل',
                                            default => $enrollmentCourse->status,
                                        } }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="gs-stats">
            <div class="gs-stat gs-stat--success">
                <strong>النشطون</strong>
                <span>{{ $enrollmentCourses->where('status', 'active')->count() }}</span>
            </div>
            <div class="gs-stat gs-stat--warning">
                <strong>غير نشط</strong>
                <span>{{ $enrollmentCourses->where('status', 'inactive')->count() }}</span>
            </div>
            <div class="gs-stat gs-stat--info">
                <strong>مكتملون</strong>
                <span>{{ $enrollmentCourses->where('status', 'completed')->count() }}</span>
            </div>
            <div class="gs-stat gs-stat--total">
                <strong>إجمالي الطلاب</strong>
                <span>{{ $enrollmentCourses->count() }}</span>
            </div>
        </div>
    @else
        <div style="text-align: center; padding: 60px 0;">
            <div style="width: 96px; height: 96px; margin: 0 auto 16px; border-radius: 999px; background: #f3f4f6;"></div>
            <h3 style="font-size: 20px; font-weight: 800; margin: 0 0 6px;">لا يوجد طلاب</h3>
            <p style="color: #6b7280; margin: 0;">لا يوجد طلاب مسجلين في هذه المجموعة بعد</p>
        </div>
    @endif
</div>
