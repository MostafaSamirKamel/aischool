<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التقرير المالي للكورسات والمجموعات</title>
    <style>
        body {
            font-family: 'XBRiyaz', sans-serif;
            direction: rtl;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>التقرير المالي للكورسات والمجموعات</h1>
        <p>تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</p>
        <p>إجمالي الإيرادات: {{ number_format($reportData['totalAllCourses'], 2) }}</p>
    </div>

    @foreach($reportData['courses'] as $courseData)
        <div style="margin-bottom: 30px;">
            <div style="background-color: #e5e7eb; padding: 10px; font-weight: bold;">
                كورس: {{ $courseData['course']->name }} | إجمالي الإيرادات: {{ number_format($courseData['total_course_revenue'], 2) }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>المجموعة</th>
                        <th>المدرس</th>
                        <th>عدد الطلاب</th>
                        <th>المبلغ المحصل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courseData['groups'] as $groupData)
                        <tr>
                            <td>{{ $groupData['group']->name }}</td>
                            <td>{{ $groupData['group']->instructor?->user?->name ?? 'غير محدد' }}</td>
                            <td>{{ $groupData['group']->enrollmentCourses->count() }}</td>
                            <td>{{ number_format($groupData['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">لا توجد مجموعات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
