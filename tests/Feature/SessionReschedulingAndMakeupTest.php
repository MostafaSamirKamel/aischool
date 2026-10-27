<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\SessionSchedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionReschedulingAndMakeupTest extends TestCase
{
    use RefreshDatabase;

    public function test_reschedule_session_creates_linked_makeup_session(): void
    {
        $tenant = Tenant::create(['name' => 'Session Tenant', 'domain' => 'session.test']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $instructor = Instructor::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'employee_code' => 'INS-TEST-1',
            'hourly_rate' => 150,
        ]);

        $course = Course::create([
            'tenant_id' => $tenant->id,
            'name' => 'Python Basics',
            'price' => 500,
            'sessions_count' => 10,
        ]);

        $group = Group::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'name' => 'Group Py-1',
            'status' => 'active',
            'total_sessions' => 10,
        ]);

        $originalSession = SessionSchedule::create([
            'tenant_id' => $tenant->id,
            'group_id' => $group->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'title' => 'الدرس الأول',
            'type' => 'regular',
            'status' => 'scheduled',
        ]);

        // Reschedule session
        $newDate = now()->addDays(3)->toDateString();
        $makeupSession = $originalSession->reschedule($newDate, '14:00:00', '16:00:00', $user->id, 'عذر طارئ للمدرب');

        $this->assertEquals('rescheduled', $originalSession->fresh()->status);
        $this->assertEquals('عذر طارئ للمدرب', $originalSession->fresh()->cancellation_reason);
        $this->assertNotNull($originalSession->fresh()->rescheduled_at);

        $this->assertEquals('makeup', $makeupSession->type);
        $this->assertEquals('scheduled', $makeupSession->status);
        $this->assertEquals($originalSession->id, $makeupSession->original_session_id);
        $this->assertEquals($newDate, $makeupSession->date->toDateString());

        // Check relationship
        $this->assertTrue($originalSession->makeupSessions->contains($makeupSession));
        $this->assertEquals($originalSession->id, $makeupSession->originalSession->id);
    }
}
