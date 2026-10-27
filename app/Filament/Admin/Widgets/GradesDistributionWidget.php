<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Grade;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class GradesDistributionWidget extends ChartWidget
{
    use HasWidgetShield;
    protected ?string $heading = null;
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('filament/admin/reports.charts.grades_distribution');
    }

    protected function getData(): array
    {
        $tenantId = $this->getTenantId();

        $distribution = [
            'A+' => Grade::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('grade', 'A+')->count(),
            'A' => Grade::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('grade', 'A')->count(),
            'B+' => Grade::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('grade', 'B+')->count(),
            'B' => Grade::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('grade', 'B')->count(),
            'C+' => Grade::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('grade', 'C+')->count(),
            'C' => Grade::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('grade', 'C')->count(),
            'D' => Grade::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('grade', 'D')->count(),
            'F' => Grade::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('grade', 'F')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => __('filament/admin/reports.charts.students_count'),
                    'data' => array_values($distribution),
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                        'rgb(251, 146, 60)',
                        'rgb(234, 179, 8)',
                        'rgb(239, 68, 68)',
                        'rgb(148, 163, 184)',
                    ],
                ],
            ],
            'labels' => array_keys($distribution),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getTenantId(): ?int
    {
        if (auth()->user()->hasRole('super_admin')) {
            return null;
        }

        return auth()->user()->tenant_id;
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
