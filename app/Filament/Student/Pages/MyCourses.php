<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use App\Models\EnrollmentCourse;

class MyCourses extends Page
{
    protected string $view = 'filament.student.pages.my-courses';
    protected static ?string $title = 'مجموعاتي وكورساتي';
    protected static ?string $navigationLabel = 'مجموعاتي وكورساتي';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 4;

    public function getTitle(): string
    {
        return __('مجموعاتي وكورساتي');
    }

    public function getHeading(): string
    {
        return __('مجموعاتي وكورساتي');
    }

    protected function getViewData(): array
    {
        $student = auth()->user()->student;
        
        $courses = EnrollmentCourse::query()
            ->whereHas('enrollment', fn($q) => $q->where('student_id', $student?->id)
                ->where('status', 'active'))
            ->with([
                'course',
                'group.instructor.user',
                'group.attendanceSessions',
            ])
            ->get()
            ->map(function($ec) {
                // Try to find the group from enrollment if not set on ec
                $group = $ec->group;
                if (!$group) {
                    $groups = $ec->enrollment?->groups;
                    $matched = $groups ? $groups->where('course_id', $ec->course_id) : null;
                    $group = $matched?->first();
                }

                // Parse schedule
                $scheduleText = 'غير محدد';
                $scheduleArr = $group?->schedule;
                if ($scheduleArr) {
                    $schedules = is_string($scheduleArr) ? json_decode($scheduleArr, true) : $scheduleArr;
                    if (is_array($schedules)) {
                        $days = [
                            'saturday' => 'السبت',
                            'sunday' => 'الأحد',
                            'monday' => 'الاثنين',
                            'tuesday' => 'الثلاثاء',
                            'wednesday' => 'الأربعاء',
                            'thursday' => 'الخميس',
                            'friday' => 'الجمعة',
                        ];
                        $formatted = [];
                        foreach ($schedules as $sch) {
                            $day = $days[$sch['day']] ?? $sch['day'];
                            $formatted[] = "<b>{$day}</b>: {$sch['start_time']}";
                        }
                        $scheduleText = implode(' • ', $formatted);
                    }
                }

                return [
                    'id' => $ec->course?->id,
                    'title' => $ec->course?->name ?? 'كورس غير معروف',
                    'teacher' => $group?->instructor?->name ?? $ec->course?->instructor?->name ?? 'غير محدد',
                    'group' => $group?->name ?? 'غير محدد',
                    'schedule' => $scheduleText,
                    'status' => $this->resolveGroupStatus($ec),
                    'color' => ['violet', 'blue', 'peach', 'mint'][crc32($ec->course_id) % 4]
                ];
            });

        return [
            'student' => $student,
            'activeCourses' => $courses
        ];
    }

    public function resolveGroupStatus(object $record): string
    {
        $group = $record->group ?? null;
        if (!$group && isset($record->enrollment) && $record->enrollment) {
            $groups = $record->enrollment->groups ?? null;
            $matched = $groups ? $groups->where('course_id', $record->course_id) : null;
            $group = $matched?->first();
        }

        return strtolower(trim((string)($group->status ?? $record->status ?? 'active')));
    }
}
