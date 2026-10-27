<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Group;
use App\Models\SessionSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service for calculating and visualizing classroom availability and occupancy.
 * Strictly READ-ONLY. Does NOT create, update, or delete any records.
 */
class ClassroomAvailabilityService
{
    /**
     * Default operating hours window when no branch/classroom-specific hours are defined.
     */
    public const DEFAULT_WORK_START = '09:00';
    public const DEFAULT_WORK_END = '21:00';

    /**
     * Day mapping for English and Arabic day names.
     */
    public const ARABIC_DAYS = [
        'saturday' => 'السبت',
        'sunday' => 'الأحد',
        'monday' => 'الاثنين',
        'tuesday' => 'الثلاثاء',
        'wednesday' => 'الأربعاء',
        'thursday' => 'الخميس',
        'friday' => 'الجمعة',
    ];

    /**
     * Get availability for a specific calendar date across classrooms.
     *
     * @param string|Carbon $date
     * @param int|null $classroomId
     * @param int|null $tenantId
     * @return array
     */
    public function getAvailabilityForDate($date, ?int $classroomId = null, ?int $tenantId = null): array
    {
        $carbonDate = Carbon::parse($date);
        $dateString = $carbonDate->format('Y-m-d');
        $dayOfWeek = strtolower($carbonDate->format('l')); // e.g. 'saturday'
        $dayNameAr = self::ARABIC_DAYS[$dayOfWeek] ?? $carbonDate->locale('ar')->dayName;

        // Query classrooms respecting tenant and optional classroom filter
        $query = Classroom::query()->with(['tenant']);

        if ($classroomId) {
            $query->where('id', $classroomId);
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $classrooms = $query->orderBy('name')->get();

        $results = [];

        foreach ($classrooms as $classroom) {
            $results[] = $this->calculateClassroomDayAvailability($classroom, $carbonDate, $dateString, $dayOfWeek, $dayNameAr);
        }

        return $results;
    }

    /**
     * Calculate single classroom availability for a given date.
     */
    public function calculateClassroomDayAvailability(
        Classroom $classroom,
        Carbon $carbonDate,
        string $dateString,
        string $dayOfWeek,
        string $dayNameAr
    ): array {
        $isActive = ($classroom->status === 'active');
        $workStart = self::DEFAULT_WORK_START;
        $workEnd = self::DEFAULT_WORK_END;

        // If classroom is not active, it is entirely unavailable
        if (!$isActive) {
            return [
                'classroom_id' => $classroom->id,
                'classroom_name' => $classroom->name,
                'capacity' => $classroom->capacity,
                'location' => $classroom->location,
                'status' => $classroom->status,
                'status_label' => match($classroom->status) {
                    'inactive' => 'غير نشطة',
                    'maintenance' => 'تحت الصيانة',
                    default => $classroom->status,
                },
                'is_active' => false,
                'date' => $dateString,
                'day_of_week' => $dayOfWeek,
                'day_name_ar' => $dayNameAr,
                'working_hours' => ['start' => $workStart, 'end' => $workEnd],
                'occupied_slots' => [],
                'available_slots' => [],
                'timeline' => [
                    [
                        'status' => 'unavailable',
                        'start_time' => $workStart,
                        'end_time' => $workEnd,
                        'label' => match($classroom->status) {
                            'maintenance' => 'القاعة مغلقة لأعمال الصيانة',
                            'inactive' => 'القاعة غير نشطة حالياً',
                            default => 'غير متاحة',
                        },
                    ]
                ],
                'stats' => [
                    'occupied_hours' => 0,
                    'available_hours' => 0,
                    'occupied_percentage' => 0,
                    'groups_count' => 0,
                ],
            ];
        }

        // 1. Fetch specific SessionSchedule records for this classroom on this date (HIGHEST PRECEDENCE)
        $sessionRecords = SessionSchedule::where('classroom_id', $classroom->id)
            ->whereDate('date', $dateString)
            ->where('status', '!=', 'cancelled')
            ->with(['group.course', 'group.instructor.user', 'group.enrollmentCourses', 'instructor.user', 'course'])
            ->get();

        $occupiedSlots = [];
        $handledGroupIds = [];

        foreach ($sessionRecords as $session) {
            $startTime = Carbon::parse($session->start_time)->format('H:i');
            $endTime = Carbon::parse($session->end_time)->format('H:i');
            
            $group = $session->group;
            $groupName = $group?->name ?? $session->title ?? 'جلسة مجدولة';
            $courseName = $group?->course?->name ?? $session->course?->name ?? '-';
            $instructorName = $session->instructor?->user?->name ?? $group?->instructor?->user?->name ?? '-';
            $studentsCount = $group ? $group->enrollmentCourses()->count() : 0;

            $occupiedSlots[] = [
                'source' => 'session_schedule',
                'session_id' => $session->id,
                'group_id' => $session->group_id,
                'group_name' => $groupName,
                'course_name' => $courseName,
                'instructor_name' => $instructorName,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => Carbon::parse($endTime)->diffInMinutes(Carbon::parse($startTime)),
                'students_count' => $studentsCount,
                'capacity' => $classroom->capacity,
                'available_seats' => max(0, $classroom->capacity - $studentsCount),
                'status' => $session->status,
                'status_label' => 'مؤكدة (جلسة محددة)',
            ];

            if ($session->group_id) {
                $handledGroupIds[] = $session->group_id;
            }
        }

        // 2. Fetch recurring groups assigned to this classroom (BASE PRECEDENCE)
        $activeGroups = Group::where('classroom_id', $classroom->id)
            ->whereIn('status', ['active', 'in_progress'])
            ->with(['course', 'instructor.user', 'enrollmentCourses'])
            ->get();

        foreach ($activeGroups as $group) {
            // Prevent double counting if this group already has a specific SessionSchedule on this date
            if (in_array($group->id, $handledGroupIds)) {
                continue;
            }

            // Check calendar boundaries
            $groupStart = $group->start_date ? Carbon::parse($group->start_date)->startOfDay() : null;
            $groupEnd = $group->end_date ? Carbon::parse($group->end_date)->endOfDay() : null;

            if ($groupStart && $carbonDate->lt($groupStart)) {
                continue;
            }
            if ($groupEnd && $carbonDate->gt($groupEnd)) {
                continue;
            }

            // Check if group's weekly schedule includes this day of the week
            if (is_array($group->schedule)) {
                foreach ($group->schedule as $scheduleItem) {
                    if (strtolower($scheduleItem['day'] ?? '') === $dayOfWeek) {
                        $startTime = Carbon::parse($scheduleItem['start_time'])->format('H:i');
                        $endTime = Carbon::parse($scheduleItem['end_time'])->format('H:i');
                        $studentsCount = $group->enrollmentCourses()->count();

                        $durationMinutes = abs((int) Carbon::parse($endTime)->diffInMinutes(Carbon::parse($startTime)));

                        $occupiedSlots[] = [
                            'source' => 'recurring_group',
                            'session_id' => null,
                            'group_id' => $group->id,
                            'group_name' => $group->name,
                            'course_name' => $group->course?->name ?? '-',
                            'instructor_name' => $group->instructor?->user?->name ?? '-',
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'duration_minutes' => $durationMinutes,
                            'students_count' => $studentsCount,
                            'capacity' => $classroom->capacity,
                            'available_seats' => max(0, $classroom->capacity - $studentsCount),
                            'status' => 'active',
                            'status_label' => 'جدول أسبوعي دوري',
                        ];
                    }
                }
            }
        }

        // Detect overlapping slots and compute true clock occupied span
        $mergedIntervals = $this->mergeIntervals($occupiedSlots);
        $trueClockOccupiedMinutes = 0;
        foreach ($mergedIntervals as $interval) {
            $trueClockOccupiedMinutes += abs((int) Carbon::parse($interval['end'])->diffInMinutes(Carbon::parse($interval['start'])));
        }

        // Build continuous timeline: available gaps vs occupied slots
        $timeline = [];
        $availableSlots = [];
        $pointer = $workStart;

        // Mark conflicts in occupied slots if any overlaps exist
        $hasConflicts = false;
        for ($i = 0; $i < count($occupiedSlots); $i++) {
            $occupiedSlots[$i]['has_conflict'] = false;
            for ($j = 0; $j < count($occupiedSlots); $j++) {
                if ($i !== $j) {
                    if ($this->isOverlapping($occupiedSlots[$i], $occupiedSlots[$j])) {
                        $occupiedSlots[$i]['has_conflict'] = true;
                        $hasConflicts = true;
                        break;
                    }
                }
            }
        }

        foreach ($occupiedSlots as $slot) {
            // Available gap before this slot?
            if ($slot['start_time'] > $pointer) {
                $gapMinutes = abs((int) Carbon::parse($slot['start_time'])->diffInMinutes(Carbon::parse($pointer)));
                if ($gapMinutes > 0) {
                    $availableBlock = [
                        'status' => 'available',
                        'start_time' => $pointer,
                        'end_time' => $slot['start_time'],
                        'duration_minutes' => $gapMinutes,
                        'duration_formatted' => $this->formatDuration($gapMinutes),
                        'label' => 'متاحة للحجز',
                    ];
                    $timeline[] = $availableBlock;
                    $availableSlots[] = $availableBlock;
                }
            }

            // Add occupied block
            $slotMinutes = abs((int) Carbon::parse($slot['end_time'])->diffInMinutes(Carbon::parse($slot['start_time'])));

            $timeline[] = [
                'status' => 'occupied',
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'duration_minutes' => $slotMinutes,
                'duration_formatted' => $this->formatDuration($slotMinutes),
                'label' => $slot['group_name'],
                'group_id' => $slot['group_id'],
                'course' => $slot['course_name'],
                'instructor' => $slot['instructor_name'],
                'students_count' => $slot['students_count'],
                'capacity' => $classroom->capacity,
                'available_seats' => $slot['available_seats'],
                'source' => $slot['source'],
                'has_conflict' => $slot['has_conflict'],
            ];

            // Advance pointer to the latest end time
            if ($slot['end_time'] > $pointer) {
                $pointer = $slot['end_time'];
            }
        }

        // Available gap after last slot until workEnd?
        if ($pointer < $workEnd) {
            $endMinutes = abs((int) Carbon::parse($workEnd)->diffInMinutes(Carbon::parse($pointer)));
            if ($endMinutes > 0) {
                $availableBlock = [
                    'status' => 'available',
                    'start_time' => $pointer,
                    'end_time' => $workEnd,
                    'duration_minutes' => $endMinutes,
                    'duration_formatted' => $this->formatDuration($endMinutes),
                    'label' => 'متاحة للحجز',
                ];
                $timeline[] = $availableBlock;
                $availableSlots[] = $availableBlock;
            }
        }

        $totalWorkMinutes = abs((int) Carbon::parse($workEnd)->diffInMinutes(Carbon::parse($workStart)));
        $totalAvailableMinutes = max(0, $totalWorkMinutes - $trueClockOccupiedMinutes);

        return [
            'classroom_id' => $classroom->id,
            'classroom_name' => $classroom->name,
            'capacity' => $classroom->capacity,
            'location' => $classroom->location,
            'status' => $classroom->status,
            'status_label' => 'نشطة',
            'is_active' => true,
            'date' => $dateString,
            'day_of_week' => $dayOfWeek,
            'day_name_ar' => $dayNameAr,
            'working_hours' => ['start' => $workStart, 'end' => $workEnd],
            'occupied_slots' => $occupiedSlots,
            'available_slots' => $availableSlots,
            'timeline' => $timeline,
            'stats' => [
                'occupied_hours' => round($trueClockOccupiedMinutes / 60, 1),
                'available_hours' => round($totalAvailableMinutes / 60, 1),
                'occupied_percentage' => $totalWorkMinutes > 0 ? round(($trueClockOccupiedMinutes / $totalWorkMinutes) * 100) : 0,
                'groups_count' => count($occupiedSlots),
                'has_conflicts' => $hasConflicts,
            ],
        ];
    }

    /**
     * Merge overlapping time intervals to calculate actual clock occupancy span.
     */
    protected function mergeIntervals(array $slots): array
    {
        if (empty($slots)) {
            return [];
        }

        // Sort by start_time
        usort($slots, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));

        $merged = [];
        $current = [
            'start' => $slots[0]['start_time'],
            'end' => $slots[0]['end_time'],
        ];

        for ($i = 1; $i < count($slots); $i++) {
            $next = $slots[$i];
            // If next starts before current ends -> overlap/merge
            if ($next['start_time'] <= $current['end']) {
                if ($next['end_time'] > $current['end']) {
                    $current['end'] = $next['end_time'];
                }
            } else {
                $merged[] = $current;
                $current = [
                    'start' => $next['start_time'],
                    'end' => $next['end_time'],
                ];
            }
        }

        $merged[] = $current;
        return $merged;
    }

