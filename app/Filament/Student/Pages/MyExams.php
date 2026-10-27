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

        // Get exams for current groups or exams the student already attempted in previous groups
        $exams = Exam::query()
            ->where(function ($q) use ($groupIds, $student) {
                if ($groupIds->isNotEmpty()) {
                    $q->whereIn('group_id', $groupIds);
                }
                if ($student) {
                    $q->orWhereHas('attempts', fn($aq) => $aq->where('student_id', $student->id));
                }
            })
            ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
            ->with([
                'group',
                'attempts' => function ($q) use ($student) {
                    $q->where('student_id', $student?->id);
                }
            ])
            ->orderBy('exam_date', 'desc')
            ->get()
            ->map(function($exam) use ($student) {
                // Find all attempts by this student
                $attempts = $exam->attempts;
                $latestAttempt = $attempts->first();
                $officialAttempt = $attempts->where('is_official', true)->first() ?: $attempts->whereNotNull('completed_at')->sortByDesc('score')->first();
                
                // Check if an active retake grant exists
                $hasRetakeGrant = false;
                if ($student) {
                    $hasRetakeGrant = \App\Models\ExamRetakeGrant::where('exam_id', $exam->id)
                        ->where('student_id', $student->id)
                        ->where('status', 'active')
                        ->whereNull('consumed_at')
                        ->where('expires_at', '>', now())
                        ->exists();
                }

                // Determine status
                $status = 'available'; // Default
                if (!$latestAttempt) {
                    if ($exam->exam_date && $exam->exam_date->isFuture()) {
                        $status = 'upcoming';
                    } elseif ($exam->hasEnded()) {
                        $status = 'expired';
                    } else {
                        $status = 'available';
                    }
                } else {
                    if ($latestAttempt->completed_at) {
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

                if (!$latestAttempt) {
                    if ($exam->isAvailableNow()) {
                        $canTake = true;
                        $actionUrl = TakeExam::getUrl(['exam' => $exam->id]);
                        $actionLabel = 'ابدأ الاختبار';
                        $actionClass = 'btn-primary';
                    }
                } else {
                    if ($latestAttempt->completed_at) {
                        if ($hasRetakeGrant) {
                            $canTake = true;
                            $actionUrl = TakeExam::getUrl(['exam' => $exam->id]);
                            $actionLabel = 'إعادة الاختبار (مصرح بها)';
                            $actionClass = 'btn-warning';
                        } else {
                            $actionUrl = ExamResult::getUrl(['attempt' => $officialAttempt ? $officialAttempt->id : $latestAttempt->id]);
                            $actionLabel = 'النتيجة';
                            $actionClass = 'btn-outline';
                        }
                    } else {
                        // in-progress
                        $canTake = true;
                        $actionUrl = TakeExam::getUrl(['exam' => $exam->id]);
                        $actionLabel = 'استكمال الاختبار';
                        $actionClass = 'btn-primary';
                    }
                }

                // Official score is visible ONLY if exam is published
                $isPublished = (bool) $exam->published_at;
                $scoreDisplay = ($isPublished && $officialAttempt && $officialAttempt->completed_at) ? $officialAttempt->score : null;

                return [
                    'id' => $exam->id,
                    'title' => $exam->title ?? 'اختبار',
                    'course' => $exam->group?->name ?? 'مجموعة غير محددة',
                    'date' => $exam->exam_date ? $exam->exam_date->format('Y-m-d') : '-',
                    'duration' => $exam->duration,
                    'total' => $exam->total_marks,
                    'score' => $scoreDisplay,
                    'is_published' => $isPublished,
                    'has_retake_grant' => $hasRetakeGrant,
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

