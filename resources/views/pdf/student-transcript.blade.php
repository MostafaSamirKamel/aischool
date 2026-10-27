<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>كشف درجات - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2563eb;
            font-size: 28px;
            margin: 10px 0;
        }
        .header h2 {
            color: #666;
            font-size: 18px;
            margin: 5px 0;
        }
        .student-info {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .student-info table {
            width: 100%;
        }
        .student-info td {
            padding: 5px 10px;
        }
        .student-info strong {
            color: #1f2937;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 5px;
        }
        .stat-box h3 {
            font-size: 24px;
            color: #2563eb;
            margin: 0;
        }
        .stat-box p {
            font-size: 11px;
            color: #666;
            margin: 5px 0 0 0;
        }
        .course-section {
            margin-bottom: 25px;
        }
        .course-title {
            background: #2563eb;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background: #f3f4f6;
            color: #1f2937;
            padding: 10px;
            text-align: right;
            border: 1px solid #d1d5db;
            font-weight: bold;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .grade-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
        }
        .grade-A { background: #d1fae5; color: #065f46; }
        .grade-B { background: #dbeafe; color: #1e40af; }
        .grade-C { background: #fef3c7; color: #92400e; }
        .grade-D { background: #fee2e2; color: #991b1b; }
        .grade-F { background: #fca5a5; color: #7f1d1d; }
        .status-pass { color: #059669; font-weight: bold; }
        .status-fail { color: #dc2626; font-weight: bold; }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>كشف درجات الطالب</h1>
        <h2>Academic Transcript</h2>
        <p>تاريخ الإصدار: {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td width="50%"><strong>اسم الطالب:</strong> {{ $student->first_name }} {{ $student->last_name }}</td>
                <td width="50%"><strong>البريد الإلكتروني:</strong> {{ $student->email }}</td>
            </tr>
            <tr>
                <td><strong>الهاتف:</strong> {{ $student->phone ?? 'غير متوفر' }}</td>
                <td><strong>تاريخ الميلاد:</strong> {{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'غير متوفر' }}</td>
            </tr>
        </table>
    </div>

    @if($stats && $stats['total_exams'] > 0)
        <div class="stats-grid">
            <div class="stat-box">
                <h3>{{ $stats['total_exams'] }}</h3>
                <p>إجمالي الامتحانات</p>
            </div>
            <div class="stat-box">
                <h3>{{ $stats['average_percentage'] }}%</h3>
                <p>متوسط النسبة</p>
            </div>
            <div class="stat-box">
                <h3>{{ $stats['passing_count'] }}</h3>
                <p>الامتحانات الناجحة</p>
            </div>
            <div class="stat-box">
                <h3>{{ $stats['highest_grade'] }}%</h3>
                <p>أعلى درجة</p>
            </div>
        </div>

        @foreach($grades as $courseName => $courseGrades)
            <div class="course-section">
                <div class="course-title">{{ $courseName }}</div>
                
                <table>
                    <thead>
                        <tr>
                            <th width="25%">الامتحان</th>
                            <th width="10%">النوع</th>
                            <th width="15%">الدرجة</th>
                            <th width="10%">النسبة</th>
                            <th width="10%">التقدير</th>
                            <th width="10%">الحالة</th>
                            <th width="20%">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courseGrades as $grade)
                            <tr>
                                <td>{{ $grade->exam->title }}</td>
                                <td>
                                    @if($grade->exam->type === 'quiz') كويز
                                    @elseif($grade->exam->type === 'midterm') ميدتيرم
                                    @elseif($grade->exam->type === 'final') فاينال
                                    @else {{ $grade->exam->type }}
                                    @endif
                                </td>
                                <td>{{ $grade->marks_obtained }} / {{ $grade->exam->total_marks }}</td>
                                <td>{{ $grade->percentage }}%</td>
                                <td>
                                    <span class="grade-badge grade-{{ substr($grade->grade, 0, 1) }}">
                                        {{ $grade->grade }}
                                    </span>
                                </td>
                                <td>
                                    @if($grade->marks_obtained >= $grade->exam->passing_marks)
                                        <span class="status-pass">✓ ناجح</span>
                                    @else
                                        <span class="status-fail">✗ راسب</span>
                                    @endif
                                </td>
                                <td>{{ $grade->graded_at ? $grade->graded_at->format('d/m/Y') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <p style="text-align: center; padding: 40px; color: #9ca3af;">لا توجد درجات مسجلة لهذا الطالب</p>
    @endif

    <div class="footer">
        <p>هذا المستند تم إنشاؤه تلقائياً بواسطة نظام إدارة المدرسة</p>
        <p>© {{ now()->year }} جميع الحقوق محفوظة</p>
    </div>
</body>
</html>
