<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Course;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class TopCoursesChart extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $heading = null;
    
    protected static ?int $sort = 6;
    
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('widgets.top_courses');
    }

    protected function getData(): array
    {
        $query = Course::query()
            ->withCount(['groups' => function($q) {
                $q->withCount('enrollments');
            }])
            ->orderByDesc('groups_count')
            ->limit(10);
            
        if (!auth()->user()->is_super_admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        $courses = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'عدد التسجيلات',
                    'data' => $courses->map(fn($course) => $course->groups_count),
                    'backgroundColor' => 'rgba(139, 92, 246, 0.8)',
                    'borderColor' => 'rgb(139, 92, 246)',
                ],
            ],
            'labels' => $courses->map(fn($course) => $course->name),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
