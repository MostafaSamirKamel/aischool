<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use App\Listeners\CreateTenantForNewUser;
use App\Listeners\AssignAdminRoleToNewUser;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

// Models
use App\Models\Attendance;
use App\Models\ExamAttempt;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\StudentPayment;

// Observers
use App\Observers\AttendanceObserver;
use App\Observers\ExamAttemptObserver;
use App\Observers\AssignmentObserver;
use App\Observers\ExamObserver;
use App\Observers\StudentPaymentObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            CreateTenantForNewUser::class,
            AssignAdminRoleToNewUser::class,
        ],
    ];

    /**
     * The model observers for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $observers = [
        Attendance::class => [AttendanceObserver::class],
        ExamAttempt::class => [ExamAttemptObserver::class],
        Assignment::class => [AssignmentObserver::class],
        Exam::class => [ExamObserver::class],
        StudentPayment::class => [StudentPaymentObserver::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
