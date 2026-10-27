<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\Assignment;
use App\Models\AttendanceSession;
use App\Models\Exam;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class InstructorCalendarWidget extends Widget
{
    use HasWidgetShield;
    protected string $view = 'filament.instructor.widgets.calendar-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = -2;

    public function getEvents(): array
    {
        $instructor = Auth::user()->instructor;
        
        if (!$instructor) {
            return [];
        }

        $events = [];
        
        // جلسات الحضور
        $sessions = AttendanceSession::whereHas('group', fn($q) => $q->where('instructor_id', $instructor->id))
            ->whereBetween('attendance_date', [now()->subMonth(), now()->addMonths(2)])
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
                    'id' => $session->id,
                    'group' => $session->group->name,
                    'status' => $session->status,
                ],
            ];
        }
        
        // الامتحانات
        $exams = Exam::whereHas('group', function($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
            ->whereBetween('exam_date', [now()->subMonth(), now()->addMonths(2)])
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
                    'id' => $exam->id,
                    'duration' => $exam->duration . ' دقيقة',
                    'total_marks' => $exam->total_marks,
                ],
            ];
        }
        
        // الواجبات
        $assignments = Assignment::whereHas('group', function($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->whereBetween('due_date', [now()->subMonth(), now()->addMonths(2)])
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
                    'id' => $assignment->id,
                    'max_score' => $assignment->max_score,
                ],
            ];
        }
        
        return $events;
    }
}
