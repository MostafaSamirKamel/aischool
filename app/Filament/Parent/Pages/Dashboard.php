<?php

namespace App\Filament\Parent\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return __('filament/parent/dashboard.title');
    }

    public function getHeading(): string
    {
        $hour = now()->hour;
        if ($hour < 12) {
            $greeting = __('dashboard.greetings.morning');
        } elseif ($hour < 17) {
            $greeting = __('dashboard.greetings.afternoon');
        } else {
            $greeting = __('dashboard.greetings.evening');
        }

        $student = \App\Services\Parent\ParentStudentAccessService::getSelectedStudent();
        $studentName = $student?->user?->name ?? '';

        return $greeting . ' ' . auth()->user()->name . '! 👋' .
            ($studentName ? ' (عرض بيانات: ' . $studentName . ')' : '');
    }

    public function getSubheading(): ?string
    {
        return __('filament/parent/dashboard.subheading');
    }

    public function getView(): string
    {
        return 'filament.parent.pages.dashboard';
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Parent\Widgets\OverdueInstallmentsWidget::class,
            \App\Filament\Parent\Widgets\StudentSwitcher::class,
            \App\Filament\Parent\Widgets\StatsOverviewWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Parent\Widgets\ParentStudentCoursesWidget::class,
            \App\Filament\Parent\Widgets\MyCoursesWidget::class,
            \App\Filament\Parent\Widgets\RecentGradesWidget::class,
            \App\Filament\Parent\Widgets\AttendanceWidget::class,
            \App\Filament\Parent\Widgets\PaymentsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/parent/dashboard.title');
    }
}
