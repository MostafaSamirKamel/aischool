<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use App\Models\Exam;
use App\Models\Enrollment;
use App\Filament\Student\Pages\TakeExam;
use App\Filament\Student\Pages\ExamResult;

class MyExams extends Page
{
    protected string $view = 'filament.student.pages.my-exams';
    protected static ?string $title = 'امتحاناتي ونتائجي';
    protected static ?string $navigationLabel = 'امتحاناتي';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';
    protected static ?int $navigationSort = 6;

    public function getTitle(): string
    {
        return __('امتحاناتي ونتائجي');
    }

    public function getHeading(): string
    {
        return __('امتحاناتي ونتائجي');
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

        // Get exams for these groups
        $exams = Exam::query()
            ->whereIn('group_id', $groupIds)
            ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
            ->with([
                'group',
                'attempts' => function ($q) use ($student) {
                    $q->where('student_id', $student?->id);
                }
            ])
            ->orderBy('exam_date', 'desc')
            ->get()
            ->map(function($exam) {
                $attempt = $exam->attempts->first();
                
                // Determine status
                $status = 'available'; // Default
                if (!$attempt) {
                    if ($exam->exam_date && $exam->exam_date->isFuture()) {
                        $status = 'upcoming';
                    } elseif ($exam->hasEnded()) {
                        $status = 'expired';
                    } else {
                        $status = 'available';
                    }
                } else {
                    if ($attempt->completed_at) {
                        $status = 'completed';
                    } else {
                        $status = 'available'; // in-progress
                    }
                }

                // Determine actions
                $canTake = false;
                $actionUrl = '#';
                $actionLabel = 'التفاصيل';
                $actionClass = 'btn-outline';

                if (!$attempt) {
                    if ($exam->isAvailableNow()) {
                        $canTake = true;
                        $actionUrl = TakeExam::getUrl(['exam' => $exam->id]);
                        $actionLabel = 'ابدأ الاختبار';
                        $actionClass = 'btn-primary';
                    }
                } else {
                    if ($attempt->completed_at) {
                        $actionUrl = ExamResult::getUrl(['attempt' => $attempt->id]);
                        $actionLabel = 'النتيجة';
                        $actionClass = 'btn-outline';
                    } else {
                        // in-progress
                        $canTake = true;
                        $actionUrl = TakeExam::getUrl(['exam' => $exam->id]);
                        $actionLabel = 'استكمال الاختبار';
                        $actionClass = 'btn-primary';
                    }
                }

                return [
                    'id' => $exam->id,
                    'title' => $exam->title ?? 'اختبار',
                    'course' => $exam->group?->name ?? 'مجموعة غير محددة',
                    'date' => $exam->exam_date ? $exam->exam_date->format('Y-m-d') : '-',
                    'duration' => $exam->duration,
                    'total' => $exam->total_marks,
                    'score' => $attempt && $attempt->completed_at ? $attempt->score : null,
                    'status' => $status, // upcoming, available, completed, expired
                    'actionUrl' => $actionUrl,
                    'actionLabel' => $actionLabel,
                    'actionClass' => $actionClass,
                ];
            });

        return [
            'student' => $student,
            'exams' => $exams,
        ];
    }
}

