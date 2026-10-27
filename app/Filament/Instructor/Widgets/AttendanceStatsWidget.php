<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStatsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $instructor = $user->instructor;
        if (!$instructor) {
            return [];
        }
        $tenantId = $user->tenant_id;
        $instructorId = $instructor->id;
        
        // Get current month sessions
        $currentMonthSessions = AttendanceSession::whereHas('group', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        })
            ->where('tenant_id', $tenantId)
        ->whereMonth('attendance_date', now()->month)
        ->whereYear('attendance_date', now()->year)
        ->count();
        
        // Get today's sessions
        $todaySessions = AttendanceSession::whereHas('group', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        })
            ->where('tenant_id', $tenantId)
        ->whereDate('attendance_date', today())
        ->count();
        
        // Get average attendance rate for this month
        $sessions = AttendanceSession::whereHas('group', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        })
            ->where('tenant_id', $tenantId)
        ->whereMonth('attendance_date', now()->month)
        ->whereYear('attendance_date', now()->year)
        ->withCount('records')
        ->with(['records' => function ($query) {
            $query->whereIn('status', ['present', 'late']);
        }])
        ->get();
        
        $totalStudents = $sessions->sum('records_count');
        $presentStudents = $sessions->sum(fn ($session) => $session->records->count());
        $avgAttendanceRate = $totalStudents > 0 ? round(($presentStudents / $totalStudents) * 100, 1) : 0;
        
        // Get most absent students count (absent more than 20% of sessions)
        $absenceThreshold = $currentMonthSessions * 0.2;
        $atRiskStudents = AttendanceRecord::whereHas('session.group', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        })
        ->whereHas('session', function ($query) {
            $query->whereMonth('attendance_date', now()->month)
                  ->whereYear('attendance_date', now()->year);
        })
        ->where('status', 'absent')
        ->select('student_id')
        ->groupBy('student_id')
        ->havingRaw('COUNT(*) > ?', [$absenceThreshold])
        ->count();

        return [
            Stat::make('سيشنات هذا الشهر', $currentMonthSessions)
                ->description('إجمالي الجلسات المسجلة')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary')
                ->chart($this->getMonthlySessionsChart($instructorId, $tenantId)),
            
            Stat::make('جلسات اليوم', $todaySessions)
                ->description($todaySessions > 0 ? 'تم التسجيل' : 'لم يتم التسجيل بعد')
                ->descriptionIcon('heroicon-m-clock')
                ->color($todaySessions > 0 ? 'success' : 'warning'),
            
            Stat::make('متوسط الحضور', $avgAttendanceRate . '%')
                ->description('نسبة الحضور للشهر الحالي')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($this->getAttendanceColor($avgAttendanceRate)),
            
            Stat::make('طلاب في خطر', $atRiskStudents)
                ->description('تجاوزوا 20% غياب')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($atRiskStudents > 0 ? 'danger' : 'success'),
        ];
    }

    protected function getMonthlySessionsChart(int $instructorId, int $tenantId): array
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = AttendanceSession::whereHas('group', function ($query) use ($instructorId) {
                $query->where('instructor_id', $instructorId);
            })
                ->where('tenant_id', $tenantId)
            ->whereDate('attendance_date', $date)
            ->count();
            
            $data[] = $count;
        }
        
        return $data;
    }

    protected function getAttendanceColor(float $rate): string
    {
        if ($rate >= 85) return 'success';
        if ($rate >= 70) return 'warning';
        return 'danger';
    }
}
