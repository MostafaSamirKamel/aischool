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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: right;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @include('filament.pdf.partials.header', [
        'title' => $title,
        'subtitle' => 'تقرير إداري',
    ])

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
                    @foreach($headers as $header)
                        <td>{{ $row[$header] ?? '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(1, count($headers)) }}">لا توجد بيانات</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
