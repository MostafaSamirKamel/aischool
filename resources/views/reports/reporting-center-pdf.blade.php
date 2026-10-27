<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Tajawal', 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11px;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .school-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0 0 4px 0;
        }
        .report-subtitle {
            font-size: 14px;
            color: #475569;
            margin: 0;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-row {
            margin-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 6px;
            border: 1px solid #0f172a;
            text-align: center;
        }
        td {
            padding: 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .footer {
            margin-top: 25px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-title">مدارس الذكاء الاصطناعي — AI School</div>
        <div class="report-subtitle">{{ $title }}</div>
    </div>

    <div class="meta-box">
        <div class="meta-row"><strong>تاريخ وساعة الإصدار:</strong> {{ $exported_at }}</div>
        @if(!empty($metadata['branch_name']))
            <div class="meta-row"><strong>الفرع:</strong> {{ $metadata['branch_name'] }}</div>
        @endif
        @if(!empty($metadata['period_label']))
            <div class="meta-row"><strong>فترة التقرير:</strong> {{ $metadata['period_label'] }}</div>
        @endif
        @if(!empty($metadata['user_name']))
            <div class="meta-row"><strong>الموظف المصدر:</strong> {{ $metadata['user_name'] }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; padding: 15px; color: #64748b;">
                        لا توجد سجلات مطابقة لمعايير الفلترة المحددة.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        تم استخراج هذا التقرير آلياً عبر مركز التقارير الموحد لمنصة AI School — تقرير رسمي موثق ولا يُعتد بأي تعديل يدوي عليه.
    </div>
</body>
</html>