    /**
     * Check if two slot intervals overlap.
     */
    protected function isOverlapping(array $a, array $b): bool
    {
        return ($a['start_time'] < $b['end_time']) && ($b['start_time'] < $a['end_time']);
    }

    /**
     * Get weekly availability matrix across all 7 days for classrooms.
     *
     * @param string|Carbon $weekStartDate
     * @param int|null $classroomId
     * @param int|null $tenantId
     * @return array
     */
    public function getWeeklyAvailability($weekStartDate, ?int $classroomId = null, ?int $tenantId = null): array
    {
        $startOfWeek = Carbon::parse($weekStartDate)->startOfWeek(Carbon::SATURDAY); // Arabic week starts Saturday

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $dayOfWeek = strtolower($dayDate->format('l'));
            $days[] = [
                'date' => $dayDate->format('Y-m-d'),
                'day_of_week' => $dayOfWeek,
                'day_name_ar' => self::ARABIC_DAYS[$dayOfWeek] ?? $dayDate->locale('ar')->dayName,
                'formatted' => $dayDate->format('d/m'),
            ];
        }

        // Query classrooms
        $query = Classroom::query()->with(['tenant']);

        if ($classroomId) {
            $query->where('id', $classroomId);
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $classrooms = $query->orderBy('name')->get();

        $matrix = [];

        foreach ($classrooms as $classroom) {
            $classroomRow = [
                'classroom_id' => $classroom->id,
                'classroom_name' => $classroom->name,
                'capacity' => $classroom->capacity,
                'location' => $classroom->location,
                'status' => $classroom->status,
                'is_active' => ($classroom->status === 'active'),
                'days' => [],
            ];

            foreach ($days as $dayInfo) {
                $dayAvailability = $this->calculateClassroomDayAvailability(
                    $classroom,
                    Carbon::parse($dayInfo['date']),
                    $dayInfo['date'],
                    $dayInfo['day_of_week'],
                    $dayInfo['day_name_ar']
                );

                $classroomRow['days'][$dayInfo['day_of_week']] = [
                    'date' => $dayInfo['date'],
                    'day_name_ar' => $dayInfo['day_name_ar'],
                    'occupied_slots' => $dayAvailability['occupied_slots'],
                    'available_slots' => $dayAvailability['available_slots'],
                    'stats' => $dayAvailability['stats'],
                    'is_active' => $dayAvailability['is_active'],
                ];
            }

            $matrix[] = $classroomRow;
        }

        return [
            'week_start' => $startOfWeek->format('Y-m-d'),
            'week_end' => $startOfWeek->copy()->addDays(6)->format('Y-m-d'),
            'days_header' => $days,
            'classrooms' => $matrix,
        ];
    }

    /**
     * Format minutes into readable Arabic duration (e.g., "ساعتان" or "ساعة ونصف" or "45 دقيقة").
     */
    public function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 دقيقة';
        }

        if ($minutes === 60) {
            return 'ساعة واحدة';
        }

        if ($minutes === 120) {
            return 'ساعتان';
        }

        if ($minutes < 60) {
            return "{$minutes} دقيقة";
        }

        $hours = floor($minutes / 60);
        $remMinutes = $minutes % 60;

        if ($remMinutes === 0) {
            return "{$hours} ساعات";
        }

        if ($remMinutes === 30) {
            return "{$hours} ساعة ونصف";
        }

        return "{$hours} س و {$remMinutes} د";
    }
}
