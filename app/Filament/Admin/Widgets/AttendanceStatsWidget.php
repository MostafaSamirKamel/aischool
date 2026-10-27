<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AttendanceStatsWidget extends StatsOverviewWidget
{
    use HasWidgetShield;
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $isSuperAdmin = auth()->user()->is_super_admin;
        $today = today();

        // 1. Session attendance records for today
        $sessionQuery = AttendanceRecord::query()->whereHas('session', function ($q) use ($today, $tenantId, $isSuperAdmin) {
            $q->whereDate('attendance_date', $today);
            if (!$isSuperAdmin && $tenantId) {
                $q->where('tenant_id', $tenantId);
            }
        });
        $sessionCount = (clone $sessionQuery)->count();

        // 2. Legacy attendance records for today
        $legacyQuery = Attendance::query()->whereDate('date', $today);
        if (!$isSuperAdmin && $tenantId) {
            $legacyQuery->where('tenant_id', $tenantId);
        }
        $legacyCount = (clone $legacyQuery)->count();

        if ($sessionCount > 0) {
            $totalToday = $sessionCount;
            $presentToday = (clone $sessionQuery)->whereIn('status', ['present'])->count();
            $absentToday = (clone $sessionQuery)->where('status', 'absent')->count();
            $lateToday = (clone $sessionQuery)->where('status', 'late')->count();
        } else {
            $totalToday = $legacyCount;
            $presentToday = (clone $legacyQuery)->where('status', 'present')->count();
            $absentToday = (clone $legacyQuery)->where('status', 'absent')->count();
            $lateToday = (clone $legacyQuery)->where('status', 'late')->count();
        }

        $attendanceRate = $totalToday > 0 
            ? round((($presentToday + $lateToday) / $totalToday) * 100, 1) 
            : 0;

        // Weekly trends
        $weeklyPresentTrend = $this->getWeeklyPresentChart($tenantId, $isSuperAdmin);
        $weeklyAttendanceTrend = $this->getWeeklyAttendanceChart($tenantId, $isSuperAdmin);

        return [
            Stat::make('حضور اليوم', $presentToday)
                ->description('إجمالي المسجلين اليوم: ' . $totalToday)
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart(!empty($weeklyPresentTrend) ? $weeklyPresentTrend : []),
                
            Stat::make('غياب اليوم', $absentToday)
                ->description($absentToday > 0 ? 'يحتاج متابعة' : 'لا يوجد غياب')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color($absentToday > 0 ? 'danger' : 'success'),
                
            Stat::make('متأخرون اليوم', $lateToday)
                ->description($lateToday > 0 ? 'التأخير المسجل' : 'لا يوجد تأخير')
                ->descriptionIcon('heroicon-o-clock')
                ->color($lateToday > 0 ? 'warning' : 'success'),
                
            Stat::make('نسبة الحضور اليوم', $attendanceRate . '%')
                ->description('معدل الحضور لليوم الحالي')
                ->descriptionIcon($attendanceRate >= 80 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger'))
                ->chart(!empty($weeklyAttendanceTrend) ? $weeklyAttendanceTrend : []),
        ];
    }

    protected function getWeeklyPresentChart(?int $tenantId, bool $isSuperAdmin): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            
            $sessionCount = AttendanceRecord::whereHas('session', function ($q) use ($date, $tenantId, $isSuperAdmin) {
                $q->whereDate('attendance_date', $date);
                if (!$isSuperAdmin && $tenantId) {
                    $q->where('tenant_id', $tenantId);
                }
            })->count();

            if ($sessionCount > 0) {
                $present = AttendanceRecord::whereHas('session', function ($q) use ($date, $tenantId, $isSuperAdmin) {
                    $q->whereDate('attendance_date', $date);
                    if (!$isSuperAdmin && $tenantId) {
                        $q->where('tenant_id', $tenantId);
                    }
                })->whereIn('status', ['present', 'late'])->count();
                $data[] = $present;
            } else {
                $legacyQuery = Attendance::whereDate('date', $date);
                if (!$isSuperAdmin && $tenantId) {
                    $legacyQuery->where('tenant_id', $tenantId);
                }
                $present = $legacyQuery->whereIn('status', ['present', 'late'])->count();
                $data[] = $present;
            }
        }
        return count(array_filter($data)) > 0 ? $data : [];
    }

    protected function getWeeklyAttendanceChart(?int $tenantId, bool $isSuperAdmin): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            
            $sessionCount = AttendanceRecord::whereHas('session', function ($q) use ($date, $tenantId, $isSuperAdmin) {
                $q->whereDate('attendance_date', $date);
                if (!$isSuperAdmin && $tenantId) {
                    $q->where('tenant_id', $tenantId);
                }
            })->count();

            if ($sessionCount > 0) {
                $present = AttendanceRecord::whereHas('session', function ($q) use ($date, $tenantId, $isSuperAdmin) {
                    $q->whereDate('attendance_date', $date);
                    if (!$isSuperAdmin && $tenantId) {
                        $q->where('tenant_id', $tenantId);
                    }
                })->whereIn('status', ['present', 'late'])->count();
                $data[] = round(($present / $sessionCount) * 100);
            } else {
                $legacyTotalQuery = Attendance::whereDate('date', $date);
                $legacyPresentQuery = Attendance::whereDate('date', $date)->whereIn('status', ['present', 'late']);
                if (!$isSuperAdmin && $tenantId) {
                    $legacyTotalQuery->where('tenant_id', $tenantId);
                    $legacyPresentQuery->where('tenant_id', $tenantId);
                }
                $total = $legacyTotalQuery->count();
                $present = $legacyPresentQuery->count();
                $data[] = $total > 0 ? round(($present / $total) * 100) : 0;
            }
        }
        return count(array_filter($data)) > 0 ? $data : [];
    }
}
