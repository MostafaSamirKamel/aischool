<?php

namespace App\Filament\Supervisor\Widgets;

use App\Services\EducationalSupervisionService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SupervisorStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $service = app(EducationalSupervisionService::class);
        $metrics = $service->getDashboardMetrics($user);

        return [
            Stat::make('حصص اليوم المجدولة', $metrics['today_scheduled_sessions'])
                ->description('منفذ منها: ' . $metrics['completed_sessions'])
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('حصص لم يُسجّل حضورها', $metrics['sessions_missing_attendance'])
                ->description('تتطلب توجيه المهندس للرصد')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($metrics['sessions_missing_attendance'] > 0 ? 'danger' : 'success'),

            Stat::make('غياب الطلاب المؤكد', $metrics['confirmed_student_absences'])
                ->description('حالات غياب مثبتة بالسجلات')
                ->descriptionIcon('heroicon-o-user-minus')
                ->color('warning'),

            Stat::make('واجبات تنتظر التصحيح', $metrics['homework_awaiting_grading'])
                ->description('متأخر منها (> 48 ساعة): ' . $metrics['homework_overdue_grading'])
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color($metrics['homework_overdue_grading'] > 0 ? 'danger' : 'warning'),

            Stat::make('امتحانات تنتظر الاعتماد', $metrics['exams_awaiting_actions'])
                ->description('محاولات مسلمة تنتظر الرصد')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info'),

            Stat::make('درجات منخفضة منشورة (< 50%)', $metrics['published_low_scores'])
                ->description('تتطلب متابعة تعليمية دقيقة')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color($metrics['published_low_scores'] > 0 ? 'danger' : 'success'),

            Stat::make('ملاحظات بانتظار مراجعتك', $metrics['observations_waiting_review'])
                ->description('أنجزها المهندسون وبانتظار الاعتماد')
                ->descriptionIcon('heroicon-o-clock')
                ->color($metrics['observations_waiting_review'] > 0 ? 'warning' : 'gray'),

            Stat::make('ملاحظات إشرافية مفتوحة', $metrics['open_observations'])
                ->description('متأخرة عن موعدها: ' . $metrics['overdue_observations'])
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color($metrics['overdue_observations'] > 0 ? 'danger' : 'info'),

            Stat::make('تقييمات مستحقة هذا الشهر', $metrics['evaluations_due_this_month'])
                ->description('مهندسون لم يُنشر تقييمهم بعد')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color($metrics['evaluations_due_this_month'] > 0 ? 'warning' : 'success'),
        ];
    }
}
