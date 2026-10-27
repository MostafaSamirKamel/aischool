<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\AttendanceSession;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class AttendanceRateChartWidget extends ChartWidget
{
    use HasWidgetShield;
    protected ?string $heading = 'معدل الحضور - آخر 6 أشهر';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $user = auth()->user();
        if (!$user || !$user->instructor) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $tenantId = $user->tenant_id;
        $instructorId = $user->instructor->id;
        
        $data = [];
        $labels = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->translatedFormat('F');
            
            // Calculate average attendance rate for the month
            $avgRate = AttendanceSession::query()
                ->where('tenant_id', $tenantId)
                ->whereHas('group', function ($query) use ($instructorId) {
                    $query->where('instructor_id', $instructorId);
                })
                ->whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->get()
                ->avg(function($session) {
                    return $session->attendance_rate;
                });
                
            $data[] = round($avgRate ?? 0, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => 'معدل الحضور %',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
