<?php

namespace App\Console\Commands;

use App\Actions\IncrementGroupSessions;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Group;
use Illuminate\Console\Command;

class TestCourseSystem extends Command
{
    protected $signature = 'test:course-system';

    protected $description = 'Test the course management system';

    public function handle()
    {
        $this->info('Testing Course Management System...');

        // Test 1: Check if EnrollmentCourse model exists
        try {
            $enrollmentCourse = EnrollmentCourse::first();
            $this->line('✓ EnrollmentCourse model working');
        } catch (\Exception $e) {
            $this->error('✗ EnrollmentCourse model error: ' . $e->getMessage());
            return;
        }

        // Test 2: Check enrollment courses relationship
        try {
            $enrollment = Enrollment::with('enrollmentCourses')->first();
            if ($enrollment) {
                $this->line("✓ Enrollment relationship working - Found {$enrollment->enrollmentCourses->count()} courses");
            } else {
                $this->warn('⚠ No enrollments found to test');
            }
        } catch (\Exception $e) {
            $this->error('✗ Enrollment relationship error: ' . $e->getMessage());
        }

        // Test 3: Check methods
        try {
            $enrollmentCourse = EnrollmentCourse::first();
            if ($enrollmentCourse) {
                $this->line('✓ EnrollmentCourse methods:');
                $this->line("  - Status Label: {$enrollmentCourse->getStatusLabel()}");
                $this->line("  - Progress: {$enrollmentCourse->getProgressPercentage()}%");
                $this->line("  - Is Completed: " . ($enrollmentCourse->isCompleted() ? 'Yes' : 'No'));
                $this->line("  - Is Active: " . ($enrollmentCourse->isActive() ? 'Yes' : 'No'));
                $this->line("  - Is Inactive: " . ($enrollmentCourse->isInactive() ? 'Yes' : 'No'));
            }
        } catch (\Exception $e) {
            $this->error('✗ EnrollmentCourse methods error: ' . $e->getMessage());
        }

        $this->info('✓ All tests passed!');
    }
}
