<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Group;
use App\Models\Payment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PerformanceReportStats extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        $query = Group::query()->withCount('enrollments');
        
        if (!auth()->user()->is_super_admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        $totalGroups = $query->count();
        $activeGroups = $query->clone()->where('status', 'active')->count();

        // حساب متوسط الإشغال بشكل صحيح
        $groups = $query->clone()->get();
        $avgOccupancy = $groups->count() > 0
            ? $groups->avg(function ($group) {
                return $group->capacity > 0 ? ($group->enrollments_count / $group->capacity) * 100 : 0;
            })
            : 0;

        $paymentQuery = Payment::query()->where('status', 'completed');
        if (!auth()->user()->is_super_admin) {
            $paymentQuery->where('tenant_id', auth()->user()->tenant_id);
        }
        $totalRevenue = $paymentQuery->sum('amount');

        return [
            Stat::make('إجمالي المجموعات', $totalGroups)
                ->description('جميع المجموعات')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
                
            Stat::make('المجموعات النشطة', $activeGroups)
                ->description(round(($activeGroups / max($totalGroups, 1)) * 100, 1) . '% من الإجمالي')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('متوسط الإشغال', number_format($avgOccupancy, 1) . '%')
                ->description('نسبة استغلال السعة')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color(match(true) {
                    $avgOccupancy >= 80 => 'success',
                    $avgOccupancy >= 60 => 'warning',
                    default => 'danger',
                }),
                
            Stat::make('إجمالي الإيرادات', number_format($totalRevenue, 2) . ' ج.م')
                ->description('الإيرادات الكلية')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
