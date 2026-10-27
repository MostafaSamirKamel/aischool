<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\AssignmentSubmission;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SubmissionsChartWidget extends ChartWidget
{
    use HasWidgetShield;
    protected ?string $heading = 'تسليمات الواجبات - آخر 6 أشهر';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
        $instructorId = $instructor->id;
        
        $data = [];
        $labels = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->translatedFormat('F');
            
            $count = AssignmentSubmission::query()
                ->whereHas('assignment', function($query) use ($instructorId) {
                    $query->where('instructor_id', $instructorId);
                })
                ->whereMonth('submitted_at', $month->month)
                ->whereYear('submitted_at', $month->year)
                ->count();
                
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'التسليمات',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
