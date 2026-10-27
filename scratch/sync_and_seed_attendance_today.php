<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "Starting Attendance Seeding and Sync for Today..." . PHP_EOL;

$today = Carbon::today();

// 1. Groups to seed for today (Tuesday)
$cairoGroups = [21, 22, 23, 24, 25, 26];
$damanhourGroups = [28, 29, 30, 31];
$targetGroups = array_merge($cairoGroups, $damanhourGroups);

$sessionsCreated = 0;
$recordsCreated = 0;

DB::beginTransaction();

try {
    foreach ($targetGroups as $groupId) {
        $group = Group::with(['course'])->find($groupId);
        if (!$group) {
            echo "Group {$groupId} not found, skipping." . PHP_EOL;
            continue;
        }

        $students = $group->activeStudents()->get();
        if ($students->isEmpty()) {
            echo "Group {$groupId} ({$group->name}) has no active students, skipping." . PHP_EOL;
            continue;
        }

        // Check if session already exists for today
        $session = AttendanceSession::where('group_id', $group->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$session) {
            $session = AttendanceSession::create([
                'tenant_id' => $group->tenant_id,
                'course_id' => $group->course_id,
                'group_id' => $group->id,
                'department_id' => $group->department_id,
                'classroom' => $group->classroom?->name ?? 'قاعة المحاضرات',
                'session_notes' => 'جلسة يوم الثلاثاء - تم إنجاز المحاضرة بنجاح',
                'recorded_by' => 1,
                'attendance_date' => $today->toDateString(),
            ]);
            $sessionsCreated++;
        }

        $studentIndex = 0;
        foreach ($students as $student) {
            $studentIndex++;

            // Pattern: mostly present, occasionally absent or late
            if ($studentIndex === 3 && $groupId === 21) {
                $status = 'absent';
                $checkIn = null;
                $notes = 'غياب بدون إذن مسبق';
            } elseif ($studentIndex === 2 && $groupId === 24) {
                $status = 'absent';
                $checkIn = null;
                $notes = 'لم يحضر الجلسة';
            } elseif ($studentIndex === 5 && $groupId === 28) {
                $status = 'absent';
                $checkIn = null;
                $notes = 'غياب مرضي';
            } elseif ($studentIndex === 3 && $groupId === 23) {
                $status = 'late';
                $checkIn = '09:20';
                $notes = 'تأخر 20 دقيقة';
            } elseif ($studentIndex === 5 && $groupId === 30) {
                $status = 'late';
                $checkIn = '09:15';
                $notes = 'تأخر 15 دقيقة';
            } else {
                $status = 'present';
                $checkIn = '09:00';
                $notes = 'حضور وانضباط ممتاز';
            }

            // Create or update AttendanceRecord
            AttendanceRecord::updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => $status,
                    'check_in_time' => $checkIn,
                    'check_out_time' => $status === 'present' ? '12:00' : null,
                    'commitment_score' => $status === 'present' ? 9 : ($status === 'late' ? 6 : 0),
                    'focus_score' => $status === 'present' ? 9 : ($status === 'late' ? 7 : 0),
                    'participation_score' => $status === 'present' ? 8 : ($status === 'late' ? 7 : 0),
                    'notes' => $notes,
                ]
            );

            // Create or update legacy Attendance record for dual model sync
            Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'group_id' => $group->id,
                    'date' => $today->toDateString(),
                ],
                [
                    'tenant_id' => $group->tenant_id,
                    'status' => $status,
                    'check_in_time' => $checkIn ? $today->toDateString() . ' ' . $checkIn . ':00' : null,
                    'check_out_time' => $status === 'present' ? $today->toDateString() . ' 12:00:00' : null,
                    'notes' => $notes,
                    'recorded_by' => 1,
                ]
            );

            $recordsCreated++;
        }
    }

    DB::commit();
    echo "SUCCESS: Created/Verified {$sessionsCreated} sessions and {$recordsCreated} attendance records for today ({$today->toDateString()})." . PHP_EOL;

} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
