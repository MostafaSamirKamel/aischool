<?php

namespace App\Livewire\Student;

use App\Models\Enrollment;
use App\Models\Group;
use Livewire\Component;
use Livewire\Attributes\On;

class EnrollmentCoursesManager extends Component
{
    public Enrollment $enrollment;
    public ?int $selectedCourseId = null;
    public array $availableGroups = [];
    public bool $showGroupSelection = false;

    public function mount(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment->load('enrollmentCourses.course', 'enrollmentCourses.group');
    }

    /**
     * Open group selection modal for a course
     */
    public function openGroupSelection($enrollmentCourseId)
    {
        $enrollmentCourse = $this->enrollment->enrollmentCourses()
            ->findOrFail($enrollmentCourseId);

        if ($enrollmentCourse->isCompleted()) {
            $this->dispatch('notify', message: 'لا يمكن تعديل كورس مكتمل', type: 'error');
            return;
        }

        $this->selectedCourseId = $enrollmentCourseId;
        $this->loadAvailableGroups($enrollmentCourse->course_id, $enrollmentCourse->current_session);
        $this->showGroupSelection = true;
    }

    /**
     * Load available groups for the course
     */
    private function loadAvailableGroups($courseId, $currentSession)
    {
        $this->availableGroups = Group::where('course_id', $courseId)
            ->where('status', 'active')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->get()
            ->map(function ($group) use ($currentSession) {
                // Check if group session is compatible
                $sessionInfo = $this->getGroupSessionInfo($group);
                $isCompatible = $sessionInfo['completed_sessions'] <= $currentSession;

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'instructor' => $group->instructor?->user?->name ?? 'غير محدد',
                    'schedule' => $group->schedule,
                    'capacity' => $group->capacity,
                    'completed_sessions' => $sessionInfo['completed_sessions'],
                    'is_compatible' => $isCompatible,
                    'incompatible_reason' => !$isCompatible 
                        ? "لا يمكن الانتقال لمجموعة في السيشن {$sessionInfo['completed_sessions']} والطالب في السيشن {$currentSession}"
                        : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get group session information
     */
    private function getGroupSessionInfo($group)
    {
        return [
            'completed_sessions' => $group->completed_sessions ?? 0,
        ];
    }

    /**
     * Select a group for the course
     */
    public function selectGroup($groupId)
    {
        if (!$this->selectedCourseId) {
            return;
        }

        $enrollmentCourse = $this->enrollment->enrollmentCourses()
            ->findOrFail($this->selectedCourseId);

        // Verify the group is for the same course
        $group = Group::findOrFail($groupId);
        if ($group->course_id !== $enrollmentCourse->course_id) {
            $this->dispatch('notify', message: 'المجموعة المختارة لا تنتمي لهذا الكورس', type: 'error');
            return;
        }

        // Check compatibility
        $sessionInfo = $this->getGroupSessionInfo($group);
        if ($sessionInfo['completed_sessions'] > $enrollmentCourse->current_session) {
            $this->dispatch('notify', 
                message: "لا يمكن الانتقال لمجموعة متقدمة من السيشن الحالي", 
                type: 'error'
            );
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

        $this->showGroupSelection = false;
        $this->enrollment->refresh();
        
        $this->dispatch('notify', message: $message, type: 'success');
    }

    /**
     * Close group selection modal
     */
    public function closeGroupSelection()
    {
        $this->showGroupSelection = false;
        $this->selectedCourseId = null;
        $this->availableGroups = [];
    }

    public function render()
    {
        return view('livewire.student.enrollment-courses-manager');
    }
}
