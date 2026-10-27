<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Enrollments\Pages\ViewEnrollment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CancelSubscriptionLivewireActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_mount_cancel_subscription_action_without_type_error(): void
    {
        $tenant = Tenant::create(['name' => 'Test Branch', 'domain' => 'test.ai-school.test']);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_super_admin' => true,
        ]);

        $studentUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $student = Student::create([
            'tenant_id' => $tenant->id,
            'user_id' => $studentUser->id,
            'student_code' => 'ST001',
            'national_id' => '29901011234567',
            'gender' => 'male',
            'phone' => '01000000001',
            'status' => 'active',
        ]);

        $course = Course::create([
            'tenant_id' => $tenant->id,
            'name' => 'Python for AI',
            'total_sessions' => 12,
            'price' => 1500,
        ]);

        $group = Group::create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'name' => 'Group A',
            'type' => 'offline',
            'status' => 'active',
        ]);

        $vault = \App\Models\Vault::create([
            'tenant_id' => $tenant->id,
            'name' => 'الخزينة الرئيسية',
            'type' => 'cash',
            'opening_balance' => 5000,
            'is_active' => true,
        ]);

        $enrollment = Enrollment::create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $vault->id,
            'enrollment_code' => 'ENR001',
            'status' => 'active',
            'total_amount' => 1500,
            'paid_amount' => 1500,
            'payment_plan' => 'full',
            'enrollment_date' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(ViewEnrollment::class, ['record' => $enrollment->id])
            ->mountAction('cancel_subscription')
            ->setActionData([
                'settlement_type' => 'session_based',
                'chargeable_sessions' => 3,
            ])
            ->assertSuccessful();
    }
}
