<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use App\Models\EnrollmentCourse;
use App\Models\LearningResource;
use Illuminate\Support\Collection;

class StudentLearningResources extends Page
{
    protected string $view = 'filament.student.pages.student-learning-resources';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';
    
    protected static ?int $navigationSort = 5;

    public ?int $selectedCourseId = null;
    public Collection $courses;
    public Collection $resources;

    public static function getNavigationLabel(): string
    {
        return __('المصادر التعليمية');
    }

    public function getTitle(): string
    {
        return __('المصادر التعليمية');
    }

    public function getHeading(): string
    {
        return __('المصادر التعليمية');
    }

    public function mount(): void
    {
        $this->loadCourses();
        $this->resources = collect();
    }

    public function loadCourses(): void
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            $this->courses = collect();
            return;
        }

        // Get all student courses from enrollment_courses (active + completed)
        $enrollmentCourses = EnrollmentCourse::query()
            ->whereIn('status', ['active', 'completed'])
            ->whereHas('enrollment', fn($q) =>
                $q->where('student_id', $student->id)
                    ->whereIn('status', ['active', 'completed'])
            )
            ->with(['course', 'group.instructor.user', 'enrollment'])
            ->get();

        $this->courses = $enrollmentCourses
            ->groupBy('course_id')
            ->map(function ($items, $courseId) {
                $first = $items->first();

                $status = $items->contains(fn($item) => $item->status === 'active')
                    ? 'active'
                    : 'completed';

                return (object) [
                    'id' => (int) $courseId,
                    'name' => $first?->course?->name,
                    'description' => $first?->course?->description,
                    'image' => $first?->course?->image,
                    'status' => $status,
                    'group_ids' => $items->pluck('group_id')->filter()->unique()->values()->toArray(),
                    'enrollment_date' => $items->pluck('enrollment.enrollment_date')->filter()->sort()->first(),
                ];
            })
            ->values();

        if ($this->selectedCourseId) {
            $this->loadResourcesForCourse($this->selectedCourseId);
        }
    }

    public function loadResourcesForCourse(int $courseId): void
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            $this->resources = collect();
            return;
        }

        $course = $this->courses->firstWhere('id', $courseId);
        
        if (!$course) {
            $this->resources = collect();
            return;
        }

        // Get groups for this course for this student
        $groupIds = $course->group_ids ?? [];

        // Get learning resources based on visibility
        $this->resources = LearningResource::query()
            ->where('course_id', $courseId)
            ->where('is_active', true)
            ->where('tenant_id', $student->tenant_id)
            ->where(function ($query) use ($groupIds) {
                $query->where('visibility', 'all')
                      ->orWhere(function ($q) {
                          $q->where('visibility', 'course');
                      })
                      ->orWhere(function ($q) use ($groupIds) {
                          $q->where('visibility', 'group')
                            ->whereIn('group_id', $groupIds);
                      });
            })
            ->with(['course', 'group', 'sessionSchedule'])
            ->orderBy('session_number', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function selectCourse(int $courseId): void
    {
        $this->selectedCourseId = $courseId;
        $this->loadResourcesForCourse($courseId);
    }

    public function getSelectedCourseName(): ?string
    {
        $course = $this->courses->firstWhere('id', $this->selectedCourseId);
        return $course?->name;
    }

    public function getSelectedCourseStatus(): ?string
    {
        $course = $this->courses->firstWhere('id', $this->selectedCourseId);
        
        if (!$course) {
            return null;
        }

        $normalized = strtolower(trim((string) $course->status));
        return match ($normalized) {
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'completed' => 'مكتمل',
            'dropped' => 'منسحب',
            default => $course->status,
        };
    }

    public function getResourceTypeLabel(string $type): string
    {
        return match ($type) {
            'pdf' => 'PDF',
            'video' => 'فيديو',
            'presentation' => 'عرض توضيحي',
            'document' => 'مستند',
            'image' => 'صورة',
            'audio' => 'صوت',
            'link' => 'رابط خارجي',
            default => 'ملف',
        };
    }

    public function getResourceTypeIcon(string $type): string
    {
        return match ($type) {
            'pdf' => 'heroicon-o-document-text',
            'video' => 'heroicon-o-video-camera',
            'presentation' => 'heroicon-o-presentation-chart-bar',
            'document' => 'heroicon-o-document',
            'image' => 'heroicon-o-photo',
            'audio' => 'heroicon-o-musical-note',
            'link' => 'heroicon-o-link',
            default => 'heroicon-o-document',
        };
    }

    public function getResourceTypeColor(string $type): string
    {
        return match ($type) {
            'pdf' => 'danger',
            'video' => 'info',
            'presentation' => 'warning',
            'document' => 'primary',
            'link' => 'success',
            'image' => 'secondary',
            'audio' => 'purple',
            default => 'gray',
        };
    }
}
