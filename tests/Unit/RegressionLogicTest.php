<?php

use App\Filament\Student\Pages\MyCourses;
use App\Models\InstructorPayment;

test('instructor payment should reopen when paid record receives new sessions', function () {
    $existing = new InstructorPayment([
        'status' => 'paid',
        'completed_sessions' => 1,
    ]);

    $shouldReopen = InstructorPayment::shouldReopenAfterSessionChange($existing, 2);

    expect($shouldReopen)->toBeTrue();
});

test('instructor payment should not reopen when status is pending', function () {
    $existing = new InstructorPayment([
        'status' => 'pending',
        'completed_sessions' => 1,
    ]);

    $shouldReopen = InstructorPayment::shouldReopenAfterSessionChange($existing, 2);

    expect($shouldReopen)->toBeFalse();
});

test('my courses status resolution reads group status not enrollment status', function () {
    $record = (object) [
        'status' => 'active',
        'course_id' => 77,
        'group' => (object) [
            'status' => 'completed',
        ],
        'enrollment' => null,
    ];

    $page = new class extends MyCourses {
        public function exposedResolveGroupStatus($record): string
        {
            return $this->resolveGroupStatus($record);
        }
    };

    $resolvedStatus = $page->exposedResolveGroupStatus($record);

    expect($resolvedStatus)->toBe('completed');
    expect($record->status)->toBe('active');
});
