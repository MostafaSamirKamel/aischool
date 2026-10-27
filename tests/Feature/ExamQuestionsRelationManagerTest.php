<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Exams\Pages\EditExam;
use App\Filament\Admin\Resources\Exams\RelationManagers\QuestionsRelationManager;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExamQuestionsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_load_questions_relation_manager_without_class_not_found_error(): void
    {
        $tenant = Tenant::create(['name' => 'Main Branch', 'domain' => 'main.test']);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_super_admin' => true,
        ]);

        $course = Course::create([
            'tenant_id' => $tenant->id,
            'name' => 'Python Basics',
            'price' => 1000,
        ]);

        $group = \App\Models\Group::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'name' => 'Group A',
            'status' => 'active',
            'type' => 'offline',
        ]);

        $exam = Exam::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'title' => 'Midterm Exam',
            'total_marks' => 100,
            'passing_marks' => 50,
            'duration_minutes' => 60,
            'is_active' => true,
            'exam_date' => now(),
        ]);

        $this->actingAs($user);

        // Mount the relation manager directly via Livewire
        Livewire::test(QuestionsRelationManager::class, [
            'ownerRecord' => $exam,
            'pageClass' => EditExam::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([]);
    }
}
