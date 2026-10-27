<?php

namespace App\Livewire\Student;

use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Group;
use Livewire\Component;
use Livewire\Attributes\Computed;

class CourseStatusButton extends Component
{
    public int $enrollmentCourseId;
    public int $enrollmentId;
    public ?EnrollmentCourse $enrollmentCourse = null;
    public ?Enrollment $enrollment = null;
    public bool $showGroupModal = false;
    public array $availableGroups = [];

    public function mount(int $enrollmentCourseId, int $enrollmentId)
    {
        $this->enrollmentCourseId = $enrollmentCourseId;
        $this->enrollmentId = $enrollmentId;
        $this->loadData();
    }

    public function loadData()
    {
        $this->enrollmentCourse = EnrollmentCourse::findOrFail($this->enrollmentCourseId);
        $this->enrollment = Enrollment::findOrFail($this->enrollmentId);
    }

    public function openGroupSelection()
    {
        if ($this->enrollmentCourse->isCompleted()) {
            $this->dispatch('notify', 
                message: 'لا يمكن تعديل كورس مكتمل', 
                type: 'error'
            );
            return;
        }

        $this->loadAvailableGroups();
        $this->showGroupModal = true;
    }

    private function loadAvailableGroups()
    {
        $this->availableGroups = Group::where('course_id', $this->enrollmentCourse->course_id)
            ->where('status', 'active')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with('instructor.user', 'classroom')
            ->get()
            ->map(function ($group) {
                $completedSessions = $group->completed_sessions ?? 0;
                $isCompatible = $completedSessions <= $this->enrollmentCourse->current_session;

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'instructor_name' => $group->instructor?->user?->name ?? 'غير محدد',
                    'schedule' => $group->schedule,
                    'capacity' => $group->capacity ?? 0,
                    'current_students' => $group->enrollments()->count(),
                    'completed_sessions' => $completedSessions,
                    'is_compatible' => $isCompatible,
                    'incompatible_reason' => !$isCompatible 
                        ? "لا يمكن الانتقال لمجموعة في السيشن {$completedSessions} والطالب في السيشن {$this->enrollmentCourse->current_session}"
                        : null,
                ];
            })
            ->toArray();
    }

    public function selectGroup(int $groupId)
    {
        if ($this->enrollmentCourse->isCompleted()) {
            $this->dispatch('notify', message: 'لا يمكن تعديل كورس مكتمل', type: 'error');
            return;
        }

        $group = Group::findOrFail($groupId);

        // Verify group belongs to the same course
        if ($group->course_id !== $this->enrollmentCourse->course_id) {
            $this->dispatch('notify', 
                message: 'المجموعة المختارة لا تنتمي لهذا الكورس', 
                type: 'error'
            );
            return;
        }

        // Check compatibility
        $completedSessions = $group->completed_sessions ?? 0;
        if ($completedSessions > $this->enrollmentCourse->current_session) {
            $this->dispatch('notify', 
                message: 'لا يمكن الانتقال لمجموعة متقدمة من السيشن الحالي', 
                type: 'error'
            );
            return;
        }

        // Activate or change group
        try {
            if ($this->enrollmentCourse->isInactive()) {
                $this->enrollmentCourse->activateWithGroup($groupId);
                $message = '✅ تم تفعيل الكورس بنجاح!';
            } else {
                $this->enrollmentCourse->changeGroup($groupId);
                $message = '✅ تم تغيير المجموعة بنجاح!';
            }

            $this->showGroupModal = false;
            $this->loadData();
            
            $this->dispatch('notify', message: $message, type: 'success');
            $this->dispatch('refresh-parent');
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                message: 'حدث خطأ: ' . $e->getMessage(), 
                type: 'error'
            );
        }
    }

    public function closeModal()
    {
        $this->showGroupModal = false;
        $this->availableGroups = [];
    }

    #[Computed]
    public function statusLabel()
    {
        return $this->enrollmentCourse?->getStatusLabel() ?? '';
    }

    #[Computed]
    public function statusColor()
    {
        return match ($this->enrollmentCourse?->status) {
            'inactive' => 'yellow',
            'active' => 'green',
            'completed' => 'blue',
            default => 'gray',
        };
    }

    #[Computed]
    public function statusIcon()
    {
        return match ($this->enrollmentCourse?->status) {
            'inactive' => '⭕',
            'active' => '🟢',
            'completed' => '✅',
            default => '❓',
        };
    }

    public function render()
    {
        return view('livewire.student.course-status-button');
    }
}
