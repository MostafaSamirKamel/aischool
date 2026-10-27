<?php

namespace App\Filament\Supervisor\Widgets;

use App\Models\Assignment;
use App\Models\AttendanceSession;
use App\Models\Exam;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SupervisorCalendarWidget extends Widget
{
    use HasWidgetShield;
    protected string $view = 'filament.supervisor.widgets.calendar-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = -2;

    public function getEvents(): array
    {
        $supervisor = Auth::user()->supervisor;
        
        if (!$supervisor) {
            return [];
        }

        $events = [];
        
        // المجموعات التي يشرف عليها
        $groupIds = $supervisor->groups()->pluck('id');
        
        // جلسات المجموعات
        $sessions = AttendanceSession::whereIn('group_id', $groupIds)
            ->whereBetween('attendance_date', [now()->subMonth(), now()->addMonths(2)])
            ->with(['group.instructor'])
            ->get();
            
        foreach ($sessions as $session) {
            $events[] = [
                'title' => '📊 جلسة: ' . $session->group->name,
                'start' => $session->attendance_date,
                'backgroundColor' => '#10b981',
                'borderColor' => '#059669',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'session',
                    'group' => $session->group->name,
                    'instructor' => $session->group?->instructor?->user?->name ?? 'غير محدد',
                    'status' => $session->status,
                ],
            ];
        }
        
        // امتحانات المجموعات
        $exams = Exam::whereIn('group_id', $groupIds)
            ->whereBetween('exam_date', [now()->subMonth(), now()->addMonths(2)])
            ->with(['group'])
            ->get();
        
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
                    'duration' => $exam->duration . ' دقيقة',
                    'total_marks' => $exam->total_marks,
                ],
            ];
        }
        
        // واجبات المجموعات
        $assignments = Assignment::whereIn('group_id', $groupIds)
            ->whereBetween('due_date', [now()->subMonth(), now()->addMonths(2)])
            ->with(['group'])
            ->get();
        
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
                    'max_score' => $assignment->max_score,
                ],
            ];
        }
        
        return $events;
    }
}
