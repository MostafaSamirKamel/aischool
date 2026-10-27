<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الأقساط المستحقة والقريبة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', 'Helvetica', sans-serif; line-height: 1.6; color: #111827; background: #ffffff; }
        .page { padding: 24px; }
    </style>
</head>
<body>
    @php
        $overdueStats = $overdueStats ?? [];
        $upcomingStats = $upcomingStats ?? [];
        $overdueInstallments = $overdueInstallments ?? [];
        $upcomingInstallments = $upcomingInstallments ?? [];
    @endphp

    <div class="page">
        <div style="position: relative; overflow: hidden; border-radius: 24px; background: linear-gradient(135deg, rgb(79, 70, 229), rgb(139, 92, 246), rgb(236, 72, 153)); padding: 32px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                    <div style="background: rgba(255, 255, 255, 0.2); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.3);">
                        📊
                    </div>
                    <div>
                        <h1 style="font-size: 28px; font-weight: 900; color: white; margin: 0 0 8px 0;">تقرير الأقساط المستحقة والقريبة</h1>
                        <p style="color: #fed7aa; margin: 0; font-size: 14px;">متابعة شاملة لحالة الأقساط الدراسية</p>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 24px;">
            <div style="position: relative; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(239, 68, 68), rgb(244, 114, 147), rgb(244, 63, 94));"></div>
                <div style="position: relative; padding: 32px;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                        <div style="font-size: 40px;">🔔</div>
                        <div>
                            <h3 style="font-size: 22px; font-weight: 900; color: white; margin: 0 0 4px 0;">الأقساط المتأخرة</h3>
                            <p style="color: #fecaca; margin: 0; font-size: 13px;">تحتاج متابعة فورية</p>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                            <p style="color: #fecaca; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">العدد</p>
                            <p style="font-size: 42px; font-weight: 900; color: white; margin: 0;">{{ $overdueStats['count'] ?? 0 }}</p>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                            <p style="color: #fecaca; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">المستحق</p>
                            <p style="font-size: 32px; font-weight: 900; color: white; margin: 0;">{{ number_format((float)($overdueStats['total_due'] ?? 0), 0) }}</p>
                            <p style="color: #fecaca; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                            <p style="color: #fecaca; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">المتبقي</p>
                            <p style="font-size: 32px; font-weight: 900; color: white; margin: 0;">{{ number_format((float)($overdueStats['total_amount'] ?? 0), 0) }}</p>
                            <p style="color: #fecaca; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                        </div>
                    </div>
                </div>
            </div>

            <div style="position: relative; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(217, 119, 6), rgb(249, 115, 22), rgb(234, 179, 8));"></div>
                <div style="position: relative; padding: 32px;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                        <div style="font-size: 40px;">⏰</div>
                        <div>
                            <h3 style="font-size: 22px; font-weight: 900; color: white; margin: 0 0 4px 0;">الأقساط القريبة</h3>
                            <p style="color: #fef3c7; margin: 0; font-size: 13px;">خلال 7 أيام</p>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                            <p style="color: #fef3c7; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">العدد</p>
                            <p style="font-size: 42px; font-weight: 900; color: white; margin: 0;">{{ $upcomingStats['count'] ?? 0 }}</p>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                            <p style="color: #fef3c7; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">المستحق</p>
                            <p style="font-size: 32px; font-weight: 900; color: white; margin: 0;">{{ number_format((float)($upcomingStats['total_due'] ?? 0), 0) }}</p>
                            <p style="color: #fef3c7; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                            <p style="color: #fef3c7; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">المتبقي</p>
                            <p style="font-size: 32px; font-weight: 900; color: white; margin: 0;">{{ number_format((float)($upcomingStats['total_amount'] ?? 0), 0) }}</p>
                            <p style="color: #fef3c7; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(count($overdueInstallments) === 0 && count($upcomingInstallments) === 0)
            <div style="background: linear-gradient(135deg, #dcfce7, #e0f2fe); border-radius: 16px; padding: 40px; text-align: center; border: 2px solid #bbf7d0; margin-top: 24px;">
                <div style="font-size: 48px; margin-bottom: 16px;">✅</div>
                <h3 style="font-size: 20px; font-weight: 900; color: #15803d; margin: 0 0 8px 0;">ممتاز!</h3>
                <p style="color: #059669; margin: 0;">جميع الأقساط مدفوعة في موعدها</p>
            </div>
        @endif
    </div>
</body>
</html>
