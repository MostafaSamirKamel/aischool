<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\EnrollmentCourse;
use Illuminate\Console\Command;

class RecalculateEnrollmentProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollments:recalculate-progress
                            {--student_id= : Recalculate for a specific student}
                            {--enrollment_id= : Recalculate for a specific enrollment}
                            {--dry-run : Show what would change without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate enrollment course progress and status from attendance records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $studentId = $this->option('student_id');
        $enrollmentId = $this->option('enrollment_id');
        $dryRun = (bool) $this->option('dry-run');

        $query = EnrollmentCourse::query()
            ->with(['enrollment', 'group'])
            ->when($studentId, function ($q) use ($studentId) {
                $q->whereHas('enrollment', function ($eq) use ($studentId) {
                    $eq->where('student_id', (int) $studentId);
                });
            })
            ->when($enrollmentId, function ($q) use ($enrollmentId) {
                $q->where('enrollment_id', (int) $enrollmentId);
            });

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->warn('No enrollment courses found for the selected filters.');
            return Command::SUCCESS;
        }

        $this->info('Found ' . $total . ' enrollment course record(s).');
        if ($dryRun) {
            $this->warn('Running in dry-run mode. No data will be updated.');
        }

        $updated = 0;
        $completedNow = 0;

        $query->chunkById(200, function ($courses) use (&$updated, &$completedNow, $dryRun) {
            foreach ($courses as $course) {
                if (!$course instanceof EnrollmentCourse) {
                    continue;
                }

                if (!$course->enrollment || !$course->group) {
                    continue;
                }

                $studentId = $course->enrollment->student_id;

                if (!$studentId || !$course->group_id || !$course->course_id) {
                    continue;
                }

                $attendedSessions = AttendanceRecord::query()
                    ->where('student_id', $studentId)
                    ->whereIn('status', ['present', 'late'])
                    ->whereHas('session', function ($q) use ($course) {
                        $q->where('group_id', $course->group_id)
                            ->where('course_id', $course->course_id);
                    })
                    ->count();

                $totalSessions = (int) ($course->total_sessions ?: ($course->group->total_sessions ?: 12));
                $newCurrentSession = min($attendedSessions, $totalSessions);
                $newStatus = $newCurrentSession >= $totalSessions ? 'completed' : 'active';

                $hasChanges =
                    (int) $course->current_session !== $newCurrentSession ||
                    (string) $course->status !== $newStatus ||
                    (int) $course->total_sessions !== $totalSessions;

                if (!$hasChanges) {
                    continue;
                }

                if (!$dryRun) {
                    $course->update([
                        'current_session' => $newCurrentSession,
                        'status' => $newStatus,
                        'total_sessions' => $totalSessions,
                    ]);
                }

                $updated++;

                if ((string) $course->status !== 'completed' && $newStatus === 'completed') {
                    $completedNow++;
                }

                $this->line(
                    sprintf(
                        'EnrollmentCourse #%d: %s %s/%s -> %s %s/%s',
                        $course->id,
                        (string) $course->status,
                        (int) $course->current_session,
                        (int) $course->total_sessions,
                        $newStatus,
                        $newCurrentSession,
                        $totalSessions
                    )
                );
            }
        });

        $this->newLine();
        $this->info('Finished recalculation.');
        $this->info('Updated records: ' . $updated);
        $this->info('Newly completed courses: ' . $completedNow);

        return Command::SUCCESS;
    }
}
