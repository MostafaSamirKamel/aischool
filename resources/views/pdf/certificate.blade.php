<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>شهادة إتمام - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: center;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #1f2937;
        }
        .certificate {
            width: 100%;
            height: 100vh;
            padding: 60px 80px;
            box-sizing: border-box;
            position: relative;
        }
        .border {
            border: 15px solid #fbbf24;
            border-radius: 20px;
            padding: 40px;
            background: white;
            height: 100%;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
        }
        .inner-border {
            border: 3px solid #fbbf24;
            border-radius: 15px;
            padding: 40px;
            height: 100%;
            position: relative;
        }
        .logo {
            font-size: 48px;
            color: #2563eb;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 52px;
            color: #2563eb;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        .subtitle {
            font-size: 20px;
            color: #6b7280;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        .awarded-to {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        .student-name {
            font-size: 42px;
            color: #1f2937;
            font-weight: bold;
            margin: 20px 0 30px 0;
            border-bottom: 3px solid #fbbf24;
            display: inline-block;
            padding: 0 50px 10px 50px;
        }
        .completion-text {
            font-size: 18px;
            color: #4b5563;
            line-height: 1.8;
            margin: 30px auto;
            max-width: 600px;
        }
        .course-name {
            font-size: 32px;
            color: #2563eb;
            font-weight: bold;
            margin: 20px 0;
        }
        .grade-section {
            margin: 30px 0;
            display: inline-block;
            background: #eff6ff;
            padding: 20px 60px;
            border-radius: 10px;
            border: 2px solid #2563eb;
        }
        .grade-label {
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .grade-value {
            font-size: 42px;
            color: #2563eb;
            font-weight: bold;
            margin: 5px 0;
        }
        .date-section {
            margin-top: 40px;
            font-size: 16px;
            color: #6b7280;
        }
        .signatures {
            position: absolute;
            bottom: 40px;
            left: 80px;
            right: 80px;
            display: table;
            width: calc(100% - 160px);
        }
        .signature {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        .signature-line {
            border-top: 2px solid #1f2937;
            width: 200px;
            margin: 0 auto 10px auto;
        }
        .seal {
            position: absolute;
            bottom: 80px;
            right: 100px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 5px solid #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="border">
            <div class="inner-border">
                <div class="logo">🎓</div>
                
                <h1>شهادة إتمام</h1>
                <div class="subtitle">Certificate of Completion</div>
                
                <div class="awarded-to">هذه الشهادة تمنح لـ</div>
                
                <div class="student-name">
                    {{ $student->first_name }} {{ $student->last_name }}
                </div>
                
                <div class="completion-text">
                    تقديراً لإتمام الكورس بنجاح
                </div>
                
                <div class="course-name">
                    {{ $course->name }}
                </div>
                
                <div class="grade-section">
                    <div class="grade-label">التقدير النهائي</div>
                    <div class="grade-value">{{ $finalGrade }}</div>
                    <div class="grade-label">{{ $finalPercentage }}%</div>
                </div>
                
                <div class="date-section">
                    تاريخ الإتمام: {{ $completionDate->format('d/m/Y') }}
                </div>
                
                <div class="signatures">
                    <div class="signature">
                        <div class="signature-line"></div>
                        <div><strong>المدير الأكاديمي</strong></div>
                        <div style="font-size: 12px; color: #9ca3af;">Academic Director</div>
                    </div>
                    <div class="signature">
                        <div class="signature-line"></div>
                        <div><strong>مدير المدرسة</strong></div>
                        <div style="font-size: 12px; color: #9ca3af;">School Director</div>
                    </div>
                </div>
                
                <div class="seal">
                    ✓<br>مُعتمد
                </div>
            </div>
        </div>
    </div>
</body>
</html>
