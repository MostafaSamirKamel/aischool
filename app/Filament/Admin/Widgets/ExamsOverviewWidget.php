<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Exam;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ExamsOverviewWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $isSuperAdmin = auth()->user()->is_super_admin;

        $baseQuery = Exam::query();
        if (!$isSuperAdmin && $tenantId) {
            $baseQuery->where('tenant_id', $tenantId);
        }

        $totalExams = (clone $baseQuery)->count();
        $upcomingExams = (clone $baseQuery)->where('exam_date', '>', now())->where('status', 'scheduled')->count();
        $completedExams = (clone $baseQuery)->where('status', 'completed')->count();
        $todayExams = (clone $baseQuery)->whereDate('exam_date', today())->count();

        $totalExamsTrend = $this->getExamsTrend($tenantId, $isSuperAdmin);
        $completedExamsTrend = $this->getCompletedExamsTrend($tenantId, $isSuperAdmin);

        return [
            Stat::make('إجمالي الامتحانات', $totalExams)
                ->description('جميع الامتحانات في النظام')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('primary')
                ->chart(!empty($totalExamsTrend) ? $totalExamsTrend : []),

            Stat::make('امتحانات قادمة', $upcomingExams)
                ->description('مجدولة ولم تبدأ بعد')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make('امتحانات مكتملة', $completedExams)
                ->description('تم إجراؤها بنجاح')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart(!empty($completedExamsTrend) ? $completedExamsTrend : []),

            Stat::make('امتحانات اليوم', $todayExams)
                ->description('المقررة لهذا اليوم')
                ->descriptionIcon('heroicon-o-clock')
                ->color($todayExams > 0 ? 'danger' : 'gray'),
        ];
    }

    protected function getExamsTrend(?int $tenantId, bool $isSuperAdmin): array
    {
        $query = Exam::query();
        if (!$isSuperAdmin && $tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        $trend = Trend::query($query)
            ->between(start: now()->subMonths(5)->startOfMonth(), end: now()->endOfMonth())
            ->perMonth()
            ->count();
        $data = $trend->map(fn (TrendValue $val) => (int) $val->aggregate)->toArray();
        return count(array_filter($data)) > 0 ? $data : [];
    }

    protected function getCompletedExamsTrend(?int $tenantId, bool $isSuperAdmin): array
    {
        $query = Exam::query()->where('status', 'completed');
        if (!$isSuperAdmin && $tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        $trend = Trend::query($query)
            ->between(start: now()->subMonths(5)->startOfMonth(), end: now()->endOfMonth())
            ->perMonth()
            ->count();
        $data = $trend->map(fn (TrendValue $val) => (int) $val->aggregate)->toArray();
        return count(array_filter($data)) > 0 ? $data : [];
    }
}
