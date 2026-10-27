<?php

namespace Tests\Unit\Repositories;

use App\Models\Student;
use App\Models\User;
use App\Repositories\StudentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected StudentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StudentRepository(new Student());
    }

    /** @test */
    public function it_can_create_student(): void
    {
        $user = User::factory()->create();

        $student = $this->repository->create([
            'user_id' => $user->id,
            'student_code' => 'STU001',
            'gender' => 'male',
            'phone' => '01234567890',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('STU001', $student->student_code);
    }

    /** @test */
    public function it_can_find_student_by_id(): void
    {
        $student = Student::factory()->create();

        $found = $this->repository->find($student->id);

        $this->assertNotNull($found);
        $this->assertEquals($student->id, $found->id);
    }

    /** @test */
    public function it_can_update_student(): void
    {
        $student = Student::factory()->create();

        $result = $this->repository->update($student->id, [
            'phone' => '09876543210',
        ]);

        $this->assertTrue($result);
        $this->assertEquals('09876543210', $student->fresh()->phone);
    }

    /** @test */
    public function it_can_delete_student(): void
    {
        $student = Student::factory()->create();

        $result = $this->repository->delete($student->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('students', ['id' => $student->id]);
    }

    /** @test */
    public function it_can_get_active_students(): void
    {
        Student::factory()->count(3)->create(['status' => 'active']);
        Student::factory()->count(2)->create(['status' => 'inactive']);

        $activeStudents = $this->repository->getActiveStudents();

        $this->assertCount(3, $activeStudents);
    }

    /** @test */
    public function it_can_search_students(): void
    {
        $user = User::factory()->create(['name' => 'أحمد محمد']);
        Student::factory()->create(['user_id' => $user->id]);

        $results = $this->repository->search('أحمد');

        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_can_get_students_statistics(): void
    {
        Student::factory()->count(5)->create(['status' => 'active']);
        Student::factory()->count(2)->create(['status' => 'inactive']);
        Student::factory()->count(1)->create(['status' => 'graduated']);

        $stats = $this->repository->getStatistics();

        $this->assertEquals(8, $stats['total']);
        $this->assertEquals(5, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);
        $this->assertEquals(1, $stats['graduated']);
    }
}
