<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .pdf-header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .pdf-header-main {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .pdf-logo {
            height: 48px;
            max-width: 120px;
            object-fit: contain;
            display: table-cell;
            vertical-align: middle;
        }

        .pdf-brand-block {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .pdf-brand {
            font-size: 12px;
            color: #6b7280;
        }

        .pdf-title {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }

        .pdf-subtitle {
            font-size: 12px;
            color: #4b5563;
        }

        .pdf-meta {
            font-size: 11px;
            color: #6b7280;
            display: table;
            width: 100%;
        }

        .pdf-period {
            display: table-cell;
            text-align: left;
        }

        h2 {
            font-size: 15px;
            margin: 14px 0 8px;
        }

        .cards {
            margin-bottom: 10px;
        }

        .card {
            display: inline-block;
            width: 23%;
            margin-left: 1%;
            margin-bottom: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px;
            vertical-align: top;
            min-height: 45px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: right;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    @include('filament.pdf.partials.header', [
        'title' => 'تقرير الطالب - ' . ($studentName ?? ''),
        'subtitle' => 'ملخص الأداء الأكاديمي',
    ])

    <div class="cards">
        <div class="card">متوسط الدرجات: {{ $stats['avgScore'] ?? 0 }}%</div>
        <div class="card">نسبة الحضور: {{ $stats['attendanceRate'] ?? 0 }}%</div>
        <div class="card">الامتحانات: {{ $stats['completedExams'] ?? 0 }}</div>
        <div class="card">الواجبات: {{ $stats['submittedAssignments'] ?? 0 }}</div>
    </div>

    <h2>الأداء حسب المجموعة</h2>
    <table>
        <thead>
            <tr>
                <th>المجموعة</th>
                <th>متوسط الدرجات</th>
                <th>نسبة الحضور</th>
                <th>عدد الامتحانات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($performanceByGroup as $group)
                <tr>
                    <td>{{ $group['group_name'] ?? '' }}</td>
                    <td>{{ $group['avg_score'] ?? 0 }}%</td>
                    <td>{{ $group['attendance_rate'] ?? 0 }}%</td>
                    <td>{{ $group['exams_count'] ?? 0 }}</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد بيانات</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>درجات الامتحانات</h2>
    <table>
        <thead>
            <tr>
                <th>الامتحان</th>
                <th>التاريخ</th>
                <th>الدرجة</th>
                <th>النسبة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($examScores as $exam)
                <tr>
                    <td>{{ $exam['exam_title'] ?? '' }}</td>
                    <td>{{ $exam['date'] ?? '' }}</td>
                    <td>{{ $exam['score'] ?? 0 }}/{{ $exam['total_marks'] ?? 0 }}</td>
                    <td>{{ $exam['percentage'] ?? 0 }}%</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد بيانات</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>الواجبات</h2>
    <table>
        <thead>
            <tr>
                <th>الواجب</th>
                <th>التاريخ</th>
                <th>الحالة</th>
                <th>الدرجة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignmentsProgress as $assignment)
                <tr>
                    <td>{{ $assignment['assignment_title'] ?? '' }}</td>
                    <td>{{ $assignment['submitted_at'] ?? '' }}</td>
                    <td>{{ $assignment['status'] ?? '' }}</td>
                    <td>{{ $assignment['score'] ?? '-' }}/{{ $assignment['max_score'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد بيانات</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>الحضور الشهري</h2>
    <table>
        <thead>
            <tr>
                <th>الشهر</th>
                <th>حاضر</th>
                <th>غائب</th>
                <th>متأخر</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendanceByMonth as $month)
                <tr>
                    <td>{{ $month['month'] ?? '' }}</td>
                    <td>{{ $month['present'] ?? 0 }}</td>
                    <td>{{ $month['absent'] ?? 0 }}</td>
                    <td>{{ $month['late'] ?? 0 }}</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد بيانات</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
