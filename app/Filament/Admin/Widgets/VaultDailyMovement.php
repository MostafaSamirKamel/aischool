<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Vault;
use App\Models\Tenant;
use App\Models\Expense;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\SalaryPayment;
use App\Models\StudentPayment;
use App\Models\VaultTransfer;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class VaultDailyMovement extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.admin.widgets.vault-daily-movement';
    
    protected int | string | array $columnSpan = 'full';
    
    public static function canView(): bool
    {
        return auth()->check() && (auth()->user()->is_super_admin || auth()->user()->can('view_vault_transactions'));
    }
    
    public ?string $selectedDate = null;
    public ?int $selectedVaultId = null;
    public ?int $selectedTenantId = null;

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function getViewData(): array
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;
        $date = Carbon::parse($this->selectedDate ?? now());

        if ($user->is_super_admin && is_null($tenantId) && $this->selectedTenantId) {
            $tenantId = $this->selectedTenantId;
        }

        // Get vaults
        $vaultsQuery = Vault::query()->where('is_active', true);
        if (!($user->is_super_admin && is_null($tenantId))) {
            $vaultsQuery->where('tenant_id', $tenantId);
        }
        $allVaults = $vaultsQuery->get();

        // Filter by selected vault if any
        $vaults = $allVaults;
        if ($this->selectedVaultId) {
            $vaults = $allVaults->where('id', $this->selectedVaultId);
        }

        $dailyData = [];

        foreach ($vaults as $vault) {
            if (!($vault instanceof Vault)) {
                continue;
            }

            // Opening balance (as of end of previous day) using full vault components.
            $previousDate = $date->copy()->subDay();
            $openingBalance = $this->calculateVaultBalanceUntilDate($vault, $previousDate);

            // Today's transactions
            $todayRevenues = Revenue::where('vault_id', $vault->id)
                ->whereNull('payment_id')
                ->whereDate('revenue_date', $date)
                ->sum('amount');

            $todayStudentPaymentsLegacy = StudentPayment::where('vault_id', $vault->id)
                ->whereDate('payment_date', $date)
                ->where('status', 'completed')
                ->sum('net_amount');

            $todayPayments = Payment::where('vault_id', $vault->id)
                ->where(function ($query) use ($date) {
                    $query->whereDate('payment_date', $date->toDateString())
                        ->orWhere(function ($fallback) use ($date) {
                            $fallback->whereNull('payment_date')
                                ->whereDate('created_at', $date->toDateString());
                        });
                })
                ->where('status', 'completed')
                ->sum('amount');

            // Enrollment direct upfront collections without double-counting payments
            $todayEnrollmentPayments = 0.0;
            $todayEnrollments = Enrollment::where('vault_id', $vault->id)
                ->where(function ($query) use ($date) {
                    $query->whereDate('enrollment_date', $date->toDateString())
                        ->orWhere(function ($fallback) use ($date) {
                            $fallback->whereNull('enrollment_date')
                                ->whereDate('created_at', $date->toDateString());
                        });
                })
                ->get();

            foreach ($todayEnrollments as $enrollment) {
                $upfront = $enrollment->getCalculatedUpfrontAmount();
                $recordedPayments = (float) $enrollment->payments()
                    ->where('vault_id', $vault->id)
                    ->where('status', 'completed')
                    ->sum('amount');
                if ($upfront > $recordedPayments) {
                    $todayEnrollmentPayments += ($upfront - $recordedPayments);
                }
            }

            $todayTotalStudentPayments = $todayStudentPaymentsLegacy + $todayPayments + $todayEnrollmentPayments;

            $todayExpenses = Expense::where('vault_id', $vault->id)
                ->whereDate('expense_date', $date)
                ->sum('amount');

            $todaySalaryPayments = SalaryPayment::where('vault_id', $vault->id)
                ->where('status', 'paid')
                ->whereDate('payment_date', $date)
                ->sum('amount');

            $todayTransfersIn = VaultTransfer::where('to_vault_id', $vault->id)
                ->where('status', 'completed')
                ->whereDate('transfer_date', $date)
                ->sum('amount');

            $todayTransfersOut = VaultTransfer::where('from_vault_id', $vault->id)
                ->where('status', 'completed')
                ->whereDate('transfer_date', $date)
                ->sum('amount');

            $todayTotalIncome = $todayRevenues + $todayTotalStudentPayments + $todayTransfersIn;

            $totalExpenses = $todayExpenses + $todaySalaryPayments;
            $closingBalance = $openingBalance + $todayTotalIncome - $totalExpenses - $todayTransfersOut;

            $dailyData[] = [
                'vault' => $vault,
                'opening_balance' => $openingBalance,
                'revenues' => $todayRevenues,
                'student_payments' => $todayTotalStudentPayments,
                'total_income' => $todayTotalIncome,
                'expenses' => $todayExpenses,
                'salary_payments' => $todaySalaryPayments,
                'total_expenses' => $totalExpenses,
                'net_movement' => $todayTotalIncome - $totalExpenses - $todayTransfersOut,
                'closing_balance' => $closingBalance,
            ];
        }

        $tenants = [];
        if ($user->is_super_admin && is_null($user->tenant_id)) {
            $tenants = Tenant::query()->orderBy('name')->get();
        }

        return [
            'date' => $date,
            'vaults' => $allVaults,
            'dailyData' => $dailyData,
            'tenants' => $tenants,
        ];
    }

    private function calculateVaultBalanceUntilDate(Vault $vault, Carbon $date): float
    {
        $asOf = $date->toDateString();

        $revenues = Revenue::where('vault_id', $vault->id)
            ->whereNull('payment_id')
            ->whereDate('revenue_date', '<=', $asOf)
            ->sum('amount');

        $studentPayments = 0;
        if (\Schema::hasTable('student_payments')) {
            $studentPayments = StudentPayment::where('vault_id', $vault->id)
                ->whereDate('payment_date', '<=', $asOf)
                ->where('status', 'completed')
                ->sum('net_amount');
        }

        $enrollmentPayments = 0.0;
        $asOfEnrollments = Enrollment::where('vault_id', $vault->id)
            ->where(function ($query) use ($asOf) {
                $query->whereDate('enrollment_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('enrollment_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->get();

        foreach ($asOfEnrollments as $enrollment) {
            $upfront = $enrollment->getCalculatedUpfrontAmount();
            $recordedPayments = (float) $enrollment->payments()
                ->where('vault_id', $vault->id)
                ->where('status', 'completed')
                ->whereDate('payment_date', '<=', $asOf)
                ->sum('amount');
            if ($upfront > $recordedPayments) {
                $enrollmentPayments += ($upfront - $recordedPayments);
            }
        }

        $payments = Payment::where('vault_id', $vault->id)
            ->where(function ($query) use ($asOf) {
                $query->whereDate('payment_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('payment_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->where('status', 'completed')
            ->sum('amount');

        $expenses = Expense::where('vault_id', $vault->id)
            ->whereDate('expense_date', '<=', $asOf)
            ->sum('amount');

        $salaryPayments = SalaryPayment::where('vault_id', $vault->id)
            ->where('status', 'paid')
            ->where(function ($query) use ($asOf) {
                $query->whereDate('payment_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('payment_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->sum('amount');

        $transfersIn = VaultTransfer::where('to_vault_id', $vault->id)
            ->where('status', 'completed')
            ->where(function ($query) use ($asOf) {
                $query->whereDate('transfer_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('transfer_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->sum('amount');

        $transfersOut = VaultTransfer::where('from_vault_id', $vault->id)
            ->where('status', 'completed')
            ->where(function ($query) use ($asOf) {
                $query->whereDate('transfer_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('transfer_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->sum('amount');

        return (float) $vault->opening_balance
            + (float) $revenues
            + (float) $studentPayments
            + (float) $enrollmentPayments
            + (float) $payments
            + (float) $transfersIn
            - (float) $expenses
            - (float) $salaryPayments
            - (float) $transfersOut;
    }
}
