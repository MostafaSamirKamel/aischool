<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Enrollment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnrollmentReportStats extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        $query = Enrollment::query();
        
        if (!auth()->user()->is_super_admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        $totalEnrollments = $query->count();
        $activeEnrollments = $query->clone()->where('status', 'active')->count();
        $completedEnrollments = $query->clone()->where('status', 'completed')->count();
        $thisMonthEnrollments = $query->clone()
            ->whereMonth('enrollment_date', now()->month)
            ->whereYear('enrollment_date', now()->year)
            ->count();

        return [
            Stat::make('إجمالي التسجيلات', $totalEnrollments)
                ->description('جميع التسجيلات')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
                
            Stat::make('التسجيلات النشطة', $activeEnrollments)
                ->description(round(($activeEnrollments / max($totalEnrollments, 1)) * 100, 1) . '% من الإجمالي')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('التسجيلات المكتملة', $completedEnrollments)
                ->description(round(($completedEnrollments / max($totalEnrollments, 1)) * 100, 1) . '% من الإجمالي')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('info'),
                
            Stat::make('تسجيلات الشهر الحالي', $thisMonthEnrollments)
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
        ];
    }
}
