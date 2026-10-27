<?php

namespace App\Services\Api;

use App\Models\Exam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExamService
{
    /**
     * @param array<string, mixed> $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Exam::with(['course', 'group'])
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(isset($filters['course_id']), fn ($query) => $query->where('course_id', $filters['course_id']))
            ->orderBy('exam_date', 'desc')
            ->paginate($perPage);
    }

    public function findDetailed(int $examId): Exam
    {
        return Exam::with(['course', 'group', 'instructor', 'grades'])->findOrFail($examId);
    }

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, Exam>
     */
    public function upcoming(array $filters): Collection
    {
        $limit = (int) ($filters['limit'] ?? 10);

        return Exam::with(['course', 'group'])
            ->where('status', 'scheduled')
            ->where('exam_date', '>', now())
            ->orderBy('exam_date', 'asc')
            ->limit($limit)
            ->get();
    }
}
