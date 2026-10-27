<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class PaymentMethodsChart extends ChartWidget
{
    use HasWidgetShield;
    protected ?string $heading = null;
    
    protected static ?int $sort = 5;
    
    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->check() && (auth()->user()->is_super_admin || auth()->user()->can('view_vault_balances'));
    }

    public function getHeading(): ?string
    {
        return 'توزيع التحصيلات حسب الخزائن';
    }

    protected function getData(): array
    {
        $query = Payment::query()->where('status', 'completed');
            
        if (!auth()->user()->is_super_admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        $payments = (clone $query)->with('vault')->get();
        $vaultTotals = [];
        foreach ($payments as $payment) {
            $vaultName = $payment->vault?->name ?? 'مدفوعات تاريخية';
            $vaultTotals[$vaultName] = ($vaultTotals[$vaultName] ?? 0) + (float) $payment->amount;
        }

        if (empty($vaultTotals)) {
            $vaultTotals = ['لا توجد تحصيلات' => 0];
        }

        $palette = [
            'rgba(34, 197, 94, 0.8)',   // green
            'rgba(59, 130, 246, 0.8)',  // blue
            'rgba(168, 85, 247, 0.8)',  // purple
            'rgba(245, 158, 11, 0.8)',  // orange
            'rgba(236, 72, 153, 0.8)',  // pink
            'rgba(20, 184, 166, 0.8)',  // teal
            'rgba(107, 114, 128, 0.8)', // gray
        ];

        return [
            'datasets' => [
                [
                    'data' => array_values($vaultTotals),
                    'backgroundColor' => array_slice(array_merge($palette, $palette, $palette), 0, count($vaultTotals)),
                ],
            ],
            'labels' => array_keys($vaultTotals),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
