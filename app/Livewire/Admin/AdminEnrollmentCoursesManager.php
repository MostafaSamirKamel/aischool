<?php

namespace App\Livewire\Admin;

use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Group;
use Livewire\Component;

class AdminEnrollmentCoursesManager extends Component
{
    public $enrollmentId;
    public $enrollment;
    public $selectedCourseId = null;
    public $availableGroups = [];
    public $showGroupModal = false;
    public $selectedGroups = [];

    public function mount($enrollmentId)
    {
        $this->enrollmentId = $enrollmentId;
        $this->loadEnrollment();
        $this->loadSelectedGroups();
    }

    public function loadSelectedGroups()
    {
        foreach ($this->enrollment->enrollmentCourses as $enrollmentCourse) {
            $this->selectedGroups[$enrollmentCourse->id] = $enrollmentCourse->group_id;
        }
    }

    public function loadEnrollment()
    {
        $this->enrollment = Enrollment::with(['enrollmentCourses.course', 'enrollmentCourses.group.instructor.user'])
            ->findOrFail($this->enrollmentId);
    }

    public function openGroupSelection($enrollmentCourseId)
    {
        $enrollmentCourse = EnrollmentCourse::with('course')->findOrFail($enrollmentCourseId);

        // التحقق من وجود الكورس
        if (!$enrollmentCourse->course) {
            $this->dispatch('notify', [
                'message' => 'الكورس غير موجود أو محذوف',
                'type' => 'error'
            ]);
            return;
        }

        if ($enrollmentCourse->isCompleted()) {
            $this->dispatch('notify', [
                'message' => 'لا يمكن تعديل كورس مكتمل',
                'type' => 'error'
            ]);
            return;
        }

        $this->selectedCourseId = $enrollmentCourseId;
        $this->loadAvailableGroups($enrollmentCourse->course_id, $enrollmentCourse->current_session);
        $this->showGroupModal = true;
    }

    private function loadAvailableGroups($courseId, $currentSession)
    {
        $groups = Group::where('course_id', $courseId)
            ->where('status', 'active')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['instructor.user', 'course'])
            ->get();

        $this->availableGroups = $groups->map(function ($group) use ($currentSession) {
            $completedSessions = $group->completed_sessions ?? 0;
            $isCompatible = $completedSessions <= $currentSession;

            return [
                'id' => $group->id,
                'name' => $group->name,
                'instructor_name' => $group->instructor?->user?->name ?? 'غير محدد',
                'completed_sessions' => $completedSessions,
                'capacity' => $group->capacity ?? 0,
                'max_students' => $group->max_students ?? 0,
                'current_students' => $group->enrollments()->count(),
                'schedule' => $group->schedule,
                'is_compatible' => $isCompatible,
                'incompatible_reason' => !$isCompatible 
                    ? "لا يمكن الانتقال لمجموعة في السيشن {$completedSessions} والطالب في السيشن {$currentSession}"
                    : null,
            ];
        })->toArray();
    }

    public function selectGroup($groupId)
    {
        if (!$this->selectedCourseId) {
            return;
        }

        $enrollmentCourse = EnrollmentCourse::findOrFail($this->selectedCourseId);

        // Verify the group is for the same course
        $group = Group::findOrFail($groupId);
        if ($group->course_id !== $enrollmentCourse->course_id) {
            $this->dispatch('notify', [
                'message' => 'المجموعة المختارة لا تنتمي لهذا الكورس',
                'type' => 'error'
            ]);
            return;
        }

        // Check compatibility
        $completedSessions = $group->completed_sessions ?? 0;
        if ($completedSessions > $enrollmentCourse->current_session) {
            $this->dispatch('notify', [
                'message' => 'لا يمكن الانتقال لمجموعة متقدمة من السيشن الحالي',
                'type' => 'error'
            ]);
            return;
        }

        // Activate or change group
        if ($enrollmentCourse->isInactive()) {
            $enrollmentCourse->activateWithGroup($groupId);
            $message = 'تم تفعيل الكورس بنجاح';
        } else {
            $enrollmentCourse->changeGroup($groupId);
            $message = 'تم تغيير المجموعة بنجاح';
        }

        $this->showGroupModal = false;
        $this->loadEnrollment();
        
        $this->dispatch('notify', [
            'message' => $message,
            'type' => 'success'
        ]);
    }

    public function saveGroup($enrollmentCourseId)
    {
        $enrollmentCourse = EnrollmentCourse::findOrFail($enrollmentCourseId);

        if ($enrollmentCourse->isCompleted()) {
            $this->dispatch('notify', [
                'message' => 'لا يمكن تعديل كورس مكتمل',
                'type' => 'error'
            ]);
            return;
        }

        $groupId = $this->selectedGroups[$enrollmentCourseId] ?? null;

        if (!$groupId) {
            $this->dispatch('notify', [
                'message' => 'يرجى اختيار مجموعة',
                'type' => 'error'
            ]);
            return;
        }

        // Verify the group is for the same course
        $group = Group::findOrFail($groupId);
        if ($group->course_id !== $enrollmentCourse->course_id) {
            $this->dispatch('notify', [
                'message' => 'المجموعة المختارة لا تنتمي لهذا الكورس',
                'type' => 'error'
            ]);
            return;
        }

        // Check compatibility
        $completedSessions = $group->completed_sessions ?? 0;
        if ($completedSessions > $enrollmentCourse->current_session) {
            $this->dispatch('notify', [
                'message' => 'لا يمكن الانتقال لمجموعة متقدمة من السيشن الحالي',
                'type' => 'error'
            ]);
            return;
        }

        // Activate or change group
        if ($enrollmentCourse->isInactive()) {
            $enrollmentCourse->activateWithGroup($groupId);
            $message = 'تم تفعيل الكورس بنجاح';
        } else {
            $enrollmentCourse->changeGroup($groupId);
            $message = 'تم تغيير المجموعة بنجاح';
        }

        $this->loadEnrollment();
        $this->loadSelectedGroups();

        $this->dispatch('notify', [
            'message' => $message,
            'type' => 'success'
        ]);
    }

    public function closeModal()
    {
        $this->showGroupModal = false;
        $this->selectedCourseId = null;
        $this->availableGroups = [];
    }

    public function render()
    {
        return view('livewire.admin.admin-enrollment-courses-manager');
    }
}
