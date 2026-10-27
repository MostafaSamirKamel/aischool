<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\AssignmentSubmission;

class MyAssignments extends Page
{
    protected string $view = 'filament.student.pages.my-assignments';
    protected static ?string $title = 'واجباتي والتسليمات';
    protected static ?string $navigationLabel = 'واجباتي';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?int $navigationSort = 7;

    public function getTitle(): string
    {
        return __('واجباتي والتسليمات');
    }

    public function getHeading(): string
    {
        return __('واجباتي والتسليمات');
    }

    protected function getViewData(): array
    {
        $student = auth()->user()->student;

        // Get all group IDs the student is enrolled in
        $groupIds = Enrollment::where('student_id', $student?->id)
            ->where('status', 'active')
            ->with('enrollmentCourses')
            ->get()
            ->flatMap(fn($enrollment) => $enrollment->enrollmentCourses->pluck('group_id'))
            ->unique()
            ->filter();

        // Get assignments for these groups
        $assignments = Assignment::query()
            ->whereIn('group_id', $groupIds)
            ->with([
                'group',
                'submissions' => function ($q) use ($student) {
                    $q->where('student_id', $student?->id);
                }
            ])
            ->orderBy('due_date', 'desc')
            ->get()
            ->map(function($assignment) {
                $submission = $assignment->submissions->first();
                
                // Determine status
                $status = 'pending';
                if ($submission) {
                    if ($submission->status === 'graded') {
                        $status = 'graded';
                    } else {
                        $status = 'submitted';
                    }
                } else {
                    if ($assignment->due_date && $assignment->due_date->isPast()) {
                        $status = 'overdue';
                    } else {
                        $status = 'pending';
                    }
                }

                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title ?? 'واجب',
                    'course' => $assignment->group?->name ?? 'مجموعة غير محددة',
                    'due' => $assignment->due_date ? $assignment->due_date->format('Y-m-d') : '-',
                    'total' => $assignment->marks ?? 10,
                    'score' => $submission && $submission->status === 'graded' ? $submission->marks : null,
                    'status' => $status, // pending, submitted, graded, overdue
                    'has_file' => $assignment->file_path ? true : false,
                ];
            });

        return [
            'student' => $student,
            'assignments' => $assignments,
        ];
    }
}
