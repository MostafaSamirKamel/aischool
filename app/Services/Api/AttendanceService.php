<?php

namespace App\Services\Api;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, int $recordedBy): Attendance
    {
        $student = Student::findOrFail((int) $data['student_id']);
        $group = Group::findOrFail((int) $data['group_id']);

        if ((int) $student->tenant_id !== (int) $group->tenant_id) {
            throw ValidationException::withMessages([
                'group_id' => ['Student and group must belong to the same tenant.'],
            ]);
        }

        $exists = Attendance::query()
            ->where('student_id', (int) $data['student_id'])
            ->where('group_id', (int) $data['group_id'])
            ->whereDate('date', (string) $data['date'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'date' => ['Attendance record already exists for this date.'],
            ]);
        }

        try {
            return Attendance::create([
                'student_id' => (int) $data['student_id'],
                'group_id' => (int) $data['group_id'],
                'date' => (string) $data['date'],
                'status' => (string) $data['status'],
                'check_in_time' => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $recordedBy,
            ]);
        } catch (QueryException $e) {
            if (str_contains(strtolower($e->getMessage()), 'unique')) {
                throw ValidationException::withMessages([
                    'date' => ['Attendance record already exists for this date.'],
                ]);
            }

            throw $e;
        }
    }
}
