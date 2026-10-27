<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Grade;
use App\Models\Exam;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class GradesStatsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $isSuperAdmin = auth()->user()->is_super_admin;

        $baseQuery = Grade::query();
        if (!$isSuperAdmin && $tenantId) {
            $baseQuery->whereHas('student', fn($q) => $q->where('tenant_id', $tenantId));
        }

        $totalGrades = (clone $baseQuery)->count();
        $averagePercentage = (clone $baseQuery)->avg('percentage') ?? 0;
        $passingGrades = (clone $baseQuery)->whereHas('exam', function ($query) {
            $query->whereRaw('grades.marks_obtained >= exams.passing_marks');
        })->count();
        $passingRate = $totalGrades > 0 ? round(($passingGrades / $totalGrades) * 100, 1) : 0;

        $highAchievers = (clone $baseQuery)->where('percentage', '>=', 90)->count();

        $gradesTrend = $this->getGradesTrend($tenantId, $isSuperAdmin);

        return [
            Stat::make('إجمالي الدرجات', $totalGrades)
                ->description('جميع الدرجات المسجلة')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('primary')
                ->chart(!empty($gradesTrend) ? $gradesTrend : []),

            Stat::make('متوسط النسبة المئوية', round($averagePercentage, 1) . '%')
                ->description('للطلاب في جميع الامتحانات')
                ->descriptionIcon('heroicon-o-calculator')
                ->color($averagePercentage >= 75 ? 'success' : ($averagePercentage >= 60 ? 'warning' : 'danger')),

            Stat::make('نسبة النجاح', $passingRate . '%')
                ->description($passingGrades . ' من ' . $totalGrades . ' طالب')
                ->descriptionIcon('heroicon-o-trophy')
                ->color($passingRate >= 80 ? 'success' : ($passingRate >= 60 ? 'warning' : 'danger')),

            Stat::make('المتفوقين', $highAchievers)
                ->description('الحاصلين على 90% فأكثر')
                ->descriptionIcon('heroicon-o-star')
                ->color('success'),
        ];
    }

    protected function getGradesTrend(?int $tenantId, bool $isSuperAdmin): array
    {
        $query = Grade::query();
        if (!$isSuperAdmin && $tenantId) {
            $query->whereHas('student', fn($q) => $q->where('tenant_id', $tenantId));
        }
        $trend = Trend::query($query)
            ->between(start: now()->subMonths(5)->startOfMonth(), end: now()->endOfMonth())
            ->perMonth()
            ->count();
        $data = $trend->map(fn (TrendValue $val) => (int) $val->aggregate)->toArray();
        return count(array_filter($data)) > 0 ? $data : [];
    }
}
