<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VaultTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeGenerationUniquenessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF;');
    }

    public function test_revenue_code_generation_is_globally_unique_across_tenants(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $cat1 = \App\Models\RevenueCategory::create(['name' => 'General', 'code' => 'CAT1', 'tenant_id' => $tenant1->id, 'is_active' => true]);
        $cat2 = \App\Models\RevenueCategory::create(['name' => 'General 2', 'code' => 'CAT2', 'tenant_id' => $tenant2->id, 'is_active' => true]);

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        // Create revenue in tenant 2 with REV-000001
        Revenue::withoutGlobalScopes()->create([
            'tenant_id' => $tenant2->id,
            'revenue_category_id' => $cat2->id,
            'revenue_code' => 'REV-000001',
            'amount' => 100,
            'revenue_date' => now(),
            'title' => 'Revenue in Branch 2',
            'created_by' => $user2->id,
        ]);

        // Login as user from tenant 1
        $this->actingAs($user1);

        // Next code should NOT be REV-000001; it must be REV-000002
        $nextCode = Revenue::generateRevenueCode();
        $this->assertEquals('REV-000002', $nextCode);

        // Creating revenue in tenant 1 succeeds without unique collision
        $revenue1 = Revenue::create([
            'revenue_category_id' => $cat1->id,
            'amount' => 200,
            'revenue_date' => now(),
            'title' => 'Revenue in Branch 1',
        ]);

        $this->assertEquals('REV-000002', $revenue1->revenue_code);
        $this->assertEquals($tenant1->id, $revenue1->tenant_id);
    }

    public function test_expense_code_generation_is_globally_unique_across_tenants(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $cat2 = \App\Models\ExpenseCategory::create(['name' => 'General Exp', 'code' => 'EXP1', 'tenant_id' => $tenant2->id, 'is_active' => true]);

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        Expense::withoutGlobalScopes()->create([
            'tenant_id' => $tenant2->id,
            'expense_category_id' => $cat2->id,
            'expense_code' => 'EXP-000001',
            'amount' => 50,
            'expense_date' => now(),
            'title' => 'Expense in Branch 2',
            'created_by' => $user2->id,
        ]);

        $this->actingAs($user1);

        $nextCode = Expense::generateExpenseCode();
        $this->assertEquals('EXP-000002', $nextCode);
    }

    public function test_payment_code_generation_is_globally_unique(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $studentUser = User::factory()->create(['tenant_id' => $tenant2->id]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'tenant_id' => $tenant2->id,
            'student_code' => 'STU-TEST-1',
            'gender' => 'male',
            'birth_date' => '2012-01-01',
            'phone' => '01234567891',
        ]);

        $course = \App\Models\Course::create([
            'tenant_id' => $tenant2->id,
            'name' => 'دورة تجريبية',
            'code' => 'CRS-TEST',
            'is_active' => true,
        ]);

        $group = \App\Models\Group::create([
            'tenant_id' => $tenant2->id,
            'course_id' => $course->id,
            'name' => 'المجموعة 1',
            'is_active' => true,
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'tenant_id' => $tenant2->id,
            'enrollment_code' => 'ENR-TEST-1',
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'status' => 'active',
        ]);

        $year = date('Y');
        $month = date('m');
        $prefix = 'PAY' . $year . $month;

        $vault2 = \App\Models\Vault::create([
            'tenant_id' => $tenant2->id,
            'name' => 'خزينة الفرع 2',
            'type' => 'cash',
            'opening_balance' => 1000,
            'is_active' => true,
        ]);

        Payment::withoutGlobalScopes()->create([
            'tenant_id' => $tenant2->id,
            'enrollment_id' => $enrollment->id,
            'vault_id' => $vault2->id,
            'payment_code' => $prefix . '00001',
            'amount' => 500,
            'status' => 'completed',
            'payment_method' => 'cash',
            'payment_date' => now(),
        ]);

        $this->actingAs($user1);

        $nextCode = Payment::generatePaymentCode();
        $this->assertEquals($prefix . '00002', $nextCode);
    }

    public function test_vault_transfer_code_generation_is_globally_unique(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);

        $vault1 = \App\Models\Vault::create([
            'name' => 'خزينة 1',
            'tenant_id' => $tenant2->id,
            'type' => 'cash',
            'opening_balance' => 1000,
            'is_active' => true,
        ]);

        $vault2 = \App\Models\Vault::create([
            'name' => 'خزينة 2',
            'tenant_id' => $tenant2->id,
            'type' => 'cash',
            'opening_balance' => 1000,
            'is_active' => true,
        ]);

        $prefix = 'VT-' . now()->format('ymd') . '-';

        VaultTransfer::withoutGlobalScopes()->create([
            'tenant_id' => $tenant2->id,
            'transfer_code' => $prefix . '0001',
            'from_vault_id' => $vault1->id,
            'to_vault_id' => $vault2->id,
            'amount' => 100,
            'status' => 'completed',
            'transfer_date' => now(),
        ]);

        $this->actingAs($user1);

        $nextCode = VaultTransfer::generateTransferCode();
        $this->assertEquals($prefix . '0002', $nextCode);
    }

    public function test_student_code_generation_is_globally_unique(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $studentUser = User::factory()->create(['tenant_id' => $tenant2->id]);

        $year = now()->year;

        Student::withoutGlobalScopes()->create([
            'user_id' => $studentUser->id,
            'tenant_id' => $tenant2->id,
            'student_code' => "STU-{$year}-0001",
            'birth_date' => '2010-01-01',
            'gender' => 'male',
            'phone' => '01011112222',
        ]);

        $this->actingAs($user1);

        $nextCode = Student::generateStudentCode();
        $this->assertEquals("STU-{$year}-0002", $nextCode);
    }
}
