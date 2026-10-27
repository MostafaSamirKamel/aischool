<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('vault current balance correctly accounts for payments and revenues without double counting', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create(['user_id' => $user->id, 'tenant_id' => $tenant->id]);

    $vault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Safe',
        'type' => 'cash',
        'opening_balance' => 1000.00,
        'is_active' => true,
    ]);

    expect($vault->getCurrentBalance())->toBe(1000.00);

    // Create an enrollment with 200 upfront
    $enrollment = Enrollment::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'vault_id' => $vault->id,
        'total_amount' => 1000.00,
        'paid_amount' => 200.00,
        'discount' => 0,
        'payment_plan' => 'full',
    ]);

    // Vault should reflect opening_balance + upfront = 1200
    expect($vault->getCurrentBalance())->toBe(1200.00);

    // Record a completed payment of 300 in the vault
    $this->actingAs($user);
    $payment = Payment::create([
        'tenant_id' => $tenant->id,
        'enrollment_id' => $enrollment->id,
        'vault_id' => $vault->id,
        'payment_code' => 'PAY-TEST-001',
        'amount' => 300.00,
        'payment_method' => 'cash',
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    // Payment observer creates a revenue record linked to payment
    $revenue = Revenue::where('payment_id', $payment->id)->first();
    expect($revenue)->not->toBeNull()
        ->and((float) $revenue->amount)->toBe(300.00)
        ->and($revenue->vault_id)->toBe($vault->id);

    // CRITICAL: Vault balance must be opening (1000) + upfront (200) + payment (300) = 1500.
    // It must NOT double-count the revenue or payment as 1800 or 2100!
    expect($vault->getCurrentBalance())->toBe(1500.00);

    // Record an expense of 150
    $category = \App\Models\ExpenseCategory::create([
        'tenant_id' => $tenant->id,
        'name' => 'General Expenses',
        'code' => 'EXP-CAT-01',
        'is_active' => true,
    ]);

    Expense::create([
        'tenant_id' => $tenant->id,
        'expense_category_id' => $category->id,
        'vault_id' => $vault->id,
        'expense_code' => 'EXP-001',
        'title' => 'Office Supplies',
        'amount' => 150.00,
        'expense_date' => now(),
        'payment_method' => 'cash',
        'status' => 'paid',
    ]);

    // 1500 - 150 = 1350
    expect($vault->getCurrentBalance())->toBe(1350.00);

    // When payment is deleted, linked revenue should also be deleted and balance decremented
    $payment->delete();
    $existingRevenue = Revenue::where('payment_id', $payment->id)->first();
    expect($existingRevenue)->toBeNull();

    // Balance without payment: 1350 - 300 = 1050
    expect($vault->getCurrentBalance())->toBe(1050.00);
});

test('vault transfer fails when source vault has insufficient balance', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $fromVault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Source Vault',
        'type' => 'cash',
        'opening_balance' => 200.00,
        'is_active' => true,
    ]);

    $toVault = Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Destination Vault',
        'type' => 'cash',
        'opening_balance' => 50.00,
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $transfer = VaultTransfer::create([
        'tenant_id' => $tenant->id,
        'from_vault_id' => $fromVault->id,
        'to_vault_id' => $toVault->id,
        'amount' => 500.00, // Exceeds 200.00
        'transfer_date' => now(),
        'status' => 'pending',
    ]);

    // Attempting to complete transfer should throw exception due to insufficient funds
    expect(fn () => $transfer->update(['status' => 'completed']))
        ->toThrow(\Exception::class, 'لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية.');

    expect($transfer->fresh()->status)->toBe('pending')
        ->and($fromVault->getCurrentBalance())->toBe(200.00)
        ->and($toVault->getCurrentBalance())->toBe(50.00);
});
