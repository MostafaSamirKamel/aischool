<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\AttendanceSession;
use App\Models\InstructorPayment;
use App\Models\Instructor;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstructorPaymentStatsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $tenantId = $user->tenant_id;

        $instructor = Instructor::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->first();
        
        if (!$instructor) {
            return [];
        }

        $latestPaymentIds = InstructorPayment::query()
            ->where('tenant_id', $tenantId)
            ->where('instructor_id', $instructor->id)
            ->selectRaw('MAX(id)')
            ->groupBy('period_start', 'period_end');

        // Use only the latest payment record for each period to avoid duplicate monthly snapshots.
        $paymentsQuery = InstructorPayment::query()
            ->whereIn('id', $latestPaymentIds);

        // Current month payment
        $currentMonth = (clone $paymentsQuery)
            ->currentMonth()
            ->first();

        // Total earnings (all paid)
        $totalEarnings = (clone $paymentsQuery)
            ->where('status', 'paid')
            ->sum('total_amount');

        // Total payments count
        $totalPayments = (clone $paymentsQuery)
            ->count();

        // Pending payments should reflect only unpaid amounts, not approved ones.
        $pendingAmount = (clone $paymentsQuery)
            ->where('status', 'pending')
            ->sum('total_amount');

        $pendingCount = (clone $paymentsQuery)
            ->where('status', 'pending')
            ->count();

        // Count actual recorded attendance sessions to avoid duplicated totals from payment snapshots.
        $sessionsThisMonth = AttendanceSession::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('group', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->whereNull('salary_payment_id')
            ->count();

        $totalSessions = AttendanceSession::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('group', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->count();

        // Last 6 months average
        $lastSixMonths = (clone $paymentsQuery)
            ->where('period_start', '>=', now()->subMonths(6))
            ->where('status', 'paid')
            ->get();

        $lastSixMonthsTotal = $lastSixMonths->sum('total_amount');
        $lastSixMonthsAvg = $lastSixMonths->count() > 0 ? $lastSixMonthsTotal / $lastSixMonths->count() : 0;

        return [
            Stat::make('أجر الشهر الحالي', number_format($currentMonth?->total_amount ?? 0, 0) . ' ج.م')
                ->description($currentMonth ? $currentMonth->status_label . ' • ' . $sessionsThisMonth . ' سيشن' : 'لم يتم الإنشاء بعد')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color($currentMonth ? match($currentMonth->status) {
                    'paid' => 'success',
                    'approved' => 'info',
                    'pending' => 'warning',
                    default => 'gray',
                } : 'gray')
                ->chart($this->getMonthlyChart($user->id, $tenantId)),

            Stat::make('إجمالي الأرباح', number_format($totalEarnings, 0) . ' ج.م')
                ->description($totalPayments . ' دفعة مدفوعة')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart($this->getMonthlyChart($user->id, $tenantId)),

            Stat::make('قيد الانتظار', number_format($pendingAmount, 0) . ' ج.م')
                ->description($pendingCount . ' دفعة لم يتم دفعها بعد')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('إجمالي السيشنات', number_format($totalSessions, 0))
                ->description('السيشنات المكتملة')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('info'),

            Stat::make('متوسط الأجر الشهري', number_format($lastSixMonthsAvg, 0) . ' ج.م')
                ->description('آخر 6 شهور')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('primary'),

            Stat::make('معدل السيشن', number_format($instructor->hourly_rate ?? 0, 0) . ' ج.م')
                ->description('السعر لكل سيشن')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('info'),
        ];
    }

    protected function getMonthlyChart(int $userId, int $tenantId): array
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            // Get instructor_id from user_id
            $instructorId = Instructor::where('user_id', $userId)
                ->where('tenant_id', $tenantId)
                ->value('id');

            $amount = InstructorPayment::query()
                ->where('tenant_id', $tenantId)
                ->where('instructor_id', $instructorId)
                ->where('period_start', '>=', $startOfMonth)
                ->where('period_end', '<=', $endOfMonth)
                ->where('status', 'paid')
                ->latest('id')
                ->value('total_amount');

            $data[] = $amount ? round($amount) : 0;
        }

        return $data;
    }
}
