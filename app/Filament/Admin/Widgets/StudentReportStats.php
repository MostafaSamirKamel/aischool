<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Student;
use App\Models\Payment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentReportStats extends BaseWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        $query = Student::query();
        
        if (!auth()->user()->is_super_admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        $totalStudents = $query->count();
        $activeStudents = $query->clone()->where('status', 'active')->count();
        $maleStudents = $query->clone()->where('gender', 'male')->count();
        $femaleStudents = $query->clone()->where('gender', 'female')->count();

        return [
            Stat::make('إجمالي الطلاب', $totalStudents)
                ->description('العدد الكلي للطلاب')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
                
            Stat::make('الطلاب النشطين', $activeStudents)
                ->description(round(($activeStudents / max($totalStudents, 1)) * 100, 1) . '% من الإجمالي')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('الطلاب الذكور', $maleStudents)
                ->description(round(($maleStudents / max($totalStudents, 1)) * 100, 1) . '%')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),
                
            Stat::make('الطلاب الإناث', $femaleStudents)
                ->description(round(($femaleStudents / max($totalStudents, 1)) * 100, 1) . '%')
                ->descriptionIcon('heroicon-m-user')
                ->color('pink'),
        ];
    }
}
