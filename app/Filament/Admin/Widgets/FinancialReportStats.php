<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialReportStats extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        $query = Payment::query();
        
        if (!auth()->user()->is_super_admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        $totalRevenue = $query->clone()->where('status', 'completed')->sum('amount');
        $thisMonthRevenue = $query->clone()->where('status', 'completed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        $pendingPayments = $query->clone()->where('status', 'pending')->sum('amount');
        $refundedAmount = $query->clone()->where('status', 'refunded')->sum('amount');

        return [
            Stat::make('إجمالي الإيرادات', number_format($totalRevenue, 2) . ' ج.م')
                ->description('جميع المدفوعات المكتملة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('إيرادات الشهر الحالي', number_format($thisMonthRevenue, 2) . ' ج.م')
                ->description('مدفوعات ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
                
            Stat::make('مدفوعات معلقة', number_format($pendingPayments, 2) . ' ج.م')
                ->description('تحتاج للمتابعة')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('المبالغ المستردة', number_format($refundedAmount, 2) . ' ج.م')
                ->description('إجمالي المرتجعات')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('danger'),
        ];
    }
}
