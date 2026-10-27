<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $heading = null;
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '6months';

    public function getHeading(): ?string
    {
        return __('widgets.revenue_chart');
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        
        $query = Payment::query()
            ->where('status', 'completed');
            
        if (!auth()->user()->is_super_admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        $start = match($activeFilter) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            '3months' => now()->subMonths(3),
            '6months' => now()->subMonths(6),
            'year' => now()->subYear(),
            default => now()->subMonths(6),
        };

        $trend = Trend::query($query)->between(start: $start, end: now());
        $isDaily = in_array($activeFilter, ['week', 'month']);

        $data = $isDaily 
            ? $trend->perDay()->sum('amount') 
            : $trend->perMonth()->sum('amount');

        $labelFormat = match($activeFilter) {
            'week' => 'D, d M',
            'month' => 'd M',
            default => 'M Y',
        };

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات (جنيه)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->translatedFormat($labelFormat)),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => 'أسبوع',
            'month' => 'شهر',
            '3months' => '3 أشهر',
            '6months' => '6 أشهر',
            'year' => 'سنة',
        ];
    }
}
