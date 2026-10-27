<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Group;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InstructorStatsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            return [];
        }
        $instructorId = $instructor->id;

        // عدد المجموعات النشطة
        $activeGroups = Group::where('instructor_id', $instructorId)
            ->where('status', 'active')
            ->count();

        // عدد الطلاب
        $studentsCount = Student::query()
            ->whereHas('enrollments.enrollmentCourses', function ($query) use ($instructorId) {
                $query->whereHas('group', function ($q) use ($instructorId) {
                    $q->where('instructor_id', $instructorId)
                        ->where('groups.status', 'active');
                });
            })
            ->distinct()
            ->count('students.id');

        // عدد الواجبات المنشورة
        $activeAssignments = Assignment::where('instructor_id', $instructorId)
            ->where('status', 'published')
            ->where('due_date', '>', now())
            ->count();

        // التسليمات قيد المراجعة
        $pendingSubmissions = AssignmentSubmission::query()
            ->whereHas('assignment', function($query) use ($instructorId) {
                $query->where('instructor_id', $instructorId);
            })
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('المجموعات النشطة', $activeGroups)
                ->description('المجموعات التي تدرسها حالياً')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart($this->getGroupsChart()),

            Stat::make('إجمالي الطلاب', $studentsCount)
                ->description('الطلاب في جميع مجموعاتك')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('info'),

            Stat::make('الواجبات النشطة', $activeAssignments)
                ->description('الواجبات المنشورة حالياً')
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('warning'),

            Stat::make('تسليمات قيد المراجعة', $pendingSubmissions)
                ->description('تحتاج للتصحيح')
                ->descriptionIcon('heroicon-o-document-check')
                ->color($pendingSubmissions > 0 ? 'danger' : 'success'),
        ];
    }

    protected function getGroupsChart(): array
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            return [];
        }
        $instructorId = $instructor->id;
        
        // آخر 6 شهور من عدد المجموعات
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = Group::where('instructor_id', $instructorId)
                ->whereMonth('start_date', $month->month)
                ->whereYear('start_date', $month->year)
                ->count();
            $data[] = $count;
        }
        
        return $data;
    }
}
