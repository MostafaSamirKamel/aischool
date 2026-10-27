<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        $student = \App\Services\Parent\ParentStudentAccessService::getSelectedStudent();
        
        if (!$student) {
            return [
                Stat::make('عدد الدورات', '0'),
                Stat::make('نسبة الحضور', '0%'),
                Stat::make('الواجبات المعلقة', '0'),
            ];
        }

        $totalCourses = $student->enrollments()->count();
        
        // Calculate attendance rate (check new session records first, fallback to legacy attendances)
        $totalAttendance = $student->attendanceRecords()->count();
        if ($totalAttendance > 0) {
            $presentCount = $student->attendanceRecords()
                ->whereIn('status', ['present', 'late'])
                ->count();
            $attendanceRate = ($presentCount * 100 / $totalAttendance);
        } else {
            $legacyTotal = \App\Models\Attendance::where('student_id', $student->id)->count();
            $legacyPresent = \App\Models\Attendance::where('student_id', $student->id)
                ->whereIn('status', ['present', 'late'])
                ->count();
            $attendanceRate = $legacyTotal > 0 ? ($legacyPresent * 100 / $legacyTotal) : 0;
        }
        
        // Count pending assignments
        $pendingAssignments = $student->assignmentSubmissions()
            ->whereNull('submitted_at')
            ->count();

        return [
            Stat::make('عدد الدورات', $totalCourses)
                ->description('الدورات المسجلة')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
                
            Stat::make('نسبة الحضور', number_format($attendanceRate, 1) . '%')
                ->description('معدل الحضور')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger')),
                
            Stat::make('الواجبات المعلقة', $pendingAssignments)
                ->description('واجبات غير مسلمة')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($pendingAssignments > 0 ? 'warning' : 'success'),
        ];
    }
}
