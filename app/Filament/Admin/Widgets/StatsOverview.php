<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Group;
use App\Models\Vault;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class StatsOverview extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $isSuperAdmin = auth()->user()->is_super_admin;

        // إحصائيات الطلاب
        $studentsQuery = Student::query();
        if (!$isSuperAdmin && $tenantId) {
            $studentsQuery->where('tenant_id', $tenantId);
        }
        $totalStudents = (clone $studentsQuery)->count();
        $activeStudents = (clone $studentsQuery)->where('status', 'active')->count();

        // إحصائيات التسجيلات
        $enrollmentsQuery = Enrollment::query();
        if (!$isSuperAdmin && $tenantId) {
            $enrollmentsQuery->where('tenant_id', $tenantId);
        }
        $totalEnrollments = (clone $enrollmentsQuery)->count();
        $activeEnrollments = (clone $enrollmentsQuery)->where('status', 'active')->count();

        // إحصائيات المدفوعات
        $paymentsQuery = Payment::query();
        if (!$isSuperAdmin && $tenantId) {
            $paymentsQuery->where('tenant_id', $tenantId);
        }
        $totalRevenue = (clone $paymentsQuery)->where('status', 'completed')->sum('amount');
        $thisMonthRevenue = (clone $paymentsQuery)->where('status', 'completed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        // إحصائيات المجموعات
        $groupsQuery = Group::query();
        if (!$isSuperAdmin && $tenantId) {
            $groupsQuery->where('tenant_id', $tenantId);
        }
        $totalGroups = (clone $groupsQuery)->count();
        $activeGroups = (clone $groupsQuery)->where('status', 'active')->count();

        $studentChart = $this->getStudentTrend($tenantId, $isSuperAdmin);
        $enrollmentChart = $this->getEnrollmentTrend($tenantId, $isSuperAdmin);
        $groupChart = $this->getGroupTrend($tenantId, $isSuperAdmin);
        $paymentChart = $this->getPaymentTrend($tenantId, $isSuperAdmin);

        $stats = [
            Stat::make('إجمالي الطلاب', $totalStudents)
                ->description("{$activeStudents} طالب نشط")
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart(!empty($studentChart) ? $studentChart : []),

            Stat::make('التسجيلات', $totalEnrollments)
                ->description("{$activeEnrollments} تسجيل نشط")
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('info')
                ->chart(!empty($enrollmentChart) ? $enrollmentChart : []),

            Stat::make('المجموعات', $totalGroups)
                ->description("{$activeGroups} مجموعة نشطة")
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('primary')
                ->chart(!empty($groupChart) ? $groupChart : []),
        ];

        if ($isSuperAdmin || (auth()->check() && auth()->user()->can('view_vault_balances'))) {
            $vaultsQuery = Vault::query()->where('is_active', true);
            if (!$isSuperAdmin && $tenantId) {
                $vaultsQuery->where('tenant_id', $tenantId);
            }
            $totalVaultBalance = $vaultsQuery->get()->sum(fn(Vault $vault) => $vault->getCurrentBalance());

            $stats[] = Stat::make('إجمالي رصيد الخزائن', number_format($totalVaultBalance, 2) . ' ج.م')
                ->description('رصيد جميع الخزائن النشطة')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart(!empty($paymentChart) ? $paymentChart : []);
        }

        return $stats;
    }

    protected function getStudentTrend(?int $tenantId, bool $isSuperAdmin): array
    {
        $query = Student::query();
        if (!$isSuperAdmin && $tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        $trend = Trend::query($query)
            ->between(start: now()->subMonths(5)->startOfMonth(), end: now()->endOfMonth())
            ->perMonth()
            ->count();
        $data = $trend->map(fn (TrendValue $val) => (int) $val->aggregate)->toArray();
        return count(array_filter($data)) > 0 ? $data : [];
    }

    protected function getEnrollmentTrend(?int $tenantId, bool $isSuperAdmin): array
    {
        $query = Enrollment::query();
        if (!$isSuperAdmin && $tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        $trend = Trend::query($query)
            ->between(start: now()->subMonths(5)->startOfMonth(), end: now()->endOfMonth())
            ->perMonth()
            ->count();
        $data = $trend->map(fn (TrendValue $val) => (int) $val->aggregate)->toArray();
        return count(array_filter($data)) > 0 ? $data : [];
    }

    protected function getGroupTrend(?int $tenantId, bool $isSuperAdmin): array
    {
        $query = Group::query();
        if (!$isSuperAdmin && $tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        $trend = Trend::query($query)
            ->between(start: now()->subMonths(5)->startOfMonth(), end: now()->endOfMonth())
            ->perMonth()
            ->count();
        $data = $trend->map(fn (TrendValue $val) => (int) $val->aggregate)->toArray();
        return count(array_filter($data)) > 0 ? $data : [];
    }

    protected function getPaymentTrend(?int $tenantId, bool $isSuperAdmin): array
    {
        $query = Payment::query()->where('status', 'completed');
        if (!$isSuperAdmin && $tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        $trend = Trend::query($query)
            ->between(start: now()->subMonths(5)->startOfMonth(), end: now()->endOfMonth())
            ->perMonth()
            ->sum('amount');
        $data = $trend->map(fn (TrendValue $val) => (float) $val->aggregate)->toArray();
        return count(array_filter($data)) > 0 ? $data : [];
    }
}
