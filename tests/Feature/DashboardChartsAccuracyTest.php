<?php

use App\Filament\Admin\Widgets\ExamsOverviewWidget;
use App\Filament\Admin\Widgets\GradesStatsWidget;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin stats overview returns empty chart when no historical records exist instead of fake arrays', function () {
    $tenant = Tenant::factory()->create();
    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_super_admin' => true,
    ]);

    $this->actingAs($adminUser);

    $widget = new StatsOverview();
    $reflection = new \ReflectionClass($widget);

    $studentTrendMethod = $reflection->getMethod('getStudentTrend');
    $studentTrendMethod->setAccessible(true);
    $studentChart = $studentTrendMethod->invoke($widget, $tenant->id, false);

    // Empty array returned when no students exist (NO fake hardcoded array)
    expect($studentChart)->toBeArray()
        ->and($studentChart)->toBeEmpty();

    // Verify stats return array contains 4 cards
    $getStatsMethod = $reflection->getMethod('getStats');
    $getStatsMethod->setAccessible(true);
    $stats = $getStatsMethod->invoke($widget);

    expect($stats)->toBeArray()
        ->and(count($stats))->toBe(4);

    // Verify charts on the Stat cards are empty
    foreach ($stats as $stat) {
        expect($stat->getChart())->toBe([]);
    }
});

test('exams overview widget returns real historical trend and no fake values', function () {
    $tenant = Tenant::factory()->create();
    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_super_admin' => false,
    ]);

    $this->actingAs($adminUser);

    $widget = new ExamsOverviewWidget();
    $reflection = new \ReflectionClass($widget);

    $getExamsTrendMethod = $reflection->getMethod('getExamsTrend');
    $getExamsTrendMethod->setAccessible(true);

    // With zero exams
    $chart = $getExamsTrendMethod->invoke($widget, $tenant->id, false);
    expect($chart)->toBe([]);

    // Create course, group, and exam
    $course = \App\Models\Course::factory()->create(['tenant_id' => $tenant->id]);
    $group = \App\Models\Group::factory()->create(['tenant_id' => $tenant->id, 'course_id' => $course->id]);
    $exam = Exam::create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'group_id' => $group->id,
        'title' => 'Test Midterm',
        'exam_date' => now(),
        'duration' => 60,
        'total_marks' => 100,
        'passing_marks' => 50,
        'status' => 'completed',
        'created_at' => now(),
    ]);

    $chartWithData = $getExamsTrendMethod->invoke($widget, $tenant->id, false);
    expect($chartWithData)->toBeArray()
        ->and($chartWithData)->not->toBe([7, 12, 18, 25, 30, 1]) // Must NOT be fake array
        ->and(array_sum($chartWithData))->toBeGreaterThanOrEqual(1);
});

test('grades stats widget returns real database trend and no fake values', function () {
    $tenant = Tenant::factory()->create();
    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'is_super_admin' => false,
    ]);

    $this->actingAs($adminUser);

    $widget = new GradesStatsWidget();
    $reflection = new \ReflectionClass($widget);

    $getGradesTrendMethod = $reflection->getMethod('getGradesTrend');
    $getGradesTrendMethod->setAccessible(true);

    // Zero grades
    $chart = $getGradesTrendMethod->invoke($widget, $tenant->id, false);
    expect($chart)->toBe([]);

    // Create course, group, exam, student, and grade
    $course = \App\Models\Course::factory()->create(['tenant_id' => $tenant->id]);
    $group = \App\Models\Group::factory()->create(['tenant_id' => $tenant->id, 'course_id' => $course->id]);
    $exam = Exam::create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'group_id' => $group->id,
        'title' => 'Grade Test Exam',
        'exam_date' => now(),
        'duration' => 60,
        'total_marks' => 100,
        'passing_marks' => 50,
        'status' => 'completed',
    ]);

    $studentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create(['user_id' => $studentUser->id, 'tenant_id' => $tenant->id]);

    Grade::create([
        'tenant_id' => $tenant->id,
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'marks_obtained' => 95.0,
        'percentage' => 95.0,
        'grade' => 'A+',
        'graded_at' => now(),
        'created_at' => now(),
    ]);

    $chartWithData = $getGradesTrendMethod->invoke($widget, $tenant->id, false);
    expect($chartWithData)->toBeArray()
        ->and($chartWithData)->not->toBe([15, 25, 35, 45, 1]) // Must NOT be fake array
        ->and(array_sum($chartWithData))->toBeGreaterThanOrEqual(1);
});
