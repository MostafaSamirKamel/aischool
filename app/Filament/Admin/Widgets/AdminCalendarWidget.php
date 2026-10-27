<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Exam;
use App\Models\Assignment;
use Filament\Widgets\Widget;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class AdminCalendarWidget extends Widget
{
    use HasWidgetShield;
    protected string $view = 'filament.admin.widgets.calendar-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = -1;

    public function getEvents(): array
    {
        $user = Auth::user();
        $events = [];
        
        // تصفية بناءً على الفرع
        $query = null;
        
        if ($user->hasRole('branch_manager')) {
            // مدير الفرع يرى فقط فرعه
            $tenantId = $user->tenant_id;
            $query = function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            };
        }
        // super_admin يرى كل شيء
        
        // جميع الجلسات
        $sessionsQuery = AttendanceSession::query()
            ->whereBetween('attendance_date', [now()->subMonth(), now()->addMonths(2)])
            ->with(['group.instructor']);
            
        if ($query && $user->hasRole('branch_manager')) {
            $sessionsQuery->whereHas('group', $query);
        }
        
        $sessions = $sessionsQuery->get();
            
        foreach ($sessions as $session) {
            // استخدام attendance_date بدلاً من session_date
            $sessionDateTime = $session->attendance_date;

            $events[] = [
                'title' => '📊 جلسة: ' . $session->group->name,
                'start' => $sessionDateTime,
                'backgroundColor' => '#10b981',
                'borderColor' => '#059669',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'session',
                    'group' => $session->group->name,
                    'instructor' => $session->group?->instructor?->user?->name ?? 'غير محدد',
                    'branch' => $session->group->tenant->name ?? 'الفرع الرئيسي',
                    'status' => $session->status,
                ],
            ];
        }
        
        // جميع الامتحانات
        $examsQuery = Exam::query()
            ->whereBetween('exam_date', [now()->subMonth(), now()->addMonths(2)])
            ->with(['group']);
            
        if ($query && $user->hasRole('branch_manager')) {
            $examsQuery->whereHas('group', $query);
        }
        
        $exams = $examsQuery->get();
        
        foreach ($exams as $exam) {
            $events[] = [
                'title' => '📝 امتحان: ' . $exam->title,
                'start' => $exam->exam_date,
                'backgroundColor' => '#ef4444',
                'borderColor' => '#dc2626',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'exam',
                    'group' => $exam->group->name,
                    'branch' => $exam->group->tenant->name ?? 'الفرع الرئيسي',
                    'duration' => $exam->duration . ' دقيقة',
                    'total_marks' => $exam->total_marks,
                ],
            ];
        }
        
        // جميع الواجبات
        $assignmentsQuery = Assignment::query()
            ->whereBetween('due_date', [now()->subMonth(), now()->addMonths(2)])
            ->with(['group']);
            
        if ($query && $user->hasRole('branch_manager')) {
            $assignmentsQuery->whereHas('group', $query);
        }
        
        $assignments = $assignmentsQuery->get();
        
        foreach ($assignments as $assignment) {
            $events[] = [
                'title' => '📚 واجب: ' . $assignment->title,
                'start' => $assignment->due_date,
                'backgroundColor' => '#f59e0b',
                'borderColor' => '#d97706',
                'textColor' => '#ffffff',
                'allDay' => true,
                'extendedProps' => [
                    'type' => 'assignment',
                    'group' => $assignment->group->name,
                    'branch' => $assignment->group->tenant->name ?? 'الفرع الرئيسي',
                    'max_score' => $assignment->max_score,
                ],
            ];
        }
        
        return $events;
    }
}
