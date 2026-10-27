<?php

namespace App\Observers;

use App\Models\Group;
use App\Models\SessionSchedule;
use Carbon\Carbon;

class GroupObserver
{
    /**
     * Handle the Group "created" event.
     */
    public function created(Group $group): void
    {
        $this->generateSessionSchedules($group);
    }

    /**
     * Handle the Group "updated" event.
     */
    public function updated(Group $group): void
    {
        // إذا تغيرت المواعيد أو التواريخ، نحدث الـ SessionSchedules
        if ($group->isDirty(['schedule', 'start_date', 'end_date', 'instructor_id', 'course_id', 'classroom_id'])) {
            $this->regenerateSessionSchedules($group);
        }
    }

    /**
     * توليد جداول الجلسات للمجموعة
     */
    protected function generateSessionSchedules(Group $group): void
    {
        if (!$group->schedule || !$group->start_date || !$group->end_date || !$group->instructor_id) {
            return;
        }

        $startDate = Carbon::parse($group->start_date);
        $endDate = Carbon::parse($group->end_date);

        // تحويل أسماء الأيام للعربية إلى الإنجليزية لـ Carbon
        $dayMap = [
            'saturday' => Carbon::SATURDAY,
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
        ];

        foreach ($group->schedule as $scheduleItem) {
            $dayOfWeek = $dayMap[$scheduleItem['day']] ?? null;
            
            if (!$dayOfWeek) continue;

            // البحث عن كل التواريخ من start_date إلى end_date لهذا اليوم
            $currentDate = $startDate->copy();
            
            // الانتقال لأول يوم يطابق day_of_week
            while ($currentDate->dayOfWeek !== $dayOfWeek && $currentDate->lte($endDate)) {
                $currentDate->addDay();
            }

            $instRecordId = \App\Models\Instructor::where('id', $group->instructor_id)
                ->orWhere('user_id', $group->instructor_id)
                ->value('id') ?? $group->instructor_id;

            // إنشاء SessionSchedule لكل أسبوع
            while ($currentDate->lte($endDate)) {
                SessionSchedule::create([
                    'tenant_id' => $group->tenant_id,
                    'group_id' => $group->id,
                    'instructor_id' => $instRecordId,
                    'course_id' => $group->course_id,
                    'classroom_id' => $group->classroom_id,
                    'date' => $currentDate->format('Y-m-d'),
                    'start_time' => $scheduleItem['start_time'],
                    'end_time' => $scheduleItem['end_time'],
                    'day_of_week' => $scheduleItem['day'],
                    'type' => 'regular',
                    'status' => 'scheduled',
                ]);

                $currentDate->addWeek();
            }
        }

        // تحديث total_sessions في المجموعة بدون إطلاق أحداث لتجنب التكرار اللانهائي
        $totalSessions = SessionSchedule::where('group_id', $group->id)->count();
        $group->updateQuietly(['total_sessions' => $totalSessions]);
    }

    /**
     * إعادة توليد جداول الجلسات (حذف القديم وتوليد جديد)
     */
    protected function regenerateSessionSchedules(Group $group): void
    {
        // حذف SessionSchedules القديمة التي لم يتم تسجيل حضور لها
        SessionSchedule::where('group_id', $group->id)
            ->where('attendance_taken', false)
            ->delete();

        // توليد جداول جديدة
        $this->generateSessionSchedules($group);
    }
}