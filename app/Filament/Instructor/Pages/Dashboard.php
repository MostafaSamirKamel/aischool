<?php

namespace App\Filament\Instructor\Pages;

use App\Filament\Instructor\Widgets\AttendanceRateChartWidget;
use App\Filament\Instructor\Widgets\AttendanceStatsWidget;
use App\Filament\Instructor\Widgets\InstructorPaymentStatsWidget;
use App\Filament\Instructor\Widgets\InstructorStatsWidget;
use App\Filament\Instructor\Widgets\RecentAssignmentsWidget;
use App\Filament\Instructor\Widgets\SubmissionsChartWidget;
use App\Filament\Instructor\Widgets\UpcomingGroupsWidget;
use App\Filament\Instructor\Widgets\UpcomingSessionsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = null;
    
    public function getTitle(): string
    {
        return __('filament/instructor/dashboard.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/dashboard.title');
    }

    public function getWidgets(): array
    {
        return [
            InstructorStatsWidget::class,
            InstructorPaymentStatsWidget::class,
            UpcomingGroupsWidget::class,
            UpcomingSessionsWidget::class,
            RecentAssignmentsWidget::class,
            SubmissionsChartWidget::class,
            AttendanceStatsWidget::class,
            AttendanceRateChartWidget::class,
        ];
    }
}
