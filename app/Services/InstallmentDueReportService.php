<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\Vault;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class InstallmentDueReportService
{
    /**
     * Get installments due today for a tenant.
     */
    public function getTodayInstallments(?int $tenantId = null): Collection
    {
        $today = now()->format('Y-m-d');

        return Installment::with(['enrollment.student.user'])
            ->when($tenantId, fn(Builder $q) => $q->where('tenant_id', $tenantId))
            ->whereDate('due_date', $today)
            ->whereNotIn('status', ['cancelled', 'waived'])
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Get weekly installments query for a tenant.
     */
    public function getWeekInstallmentsQuery(?int $tenantId = null, string $filter = 'all'): Builder
    {
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = now()->endOfWeek()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $query = Installment::with(['enrollment.student.user'])
            ->when($tenantId, fn(Builder $q) => $q->where('tenant_id', $tenantId))
            ->whereDate('due_date', '>=', $startOfWeek)
            ->whereDate('due_date', '<=', $endOfWeek)
            ->whereNotIn('status', ['cancelled', 'waived'])
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'));

        return $this->applyFilter($query, $filter, $today);
    }

    /**
     * Get weekly installments grouped by day for display.
     */
    public function getWeekInstallmentsByDay(?int $tenantId = null, string $filter = 'all'): array
    {
        $installments = $this->getWeekInstallmentsQuery($tenantId, $filter)
            ->orderBy('due_date', 'asc')
            ->orderBy('installment_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $days = [];
        foreach ($installments as $installment) {
            $day = Carbon::parse($installment->due_date)->translatedFormat('l Y-m-d');
            $days[$day][] = $installment;
        }

        return $days;
    }

    /**
     * Calculate weekly installment statistics.
     */
    public function getWeekStats(?int $tenantId = null): array
    {
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = now()->endOfWeek()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $baseQuery = Installment::when($tenantId, fn(Builder $q) => $q->where('tenant_id', $tenantId))
            ->whereDate('due_date', '>=', $startOfWeek)
            ->whereDate('due_date', '<=', $endOfWeek)
            ->whereNotIn('status', ['cancelled', 'waived'])
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'));

        return $this->calculatePeriodStats($baseQuery, $today);
    }

    /**
     * Get monthly installments query for a tenant.
     */
    public function getMonthInstallmentsQuery(?int $tenantId = null, string $filter = 'all'): Builder
    {
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $query = Installment::with(['enrollment.student.user'])
            ->when($tenantId, fn(Builder $q) => $q->where('tenant_id', $tenantId))
            ->whereDate('due_date', '>=', $startOfMonth)
            ->whereDate('due_date', '<=', $endOfMonth)
            ->whereNotIn('status', ['cancelled', 'waived'])
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'));

        return $this->applyFilter($query, $filter, $today);
    }

    /**
     * Get monthly installments collection for a tenant.
     */
    public function getMonthInstallments(?int $tenantId = null, string $filter = 'all'): Collection
    {
        return $this->getMonthInstallmentsQuery($tenantId, $filter)
            ->orderBy('due_date', 'asc')
            ->orderBy('installment_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Calculate monthly installment statistics.
     */
    public function getMonthStats(?int $tenantId = null): array
    {
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $baseQuery = Installment::when($tenantId, fn(Builder $q) => $q->where('tenant_id', $tenantId))
            ->whereDate('due_date', '>=', $startOfMonth)
            ->whereDate('due_date', '<=', $endOfMonth)
            ->whereNotIn('status', ['cancelled', 'waived'])
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'));

        return $this->calculatePeriodStats($baseQuery, $today);
    }

    /**
     * Apply status / period filter to an installment query.
     */
    protected function applyFilter(Builder $query, string $filter, string $today): Builder
    {
        return match ($filter) {
            'overdue' => $query->whereDate('due_date', '<', $today)
                ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
                ->whereColumn('paid_amount', '<', 'amount'),
            'today' => $query->whereDate('due_date', '=', $today)
                ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
                ->whereColumn('paid_amount', '<', 'amount'),
            'upcoming' => $query->whereDate('due_date', '>', $today)
                ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
                ->whereColumn('paid_amount', '<', 'amount'),
            'paid' => $query->where(fn($q) => $q->where('status', 'paid')->orWhereColumn('paid_amount', '>=', 'amount')),
            default => $query,
        };
    }

    /**
     * Calculate unified period stats (Total, Overdue, Today, Upcoming).
     * Distinguishes total volume vs genuine outstanding overdue debt.
     */
    protected function calculatePeriodStats(Builder $baseQuery, string $today): array
    {
        // Total volume
        $totalCount = (clone $baseQuery)->count();
        $totalAmount = (float) (clone $baseQuery)->sum('amount');

        // Overdue (due before today and still owes balance): amount is genuine remaining debt
        $overdueQuery = (clone $baseQuery)
            ->whereDate('due_date', '<', $today)
            ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
            ->whereColumn('paid_amount', '<', 'amount');

        $overdueCount = $overdueQuery->count();
        $overdueAmount = (float) $overdueQuery
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as remaining')
            ->value('remaining');

        // Today (collectible outstanding due today)
        $todayQuery = (clone $baseQuery)
            ->whereDate('due_date', '=', $today)
            ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
            ->whereColumn('paid_amount', '<', 'amount');
        $todayCount = $todayQuery->count();
        $todayAmount = (float) $todayQuery
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as remaining')
            ->value('remaining');

        // Upcoming (collectible outstanding upcoming)
        $upcomingQuery = (clone $baseQuery)
            ->whereDate('due_date', '>', $today)
            ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
            ->whereColumn('paid_amount', '<', 'amount');
        $upcomingCount = $upcomingQuery->count();
        $upcomingAmount = (float) $upcomingQuery
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as remaining')
            ->value('remaining');

        return [
            'total' => ['count' => $totalCount, 'amount' => $totalAmount],
            'overdue' => ['count' => $overdueCount, 'amount' => $overdueAmount],
            'today' => ['count' => $todayCount, 'amount' => $todayAmount],
            'upcoming' => ['count' => $upcomingCount, 'amount' => $upcomingAmount],
        ];
    }

    /**
     * Get active vaults formatted with balance for dropdown selection.
     */
    public function getActiveVaults(?int $tenantId = null): array
    {
        return Vault::when($tenantId, fn(Builder $q) => $q->where('tenant_id', $tenantId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn(Vault $v) => [$v->id => $v->getDisplayNameWithBalance()])
            ->toArray();
    }
}
