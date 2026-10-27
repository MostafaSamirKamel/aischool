<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use Livewire\Component;

class StudentCoursesModal extends Component
{
    public $enrollmentId;
    public $enrollment;

    public function mount($enrollmentId)
    {
        $this->enrollmentId = $enrollmentId;
        $this->loadEnrollment();
    }

    public function loadEnrollment()
    {
        $this->enrollment = Enrollment::with([
            'enrollmentCourses' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'enrollmentCourses.course' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'enrollmentCourses.course.sessionSchedules',
            'enrollmentCourses.group.instructor.user'
        ])
        ->find($this->enrollmentId);
    }

    public function assignGroup($enrollmentCourseId, $groupId)
    {
        \Log::info('assignGroup called', [
            'enrollmentCourseId' => $enrollmentCourseId,
            'groupId' => $groupId
        ]);

        try {
            $enrollmentCourse = EnrollmentCourse::withoutGlobalScopes()->find($enrollmentCourseId);

            if ($enrollmentCourse) {
                $enrollmentCourse->group_id = $groupId;
                $enrollmentCourse->status = 'active';
                $enrollmentCourse->save();

                \Filament\Notifications\Notification::make()
                    ->title('تم تعيين المجموعة بنجاح')
                    ->body('تم تفعيل الكورس بنجاح')
                    ->success()
                    ->send();

                // Reload enrollment data
                $this->loadEnrollment();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('خطأ')
                    ->body('حدث خطأ أثناء تعيين المجموعة')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.student-courses-modal');
    }
}
