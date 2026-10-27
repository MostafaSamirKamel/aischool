<?php

namespace App\Services;

use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class GroupScheduleConflictValidator
{
    public function ensureNoConflicts(array $data, ?Group $ignoreGroup = null): void
    {
        $schedule = $data['schedule'] ?? [];
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $classroomId = $data['classroom_id'] ?? null;
        $instructorId = $data['instructor_id'] ?? null;
        $tenantId = $data['tenant_id'] ?? auth()->user()?->tenant_id;

        if (empty($schedule) || !$startDate || !$tenantId) {
            return;
        }

        $startDateValue = Carbon::parse($startDate)->toDateString();
        $endDateValue = $endDate ? Carbon::parse($endDate)->toDateString() : '9999-12-31';

        $groups = Group::query()
            ->where('tenant_id', $tenantId)
            ->when($ignoreGroup, fn ($query) => $query->where('id', '!=', $ignoreGroup->id))
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereDate('start_date', '<=', $endDateValue)
            ->where(function ($query) use ($startDateValue) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDateValue);
            })
            ->with(['course:id,name', 'instructor.user:id,name', 'classroom:id,name'])
            ->get();

        $conflicts = [];

        foreach ($schedule as $item) {
            $day = $item['day'] ?? null;
            $startTime = $item['start_time'] ?? null;
            $endTime = $item['end_time'] ?? null;

            if (!$day || !$startTime || !$endTime) {
                continue;
            }

            if (!$this->isTimeRangeValid($startTime, $endTime)) {
                throw ValidationException::withMessages([
                    'data.schedule' => 'وقت البداية يجب أن يكون قبل وقت النهاية.',
                ]);
            }

            foreach ($groups as $group) {
                if (empty($group->schedule) || !is_array($group->schedule)) {
                    continue;
                }

                foreach ($group->schedule as $existingItem) {
                    $existingDay = $existingItem['day'] ?? null;
                    $existingStart = $existingItem['start_time'] ?? null;
                    $existingEnd = $existingItem['end_time'] ?? null;

                    if (!$existingDay || !$existingStart || !$existingEnd) {
                        continue;
                    }

                    if ($day !== $existingDay) {
                        continue;
                    }

                    if (!$this->isTimeOverlapping($startTime, $endTime, $existingStart, $existingEnd)) {
                        continue;
                    }

                    if ($classroomId && (int) $group->classroom_id === (int) $classroomId) {
                        $conflicts[] = sprintf(
                            'تعارض قاعة: %s (%s - %s) مع المجموعة %s.',
                            $this->formatDay($day),
                            $startTime,
                            $endTime,
                            $group->name
                        );
                    }

                    if ($instructorId && (int) $group->instructor_id === (int) $instructorId) {
                        $conflicts[] = sprintf(
                            'تعارض مدرس: %s (%s - %s) مع المجموعة %s.',
                            $this->formatDay($day),
                            $startTime,
                            $endTime,
                            $group->name
                        );
                    }
                }
            }
        }

        if (!empty($conflicts)) {
            throw ValidationException::withMessages([
                'data.schedule' => implode(' ', array_slice(array_unique($conflicts), 0, 4)),
            ]);
        }
    }

    protected function isTimeRangeValid(string $startTime, string $endTime): bool
    {
        return strtotime($startTime) < strtotime($endTime);
    }

    protected function isTimeOverlapping(string $startA, string $endA, string $startB, string $endB): bool
    {
        return strtotime($startA) < strtotime($endB) && strtotime($endA) > strtotime($startB);
    }

    protected function formatDay(string $day): string
    {
        return match ($day) {
            'saturday' => 'السبت',
            'sunday' => 'الأحد',
            'monday' => 'الاثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            'friday' => 'الجمعة',
            default => $day,
        };
    }
}
