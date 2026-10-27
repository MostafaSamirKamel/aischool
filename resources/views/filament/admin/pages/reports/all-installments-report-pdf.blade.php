<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير جميع الأقساط</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', 'Helvetica', sans-serif; line-height: 1.6; color: #111827; background: #f3f4f6; }
        .page { padding: 24px; }
    </style>
</head>
<body>
    @php
        $stats = $stats ?? [];
        $installments = $installments ?? [];
    @endphp

    <div class="page">
        <table style="width: 100%; border-collapse: separate; border-spacing: 12px; margin-bottom: 24px;">
            <tr>
                <td style="width: 16.6%; background: linear-gradient(135deg, rgb(59, 130, 246), rgb(37, 99, 235)); padding: 16px; border-radius: 16px; border: 2px solid rgba(59, 130, 246, 0.5);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: bold; margin: 0 0 6px 0;">إجمالي الأقساط</p>
                            <p style="color: white; font-size: 24px; font-weight: 900; margin: 0;">{{ $stats['total_count'] ?? 0 }}</p>
                        </div>
                        <span style="display: inline-block; width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.25); color: white; font-weight: 900; text-align: center; line-height: 34px;">T</span>
                    </div>
                </td>
                <td style="width: 16.6%; background: linear-gradient(135deg, rgb(147, 51, 234), rgb(126, 34, 206)); padding: 16px; border-radius: 16px; border: 2px solid rgba(147, 51, 234, 0.5);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: bold; margin: 0 0 6px 0;">المبلغ الكلي</p>
                            <p style="color: white; font-size: 18px; font-weight: 900; margin: 0;">{{ number_format((float)($stats['total_amount'] ?? 0), 0) }} ج</p>
                        </div>
                        <span style="display: inline-block; width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.25); color: white; font-weight: 900; text-align: center; line-height: 34px;">A</span>
                    </div>
                </td>
                <td style="width: 16.6%; background: linear-gradient(135deg, rgb(34, 197, 94), rgb(22, 163, 74)); padding: 16px; border-radius: 16px; border: 2px solid rgba(34, 197, 94, 0.5);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: bold; margin: 0 0 6px 0;">المدفوع</p>
                            <p style="color: white; font-size: 18px; font-weight: 900; margin: 0;">{{ number_format((float)($stats['total_paid'] ?? 0), 0) }} ج</p>
                        </div>
                        <span style="display: inline-block; width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.25); color: white; font-weight: 900; text-align: center; line-height: 34px;">P</span>
                    </div>
                </td>
                <td style="width: 16.6%; background: linear-gradient(135deg, rgb(249, 115, 22), rgb(234, 88, 12)); padding: 16px; border-radius: 16px; border: 2px solid rgba(249, 115, 22, 0.5);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: bold; margin: 0 0 6px 0;">المتبقي</p>
                            <p style="color: white; font-size: 18px; font-weight: 900; margin: 0;">{{ number_format((float)($stats['total_remaining'] ?? 0), 0) }} ج</p>
                        </div>
                        <span style="display: inline-block; width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.25); color: white; font-weight: 900; text-align: center; line-height: 34px;">R</span>
                    </div>
                </td>
                <td style="width: 16.6%; background: linear-gradient(135deg, rgb(239, 68, 68), rgb(220, 38, 38)); padding: 16px; border-radius: 16px; border: 2px solid rgba(239, 68, 68, 0.5);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: bold; margin: 0 0 6px 0;">المتأخرة</p>
                            <p style="color: white; font-size: 24px; font-weight: 900; margin: 0;">{{ $stats['overdue_count'] ?? 0 }}</p>
                        </div>
                        <span style="display: inline-block; width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.25); color: white; font-weight: 900; text-align: center; line-height: 34px;">O</span>
                    </div>
                </td>
                <td style="width: 16.6%; background: linear-gradient(135deg, rgb(234, 179, 8), rgb(202, 138, 4)); padding: 16px; border-radius: 16px; border: 2px solid rgba(234, 179, 8, 0.5);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: bold; margin: 0 0 6px 0;">السداد الجزئي</p>
                            <p style="color: white; font-size: 24px; font-weight: 900; margin: 0;">{{ $stats['partial_count'] ?? 0 }}</p>
                        </div>
                        <span style="display: inline-block; width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.25); color: white; font-weight: 900; text-align: center; line-height: 34px;">S</span>
                    </div>
                </td>
            </tr>
        </table>

        @if(count($installments) > 0)
            <div style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                <div style="background: linear-gradient(135deg, rgb(79, 70, 229), rgb(147, 51, 234)); padding: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <span style="font-size: 36px;">📋</span>
                        <h3 style="color: white; font-size: 22px; font-weight: 900; margin: 0;">تفاصيل الأقساط</h3>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, rgb(249, 250, 251), rgb(243, 244, 246));">
                                <th style="padding: 16px; text-align: right; font-weight: bold; color: rgb(55, 65, 81); border-bottom: 2px solid rgb(229, 231, 235);">#</th>
                                <th style="padding: 16px; text-align: right; font-weight: bold; color: rgb(55, 65, 81); border-bottom: 2px solid rgb(229, 231, 235);">الطالب</th>
                                <th style="padding: 16px; text-align: right; font-weight: bold; color: rgb(55, 65, 81); border-bottom: 2px solid rgb(229, 231, 235);">كود الطالب</th>
                                <th style="padding: 16px; text-align: right; font-weight: bold; color: rgb(55, 65, 81); border-bottom: 2px solid rgb(229, 231, 235);">المبلغ</th>
                                <th style="padding: 16px; text-align: right; font-weight: bold; color: rgb(55, 65, 81); border-bottom: 2px solid rgb(229, 231, 235);">المدفوع</th>
                                <th style="padding: 16px; text-align: right; font-weight: bold; color: rgb(55, 65, 81); border-bottom: 2px solid rgb(229, 231, 235);">المتبقي</th>
                                <th style="padding: 16px; text-align: right; font-weight: bold; color: rgb(55, 65, 81); border-bottom: 2px solid rgb(229, 231, 235);">الحالة</th>
                                <th style="padding: 16px; text-align: right; font-weight: bold; color: rgb(55, 65, 81); border-bottom: 2px solid rgb(229, 231, 235);">تاريخ الاستحقاق</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($installments as $index => $installment)
                                <tr style="background: {{ $index % 2 == 0 ? 'white' : 'rgb(249, 250, 251)' }};">
                                    <td style="padding: 16px; color: rgb(75, 85, 99); border-bottom: 1px solid rgb(229, 231, 235);">{{ $index + 1 }}</td>
                                    <td style="padding: 16px; color: rgb(17, 24, 39); font-weight: 600; border-bottom: 1px solid rgb(229, 231, 235);">{{ $installment->enrollment?->student?->user?->name ?? 'غير محدد' }}</td>
                                    <td style="padding: 16px; color: rgb(75, 85, 99); border-bottom: 1px solid rgb(229, 231, 235);">{{ $installment->enrollment?->student?->student_code ?? '-' }}</td>
                                    <td style="padding: 16px; color: rgb(17, 24, 39); font-weight: 700; border-bottom: 1px solid rgb(229, 231, 235);">{{ number_format((float)$installment->amount, 0) }} ج</td>
                                    <td style="padding: 16px; color: rgb(34, 197, 94); font-weight: 600; border-bottom: 1px solid rgb(229, 231, 235);">{{ number_format((float)$installment->paid_amount, 0) }} ج</td>
                                    <td style="padding: 16px; color: rgb(239, 68, 68); font-weight: 600; border-bottom: 1px solid rgb(229, 231, 235);">{{ number_format((float)($installment->amount - $installment->paid_amount), 0) }} ج</td>
                                    <td style="padding: 16px; border-bottom: 1px solid rgb(229, 231, 235);">
                                        @php
                                            $status = $installment->payment_status ?? ['status' => '-', 'color' => 'blue', 'icon' => ''];
                                            $colors = [
                                                'green' => 'background: linear-gradient(135deg, rgb(34, 197, 94), rgb(22, 163, 74)); color: white;',
                                                'yellow' => 'background: linear-gradient(135deg, rgb(234, 179, 8), rgb(202, 138, 4)); color: white;',
                                                'red' => 'background: linear-gradient(135deg, rgb(239, 68, 68), rgb(220, 38, 38)); color: white;',
                                                'blue' => 'background: linear-gradient(135deg, rgb(59, 130, 246), rgb(37, 99, 235)); color: white;',
                                            ];
                                        @endphp
                                        <span style="display: inline-block; padding: 6px 14px; border-radius: 12px; font-size: 12px; font-weight: bold; {{ $colors[$status['color']] ?? '' }}">
                                            {{ $status['icon'] }} {{ $status['status'] }}
                                        </span>
                                    </td>
                                    <td style="padding: 16px; color: rgb(75, 85, 99); border-bottom: 1px solid rgb(229, 231, 235);">{{ $installment->due_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div style="background: linear-gradient(135deg, rgb(34, 197, 94), rgb(22, 163, 74), rgb(5, 150, 105)); padding: 48px; border-radius: 24px; text-align: center; box-shadow: 0 20px 60px rgba(34, 197, 94, 0.3); border: 3px solid rgba(255,255,255,0.3);">
                <div>
                    <span style="font-size: 80px; display: block; margin-bottom: 16px;">✅</span>
                </div>
                <h3 style="color: white; font-size: 28px; font-weight: 900; margin: 0 0 12px 0;">لا توجد أقساط</h3>
                <p style="color: rgba(255,255,255,0.95); font-size: 18px; margin: 0;">لا توجد أقساط تطابق الفلاتر المحددة</p>
            </div>
        @endif
    </div>
</body>
</html>
