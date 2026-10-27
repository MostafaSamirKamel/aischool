<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament-widgets::widgets
            :columns="[
                'default' => 1,
                'sm' => 2,
                'md' => 4,
            ]"
            :widgets="[
                \App\Filament\Instructor\Widgets\InstructorStatsWidget::class,
            ]"
        />
        
        <x-filament-widgets::widgets
            :columns="[
                'default' => 1,
                'md' => 2,
            ]"
            :widgets="[
                \App\Filament\Instructor\Widgets\SubmissionsChartWidget::class,
                \App\Filament\Instructor\Widgets\AttendanceRateChartWidget::class,
            ]"
        />
        
        <x-filament-widgets::widgets
            :columns="1"
            :widgets="[
                \App\Filament\Instructor\Widgets\UpcomingGroupsWidget::class,
                \App\Filament\Instructor\Widgets\RecentAssignmentsWidget::class,
            ]"
        />
    </div>
</x-filament-panels::page>
