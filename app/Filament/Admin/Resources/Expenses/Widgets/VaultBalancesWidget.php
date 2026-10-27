<?php

namespace App\Filament\Admin\Resources\Expenses\Widgets;

use App\Models\Expense;
use App\Models\Revenue;
use App\Models\StudentPayment;
use App\Models\Vault;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Notifications\Notification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VaultBalancesWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = -1; // Display at top

    public static function canView(): bool
    {
        return auth()->check() && (auth()->user()->is_super_admin || auth()->user()->can('view_vault_balances'));
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $stats = [];
        $negativeBalances = [];

        // الحصول على جميع الخزائن للفرع
        $vaultsQuery = Vault::query()->where('is_active', true);
        
        if (!($user->is_super_admin && is_null($tenantId))) {
            $vaultsQuery->where('tenant_id', $tenantId);
        }

        $vaults = $vaultsQuery->get();

        $vaultIcons = [
            'cash' => '💵',
            'vodafone' => '📱',
            'instapay' => '💳',
        ];

        foreach ($vaults as $vault) {
            $currentBalance = $vault->getCurrentBalance();
            $icon = $vaultIcons[$vault->type] ?? '💰';

            if ($currentBalance < 0) {
                $negativeBalances[] = [
                    'vault' => $vault->name,
                    'balance' => $currentBalance,
                ];
            }

            $stat = Stat::make(
                $icon . ' ' . $vault->name, 
                number_format($currentBalance, 2) . ' جنيه'
            )
                ->description('الرصيد الفعلي الحالي للخزنة')
                ->color($currentBalance >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-banknotes');

            if ($currentBalance < 0) {
                $stat->description('⚠️ رصيد سالب')
                    ->extraAttributes([
                        'class' => 'border-2 border-danger-500 shadow-lg',
                    ]);
            }

            $stats[] = $stat;
        }

        // إضافة إجمالي كل الخزائن
        $totalBalance = $vaults->sum(fn(Vault $vault) => $vault->getCurrentBalance());

        $totalStat = Stat::make('💰 الإجمالي الكلي', number_format($totalBalance, 2) . ' جنيه')
            ->description('مجموع أرصدة جميع الخزائن')
            ->color($totalBalance >= 0 ? 'success' : 'danger')
            ->icon('heroicon-o-currency-dollar');

        if ($totalBalance < 0) {
            $totalStat->description('⚠️ إجمالي سالب - يرجى مراجعة الخزائن')
                ->extraAttributes([
                    'class' => 'border-2 border-danger-600 shadow-xl',
                ]);
        }

        $stats[] = $totalStat;

        // Send notification for negative balances (once per page load)
        if (!empty($negativeBalances) && !session()->has('vault_negative_balance_notified')) {
            session()->put('vault_negative_balance_notified', true);
            
            $message = "تنبيه: هناك " . count($negativeBalances) . " خزينة برصيد سالب:\n";
            foreach ($negativeBalances as $neg) {
                $message .= "• " . $neg['vault'] . ": " . number_format(abs($neg['balance']), 2) . " جنيه\n";
            }

            Notification::make()
                ->warning()
                ->title('⚠️ تحذير: رصيد سالب في الخزائن')
                ->body($message)
                ->persistent()
                ->send();
        }

        return $stats;
    }
}
