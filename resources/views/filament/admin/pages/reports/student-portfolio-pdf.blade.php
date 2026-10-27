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
            margin: 16px 0 8px;
        }

        .cards {
            margin-bottom: 10px;
        }

        .card {
            display: inline-block;
            width: 18%;
            min-height: 52px;
            margin-left: 1%;
            margin-bottom: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px;
            vertical-align: top;
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
        'title' => 'بورتفوليو الطالب - ' . ($portfolio['student_name'] ?? ''),
        'subtitle' => 'تقرير شامل للطالب',
        'period' => 'الفترة: ' . ($dateFrom ?? '-') . ' إلى ' . ($dateTo ?? '-'),
    ])

    <div class="cards">
        <div class="card">المجموعات: {{ $portfolio['overview']['groups_count'] ?? 0 }}</div>
        <div class="card">الحضور: {{ $portfolio['overview']['attendance_rate'] ?? 0 }}%</div>
        <div class="card">الامتحانات: {{ $portfolio['overview']['exams_count'] ?? 0 }}</div>
        <div class="card">الواجبات: {{ $portfolio['overview']['assignments_count'] ?? 0 }}</div>
        <div class="card">إجمالي المدفوع: {{ number_format((float) ($portfolio['overview']['total_paid'] ?? 0), 2) }}</div>
    </div>

    <h2>المجموعات والكورسات</h2>
    <table>
        <thead>
            <tr>
                <th>المجموعة</th>
                <th>الكورس</th>
                <th>الحالة</th>
                <th>التقدم</th>
            </tr>
        </thead>
        <tbody>
            @forelse($portfolio['groups'] as $group)
                <tr>
                    <td>{{ $group['group_name'] ?? '' }}</td>
                    <td>{{ $group['course_name'] ?? '' }}</td>
                    <td>{{ $group['status'] ?? '' }}</td>
                    <td>{{ $group['progress'] ?? 0 }}%</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد بيانات</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>المدفوعات</h2>
    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>المبلغ</th>
                <th>الحالة</th>
                <th>الطريقة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($portfolio['payments'] as $payment)
                <tr>
                    <td>{{ $payment['date'] ?? '' }}</td>
                    <td>{{ $payment['amount'] ?? '' }}</td>
                    <td>{{ $payment['status'] ?? '' }}</td>
                    <td>{{ $payment['method'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد بيانات</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>الحضور</h2>
    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>المجموعة</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($portfolio['attendance'] as $attendance)
                <tr>
                    <td>{{ $attendance['date'] ?? '' }}</td>
                    <td>{{ $attendance['group'] ?? '' }}</td>
                    <td>{{ $attendance['status'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="3">لا توجد بيانات</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>الامتحانات</h2>
    <table>
        <thead>
            <tr>
                <th>الامتحان</th>
                <th>الدرجة</th>
                <th>النسبة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($portfolio['exams'] as $exam)
                <tr>
                    <td>{{ $exam['exam'] ?? '' }}</td>
                    <td>{{ $exam['score'] ?? 0 }} / {{ $exam['total'] ?? 0 }}</td>
                    <td>{{ $exam['percentage'] ?? 0 }}%</td>
                </tr>
            @empty
                <tr><td colspan="3">لا توجد بيانات</td></tr>
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
            @forelse($portfolio['assignments'] as $assignment)
                <tr>
                    <td>{{ $assignment['assignment'] ?? '' }}</td>
                    <td>{{ $assignment['submitted_at'] ?? '' }}</td>
                    <td>{{ $assignment['status'] ?? '' }}</td>
                    <td>{{ $assignment['score'] ?? '-' }} / {{ $assignment['max_score'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد بيانات</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
