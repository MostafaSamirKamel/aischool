<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Enrollment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class EnrollmentStatusChart extends ChartWidget
{
    use HasWidgetShield;
    protected ?string $heading = null;
    
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return __('widgets.enrollment_status');
    }

    protected function getData(): array
    {
        $query = Enrollment::query();
            
        if (!auth()->user()->is_super_admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        $statuses = [
            'active' => $query->clone()->where('status', 'active')->count(),
            'completed' => $query->clone()->where('status', 'completed')->count(),
            'suspended' => $query->clone()->where('status', 'suspended')->count(),
            'cancelled' => $query->clone()->where('status', 'cancelled')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'التسجيلات',
                    'data' => array_values($statuses),
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)',  // active - green
                        'rgba(59, 130, 246, 0.8)',  // completed - blue
                        'rgba(245, 158, 11, 0.8)',  // suspended - yellow
                        'rgba(239, 68, 68, 0.8)',   // cancelled - red
                    ],
                ],
            ],
            'labels' => ['نشط', 'مكتمل', 'موقوف', 'ملغي'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
