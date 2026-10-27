<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\Reports\AllInstallmentsReport;
use App\Models\Enrollment;
use App\Models\Installment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallmentOverdueCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_partially_paid_installment_past_due_date_is_recognized_as_overdue(): void
    {
        $tenant = Tenant::create(['name' => 'Test Tenant', 'domain' => 'test.org']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $student = Student::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'student_code' => 'STU-001',
            'gender' => 'male',
        ]);

        $group = \App\Models\Group::factory()->create(['tenant_id' => $tenant->id]);

        $vault = \App\Models\Vault::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Vault',
            'type' => 'cash',
            'status' => 'active',
            'opening_balance' => 0,
        ]);

        $enrollment = Enrollment::create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'vault_id' => $vault->id,
            'enrollment_code' => 'ENR-001',
            'enrollment_date' => now()->subMonths(2),
            'total_amount' => 1000,
            'paid_amount' => 300,
            'payment_plan' => 'installments',
            'installments_count' => 1,
            'status' => 'active',
        ]);

        $installment = Installment::create([
            'tenant_id' => $tenant->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 1,
            'amount' => 1000,
            'paid_amount' => 300,
            'due_date' => now()->subDays(5)->toDateString(),
            'status' => 'partial',
        ]);

        // Verify distinct payment state vs due state
        $this->assertTrue($installment->isPartiallyPaid());
        $this->assertEquals('partial', $installment->getPaymentState());
        $this->assertTrue($installment->isOverdue());
        $this->assertEquals('overdue', $installment->getDueState());
        $this->assertEquals('overdue', $installment->getActualStatus());

        // Update status should recognize overdue
        $installment->updateStatus();
        $this->assertEquals('overdue', $installment->fresh()->status);
    }

    public function test_all_installments_report_does_not_double_count_overdue_installments(): void
    {
        $tenant = Tenant::create(['name' => 'Tenant Report', 'domain' => 'rep.org']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $student = Student::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'student_code' => 'STU-002',
            'gender' => 'male',
        ]);

        $group = \App\Models\Group::factory()->create(['tenant_id' => $tenant->id]);

        $enrollment = Enrollment::create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-002',
            'enrollment_date' => now()->subMonths(2),
            'total_amount' => 2000,
            'paid_amount' => 0,
            'status' => 'active',
        ]);

        // Create 2 overdue installments: both with status 'overdue' and past due_date
        Installment::create([
            'tenant_id' => $tenant->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 1,
            'amount' => 1000,
            'paid_amount' => 0,
            'due_date' => now()->subDays(10)->toDateString(),
            'status' => 'overdue',
        ]);

        Installment::create([
            'tenant_id' => $tenant->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 2,
            'amount' => 1000,
            'paid_amount' => 200,
            'due_date' => now()->subDays(5)->toDateString(),
            'status' => 'overdue',
        ]);

        $report = new AllInstallmentsReport();
        $stats = $report->getStats();

        // Previously, overdue_count was counted as overdue count + due_date < now count = 2 + 2 = 4 (double counted!)
        // Now it MUST be exactly 2.
        $this->assertEquals(2, $stats['overdue_count']);
    }
}
