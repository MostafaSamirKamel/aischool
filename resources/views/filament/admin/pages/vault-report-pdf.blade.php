<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الخزينة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', 'Helvetica', sans-serif; line-height: 1.6; color: #111827; background: #f3f4f6; }
        .page { padding: 24px; }
        table { width: 100%; border-collapse: collapse; }
    </style>
</head>
<body>
    @php
        $data = $data ?? [];
        $transactions = $data['transactions'] ?? [];
    @endphp

    <div class="page">
        <div style="position: relative; background: linear-gradient(135deg, rgb(79, 70, 229), rgb(109, 40, 217), rgb(147, 51, 234)); color: white; padding: 32px; border-radius: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 24px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                        <div style="background: rgba(255, 255, 255, 0.2); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.3);">📄</div>
                        <div>
                            <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 6px 0;">تقرير الخزينة</h1>
                            <p style="color: #ddd6fe; font-size: 14px; margin: 0;">تقرير شامل لحركة الخزينة</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 16px; background: rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 12px 16px; width: fit-content; border: 1px solid rgba(255, 255, 255, 0.2);">
                        <div>
                            <p style="font-size: 11px; color: #ddd6fe; margin: 0 0 4px 0;">الخزينة</p>
                            <p style="font-weight: bold; font-size: 16px; margin: 0; color: white;">{{ $data['vault']->name ?? 'غير محدد' }}</p>
                        </div>
                        <div style="width: 1px; height: 32px; background: rgba(255, 255, 255, 0.3);"></div>
                        <div>
                            <p style="font-size: 11px; color: #ddd6fe; margin: 0 0 4px 0;">الفترة</p>
                            <p style="font-weight: bold; font-size: 14px; margin: 0; color: white;">
                                {{ $data['from_date']->format('d/m/Y') ?? '-' }} - {{ $data['to_date']->format('d/m/Y') ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="position: relative; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); margin-bottom: 24px;">
            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(37, 99, 235), rgb(79, 70, 229), rgb(139, 92, 246));"></div>
            <div style="position: relative; color: white; padding: 28px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div style="background: rgba(255, 255, 255, 0.25); border-radius: 24px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.3);">💰</div>
                        <div>
                            <p style="font-size: 14px; margin: 0 0 8px 0; color: #ddd6fe; font-weight: 600;">الرصيد الافتتاحي</p>
                            <p style="font-size: 46px; font-weight: 900; margin: 0; color: white;">
                                {{ number_format((float)($data['opening_balance'] ?? 0), 2) }}
                                <span style="font-size: 20px; font-weight: bold; margin-right: 6px;">ج.م</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-bottom: 24px;">
            <div style="background: linear-gradient(135deg, rgb(79, 70, 229), rgb(147, 51, 234)); padding: 20px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <span style="font-size: 30px;">📋</span>
                    <h3 style="color: white; font-size: 20px; font-weight: 900; margin: 0;">المعاملات</h3>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr style="background: #f3f4f6;">
                            <th style="padding: 16px; text-align: right; font-weight: bold; color: #374151; border-bottom: 2px solid #e5e7eb;">التاريخ</th>
                            <th style="padding: 16px; text-align: right; font-weight: bold; color: #374151; border-bottom: 2px solid #e5e7eb;">النوع</th>
                            <th style="padding: 16px; text-align: right; font-weight: bold; color: #374151; border-bottom: 2px solid #e5e7eb;">المرجع</th>
                            <th style="padding: 16px; text-align: right; font-weight: bold; color: #374151; border-bottom: 2px solid #e5e7eb;">الوصف</th>
                            <th style="padding: 16px; text-align: left; font-weight: bold; color: #374151; border-bottom: 2px solid #e5e7eb;">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $index => $transaction)
                            <tr style="background: {{ $index % 2 === 0 ? '#ffffff' : '#f9fafb' }};">
                                <td style="padding: 16px; color: #1f2937; font-weight: bold; border-bottom: 1px solid #f3f4f6;">{{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}</td>
                                <td style="padding: 16px; border-bottom: 1px solid #f3f4f6;">
                                    <span style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 12px; font-weight: 900; border-radius: 12px; color: white; background: {{ $transaction['is_income'] ? 'linear-gradient(90deg, rgb(16, 185, 129), rgb(5, 150, 105))' : 'linear-gradient(90deg, rgb(239, 68, 68), rgb(220, 38, 38))' }};">
                                        {{ $transaction['is_income'] ? '📈' : '📉' }}
                                        {{ $transaction['type'] }}
                                    </span>
                                </td>
                                <td style="padding: 16px; font-family: monospace; font-size: 12px; color: #4b5563; font-weight: bold; border-bottom: 1px solid #f3f4f6;">{{ $transaction['reference'] }}</td>
                                <td style="padding: 16px; color: #374151; font-size: 14px; font-weight: 500; border-bottom: 1px solid #f3f4f6;">{{ $transaction['description'] }}</td>
                                <td style="padding: 16px; text-align: left; font-weight: 900; font-size: 18px; color: {{ $transaction['is_income'] ? '#059669' : '#dc2626' }}; border-bottom: 1px solid #f3f4f6;">
                                    {{ $transaction['is_income'] ? '+' : '-' }}
                                    {{ number_format((float)$transaction['amount'], 2) }}
                                    <span style="font-size: 12px; font-weight: bold; margin-right: 4px;">ج.م</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 48px 16px; text-align: center; color: #9ca3af;">لا توجد معاملات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            <div style="position: relative; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(16, 185, 129), rgb(5, 150, 105), rgb(6, 182, 212));"></div>
                <div style="position: relative; color: white; padding: 28px;">
                    <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 12px;">
                        <div style="background: rgba(255, 255, 255, 0.25); border-radius: 16px; padding: 12px; border: 1px solid rgba(255, 255, 255, 0.3);">📈</div>
                        <div style="font-size: 11px; font-weight: 900; background: rgba(255, 255, 255, 0.25); padding: 6px 12px; border-radius: 9999px;">إيرادات</div>
                    </div>
                    <h3 style="font-size: 13px; font-weight: bold; margin: 0 0 10px 0; color: #d1fae5;">إجمالي الإيرادات</h3>
                    <p style="font-size: 36px; font-weight: 900; margin: 0; color: white;">
                        {{ number_format((float)($data['total_income'] ?? 0), 2) }}
                        <span style="font-size: 16px; font-weight: bold; margin-right: 4px;">ج.م</span>
                    </p>
                </div>
            </div>

            <div style="position: relative; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(239, 68, 68), rgb(220, 38, 38), rgb(236, 72, 153));"></div>
                <div style="position: relative; color: white; padding: 28px;">
                    <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 12px;">
                        <div style="background: rgba(255, 255, 255, 0.25); border-radius: 16px; padding: 12px; border: 1px solid rgba(255, 255, 255, 0.3);">📉</div>
                        <div style="font-size: 11px; font-weight: 900; background: rgba(255, 255, 255, 0.25); padding: 6px 12px; border-radius: 9999px;">مصروفات</div>
                    </div>
                    <h3 style="font-size: 13px; font-weight: bold; margin: 0 0 10px 0; color: #fecaca;">إجمالي المصروفات</h3>
                    <p style="font-size: 36px; font-weight: 900; margin: 0; color: white;">
                        {{ number_format((float)($data['total_expenses'] ?? 0), 2) }}
                        <span style="font-size: 16px; font-weight: bold; margin-right: 4px;">ج.م</span>
                    </p>
                </div>
            </div>

            <div style="position: relative; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(59, 130, 246), rgb(79, 70, 229), rgb(139, 92, 246));"></div>
                <div style="position: relative; color: white; padding: 28px;">
                    <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 12px;">
                        <div style="background: rgba(255, 255, 255, 0.25); border-radius: 16px; padding: 12px; border: 1px solid rgba(255, 255, 255, 0.3);">💰</div>
                        <div style="font-size: 11px; font-weight: 900; background: rgba(255, 255, 255, 0.25); padding: 6px 12px; border-radius: 9999px;">رصيد نهائي</div>
                    </div>
                    <h3 style="font-size: 13px; font-weight: bold; margin: 0 0 10px 0; color: #ddd6fe;">الرصيد الختامي</h3>
                    <p style="font-size: 36px; font-weight: 900; margin: 0; color: white;">
                        {{ number_format((float)($data['closing_balance'] ?? 0), 2) }}
                        <span style="font-size: 16px; font-weight: bold; margin-right: 4px;">ج.م</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
