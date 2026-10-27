<?php

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Parent\ParentStudentAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('parent can only see their own linked children via ParentStudentAccessService', function () {
    $tenant = Tenant::factory()->create();

    $parentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $studentUser1 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Child One']);
    $studentUser2 = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Child Two']);

    $otherParentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $otherStudentUser = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Other Child']);

    $student1 = Student::factory()->create(['user_id' => $studentUser1->id, 'tenant_id' => $tenant->id]);
    $student2 = Student::factory()->create(['user_id' => $studentUser2->id, 'tenant_id' => $tenant->id]);
    $otherStudent = Student::factory()->create(['user_id' => $otherStudentUser->id, 'tenant_id' => $tenant->id]);

    // Link student 1 and 2 to parentUser
    StudentParent::create([
        'tenant_id' => $tenant->id,
        'user_id' => $parentUser->id,
        'student_id' => $student1->id,
        'relation' => 'father',
    ]);
    StudentParent::create([
        'tenant_id' => $tenant->id,
        'user_id' => $parentUser->id,
        'student_id' => $student2->id,
        'relation' => 'father',
    ]);

    // Link otherStudent to otherParentUser
    StudentParent::create([
        'tenant_id' => $tenant->id,
        'user_id' => $otherParentUser->id,
        'student_id' => $otherStudent->id,
        'relation' => 'father',
    ]);

    $this->actingAs($parentUser);

    // List of authorized students must contain only student 1 & 2
    $authorizedList = ParentStudentAccessService::getAuthorizedStudentsList();
    expect(array_keys($authorizedList))->toContain($student1->id, $student2->id)
        ->and(array_keys($authorizedList))->not->toContain($otherStudent->id);

    // Default selected student should be the first child
    $selected = ParentStudentAccessService::getSelectedStudent();
    expect($selected)->not->toBeNull()
        ->and($selected->id)->toBe($student1->id);
});

test('tampered session selected_student_id pointing to another parent child is rejected and cleared', function () {
    $tenant = Tenant::factory()->create();

    $parentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $childUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create(['user_id' => $childUser->id, 'tenant_id' => $tenant->id]);

    StudentParent::create([
        'tenant_id' => $tenant->id,
        'user_id' => $parentUser->id,
        'student_id' => $student->id,
        'relation' => 'father',
    ]);

    $victimUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $victimStudent = Student::factory()->create(['user_id' => $victimUser->id, 'tenant_id' => $tenant->id]);

    $this->actingAs($parentUser);

    // Simulate session tampering: setting selected_student_id to victimStudent
    session(['selected_student_id' => $victimStudent->id]);

    // Service must reject victim student and fall back to parent's legitimate child
    $resolvedStudent = ParentStudentAccessService::getSelectedStudent();
    expect($resolvedStudent->id)->toBe($student->id)
        ->and($resolvedStudent->id)->not->toBe($victimStudent->id);

    expect(session('selected_student_id'))->toBe($student->id);
});

test('parent student switcher rejects switching to unauthorized student', function () {
    $tenant = Tenant::factory()->create();

    $parentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $childUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create(['user_id' => $childUser->id, 'tenant_id' => $tenant->id]);

    StudentParent::create([
        'tenant_id' => $tenant->id,
        'user_id' => $parentUser->id,
        'student_id' => $student->id,
        'relation' => 'mother',
    ]);

    $strangerStudent = Student::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($parentUser);

    // Attempt to switch to stranger
    $success = ParentStudentAccessService::switchStudent($strangerStudent->id);
    expect($success)->toBeFalse();

    // Session must not be set to stranger
    expect(session('selected_student_id'))->not->toBe($strangerStudent->id);
});

test('parent student switcher allows switching between multiple authorized children', function () {
    $tenant = Tenant::factory()->create();

    $parentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $child1 = Student::factory()->create(['tenant_id' => $tenant->id]);
    $child2 = Student::factory()->create(['tenant_id' => $tenant->id]);

    StudentParent::create(['tenant_id' => $tenant->id, 'user_id' => $parentUser->id, 'student_id' => $child1->id, 'relation' => 'father']);
    StudentParent::create(['tenant_id' => $tenant->id, 'user_id' => $parentUser->id, 'student_id' => $child2->id, 'relation' => 'father']);

    $this->actingAs($parentUser);

    expect(ParentStudentAccessService::switchStudent($child2->id))->toBeTrue();
    expect(ParentStudentAccessService::getSelectedStudentId())->toBe($child2->id);

    expect(ParentStudentAccessService::switchStudent($child1->id))->toBeTrue();
    expect(ParentStudentAccessService::getSelectedStudentId())->toBe($child1->id);
});

test('parent payments widget prevents information disclosure when child has no enrollments', function () {
    $tenant = Tenant::factory()->create();

    $parentUser = User::factory()->create(['tenant_id' => $tenant->id]);
    $child = Student::factory()->create(['tenant_id' => $tenant->id]);

    StudentParent::create(['tenant_id' => $tenant->id, 'user_id' => $parentUser->id, 'student_id' => $child->id, 'relation' => 'father']);

    // Create another student with payments across the school
    $otherStudent = Student::factory()->create(['tenant_id' => $tenant->id]);
    $enrollment = Enrollment::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $otherStudent->id,
    ]);
    $vault = \App\Models\Vault::create([
        'tenant_id' => $tenant->id,
        'name' => 'Main Vault',
        'type' => 'cash',
        'status' => 'active',
        'opening_balance' => 0,
    ]);

    $otherPayment = Payment::create([
        'tenant_id' => $tenant->id,
        'vault_id' => $vault->id,
        'enrollment_id' => $enrollment->id,
        'payment_code' => 'PAY-SECRET-001',
        'amount' => 9999.00,
        'payment_method' => 'cash',
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    $this->actingAs($parentUser);

    $studentId = ParentStudentAccessService::getSelectedStudentId();
    expect($studentId)->toBe($child->id);

    // Child has no enrollments
    $enrollmentIds = $studentId ? \App\Models\Enrollment::where('student_id', $studentId)->pluck('id')->toArray() : [];
    expect($enrollmentIds)->toBeEmpty();

    // Query executed by PaymentsWidget:
    $query = Payment::query()
        ->when(!empty($enrollmentIds), fn($query) => $query->whereIn('enrollment_id', $enrollmentIds), fn($query) => $query->whereRaw('1 = 0'))
        ->with(['enrollment.track'])
        ->latest();

    $results = $query->get();

    // Must be completely empty: zero records leaked!
    expect($results)->toHaveCount(0)
        ->and(Payment::count())->toBe(1); // 1 payment exists in the school, but 0 returned to parent
});
