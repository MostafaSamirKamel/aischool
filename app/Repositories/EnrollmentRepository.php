<?php

namespace App\Repositories;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;

class EnrollmentRepository extends BaseRepository
{
    public function __construct(Enrollment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get active enrollments
     */
    public function getActiveEnrollments(): Collection
    {
        return $this->model
            ->where('status', 'active')
            ->with(['student.user', 'group.course'])
            ->get();
    }

    /**
     * Get enrollments by student
     */
    public function getByStudent(int $studentId): Collection
    {
        return $this->model
            ->where('student_id', $studentId)
            ->with('group.course')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get enrollments by group
     */
    public function getByGroup(int $groupId): Collection
    {
        return $this->model
            ->where('group_id', $groupId)
            ->with('student.user')
            ->get();
    }

    /**
     * Check if student is enrolled in group
     */
    public function isStudentEnrolled(int $studentId, int $groupId): bool
    {
        return $this->model
            ->where('student_id', $studentId)
            ->where('group_id', $groupId)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Enroll student in group
     */
    public function enrollStudent(int $studentId, int $groupId, array $data = []): Enrollment
    {
        return $this->create(array_merge([
            'student_id' => $studentId,
            'group_id' => $groupId,
            'status' => 'active',
            'enrollment_date' => now(),
        ], $data));
    }

    /**
     * Complete enrollment
     */
    public function completeEnrollment(int $id): bool
    {
        return $this->update($id, [
            'status' => 'completed',
            'completion_date' => now(),
        ]);
    }

    /**
     * Cancel enrollment
     */
    public function cancelEnrollment(int $id, ?string $reason = null, ?array $settlementData = null): bool
    {
        $enrollment = $this->find($id);
        if (!$enrollment) {
            return false;
        }

        $user = auth()->user() ?? $enrollment->student?->user ?? User::first();
        $service = app(\App\Services\Enrollment\EnrollmentCancellationService::class);

        $data = array_merge([
            'reason' => $reason ?? 'إلغاء اشتراك',
            'settlement_type' => 'keep_debt',
        ], $settlementData ?? []);

        try {
            $service->cancelSubscription($enrollment, $user, $data);
            return true;
        } catch (\Exception $e) {
            \Log::error('Enrollment cancellation failed in repository: ' . $e->getMessage());
            return false;
        }
    }
}
