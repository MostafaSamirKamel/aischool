<?php

namespace App\Repositories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository extends BaseRepository
{
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        if (empty($data['tenant_id'])) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $data['tenant_id'] = auth()->user()->tenant_id;
            } elseif (!empty($data['user_id'])) {
                $user = \App\Models\User::find($data['user_id']);
                if ($user?->tenant_id) {
                    $data['tenant_id'] = $user->tenant_id;
                }
            }

            if (empty($data['tenant_id'])) {
                $tenant = \App\Models\Tenant::first() ?? \App\Models\Tenant::factory()->create();
                $data['tenant_id'] = $tenant->id;
            }
        }

        return parent::create($data);
    }

    /**
     * Get active students
     */
    public function getActiveStudents(): Collection
    {
        return $this->model
            ->where('status', 'active')
            ->with('user')
            ->get();
    }

    /**
     * Get students by group
     */
    public function getByGroup(int $groupId): Collection
    {
        return $this->model
            ->whereHas('enrollments', function ($query) use ($groupId) {
                $query->where('group_id', $groupId)
                    ->where('status', 'active');
            })
            ->with('user')
            ->get();
    }

    /**
     * Get students by course
     */
    public function getByCourse(int $courseId): Collection
    {
        return $this->model
            ->whereHas('enrollments.groups', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->with('user')
            ->get();
    }

    /**
     * Search students
     */
    public function search(string $term): Collection
    {
        return $this->model
            ->whereHas('user', function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->orWhere('student_code', 'like', "%{$term}%")
            ->orWhere('national_id', 'like', "%{$term}%")
            ->with('user')
            ->get();
    }

    /**
     * Get student with relationships
     */
    public function findWithRelations(int $id): ?Student
    {
        return $this->model
            ->with([
                'user',
                'enrollments.groups.course',
                'payments',
                'grades',
            ])
            ->find($id);
    }

    /**
     * Get students statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->where('status', 'active')->count(),
            'inactive' => $this->model->where('status', 'inactive')->count(),
            'graduated' => $this->model->where('status', 'graduated')->count(),
            'new_this_month' => $this->model->whereMonth('created_at', now()->month)->count(),
        ];
    }
}
