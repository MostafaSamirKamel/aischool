<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\InstructorReview;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstructorReviewStatsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $instructor = auth()->user()->instructor;
        
        if (!$instructor) {
            return [];
        }

        $tenantId = auth()->user()->tenant_id;
        
        // Get approved reviews only
        $reviews = InstructorReview::where('instructor_id', $instructor->id)
            ->where('tenant_id', $tenantId)
            ->where('status', 'approved');
            
        $totalReviews = $reviews->count();
        $averageRating = $reviews->avg('rating') ?? 0;
        
        // Get rating distribution
        $distribution = InstructorReview::getRatingDistribution($instructor->id, $tenantId);
        
        // Calculate percentage of 5-star ratings
        $fiveStarPercentage = $totalReviews > 0 
            ? round(($distribution[5] / $totalReviews) * 100) 
            : 0;
            
        // Get recent reviews count (last 30 days)
        $recentReviews = InstructorReview::where('instructor_id', $instructor->id)
            ->where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            Stat::make('متوسط التقييم', number_format($averageRating, 1) . ' ⭐')
                ->description($totalReviews . ' تقييم')
                ->descriptionIcon('heroicon-o-star')
                ->color(match (true) {
                    $averageRating >= 4.5 => 'success',
                    $averageRating >= 3.5 => 'warning',
                    $averageRating >= 2.5 => 'info',
                    default => 'danger',
                })
                ->chart($this->getRatingTrendChart($instructor->id, $tenantId)),
                
            Stat::make('إجمالي التقييمات', $totalReviews)
                ->description('من الطلاب وأولياء الأمور')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),
                
            Stat::make('نسبة الـ 5 نجوم', $fiveStarPercentage . '%')
                ->description($distribution[5] . ' من ' . $totalReviews . ' تقييم')
                ->descriptionIcon('heroicon-o-trophy')
                ->color($fiveStarPercentage >= 50 ? 'success' : 'warning'),
                
            Stat::make('التقييمات الأخيرة', $recentReviews)
                ->description('آخر 30 يوم')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),
        ];
    }

    /**
     * Get rating trend chart data (last 6 months)
     */
    protected function getRatingTrendChart(int $instructorId, int $tenantId): array
    {
        $months = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            $avgRating = InstructorReview::where('instructor_id', $instructorId)
                ->where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->avg('rating');
                
            $data[] = $avgRating ? round($avgRating, 1) : 0;
        }

        return $data;
    }
}
