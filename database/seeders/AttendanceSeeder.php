<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Group;
use App\Models\Enrollment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating attendance records...');

        // Get all active groups (without eager loading enrollments due to BranchScope)
        $groups = Group::withoutGlobalScopes()->get();

        if ($groups->isEmpty()) {
            $this->command->warn('No groups found. Please create groups and enrollments first.');
            return;
        }

        $startDate = now()->subDays(30);
        $endDate = now();
        $recordsCreated = 0;

        foreach ($groups as $group) {
            $this->command->info("Processing group: {$group->name}");

            // Get enrollments without global scopes
            $enrollments = Enrollment::withoutGlobalScopes()
                ->where('group_id', $group->id)
                ->get();

            // Manually load students without global scopes
            $studentIds = $enrollments->pluck('student_id')->unique();
            $students = Student::withoutGlobalScopes()
                ->whereIn('id', $studentIds)
                ->get()
                ->keyBy('id');
            
            if ($students->isEmpty()) {
                $this->command->warn("No students in group: {$group->name}");
                continue;
            }

            // Create attendance for each day in the last 30 days
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                // Skip weekends (Friday and Saturday in Arabic calendar)
                if (!in_array($currentDate->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY])) {
                    foreach ($students as $student) {
                        // Prevent duplicate entries
                        $exists = Attendance::withoutGlobalScopes()->where('student_id', $student->id)
                            ->where('group_id', $group->id)
                            ->whereDate('date', $currentDate)
                            ->exists();

                        if (!$exists) {
                            // 70% حضور، 10% غياب، 10% متأخر، 10% غياب بعذر
                            $random = rand(1, 100);

                            $status = match (true) {
                                $random <= 70 => 'present',
                                $random <= 80 => 'absent',
                                $random <= 90 => 'late',
                                default => 'excused'
                            };

                            Attendance::create([
                                'tenant_id' => $group->tenant_id,
                                'student_id' => $student->id,
                                'group_id' => $group->id,
                                'date' => $currentDate,
                                'status' => $status,
                                'check_in_time' => $status === 'present' ? '09:00:00' : null,
                                'check_out_time' => $status === 'present' ? '12:00:00' : null,
                                'notes' => $status === 'excused' ? 'عذر مقبول' : ($status === 'late' ? 'تأخر ' . rand(5, 30) . ' دقيقة' : null),
                            ]);
                            $recordsCreated++;
                        }
                    }
                }
                $currentDate->addDay();
            }
        }

        $totalRecords = Attendance::withoutGlobalScopes()->count();
        $this->command->info("Attendance seeding completed! Total records: {$totalRecords}");
    }
}
